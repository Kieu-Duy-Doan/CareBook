<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        // Tự động sinh mã nhân viên kế tiếp (LT001, LT002, ...)
        $latestStaff = StaffProfile::where('employee_code', 'regexp', '^LT[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING(employee_code, 3) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($latestStaff) {
            $numberStr = substr($latestStaff->employee_code, 2);
            if (is_numeric($numberStr)) {
                $nextNumber = (int)$numberStr + 1;
            }
        }
        $nextEmployeeCode = 'LT' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('admin.receptionists.create', compact('nextEmployeeCode'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'      => 'required|string|max:100',
            'phone'          => ['required', 'string', 'max:15', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/', 'unique:users,phone'],
            'username'       => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_\.]+$/', 'unique:users,username'],
            'id_card'        => 'nullable|string|max:20|unique:users,id_card',
            'email'          => 'nullable|email|max:150|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'employee_code'  => ['required', 'string', 'max:20', 'regex:/^LT\d{3,}$/', 'unique:staff_profiles,employee_code'],
            'department'     => ['required', 'string', 'in:Tiếp nhận bệnh nhân,Chăm sóc khách hàng'],
            'internal_phone' => 'nullable|string|max:15',
            'start_date'     => 'nullable|date|before_or_equal:today',
        ], [
            'full_name.required'     => 'Vui lòng nhập họ tên.',
            'phone.required'         => 'Vui lòng nhập số điện thoại.',
            'phone.regex'            => 'Số điện thoại không đúng định dạng (VD: 0901234567 hoặc +84901234567).',
            'phone.unique'           => 'Số điện thoại đã được sử dụng.',
            'username.required'      => 'Vui lòng nhập tên đăng nhập.',
            'username.regex'         => 'Tên đăng nhập chỉ được chứa chữ cái, số, dấu gạch dưới và dấu chấm.',
            'username.unique'        => 'Tên đăng nhập đã tồn tại.',
            'id_card.unique'         => 'Số CCCD đã được sử dụng.',
            'email.unique'           => 'Email đã được sử dụng.',
            'password.required'      => 'Vui lòng nhập mật khẩu.',
            'password.min'           => 'Mật khẩu tối thiểu 8 ký tự.',
            'password.confirmed'     => 'Xác nhận mật khẩu không khớp.',
            'employee_code.required' => 'Vui lòng nhập mã nhân viên.',
            'employee_code.regex'    => 'Mã nhân viên phải bắt đầu bằng LT và theo sau bởi ít nhất 3 chữ số (VD: LT001).',
            'employee_code.unique'   => 'Mã nhân viên đã tồn tại.',
            'department.required'    => 'Vui lòng chọn phòng ban.',
            'department.in'          => 'Phòng ban không hợp lệ.',
            'start_date.before_or_equal' => 'Ngày vào làm không được là ngày trong tương lai.',
        ]);

        DB::transaction(function() use ($validated) {
            $user = User::create([
                'full_name'  => $validated['full_name'],
                'phone'      => $validated['phone'],
                'username'   => $validated['username'],
                'id_card'    => $validated['id_card'] ?? null,
                'email'      => $validated['email'] ?? null,
                'password'   => bcrypt($validated['password']),
                'role'       => 'receptionist',
                'is_active'  => true,
            ]);

            StaffProfile::create([
                'user_id'        => $user->id,
                'employee_code'  => $validated['employee_code'],
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

    public function update(Request $request, $id)
    {
        $receptionist = User::with('staffProfile')
            ->where('role', 'receptionist')
            ->findOrFail($id);

        $staffProfileId = $receptionist->staffProfile?->id;

        $validated = $request->validate([
            'full_name'      => 'required|string|max:100',
            'phone'          => ["required", "string", "max:15", "regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/", "unique:users,phone,$id"],
            'username'       => ["required", "string", "max:50", "regex:/^[a-zA-Z0-9_\.]+$/", "unique:users,username,$id"],
            'id_card'        => "nullable|string|max:20|unique:users,id_card,$id",
            'email'          => "nullable|email|max:150|unique:users,email,$id",
            'employee_code'  => ["required", "string", "max:20", "regex:/^LT\d{3,}$/", "unique:staff_profiles,employee_code,$staffProfileId"],
            'department'     => ['required', 'string', 'in:Tiếp nhận bệnh nhân,Chăm sóc khách hàng'],
            'internal_phone' => 'nullable|string|max:15',
            'start_date'     => 'nullable|date|before_or_equal:today',
        ], [
            'full_name.required'     => 'Vui lòng nhập họ tên.',
            'phone.required'         => 'Vui lòng nhập số điện thoại.',
            'phone.regex'            => 'Số điện thoại không đúng định dạng (VD: 0901234567 hoặc +84901234567).',
            'phone.unique'           => 'Số điện thoại đã được sử dụng.',
            'username.required'      => 'Vui lòng nhập tên đăng nhập.',
            'username.regex'         => 'Tên đăng nhập chỉ được chứa chữ cái, số, dấu gạch dưới và dấu chấm.',
            'username.unique'        => 'Tên đăng nhập đã tồn tại.',
            'id_card.unique'         => 'Số CCCD đã được sử dụng.',
            'email.unique'           => 'Email đã được sử dụng.',
            'employee_code.required' => 'Vui lòng nhập mã nhân viên.',
            'employee_code.regex'    => 'Mã nhân viên phải bắt đầu bằng LT và theo sau bởi ít nhất 3 chữ số (VD: LT001).',
            'employee_code.unique'   => 'Mã nhân viên đã tồn tại.',
            'department.required'    => 'Vui lòng chọn phòng ban.',
            'department.in'          => 'Phòng ban không hợp lệ.',
            'start_date.before_or_equal' => 'Ngày vào làm không được là ngày trong tương lai.',
        ]);

        DB::transaction(function() use ($receptionist, $validated) {
            $userData = [
                'full_name' => $validated['full_name'],
                'phone'     => $validated['phone'],
                'username'  => $validated['username'],
                'id_card'   => $validated['id_card'] ?? null,
                'email'     => $validated['email'] ?? null,
            ];
            $receptionist->update($userData);

            $receptionist->staffProfile()->updateOrCreate(
                ['user_id' => $receptionist->id],
                [
                    'employee_code'  => $validated['employee_code'],
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
}
