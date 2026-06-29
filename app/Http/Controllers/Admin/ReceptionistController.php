<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\Validate\StoreReceptionistRequest;
use App\Http\Requests\Admin\Validate\UpdateReceptionistRequest;
use App\Models\User;
use App\Models\StaffProfile;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;

class ReceptionistController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total'  => User::where('role', 'receptionist')->count(),
            'active' => User::where('role', 'receptionist')->where('is_active', true)->count(),
            'locked' => User::where('role', 'receptionist')->where('is_active', false)->count(),
        ];

        $query = User::with('staffProfile')
            ->where('role', 'receptionist')
            ->latest('created_at');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
                  ->orWhereHas('staffProfile', fn($sq) =>
                      $sq->where('employee_code', 'like', '%'.$request->search.'%')
                         ->orWhere('position', 'like', '%'.$request->search.'%')
                  );
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->filled('department')) {
            $query->whereHas('staffProfile', fn($sq) =>
                $sq->where('department', 'like', '%'.$request->department.'%')
            );
        }

        $receptionists = $query->paginate(15)->withQueryString();

        // Lấy danh sách phòng ban distinct để filter
        $departments = \App\Models\StaffProfile::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        return view('admin.receptionists.index', compact('receptionists', 'stats', 'departments'));
    }
    public function create()
    {
        return view('admin.receptionists.create');
    }
   

            StaffProfile::create([
                'user_id'        => $user->id,
                'employee_code'  => $employeeCode,
                'position'       => 'Lễ tân',
                'department'     => $validated['department'] ?? null,
                'internal_phone' => $validated['internal_phone'] ?? null,
                'start_date'     => $validated['start_date'] ?? null,
                'is_active'      => true,
            ]);

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'RECEPTIONIST_CREATED',
                'module'      => 'receptionists',
                'ref_type'    => 'users',
                'ref_id'      => $user->id,
                'description' => 'Thêm lễ tân mới: ' . $validated['full_name'],
                'ip_address'  => request()->ip(),
            ]);
        });

        return redirect()->route('admin.receptionists.index')
            ->with('success', 'Thêm lễ tân thành công.');
    }
    public function show($id)
    {
        $receptionist = User::with('staffProfile')
            ->where('role', 'receptionist')
            ->findOrFail($id);

        // Thống kê check-in hôm nay và tháng này
        $checkInStats = [
            'today' => \App\Models\Appointment::where('measured_by', $id)
                        ->whereDate('checked_in_at', today())->count(),
            'month' => \App\Models\Appointment::where('measured_by', $id)
                        ->whereMonth('checked_in_at', now()->month)
                        ->whereYear('checked_in_at', now()->year)->count(),
            'total' => \App\Models\Appointment::where('measured_by', $id)->count(),
        ];

        $logs = SystemLog::where('user_id', $id)
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.receptionists.show', compact('receptionist', 'checkInStats', 'logs'));
    }

    public function edit($id)
    {
        $receptionist = User::with('staffProfile')
            ->where('role', 'receptionist')
            ->findOrFail($id);
        return view('admin.receptionists.edit', compact('receptionist'));
    }

    public function update(UpdateReceptionistRequest $request, $id)
    {
        $receptionist = User::with('staffProfile')
            ->where('role', 'receptionist')
            ->findOrFail($id);

        $validated = $request->validated();

        DB::transaction(function() use ($receptionist, $validated) {
            $userData = [
                'full_name' => $validated['full_name'],
                'phone'     => $validated['phone'],
                'username'  => $validated['username'],
                'email'     => $validated['email'] ?? null,
            ];

            if (!$receptionist->is_id_card_updated) {
                $newIdCard = $validated['id_card'] ?? null;
                if (!empty($newIdCard) && $newIdCard !== $receptionist->id_card) {
                    $userData['id_card'] = $newIdCard;
                    $userData['is_id_card_updated'] = true;
                }
            }

            $receptionist->update($userData);

            $receptionist->staffProfile()->updateOrCreate(
                ['user_id' => $receptionist->id],
                [
                    'position'       => 'Lễ tân',
                    'department'     => $validated['department'] ?? null,
                    'internal_phone' => $validated['internal_phone'] ?? null,
                    'start_date'     => $validated['start_date'] ?? null,
                ]
            );

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'RECEPTIONIST_UPDATED',
                'module'      => 'receptionists',
                'ref_type'    => 'users',
                'ref_id'      => $receptionist->id,
                'description' => 'Cập nhật thông tin lễ tân: ' . $validated['full_name'],
                'ip_address'  => request()->ip(),
            ]);
        });

        return redirect()->route('admin.receptionists.edit', $id)
            ->with('success', 'Cập nhật thông tin lễ tân thành công.');
    }

    public function toggleActive($id)
    {
        $receptionist = User::with('staffProfile')->where('role', 'receptionist')->findOrFail($id);

        DB::transaction(function() use ($receptionist) {
            $newActiveStatus = !$receptionist->is_active;
            $receptionist->update(['is_active' => $newActiveStatus]);

            if ($receptionist->staffProfile) {
                $receptionist->staffProfile->update(['is_active' => $newActiveStatus]);
            }

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => $newActiveStatus ? 'RECEPTIONIST_UNLOCKED' : 'RECEPTIONIST_LOCKED',
                'module'      => 'receptionists',
                'ref_type'    => 'users',
                'ref_id'      => $receptionist->id,
                'description' => ($newActiveStatus ? 'Mở khoá' : 'Khoá') . ' lễ tân: ' . $receptionist->full_name,
                'ip_address'  => request()->ip(),
            ]);
        });

        return redirect()->back()->with('success',
            $receptionist->refresh()->is_active ? 'Đã mở khoá tài khoản lễ tân.' : 'Đã khoá tài khoản lễ tân.'
        );
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ReceptionistsTemplateExport, 'receptionist_import_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ], [
            'file.required' => 'Vui lòng chọn file để import.',
            'file.file'     => 'File không hợp lệ.',
            'file.max'      => 'Dung lượng file tối đa là 10MB.',
        ]);

        $extension = strtolower($request->file('file')->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            return redirect()->back()->with('error', 'Chỉ chấp nhận định dạng file: .xlsx, .xls, .csv.');
        }

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\ReceptionistsImport, $request->file('file'));

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'RECEPTIONIST_IMPORTED',
                'module'      => 'receptionists',
                'ref_type'    => null,
                'ref_id'      => null,
                'description' => 'Import danh sách lễ tân từ file Excel',
                'ip_address'  => request()->ip(),
            ]);

            return redirect()->route('admin.receptionists.index')->with('success', 'Import danh sách lễ tân thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi import: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        SystemLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'RECEPTIONIST_EXPORTED',
            'module'      => 'receptionists',
            'ref_type'    => null,
            'ref_id'      => null,
            'description' => 'Export danh sách lễ tân ra file Excel',
            'ip_address'  => request()->ip(),
        ]);

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ReceptionistsExport($request), 'danh_sach_le_tan.xlsx');
    }

    private function generateEmployeeCode($fullName)
    {
        $slug = \Illuminate\Support\Str::slug($fullName); // "tran-thi-le-tan"
        $parts = array_filter(explode('-', $slug));
        
        if (count($parts) == 1) {
            $prefix = reset($parts);
        } else {
            $firstName = array_pop($parts); // tan
            $initials = '';
            foreach ($parts as $part) {
                if (!empty($part)) {
                    $initials .= substr($part, 0, 1); // t, t, l
                }
            }
            $prefix = $firstName . $initials; // tanttl
        }

        $latestStaff = StaffProfile::where('employee_code', 'regexp', '^' . $prefix . '[0-9]{2,}$')
            ->orderByRaw('CAST(SUBSTRING(employee_code, '.(strlen($prefix)+1).') AS UNSIGNED) DESC')
            ->first();
            
        $nextNumber = 1;
        if ($latestStaff) {
            $numberStr = substr($latestStaff->employee_code, strlen($prefix));
            if (is_numeric($numberStr)) {
                $nextNumber = (int)$numberStr + 1;
            }
        }
        
        return $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }
}
