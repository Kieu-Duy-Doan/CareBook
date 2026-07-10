<?php

namespace App\Http\Controllers\Receptionist;

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
            return redirect()->route('receptionist.dashboard');
        }
        return view('receptionist.auth.login');
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

            if ($user->role !== 'receptionist') {
                Auth::logout();
                return back()->withInput()->with('error', 'Tài khoản này không có quyền truy cập khu vực Lễ tân.');
            }

            $user->last_login_at = now();
            $user->save();

            SystemLog::create([
                'user_id' => $user->id,
                'action' => 'RECEPTIONIST_LOGIN',
                'module' => 'auth',
                'ip_address' => $request->ip(),
                'description' => 'Receptionist logged in successfully'
            ]);

            $request->session()->regenerate();

            return redirect()->route('receptionist.dashboard');
        }

        return back()->withInput()->with('error', 'Số điện thoại hoặc mật khẩu không đúng.');
    }
}