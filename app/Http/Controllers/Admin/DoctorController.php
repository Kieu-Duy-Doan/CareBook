<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoctorProfile;
use App\Models\Specialty;
use App\Models\Room;
use App\Models\User;
use App\Models\SystemLog;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Imports\DoctorsImport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total'   => DoctorProfile::count(),
            'active'  => DoctorProfile::whereHas('user', fn($q) => $q->where('is_active', true))->count(),
            'locked'  => DoctorProfile::whereHas('user', fn($q) => $q->where('is_active', false))->count(),
            'specialties_count' => Specialty::where('is_active', true)->count(),
        ];

        $query = DoctorProfile::with(['user', 'specialties'])
            ->whereHas('user') // chỉ lấy có user
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc');

        // Filter search: tên hoặc mã bác sĩ
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('doctor_code', 'like', '%'.$request->search.'%')
                  ->orWhereHas('user', fn($uq) =>
                      $uq->where('full_name', 'like', '%'.$request->search.'%')
                         ->orWhere('phone', 'like', '%'.$request->search.'%')
                  );
            });
        }

        // Filter chuyên khoa
        if ($request->filled('specialty_id')) {
            $query->whereHas('specialties', fn($q) =>
                $q->where('specialties.id', $request->specialty_id)
            );
        }

        // Filter cấp độ
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Filter trạng thái
        if ($request->filled('status')) {
            $query->whereHas('user', fn($q) =>
                $q->where('is_active', $request->status)
            );
        }

        $doctors = $query->paginate(12)->withQueryString();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        return view('admin.doctors.index', compact('doctors', 'stats', 'specialties'));
    }
    public function create()
    {
        $specialties = Specialty::where('is_active', true)->orderBy('display_order')->get();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();

        // Tự động sinh mã bác sĩ kế tiếp (BS001, BS002, ...)
        $latestDoctor = DoctorProfile::where('doctor_code', 'regexp', '^BS[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING(doctor_code, 3) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($latestDoctor) {
            $numberStr = substr($latestDoctor->doctor_code, 2);
            if (is_numeric($numberStr)) {
                $nextNumber = (int)$numberStr + 1;
            }
        }
        $nextDoctorCode = 'BS' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return view('admin.doctors.create', compact('specialties', 'rooms', 'nextDoctorCode'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Tài khoản
            'full_name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'max:15', 'regex:/^(0|\+84)[3|5|7|8|9][0-9]{8}$/', 'unique:users,phone'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9_\.]+$/', 'unique:users,username'],
            'email' => 'nullable|email|max:150|unique:users,email',
            'password' => ['required', 'string', Password::min(8)->letters()->mixedCase()->numbers()->symbols(), 'confirmed'],
            // Hồ sơ chuyên môn
            'academic_rank' => 'required|in:none,PGS,GS',
            'degree' => 'required|in:BS,ThS,TS,BSCK1,BSCK2,BSNT',
            'current_position' => 'required|in:INTERN,ATTENDING,CONSULTANT,DEPARTMENT_HEAD,EXPERT',
            'expertise' => 'nullable|string|max:2000',
            'experience_years' => 'nullable|integer|min:0|max:60',
            'license_number' => 'required|string|max:50|unique:doctor_profiles,license_number',
            'bio' => 'nullable|string|max:2000',
            // Chuyên khoa
            'specialty_ids' => 'required|array|min:1',
            'specialty_ids.*' => 'exists:specialties,id,is_active,1',
            'primary_specialty_id' => [
                'required',
                'exists:specialties,id,is_active,1',
                function ($attribute, $value, $fail) use ($request) {
                    $specialtyIds = $request->input('specialty_ids', []);
                    if (! in_array($value, $specialtyIds)) {
                        $fail('Chuyên khoa chính phải nằm trong danh sách chuyên khoa đã chọn.');
                    }
                },
            ],
        ], [
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không đúng định dạng (VD: 0901234567 hoặc +84901234567).',
            'phone.unique' => 'Số điện thoại đã được sử dụng.',
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.regex' => 'Tên đăng nhập chỉ được chứa chữ cái, số, dấu gạch dưới và dấu chấm.',
            'username.unique' => 'Tên đăng nhập đã tồn tại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự và phải bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'level.required' => 'Vui lòng chọn cấp độ chuyên môn.',
            'specialty_ids.required' => 'Vui lòng chọn ít nhất một chuyên khoa.',
            'specialty_ids.*.exists' => 'Chuyên khoa đã chọn không tồn tại hoặc đã bị vô hiệu hoá.',
            'primary_specialty_id.required' => 'Vui lòng chọn chuyên khoa chính.',
            'primary_specialty_id.exists' => 'Chuyên khoa chính không hợp lệ.',
            'expertise.max' => 'Lĩnh vực chuyên trị tối đa 2000 ký tự.',
            'bio.max' => 'Giới thiệu bản thân tối đa 2000 ký tự.',
            'license_number.required' => 'Vui lòng nhập số chứng chỉ hành nghề.',
        ]);

        DB::transaction(function () use ($validated) {
            // Tạo User
            $user = User::create([
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'username' => $validated['username'],
                'email' => $validated['email'] ?? null,
                'password' => bcrypt($validated['password']),
                'role' => 'doctor',
                'is_active' => true,
            ]);

            $doctorCode = $this->_generateDoctorCode($validated['full_name']);

            $level = $validated['degree'];
            if (in_array($validated['academic_rank'], ['GS', 'PGS'])) {
                $level = $validated['academic_rank'];
            } elseif ($level === 'BSNT') {
                $level = 'BS';
            }

            // Tạo DoctorProfile
            $doctor = DoctorProfile::create([
                'user_id' => $user->id,
                'doctor_code' => $doctorCode,
                'academic_rank' => $validated['academic_rank'],
                'degree' => $validated['degree'],
                'current_position' => $validated['current_position'],
                'level' => $level,
                'expertise' => $validated['expertise'] ?? null,
                'experience_years' => $validated['experience_years'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]);

            // Gán chuyên khoa
            $syncData = [];
            foreach ($validated['specialty_ids'] as $specialtyId) {
                $syncData[$specialtyId] = [
                    'is_primary' => ($specialtyId == $validated['primary_specialty_id']) ? 1 : 0,
                ];
            }
            $doctor->specialties()->sync($syncData);

            // Ghi log
            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'DOCTOR_CREATED',
                'module'      => 'doctors',
                'ref_type'    => 'doctor_profiles',
                'ref_id'      => $doctor->id,
                'description' => 'Thêm bác sĩ mới: ' . $validated['full_name'],
                'ip_address'  => request()->ip(),
            ]);
        });

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Thêm bác sĩ thành công.');
    }
    public function generateCode(Request $request)
    {
        $fullName = $request->input('full_name');
        if (empty(trim($fullName))) {
            return response()->json(['doctor_code' => '']);
        }
        return response()->json(['doctor_code' => $this->_generateDoctorCode($fullName)]);
    }

    private function _generateDoctorCode($fullName)
    {
        $nameParts = explode(' ', trim($fullName));
        $firstName = array_pop($nameParts);
        $initials = '';
        foreach ($nameParts as $part) {
            if (!empty($part)) {
                $initials .= mb_substr($part, 0, 1);
            }
        }
        $baseCode = \Illuminate\Support\Str::slug($firstName . $initials, '');
        $baseCode = str_replace('-', '', $baseCode);

        $latestProfile = DoctorProfile::where('doctor_code', 'like', $baseCode . '%')
                                      ->orderBy('id', 'desc')
                                      ->first();
        $nextNumber = 1;
        if ($latestProfile) {
            $latestCode = $latestProfile->doctor_code;
            $numberPart = str_replace($baseCode, '', $latestCode);
            if (is_numeric($numberPart)) {
                $nextNumber = intval($numberPart) + 1;
            }
        }
        return $baseCode . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }

    public function show($id)
    {
        $doctor = DoctorProfile::with([
            'user',
            'specialties',
            'workSchedules.room',
        ])->findOrFail($id);

        // Thống kê lịch hẹn
        $appointmentStats = [
            'total'     => Appointment::where('doctor_profile_id', $id)->count(),
            'pending'   => Appointment::where('doctor_profile_id', $id)->where('status', 'pending')->count(),
            'completed' => Appointment::where('doctor_profile_id', $id)->where('status', 'completed')->count(),
            'today'     => Appointment::where('doctor_profile_id', $id)
                            ->whereDate('appointment_date', today())->count(),
        ];

        // 5 lịch hẹn gần nhất
        $recentAppointments = Appointment::with('patientProfile.user')
            ->where('doctor_profile_id', $id)
            ->latest('appointment_date')
            ->limit(5)
            ->get();

        $logs = SystemLog::where('ref_type', 'doctor_profiles')
            ->where('ref_id', $id)
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.doctors.show', compact(
            'doctor', 'appointmentStats', 'recentAppointments', 'logs'
        ));
    }
    public function edit($id)
    {
        $doctor = DoctorProfile::with(['user', 'specialties'])->findOrFail($id);
        $specialties = Specialty::where('is_active', true)->orderBy('display_order')->get();
        $rooms = Room::where('is_active', true)->orderBy('name')->get();
        $selectedSpecialtyIds = $doctor->specialties->pluck('id')->toArray();
        $primarySpecialtyId = $doctor->specialties->where('pivot.is_primary', 1)->first()?->id;
        
        return view('admin.doctors.edit', compact(
            'doctor', 'specialties', 'rooms', 'selectedSpecialtyIds', 'primarySpecialtyId'
        ));
    }
    public function update(Request $request, $id)
    {
        $doctor = DoctorProfile::with('user')->findOrFail($id);

        $validated = $request->validate([
            'full_name'       => 'required|string|max:100',
            'phone'           => "required|string|max:15|unique:users,phone,{$doctor->user_id}",
            'username'        => "required|string|max:50|unique:users,username,{$doctor->user_id}",
            'email'           => "nullable|email|unique:users,email,{$doctor->user_id}",
            'doctor_code'     => "required|string|max:20|unique:doctor_profiles,doctor_code,$id",
            'academic_rank'   => 'required|in:none,PGS,GS',
            'degree'          => 'required|in:BS,ThS,TS,BSCK1,BSCK2,BSNT',
            'current_position'=> 'required|in:INTERN,ATTENDING,CONSULTANT,DEPARTMENT_HEAD,EXPERT',
            'expertise'       => 'nullable|string',
            'experience_years'=> 'nullable|integer|min:0|max:60',
            'license_number'  => "nullable|string|max:50|unique:doctor_profiles,license_number,$id",
            'bio'             => 'nullable|string',
            'specialty_ids'        => 'required|array|min:1',
            'specialty_ids.*'      => 'exists:specialties,id',
            'primary_specialty_id' => [
                'required',
                'exists:specialties,id',
                function ($attribute, $value, $fail) use ($request) {
                    $specialtyIds = $request->input('specialty_ids', []);
                    if (! in_array($value, $specialtyIds)) {
                        $fail('Chuyên khoa chính phải nằm trong danh sách chuyên khoa đã chọn.');
                    }
                },
            ],
        ], [
            'full_name.required'      => 'Vui lòng nhập họ tên.',
            'phone.required'          => 'Vui lòng nhập số điện thoại.',
            'phone.unique'            => 'Số điện thoại đã được sử dụng.',
            'username.required'       => 'Vui lòng nhập tên đăng nhập.',
            'username.unique'         => 'Tên đăng nhập đã tồn tại.',
            'doctor_code.required'    => 'Vui lòng nhập mã bác sĩ.',
            'doctor_code.unique'      => 'Mã bác sĩ đã tồn tại.',
            'level.required'          => 'Vui lòng chọn cấp độ chuyên môn.',
            'specialty_ids.required'  => 'Vui lòng chọn ít nhất một chuyên khoa.',
            'primary_specialty_id.required' => 'Vui lòng chọn chuyên khoa chính.',
        ]);

        DB::transaction(function() use ($doctor, $validated) {
            // Update User
            $userData = [
                'full_name' => $validated['full_name'],
                'phone'     => $validated['phone'],
                'username'  => $validated['username'],
                'email'     => $validated['email'] ?? null,
            ];
            $doctor->user->update($userData);

            $level = $validated['degree'];
            if (in_array($validated['academic_rank'], ['GS', 'PGS'])) {
                $level = $validated['academic_rank'];
            } elseif ($level === 'BSNT') {
                $level = 'BS';
            }

            // Update DoctorProfile
            $doctor->update([
                'doctor_code'      => $validated['doctor_code'],
                'academic_rank'    => $validated['academic_rank'],
                'degree'           => $validated['degree'],
                'current_position' => $validated['current_position'],
                'level'            => $level,
                'expertise'        => $validated['expertise'] ?? null,
                'experience_years' => $validated['experience_years'] ?? null,
                'license_number'   => $validated['license_number'] ?? null,
                'bio'              => $validated['bio'] ?? null,
            ]);

            // Sync chuyên khoa
            $syncData = [];
            foreach ($validated['specialty_ids'] as $specialtyId) {
                $syncData[$specialtyId] = [
                    'is_primary' => ($specialtyId == $validated['primary_specialty_id']) ? 1 : 0
                ];
            }
            $doctor->specialties()->sync($syncData);

            $doctor->touch();

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'DOCTOR_UPDATED',
                'module'      => 'doctors',
                'ref_type'    => 'doctor_profiles',
                'ref_id'      => $doctor->id,
                'description' => 'Cập nhật thông tin bác sĩ: ' . $validated['full_name'],
                'ip_address'  => request()->ip(),
            ]);
        });

        return redirect()->route('admin.doctors.edit', $id)
            ->with('success', 'Cập nhật thông tin bác sĩ thành công.');
    }
    public function toggleActive($id)
    {
        $doctor = DoctorProfile::with('user')->findOrFail($id);

        DB::transaction(function() use ($doctor) {
            $newActiveStatus = !$doctor->user->is_active;
            $doctor->user->update(['is_active' => $newActiveStatus]);

            // Tự động hủy lịch hẹn tương lai nếu tài khoản bị khóa
            if (!$newActiveStatus) {
                $appointments = Appointment::where('doctor_profile_id', $doctor->id)
                    ->whereIn('status', ['pending', 'checked_in'])
                    ->whereDate('appointment_date', '>=', today())
                    ->get();

                foreach ($appointments as $apt) {
                    $apt->update(['status' => 'cancelled']);
                    AppointmentLog::create([
                        'appointment_id' => $apt->id,
                        'changed_by'     => auth()->id() ?? $doctor->user_id,
                        'old_status'     => $apt->getOriginal('status'),
                        'new_status'     => 'cancelled',
                        'action'         => 'APPOINTMENT_CANCELLED',
                        'reason'         => 'Hệ thống tự động hủy do tài khoản bác sĩ bị khóa.',
                    ]);
                }
            }

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => $newActiveStatus ? 'DOCTOR_UNLOCKED' : 'DOCTOR_LOCKED',
                'module'      => 'doctors',
                'ref_type'    => 'doctor_profiles',
                'ref_id'      => $doctor->id,
                'description' => ($newActiveStatus ? 'Mở khoá' : 'Khoá') . ' bác sĩ: ' . $doctor->user->full_name,
                'ip_address'  => request()->ip(),
            ]);
        });

        return redirect()->back()->with('success',
            $doctor->user->refresh()->is_active ? 'Đã mở khoá tài khoản bác sĩ.' : 'Đã khoá tài khoản bác sĩ.'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
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
            Excel::import(new DoctorsImport, $request->file('file'));

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'DOCTOR_IMPORTED',
                'module'      => 'doctors',
                'ref_type'    => null,
                'ref_id'      => null,
                'description' => 'Import danh sách bác sĩ từ file Excel',
                'ip_address'  => request()->ip(),
            ]);

            return redirect()->route('admin.doctors.index')->with('success', 'Import danh sách bác sĩ thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi import: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DoctorsTemplateExport, 'doctor_import_template.xlsx');
    }

    public function export(Request $request)
    {
        SystemLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'DOCTOR_EXPORTED',
            'module'      => 'doctors',
            'ref_type'    => null,
            'ref_id'      => null,
            'description' => 'Export danh sách bác sĩ ra file Excel',
            'ip_address'  => request()->ip(),
        ]);

        return Excel::download(new \App\Exports\DoctorsExport($request), 'danh_sach_bac_si.xlsx');
    }
}