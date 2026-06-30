<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\SystemLog;
use App\Models\PatientProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        return view('auth.login');
    }

    public function showPatientLogin()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }
        return view('auth.patient-login');
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
            
            $loginType = $request->input('login_type');
            
            // Nếu đăng nhập từ trang bệnh nhân nhưng tài khoản không phải bệnh nhân
            if ($loginType === 'patient' && $user->role !== 'patient') {
                Auth::logout();
                return back()->withInput()->with('error', 'Tài khoản này không phải là bệnh nhân.');
            }
            
            // Nếu đăng nhập từ trang admin/nhân viên nhưng tài khoản lại là bệnh nhân
            if ($loginType === 'admin' && $user->role === 'patient') {
                Auth::logout();
                return back()->withInput()->with('error', 'Bạn không có quyền đăng nhập vào cổng quản trị.');
            }

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
            'patient' => redirect()->route('patient.dashboard'),
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

        return redirect('/');
    }

    /**
     * Show registration form for patients
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        return view('auth.register');
    }

    /**
     * Handle patient registration: create User + PatientProfile
     */
    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => 'required|string|max:15|unique:users,phone',
            'password' => 'required|string|confirmed|min:6',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other,M,F,O',
            'id_card' => 'nullable|string|max:20|unique:patient_profiles,id_card',
            'email' => 'nullable|email|max:150|unique:users,email',
        ], [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.unique' => 'Số điện thoại đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'date_of_birth.required' => 'Vui lòng chọn ngày sinh.',
            'gender.required' => 'Vui lòng chọn giới tính.',
        ]);

        $validated = $request->only([
            'full_name','phone','password','email','date_of_birth','gender','id_card','address','occupation','ethnicity','insurance_code','insurance_place','insurance_expiry','symptom_notes'
        ]);

        // Normalize gender
        $genderMap = ['M' => 'male', 'F' => 'female', 'O' => 'other'];
        $validated['gender'] = $genderMap[$validated['gender']] ?? $validated['gender'];

        foreach (['email','id_card','address','occupation','ethnicity','insurance_code','insurance_place','insurance_expiry'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        $baseUsername = 'bn_' . preg_replace('/[^0-9a-z]/', '', strtolower($validated['phone']));
        $username = substr($baseUsername, 0, 45);
        $suffix = 1;
        while (User::where('username', $username)->exists()) {
            $username = substr($baseUsername, 0, 40) . '_' . $suffix++;
        }

        try {
            DB::transaction(function() use ($validated, $username) {
                $user = User::create([
                    'full_name' => $validated['full_name'],
                    'phone' => $validated['phone'],
                    'username' => $username,
                    'email' => $validated['email'] ?? null,
                    'password' => Hash::make($validated['password']),
                    'role' => 'patient',
                    'is_active' => true,
                ]);

                PatientProfile::create([
                    'patient_code' => 'BN' . substr(str_shuffle('0123456789'), 0, 10),
                    'owner_id' => $user->id,
                    'full_name' => $validated['full_name'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'id_card' => $validated['id_card'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'occupation' => $validated['occupation'] ?? null,
                    'ethnicity' => $validated['ethnicity'] ?? null,
                    'insurance_code' => $validated['insurance_code'] ?? null,
                    'insurance_place' => $validated['insurance_place'] ?? null,
                    'insurance_expiry' => $validated['insurance_expiry'] ?? null,
                    'is_self' => true,
                ]);

                SystemLog::create([
                    'user_id' => $user->id,
                    'action' => 'USER_REGISTER',
                    'module' => 'auth',
                    'ip_address' => request()->ip(),
                    'description' => 'Patient registered: ' . $user->full_name,
                ]);
            });
        } catch (\Throwable $exception) {
            logger()->error('Patient registration failed', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'input' => $request->all(),
            ]);

            return back()->withInput()->with('error', 'Đăng ký thất bại. Vui lòng kiểm tra lại thông tin và thử lại.');
        }

        return redirect()->route('patient.login')->with('success', 'Đăng ký thành công. Vui lòng đăng nhập.');
    }
}