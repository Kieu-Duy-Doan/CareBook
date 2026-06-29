<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\Validate\StoreDoctorRequest;
use App\Http\Requests\Admin\Validate\UpdateDoctorRequest;
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
            ->latest('created_at');

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

        return view('admin.doctors.create', compact('specialties', 'rooms'));
    }
    public function store(StoreDoctorRequest $request)
    {
        $validated = $request->validated();
        
        $doctorCode = $this->generateDoctorCode($validated['full_name']);

        DB::transaction(function () use ($validated, $doctorCode) {
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

            // Tạo DoctorProfile
            $doctor = DoctorProfile::create([
                'user_id' => $user->id,
                'doctor_code' => $doctorCode,
                'academic_title' => $validated['academic_title'] ?? null,
                'level' => $validated['level'],
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
    public function update(UpdateDoctorRequest $request, $id)
    {
        $doctor = DoctorProfile::with('user')->findOrFail($id);

        $validated = $request->validated();

        DB::transaction(function() use ($doctor, $validated) {
            // Update User
            $userData = [
                'full_name' => $validated['full_name'],
                'phone'     => $validated['phone'],
                'username'  => $validated['username'],
                'email'     => $validated['email'] ?? null,
            ];
            $doctor->user->update($userData);

            // Update DoctorProfile
            $doctor->update([
                'academic_title'   => $validated['academic_title'] ?? null,
                'level'            => $validated['level'],
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

    private function generateDoctorCode($fullName)
    {
        $slug = \Illuminate\Support\Str::slug($fullName); // "bui-xuan-huan"
        $parts = array_filter(explode('-', $slug));
        
        if (count($parts) == 1) {
            $prefix = reset($parts);
        } else {
            $firstName = array_pop($parts); // huan
            $initials = '';
            foreach ($parts as $part) {
                if (!empty($part)) {
                    $initials .= substr($part, 0, 1); // b, x
                }
            }
            $prefix = $firstName . $initials; // huanbx
        }

        $latestDoctor = DoctorProfile::where('doctor_code', 'regexp', '^' . $prefix . '[0-9]{2,}$')
            ->orderByRaw('CAST(SUBSTRING(doctor_code, '.(strlen($prefix)+1).') AS UNSIGNED) DESC')
            ->first();
            
        $nextNumber = 1;
        if ($latestDoctor) {
            $numberStr = substr($latestDoctor->doctor_code, strlen($prefix));
            if (is_numeric($numberStr)) {
                $nextNumber = (int)$numberStr + 1;
            }
        }
        
        return $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }
}