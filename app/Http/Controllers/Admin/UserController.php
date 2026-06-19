<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SystemLog;
use App\Models\Appointment;
use Illuminate\Http\Request;

class UserController extends Controller
{
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