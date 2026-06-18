<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PatientProfile;
use App\Models\Appointment;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total'    => User::where('role', 'patient')->count(),
            'active'   => User::where('role', 'patient')->where('is_active', true)->count(),
            'locked'   => User::where('role', 'patient')->where('is_active', false)->count(),
            'profiles' => PatientProfile::count(),
        ];

        $query = User::with(['patientProfiles'])
            ->where('role', 'patient')
            ->latest('created_at');

        // Search theo tên, SĐT, email
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%')
                  ->orWhere('id_card', 'like', '%'.$request->search.'%')
                  ->orWhereHas('patientProfiles', fn($pq) =>
                      $pq->where('insurance_code', 'like', '%'.$request->search.'%')
                  );
            });
        }

        // Filter trạng thái
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        // Filter có BHYT hay không
        if ($request->filled('has_insurance')) {
            if ($request->has_insurance == '1') {
                $query->whereHas('patientProfiles', fn($pq) =>
                    $pq->whereNotNull('insurance_code')
                );
            } else {
                $query->whereDoesntHave('patientProfiles', fn($pq) =>
                    $pq->whereNotNull('insurance_code')
                );
            }
        }

        $patients = $query->paginate(15)->withQueryString();

        return view('admin.patients.index', compact('patients', 'stats'));
    }
    public function create()
    {
        return view('admin.patients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Tài khoản — bắt buộc tối thiểu
            'full_name'    => 'required|string|max:100',
            'phone'        => ['required', 'string', 'max:15', 'regex:/^(0[35789])[0-9]{8}$/', 'unique:users,phone'],
            'password'     => 'required|string|min:8|confirmed',
            // Tài khoản — optional (admin có thể bỏ trống, bệnh nhân tự bổ sung)
            'username'     => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_.]*$/', 'unique:users,username'],
            'id_card'      => ['required', 'string', 'regex:/^([0-9]{9}|[0-9]{12})$/', 'unique:users,id_card'],
            'email'        => 'nullable|email|max:150|unique:users,email',
            // Hồ sơ bệnh nhân — bắt buộc ngày sinh & giới tính do db constraint
            'profile_full_name'      => 'nullable|string|max:100',
            'date_of_birth'          => 'required|date|before:today',
            'gender'                 => 'required|in:male,female,other',
            'profile_phone'          => 'nullable|string|max:15',
            'address'                => 'nullable|string',
            'occupation'             => 'nullable|string|max:100',
            'ethnicity'              => 'nullable|string|max:50',
            'insurance_code'         => 'nullable|string|max:20',
            'insurance_place'        => 'nullable|string|max:255',
            'insurance_expiry'       => 'nullable|date',
            'symptom_notes'          => 'nullable|string',
        ], [
            'full_name.required'  => 'Vui lòng nhập họ tên.',
            'phone.required'      => 'Vui lòng nhập số điện thoại.',
            'phone.unique'        => 'Số điện thoại đã được sử dụng.',
            'phone.regex'         => 'Số điện thoại không đúng định dạng Việt Nam.',
            'password.required'   => 'Vui lòng nhập mật khẩu.',
            'password.min'        => 'Mật khẩu tối thiểu 8 ký tự.',
            'password.confirmed'  => 'Xác nhận mật khẩu không khớp.',
            'username.unique'     => 'Tên đăng nhập đã tồn tại.',
            'username.regex'      => 'Tên đăng nhập không được chứa ký tự đặc biệt.',
            'id_card.required'    => 'Vui lòng nhập số CCCD/CMND.',
            'id_card.regex'       => 'Số CCCD/CMND không đúng định dạng (phải là 9 hoặc 12 chữ số).',
            'id_card.unique'      => 'Số CCCD/CMND đã được sử dụng.',
            'email.unique'        => 'Email đã được sử dụng.',
            'date_of_birth.required' => 'Vui lòng nhập ngày sinh.',
            'date_of_birth.before'=> 'Ngày sinh không hợp lệ.',
            'gender.required'     => 'Vui lòng chọn giới tính.',
        ]);

        DB::transaction(function() use ($validated) {
            // Tạo User
            $user = User::create([
                'full_name' => $validated['full_name'],
                'phone'     => $validated['phone'],
                'username'  => $validated['username'] ?? $validated['phone'],
                'id_card'   => $validated['id_card'],
                'email'     => $validated['email'] ?? null,
                'password'  => bcrypt($validated['password']),
                'role'      => 'patient',
                'is_active' => true,
            ]);

            // Tạo PatientProfile bản thân (is_self=1)
            // Dùng full_name của user nếu không nhập riêng
            PatientProfile::create([
                'owner_id'        => $user->id,
                'full_name'       => $validated['profile_full_name'] ?? $validated['full_name'],
                'date_of_birth'   => $validated['date_of_birth'],
                'gender'          => $validated['gender'],
                'id_card'         => $validated['id_card'],
                'phone'           => $validated['profile_phone'] ?? $validated['phone'],
                'address'         => $validated['address'] ?? null,
                'occupation'      => $validated['occupation'] ?? null,
                'ethnicity'       => $validated['ethnicity'] ?? null,
                'insurance_code'  => $validated['insurance_code'] ?? null,
                'insurance_place' => $validated['insurance_place'] ?? null,
                'insurance_expiry'=> $validated['insurance_expiry'] ?? null,
                'symptom_notes'   => $validated['symptom_notes'] ?? null,
                'is_self'         => 1,
            ]);

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'PATIENT_CREATED',
                'module'      => 'patients',
                'ref_type'    => 'users',
                'ref_id'      => $user->id,
                'description' => 'Thêm bệnh nhân mới: ' . $validated['full_name'],
                'ip_address'  => request()->ip(),
            ]);
        });

        return redirect()->route('admin.patients.index')
            ->with('success', 'Thêm bệnh nhân thành công.');
    }
    public function show($id)
    {
        $patient = User::with(['patientProfiles'])
            ->where('role', 'patient')
            ->findOrFail($id);

        // Lịch hẹn của tất cả hồ sơ
        $profileIds = $patient->patientProfiles->pluck('id');

        $appointmentStats = [
            'total'     => Appointment::whereIn('patient_profile_id', $profileIds)->count(),
            'pending'   => Appointment::whereIn('patient_profile_id', $profileIds)->where('status', 'pending')->count(),
            'completed' => Appointment::whereIn('patient_profile_id', $profileIds)->where('status', 'completed')->count(),
            'cancelled' => Appointment::whereIn('patient_profile_id', $profileIds)->where('status', 'cancelled')->count(),
        ];

        $recentAppointments = Appointment::with(['doctor.user', 'specialty'])
            ->whereIn('patient_profile_id', $profileIds)
            ->latest('appointment_date')
            ->limit(5)
            ->get();

        $logs = SystemLog::where('user_id', $id)
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.patients.show', compact(
            'patient', 'appointmentStats', 'recentAppointments', 'logs'
        ));
    }
}