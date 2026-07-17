<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\SystemLog;

class AuthController extends Controller
{
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

        return redirect()->route('login');
    }
}
