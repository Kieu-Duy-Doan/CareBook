<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\SystemLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('doctor.dashboard');
        }
        return view('doctor.auth.login');
    }

    public function showForgotPassword()
    {
        if (Auth::check()) {
            return redirect()->route('doctor.dashboard');
        }
        return view('doctor.auth.forgot-password');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if ($user && !$user->is_active) {
            return back()->withInput()->with('error', 'Tài khoản đã bị khoá. Liên hệ quản trị viên.');
        }

        if (Auth::attempt(['phone' => $request->phone, 'password' => $request->password], $request->boolean('remember'))) {
            $user = Auth::user();

            if ($user->role !== 'doctor') {
                Auth::logout();
                return back()->withInput()->with('error', 'Tài khoản này không có quyền truy cập khu vực Bác sĩ.');
            }

            $user->last_login_at = now();
            $user->save();

            SystemLog::create([
                'user_id' => $user->id,
                'action' => 'DOCTOR_LOGIN',
                'module' => 'auth',
                'ip_address' => $request->ip(),
                'description' => 'Doctor logged in successfully'
            ]);

            $request->session()->regenerate();

            return redirect()->route('doctor.dashboard');
        }

        return back()->withInput()->with('error', 'Số điện thoại hoặc mật khẩu không đúng.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            SystemLog::create([
                'user_id' => $user->id,
                'action' => 'DOCTOR_LOGOUT',
                'module' => 'auth',
                'ip_address' => $request->ip(),
                'description' => 'Doctor logged out'
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('doctor.login');
    }
}
