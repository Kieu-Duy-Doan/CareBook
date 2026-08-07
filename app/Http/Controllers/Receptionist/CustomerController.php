<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PatientProfile;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Receptionist\StoreCustomerRequest;
use App\Http\Requests\Receptionist\UpdateCustomerRequest;
use App\Services\CustomerProfileService;
use App\Exports\CustomersExport;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    protected $customerProfileService;

    public function __construct(CustomerProfileService $customerProfileService)
    {
        $this->customerProfileService = $customerProfileService;
    }
    public function export(Request $request)
    {
        return Excel::download(new CustomersExport($request), 'customers_' . date('Ymd_His') . '.xlsx');
    }

    public function index(Request $request)
    {
        $stats = [
            'total'    => User::where('role', 'patient')->count(),
            'active'   => User::where('role', 'patient')->where('is_active', true)->count(),
            'locked'   => User::where('role', 'patient')->where('is_active', false)->count(),
            'profiles' => PatientProfile::count(),
        ];

        $query = User::with(['patientProfiles'])
            ->withCount('patientProfiles')
            ->where('role', 'patient')
            ->latest('created_at');

        // Search theo tên, SĐT, email
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%')
                  ->orWhere('id_card', 'like', '%'.$request->search.'%')
                  ->orWhereHas('patientProfiles', fn($pq) =>
                      $pq->where('insurance_code', 'like', '%'.$request->search.'%')
                  );
            });
        }

        // Filter trạng thái
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        // Filter có BHYT hay không
        if ($request->filled('has_insurance')) {
            if ($request->has_insurance == '1') {
                $query->whereHas('patientProfiles', fn($pq) =>
                    $pq->whereNotNull('insurance_code')
                );
            } else {
                $query->whereDoesntHave('patientProfiles', fn($pq) =>
                    $pq->whereNotNull('insurance_code')
                );
            }
        }

        $customers = $query->paginate(15)->withQueryString();

        return view('receptionist.customers.index', compact('customers', 'stats'));
    }

    public function create()
    {
        return view('receptionist.customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $validated = $request->validated();

        $medicalHistoryPaths = [];
        if ($request->hasFile('medical_history')) {
            foreach ($request->file('medical_history') as $file) {
                $path = $file->store('medical_histories', 'public');
                $medicalHistoryPaths[] = $path;
            }
        }

        $this->customerProfileService->createCustomer($validated, $medicalHistoryPaths);

        return redirect()->route('receptionist.customers.index')
            ->with('success', 'Thêm khách hàng thành công.');
    }

    public function show($id)
    {
        $customer = User::with(['patientProfiles' => function($query) {
            $query->withCount('appointments');
        }, 'patientProfiles.appointments'])->findOrFail($id);

        $logs = SystemLog::where('user_id', $customer->id)
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('receptionist.customers.show', compact('customer', 'logs'));
    }

    public function edit($id)
    {
        $customer = User::with(['patientProfiles' => function($q) {
            $q->where('is_self', 1);
        }])->findOrFail($id);

        return view('receptionist.customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, $id)
    {
        $customer = User::with(['patientProfiles' => function($q) {
            $q->where('is_self', 1);
        }])->findOrFail($id);

        $selfProfile = $customer->patientProfiles->first();
        $validated = $request->validated();

        $medicalHistoryPaths = [];
        if ($request->hasFile('medical_history')) {
            foreach ($request->file('medical_history') as $file) {
                $path = $file->store('medical_histories', 'public');
                $medicalHistoryPaths[] = $path;
            }
        }

        $this->customerProfileService->updateCustomer($customer, $validated, $selfProfile, $medicalHistoryPaths);

        return redirect()->route('receptionist.customers.index')
            ->with('success', 'Cập nhật khách hàng thành công.');
    }

    public function destroy($id)
    {
        $customer = User::findOrFail($id);

        // Delete associated logs
        SystemLog::where('ref_type', 'users')->where('ref_id', $customer->id)->delete();
        
        // Delete profiles and user
        $customer->patientProfiles()->delete();
        $customer->delete();

        SystemLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'CUSTOMER_DELETED',
            'module'      => 'customers',
            'ref_type'    => 'users',
            'ref_id'      => $id,
            'description' => 'Xoá khách hàng: ' . $customer->full_name,
            'ip_address'  => request()->ip(),
        ]);

        return redirect()->route('receptionist.customers.index')->with('success', 'Đã xoá khách hàng thành công.');
    }

    public function toggleActive($id)
    {
        $customer = User::findOrFail($id);
        $customer->update([
            'is_active' => !$customer->is_active,
            'locked_reason' => $customer->is_active ? null : null
        ]);

        SystemLog::create([
            'user_id'     => auth()->id(),
            'action'      => $customer->is_active ? 'CUSTOMER_UNLOCKED' : 'CUSTOMER_LOCKED',
            'module'      => 'customers',
            'ref_type'    => 'users',
            'ref_id'      => $customer->id,
            'description' => ($customer->is_active ? 'Mở khoá' : 'Khoá') . ' tài khoản khách hàng: ' . $customer->full_name,
            'ip_address'  => request()->ip(),
        ]);

        return redirect()->back()->with('success',
            $customer->is_active ? 'Đã mở khoá tài khoản khách hàng.' : 'Đã khoá tài khoản khách hàng.'
        );
    }
}
