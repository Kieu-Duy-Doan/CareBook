<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PatientProfile;
use App\Models\Appointment;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Admin\StorePatientProfileRequest;
use App\Http\Requests\Admin\UpdatePatientProfileRequest;
use App\Services\PatientProfileService;
use App\Exports\PatientsExport;
use Maatwebsite\Excel\Facades\Excel;

class PatientController extends Controller
{
    protected $patientProfileService;

    public function __construct(PatientProfileService $patientProfileService)
    {
        $this->patientProfileService = $patientProfileService;
    }
    public function export(Request $request)
    {
        return Excel::download(new PatientsExport($request), 'patients_' . date('Ymd_His') . '.xlsx');
    }

    public function index(Request $request)
    {
        $stats = [
            'total'    => PatientProfile::count(),
            'active'   => PatientProfile::whereHas('user', fn($q) => $q->where('is_active', true))->count(),
            'locked'   => PatientProfile::whereHas('user', fn($q) => $q->where('is_active', false))->count(),
            'self_profiles' => PatientProfile::where('is_self', 1)->count(),
        ];

        $query = PatientProfile::with(['user'])
            ->latest('created_at');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%')
                  ->orWhere('id_card', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
                  ->orWhere('insurance_code', 'like', '%'.$request->search.'%')
                  ->orWhereHas('user', fn($uq) =>
                      $uq->where('full_name', 'like', '%'.$request->search.'%')
                         ->orWhere('phone', 'like', '%'.$request->search.'%')
                  );
            });
        }

        if ($request->filled('status')) {
            if ($request->status == '1') {
                $query->whereHas('user', fn($uq) => $uq->where('is_active', true));
            } else {
                $query->whereHas('user', fn($uq) => $uq->where('is_active', false));
            }
        }

        if ($request->filled('has_insurance')) {
            if ($request->has_insurance == '1') {
                $query->whereNotNull('insurance_code');
            } else {
                $query->whereNull('insurance_code');
            }
        }

        $patients = $query->paginate(15)->withQueryString();

        return view('admin.patients.index', compact('patients', 'stats'));
    }

    public function create()
    {
        // Tối ưu: Chỉ lấy 20 khách hàng mới nhất làm gợi ý ban đầu, còn lại tìm kiếm trực tiếp qua AJAX cực nhanh
        $customers = User::where('role', 'patient')
            ->where('is_active', true)
            ->latest('id')
            ->limit(20)
            ->get();

        return view('admin.patients.create', compact('customers'));
    }

    public function store(StorePatientProfileRequest $request)
    {
        $validated = $request->validated();

        $medicalHistoryPaths = [];
        if ($request->hasFile('medical_history')) {
            foreach ($request->file('medical_history') as $file) {
                $path = $file->store('medical_histories', 'public');
                $medicalHistoryPaths[] = $path;
            }
        }

        $this->patientProfileService->createProfile($validated, $medicalHistoryPaths);

        return redirect()->route('admin.patients.index')
            ->with('success', 'Thêm hồ sơ bệnh nhân thành công.');
    }

    public function show($id)
    {
        $profile = PatientProfile::with([
            'user', 
            'appointments' => function($query) {
                $query->orderBy('appointment_date', 'desc')->orderBy('appointment_time', 'desc');
            }, 
            'appointments.doctor.user', 
            'appointments.specialty', 
            'appointments.medicalRecord.prescription'
        ])->findOrFail($id);

        $appointmentStats = [
            'total'     => Appointment::where('patient_profile_id', $id)->count(),
            'pending'   => Appointment::where('patient_profile_id', $id)->where('status', 'pending')->count(),
            'completed' => Appointment::where('patient_profile_id', $id)->where('status', 'completed')->count(),
            'cancelled' => Appointment::where('patient_profile_id', $id)->where('status', 'cancelled')->count(),
        ];

        $logs = SystemLog::where('ref_type', 'patient_profiles')->where('ref_id', $id)
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.patients.show', compact(
            'profile', 'appointmentStats', 'logs'
        ));
    }

    public function edit($id)
    {
        $profile = PatientProfile::with('user')->findOrFail($id);

        // Nạp khách hàng hiện tại cùng 20 khách hàng mới nhất
        $customers = User::where('role', 'patient')
            ->where('is_active', true)
            ->where('id', '!=', $profile->owner_id)
            ->latest('id')
            ->limit(20)
            ->get();

        if ($profile->user) {
            $customers->prepend($profile->user);
        }

        return view('admin.patients.edit', compact('profile', 'customers'));
    }

    public function update(UpdatePatientProfileRequest $request, $id)
    {
        $profile = PatientProfile::findOrFail($id);
        $validated = $request->validated();

        $medicalHistoryPaths = [];
        if ($request->hasFile('medical_history')) {
            foreach ($request->file('medical_history') as $file) {
                $path = $file->store('medical_histories', 'public');
                $medicalHistoryPaths[] = $path;
            }
        }

        $deletedMedicalHistories = $request->input('deleted_medical_histories', []);

        $this->patientProfileService->updateProfile($profile, $validated, $medicalHistoryPaths, $deletedMedicalHistories);

        return redirect()->route('admin.patients.edit', $id)
            ->with('success', 'Cập nhật thông tin hồ sơ thành công.');
    }

    public function destroy($id)
    {
        $profile = PatientProfile::findOrFail($id);
        
        SystemLog::where('ref_type', 'patient_profiles')->where('ref_id', $id)->delete();
        
        $profile->delete();

        SystemLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'PATIENT_PROFILE_DELETED',
            'module'      => 'patients',
            'ref_type'    => 'patient_profiles',
            'ref_id'      => $id,
            'description' => 'Xoá hồ sơ bệnh nhân: ' . $profile->full_name,
            'ip_address'  => request()->ip(),
        ]);

        return redirect()->back()->with('success', 'Đã xoá hồ sơ bệnh nhân.');
    }

    public function toggleActive($id)
    {
        $profile = PatientProfile::findOrFail($id);
        $user = $profile->user;

        if ($user) {
            if ($user->id == auth()->id()) {
                return redirect()->back()->with('error', 'Bạn không thể khoá tài khoản của chính mình.');
            }

            $user->update([
                'is_active' => !$user->is_active,
                'locked_reason' => $user->is_active ? null : null // If locking manually from here, we can leave reason as null. If unlocking, clear the reason.
            ]);

            $action = $user->is_active ? 'USER_UNLOCKED' : 'USER_LOCKED';
            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => $action,
                'module'      => 'patients',
                'ref_type'    => 'users',
                'ref_id'      => $user->id,
                'description' => ($user->is_active ? 'Mở khoá' : 'Khoá') . ' tài khoản bệnh nhân (Quản lý hồ sơ: ' . $profile->full_name . ')',
                'ip_address'  => request()->ip(),
            ]);

            $message = $user->is_active ? 'Đã mở khoá tài khoản thành công.' : 'Đã khoá tài khoản thành công.';
            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->with('error', 'Không tìm thấy tài khoản liên kết với hồ sơ này.');
    }
}