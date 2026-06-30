<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;
use App\Models\SystemLog;
use App\Models\PatientProfile;

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

    public function showForgotPassword(Request $request)
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        return view('auth.passwords.email', [
            'login_type' => $request->query('login_type', 'patient'),
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|max:150',
            'login_type' => 'nullable|in:patient,admin',
        ], [
            'identifier.required' => 'Vui lòng nhập số điện thoại hoặc email.',
        ]);

        $identifier = trim($request->input('identifier'));
        $loginType = $request->input('login_type', 'patient');
        $request->merge(['login_type' => $loginType]);

        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? User::where('email', $identifier)->first()
            : User::where('phone', $identifier)->first();

        if (! $user) {
            return back()->withInput()->with('error', 'Không tìm thấy tài khoản với thông tin này.');
        }

        if (! $user->is_active) {
            return back()->withInput()->with('error', 'Tài khoản đã bị khoá. Liên hệ quản trị viên.');
        }

        if (! $user->email) {
            return back()->withInput()->with('error', 'Tài khoản chưa có email. Vui lòng liên hệ quản trị viên hoặc cập nhật email trong hồ sơ.');
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $message = 'Đã gửi liên kết đặt lại mật khẩu tới email: ' . $this->maskEmail($user->email) . '. Vui lòng kiểm tra hộp thư (và cả thư mục Spam).';

            if (config('mail.default') === 'log') {
                $message .= ' (Hệ thống đang dùng chế độ ghi log — kiểm tra file storage/logs/laravel.log để xem nội dung email.)';
            }

            return back()->with('success', $message);
        }

        return back()->withInput()->with('error', $this->passwordResetErrorMessage($status));
    }

    public function showResetForm(Request $request, $token = null)
    {
        if (Auth::check()) {
            return $this->redirectToDashboard();
        }

        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->query('email'),
            'login_type' => $request->query('login_type', 'patient'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|confirmed|min:8',
            'login_type' => 'nullable|in:patient,admin',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = $password;
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect($this->getLoginRoute($request->input('login_type', 'patient')))
                ->with('success', 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập bằng mật khẩu mới.');
        }

        return back()->withInput($request->only('email', 'login_type'))
            ->with('error', $this->passwordResetErrorMessage($status));
    }

    protected function passwordResetErrorMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => 'Không tìm thấy tài khoản với email này.',
            Password::INVALID_TOKEN => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn. Vui lòng yêu cầu liên kết mới.',
            Password::RESET_THROTTLED => 'Bạn đã yêu cầu quá nhiều lần. Vui lòng đợi ' . config('auth.passwords.users.throttle', 60) . ' giây rồi thử lại.',
            default => 'Không thể xử lý yêu cầu đặt lại mật khẩu. Vui lòng thử lại sau.',
        };
    }

    protected function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));

        return $visible . str_repeat('*', max(1, mb_strlen($local) - 2)) . '@' . $domain;
    }

    protected function getLoginRoute(string $loginType = 'patient'): string
    {
        return $loginType === 'admin' ? route('login') : route('patient.login');
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
            'phone' => ['required', 'string', 'max:15', 'regex:/^(0[35789])[0-9]{8}$/', 'unique:users,phone'],
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|confirmed|min:8',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other,M,F,O',
            'id_card' => ['nullable', 'string', 'regex:/^([0-9]{9}|[0-9]{12})$/', 'unique:users,id_card', 'unique:patient_profiles,id_card'],
        ], [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.unique' => 'Số điện thoại đã được sử dụng.',
            'phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam.',
            'email.required' => 'Vui lòng nhập email (Gmail).',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự.',
            'date_of_birth.required' => 'Vui lòng chọn ngày sinh.',
            'date_of_birth.before' => 'Ngày sinh không hợp lệ.',
            'gender.required' => 'Vui lòng chọn giới tính.',
            'id_card.regex' => 'Số CCCD/CMND không đúng định dạng (9 hoặc 12 số).',
            'id_card.unique' => 'Số CCCD/CMND đã được sử dụng.',
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

        $baseUsername = 'bn_' . preg_replace('/\D/', '', $validated['phone']);
        $username = substr($baseUsername, 0, 45);
        if (User::where('username', $username)->exists()) {
            $username = substr($baseUsername, 0, 40) . '_' . strtolower(Str::random(4));
        }

        try {
            $user = DB::transaction(function () use ($validated, $username) {
                $user = User::create([
                    'full_name' => $validated['full_name'],
                    'phone' => $validated['phone'],
                    'username' => $username,
                    'id_card' => $validated['id_card'] ?? null,
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'role' => 'patient',
                    'is_active' => true,
                ]);

                $patientCode = ! empty($validated['id_card'])
                    ? 'BN' . $validated['id_card']
                    : 'BN' . substr(str_shuffle('0123456789'), 0, 10);

                PatientProfile::create([
                    'patient_code' => $patientCode,
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

                return $user;
            });

            try {
                SystemLog::create([
                    'user_id' => $user->id,
                    'action' => 'USER_REGISTER',
                    'module' => 'auth',
                    'ip_address' => $request->ip(),
                    'description' => 'Patient registered: ' . $user->full_name,
                ]);
            } catch (\Throwable) {
                // Không chặn đăng ký nếu ghi log thất bại
            }
        } catch (\Throwable $exception) {
            logger()->error('Patient registration failed', [
                'message' => $exception->getMessage(),
                'phone' => $request->input('phone'),
            ]);

            return back()->withInput()->with('error', 'Đăng ký thất bại. Vui lòng kiểm tra lại thông tin và thử lại.');
        }

        return redirect()
            ->route('patient.login')
            ->with('success', 'Đăng ký thành công! Vui lòng đăng nhập bằng số điện thoại và mật khẩu vừa tạo.');
    }
}