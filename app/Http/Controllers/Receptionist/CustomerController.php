<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PatientProfile;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;
use App\Exports\CustomersExport;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Tài khoản
            'full_name'    => 'required|string|max:100',
            'phone'        => ['required', 'string', 'max:15', 'regex:/^(0[35789])[0-9]{8}$/', 'unique:users,phone'],
            'password'     => 'required|string|min:8|confirmed',
            'username'     => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_.]*$/', 'unique:users,username'],
            'id_card'      => ['required', 'string', 'regex:/^([0-9]{9}|[0-9]{12})$/', 'unique:users,id_card'],
            'email'        => 'nullable|email|max:150|unique:users,email',
            // Hồ sơ
            'profile_full_name'      => 'nullable|string|max:100',
            'date_of_birth'          => 'required|date|before:today',
            'gender'                 => 'required|in:male,female,other',
            'profile_phone'          => 'nullable|string|max:15',
            'address'                => 'nullable|string',
            'occupation'             => 'nullable|string|max:100',
            'ethnicity'              => 'nullable|string|max:50',
            'insurance_code'         => 'nullable|string|max:20',
            'insurance_place'        => 'nullable|string|max:255',
            'insurance_expiry'       => 'nullable|date',
            'symptom_notes'          => 'nullable|string',
            'medical_history.*'      => 'nullable|file|mimes:pdf|max:10240',
        ], [
            'full_name.required'  => 'Vui lòng nhập họ tên.',
            'phone.required'      => 'Vui lòng nhập số điện thoại.',
            'phone.unique'        => 'Số điện thoại đã được sử dụng.',
            'phone.regex'         => 'Số điện thoại không đúng định dạng Việt Nam.',
            'password.required'   => 'Vui lòng nhập mật khẩu.',
            'password.min'        => 'Mật khẩu tối thiểu 8 ký tự.',
            'password.confirmed'  => 'Xác nhận mật khẩu không khớp.',
            'username.unique'     => 'Tên đăng nhập đã tồn tại.',
            'username.regex'      => 'Tên đăng nhập không được chứa ký tự đặc biệt.',
            'id_card.required'    => 'Vui lòng nhập số CCCD/CMND.',
            'id_card.regex'       => 'Số CCCD/CMND không đúng định dạng.',
            'id_card.unique'      => 'Số CCCD/CMND đã được sử dụng.',
            'email.unique'        => 'Email đã được sử dụng.',
            'date_of_birth.required' => 'Vui lòng nhập ngày sinh.',
            'date_of_birth.before'=> 'Ngày sinh không hợp lệ.',
            'gender.required'     => 'Vui lòng chọn giới tính.',
            'medical_history.*.mimes'=> 'File tiền sử bệnh lý phải là định dạng PDF.',
            'medical_history.*.max'  => 'Kích thước file không được vượt quá 10MB.',
        ]);

        $medicalHistoryPaths = [];
        if ($request->hasFile('medical_history')) {
            foreach ($request->file('medical_history') as $file) {
                $path = $file->store('medical_histories', 'public');
                $medicalHistoryPaths[] = $path;
            }
        }

        \DB::transaction(function() use ($validated, $medicalHistoryPaths) {
            $user = User::create([
                'full_name' => $validated['full_name'],
                'phone'     => $validated['phone'],
                'username'  => $validated['username'] ?? $validated['phone'],
                'id_card'   => $validated['id_card'],
                'email'     => $validated['email'] ?? null,
                'password'  => bcrypt($validated['password']),
                'role'      => 'patient',
                'is_active' => true,
            ]);

            PatientProfile::create([
                'patient_code'    => 'BN' . $validated['id_card'],
                'owner_id'        => $user->id,
                'full_name'       => $validated['profile_full_name'] ?? $validated['full_name'],
                'date_of_birth'   => $validated['date_of_birth'],
                'gender'          => $validated['gender'],
                'id_card'         => $validated['id_card'],
                'phone'           => $validated['profile_phone'] ?? $validated['phone'],
                'address'         => $validated['address'] ?? null,
                'occupation'      => $validated['occupation'] ?? null,
                'ethnicity'       => $validated['ethnicity'] ?? null,
                'insurance_code'  => $validated['insurance_code'] ?? null,
                'insurance_place' => $validated['insurance_place'] ?? null,
                'insurance_expiry'=> $validated['insurance_expiry'] ?? null,
                'symptom_notes'   => $validated['symptom_notes'] ?? null,
                'medical_history' => !empty($medicalHistoryPaths) ? $medicalHistoryPaths : null,
                'is_self'         => 1,
            ]);

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'CUSTOMER_CREATED',
                'module'      => 'customers',
                'ref_type'    => 'users',
                'ref_id'      => $user->id,
                'description' => 'Thêm khách hàng mới: ' . $validated['full_name'],
                'ip_address'  => request()->ip(),
            ]);
        });

        return redirect()->route('receptionist.customers.index')
            ->with('success', 'Thêm khách hàng thành công.');
    }

    public function show($id)
    {
        $customer = User::with(['patientProfiles', 'patientProfiles.appointments'])->findOrFail($id);

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

    public function update(Request $request, $id)
    {
        $customer = User::with(['patientProfiles' => function($q) {
            $q->where('is_self', 1);
        }])->findOrFail($id);

        $selfProfile = $customer->patientProfiles->first();

        $rules = [
            'full_name'    => 'required|string|max:100',
            'phone'        => ['required', 'string', 'max:15', 'regex:/^(0[35789])[0-9]{8}$/', 'unique:users,phone,' . $customer->id],
            'username'     => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_.]*$/', 'unique:users,username,' . $customer->id],
            'email'        => 'nullable|email|max:150|unique:users,email,' . $customer->id,
            // Hồ sơ
            'profile_full_name'      => 'nullable|string|max:100',
            'date_of_birth'          => 'required|date|before:today',
            'gender'                 => 'required|in:male,female,other',
            'profile_phone'          => 'nullable|string|max:15',
            'address'                => 'nullable|string',
            'occupation'             => 'nullable|string|max:100',
            'ethnicity'              => 'nullable|string|max:50',
            'insurance_code'         => 'nullable|string|max:20',
            'insurance_place'        => 'nullable|string|max:255',
            'insurance_expiry'       => 'nullable|date',
            'symptom_notes'          => 'nullable|string',
        ];

        if ($selfProfile && $selfProfile->card_id_change_count >= 1) {
            $rules['id_card'] = ['nullable', 'string', 'regex:/^([0-9]{9}|[0-9]{12})$/', 'unique:users,id_card,' . $customer->id];
        } else {
            $rules['id_card'] = ['required', 'string', 'regex:/^([0-9]{9}|[0-9]{12})$/', 'unique:users,id_card,' . $customer->id];
        }

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($rules, [
            'full_name.required'  => 'Vui lòng nhập họ tên.',
            'phone.required'      => 'Vui lòng nhập số điện thoại.',
            'phone.unique'        => 'Số điện thoại đã được sử dụng.',
            'phone.regex'         => 'Số điện thoại không đúng định dạng Việt Nam.',
            'username.unique'     => 'Tên đăng nhập đã tồn tại.',
            'username.regex'      => 'Tên đăng nhập không được chứa ký tự đặc biệt.',
            'id_card.required'    => 'Vui lòng nhập số CCCD/CMND.',
            'id_card.regex'       => 'Số CCCD/CMND không đúng định dạng.',
            'id_card.unique'      => 'Số CCCD/CMND đã được sử dụng.',
            'email.unique'        => 'Email đã được sử dụng.',
            'date_of_birth.required'=> 'Vui lòng nhập ngày sinh.',
            'date_of_birth.before'=> 'Ngày sinh không hợp lệ.',
            'gender.required'     => 'Vui lòng chọn giới tính.',
            'password.min'        => 'Mật khẩu tối thiểu 8 ký tự.',
            'password.confirmed'  => 'Xác nhận mật khẩu không khớp.',
        ]);

        \DB::transaction(function() use ($validated, $customer, $selfProfile, $request) {
            $userData = [
                'full_name' => $validated['full_name'],
                'phone'     => $validated['phone'],
                'username'  => $validated['username'] ?? $validated['phone'],
                'email'     => $validated['email'] ?? null,
            ];

            if (!$selfProfile || ($selfProfile->card_id_change_count < 1 && isset($validated['id_card']) && $validated['id_card'] !== $customer->id_card)) {
                $userData['id_card'] = $validated['id_card'] ?? $customer->id_card;
            }

            if ($request->filled('password')) {
                $userData['password'] = bcrypt($validated['password']);
            }

            $customer->update($userData);

            if ($selfProfile) {
                $selfProfileUpdateData = [
                    'full_name'       => $validated['profile_full_name'] ?? $validated['full_name'],
                    'date_of_birth'   => $validated['date_of_birth'],
                    'gender'          => $validated['gender'],
                    'phone'           => $validated['profile_phone'] ?? $validated['phone'],
                    'address'         => $validated['address'] ?? null,
                    'occupation'      => $validated['occupation'] ?? null,
                    'ethnicity'       => $validated['ethnicity'] ?? null,
                    'insurance_code'  => $validated['insurance_code'] ?? null,
                    'insurance_place' => $validated['insurance_place'] ?? null,
                    'insurance_expiry'=> $validated['insurance_expiry'] ?? null,
                    'symptom_notes'   => $validated['symptom_notes'] ?? null,
                ];

                if ($selfProfile->card_id_change_count < 1 && isset($validated['id_card']) && $validated['id_card'] !== $selfProfile->id_card) {
                    $selfProfileUpdateData['id_card'] = $validated['id_card'];
                    $selfProfileUpdateData['patient_code'] = 'BN' . $validated['id_card'];
                    $selfProfileUpdateData['card_id_change_count'] = $selfProfile->card_id_change_count + 1;
                }

                $selfProfile->update($selfProfileUpdateData);
            }

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'CUSTOMER_UPDATED',
                'module'      => 'customers',
                'ref_type'    => 'users',
                'ref_id'      => $customer->id,
                'description' => 'Cập nhật thông tin khách hàng: ' . $validated['full_name'],
                'ip_address'  => request()->ip(),
            ]);
        });

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
