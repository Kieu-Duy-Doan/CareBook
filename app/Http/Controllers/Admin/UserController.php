<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SystemLog;
use App\Models\Appointment;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Hàm cung cấp API tìm kiếm người dùng (để các ô chọn người chạy nhanh hơn, không bị đơ)
    public function ajaxSearch(Request $request)
    {
        // Nhận chữ người dùng gõ vào ô tìm kiếm
        $search = $request->query('q');
        
        $query = User::select('id', 'full_name', 'email', 'role')->where('is_active', true);
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->limit(50)->get();
        
        return response()->json([
            'items' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'text' => $user->full_name . ' (' . ($user->email ?? 'Không có email') . ') - ' . ucfirst($user->role)
                ];
            })
        ]);
    }

    public function index(Request $request)
    {
        // Stats
        $stats = [
            'total'        => User::count(),
            'patient'      => User::where('role', 'patient')->count(),
            'doctor'       => User::where('role', 'doctor')->count(),
            'receptionist' => User::where('role', 'receptionist')->count(),
            'admin'        => User::where('role', 'admin')->count(),
            'locked'       => User::where('is_active', false)->count(),
        ];

        // Query với filter
        $query = User::with(['doctorProfile', 'staffProfile'])
            ->latest('created_at');

        // Filter role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter trạng thái
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        // Search theo full_name hoặc phone
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%')
                  ->orWhere('username', 'like', '%'.$request->search.'%');
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users', 'stats'));
    }
    public function show($id)
    {
        $user = User::findOrFail($id);
        // Redirect đúng module nếu không phải admin
        if ($user->role !== 'admin') {
            return match($user->role) {
                'doctor'       => \Illuminate\Support\Facades\Route::has('admin.doctors.show')
                                    ? redirect()->route('admin.doctors.show', $user->doctorProfile->id ?? 0)
                                    : back()->with('error', 'Module bác sĩ chưa có trang chi tiết.'),
                'receptionist' => \Illuminate\Support\Facades\Route::has('admin.receptionists.show')
                                    ? redirect()->route('admin.receptionists.show', $user->id)
                                    : back()->with('error', 'Module lễ tân chưa có trang chi tiết.'),
                'patient'      => \Illuminate\Support\Facades\Route::has('admin.patients.show') 
                                    ? redirect()->route('admin.patients.show', $user->id) 
                                    : back()->with('error', 'Module bệnh nhân chưa được cấu hình.'),
            };
        }
        $logs = SystemLog::where('user_id', $id)->latest()->limit(10)->get();
        return view('admin.users.show', compact('user', 'logs'));
    }
    public function edit($id)
    {
        $user = User::findOrFail($id);
        
        // Hiện tại chỉ hỗ trợ sửa tài khoản Admin qua controller này
        if ($user->role !== 'admin') {
            return redirect()->route('admin.users.show', $id)->with('error', 'Chỉ có thể sửa thông tin của Quản trị viên tại đây.');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        if ($user->role !== 'admin') {
            return redirect()->route('admin.users.show', $id)->with('error', 'Chỉ có thể sửa thông tin của Quản trị viên tại đây.');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'required|string|max:20|unique:users,phone,' . $user->id,
            'email'     => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'id_card'   => 'nullable|string|max:20',
            'password'  => 'nullable|string|min:6',
        ], [
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.'
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        $user->update($validated);

        SystemLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'USER_UPDATED',
            'module'      => 'users',
            'ref_type'    => 'users',
            'ref_id'      => $user->id,
            'description' => 'Cập nhật thông tin Quản trị viên: ' . $user->full_name,
            'ip_address'  => request()->ip(),
        ]);

        return redirect()->route('admin.users.show', $user->id)->with('success', 'Đã cập nhật thông tin thành công.');
    }

    public function toggleActive($id)
    {
        // Không cho khoá chính mình
        if ($id == auth()->id()) {
            return redirect()->back()->with('error', 'Bạn không thể khoá tài khoản của chính mình.');
        }

        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        $action = $user->is_active ? 'USER_UNLOCKED' : 'USER_LOCKED';
        SystemLog::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'module'      => 'users',
            'ref_type'    => 'users',
            'ref_id'      => $user->id,
            'description' => ($user->is_active ? 'Mở khoá' : 'Khoá') . ' tài khoản: ' . $user->full_name,
            'ip_address'  => request()->ip(),
        ]);

        $message = $user->is_active ? 'Đã mở khoá tài khoản thành công.' : 'Đã khoá tài khoản thành công.';
        return redirect()->back()->with('success', $message);
    }
}