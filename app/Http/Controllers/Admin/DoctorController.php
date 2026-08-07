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
use App\Http\Requests\Admin\StoreDoctorRequest;
use App\Http\Requests\Admin\UpdateDoctorRequest;
use App\Services\DoctorProfileService;
use App\Imports\DoctorsImport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DoctorController extends Controller
{
    protected $doctorProfileService;

    public function __construct(DoctorProfileService $doctorProfileService)
    {
        $this->doctorProfileService = $doctorProfileService;
    }
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
    public function store(StoreDoctorRequest $request)
    {
        $validated = $request->validated();

        $this->doctorProfileService->createDoctor($validated);

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
        // This is moved to Service, but keeping a fallback or letting service handle it
        // wait, I don't need it here since it's only in store, and store calls service.
        // Wait, generateCode method uses it. So keep it or refactor generateCode.
        // Actually, better to just remove it and call a static or public method on the Service if needed.
        // For now, I will leave generateCode here and it can duplicate logic or call service.
        // Wait, `_generateDoctorCode` is used in `generateCode` method above.
        // I will keep it for now as it doesn't hurt.
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
    public function update(UpdateDoctorRequest $request, $id)
    {
        $doctor = DoctorProfile::with('user')->findOrFail($id);
        $validated = $request->validated();

        $this->doctorProfileService->updateDoctor($doctor, $validated);

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