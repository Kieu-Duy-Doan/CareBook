<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\SystemLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        return view('auth.login');
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
            $user->last_login_at = now();
            $user->save();

            SystemLog::create([
                'user_id' => $user->id,
                'action' => 'USER_LOGIN',
                'module' => 'auth',
                'ip_address' => $request->ip(),
                'description' => 'User logged in successfully'
            ]);

            $request->session()->regenerate();

            if ($request->filled('redirect')) {
                return redirect($request->input('redirect'));
            }

            return $this->redirectToDashboard();
        }

        return back()->withInput()->with('error', 'Số điện thoại hoặc mật khẩu không đúng.');
    }

    public function redirectToDashboard()
    {
        $role = Auth::user()->role;
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            // TODO: implement other dashboards
            default => redirect('/'),
        };
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            SystemLog::create([
                'user_id' => $user->id,
                'action' => 'USER_LOGOUT',
                'module' => 'auth',
                'ip_address' => $request->ip(),
                'description' => 'User logged out'
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
