<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StorePatientRequest;
use App\Http\Requests\Admin\UpdatePatientRequest;
use App\Models\User;
use App\Models\PatientProfile;
use App\Models\Appointment;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;
use App\Exports\PatientsExport;
use Maatwebsite\Excel\Facades\Excel;

class PatientController extends Controller
{
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

        

        $patients = $query->paginate(15)->withQueryString();

        return view('admin.patients.index', compact('patients', 'stats'));
    }

    public function create()
    {
        $customers = User::where('role', 'patient')->where('is_active', true)->get();
        return view('admin.patients.create', compact('customers'));
    }

    public function store(StorePatientRequest $request)
    {
        $validated = $request->validated();

        // Validate is_self: A user can only have 1 self profile
        if ($validated['is_self']) {
            $hasSelf = PatientProfile::where('owner_id', $validated['owner_id'])->where('is_self', 1)->exists();
            if ($hasSelf) {
                return back()->withInput()->with('error', 'Khách hàng này đã có hồ sơ bản thân. Vui lòng chọn loại hồ sơ là "Người thân".');
            }
        }

        DB::transaction(function() use ($validated) {
            $profile = PatientProfile::create([
                'patient_code'    => 'BN' . ($validated['id_card'] ?? substr(str_shuffle('0123456789'), 0, 10)),
                'owner_id'        => $validated['owner_id'],
                'full_name'       => $validated['full_name'],
                'date_of_birth'   => $validated['date_of_birth'],
                'gender'          => $validated['gender'],
                'id_card'         => $validated['id_card'] ?? null,
                'phone'           => $validated['phone'] ?? null,
                'address'         => $validated['address'] ?? null,
                'occupation'      => $validated['occupation'] ?? null,
                'ethnicity'       => $validated['ethnicity'] ?? null,
                'insurance_code'  => $validated['insurance_code'] ?? null,
                'insurance_place' => $validated['insurance_place'] ?? null,
                'insurance_expiry'=> $validated['insurance_expiry'] ?? null,
                'symptom_notes'   => $validated['symptom_notes'] ?? null,
                'is_self'         => $validated['is_self'],
            ]);

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'PATIENT_PROFILE_CREATED',
                'module'      => 'patients',
                'ref_type'    => 'patient_profiles',
                'ref_id'      => $profile->id,
                'description' => 'Thêm hồ sơ bệnh nhân mới: ' . $validated['full_name'],
                'ip_address'  => request()->ip(),
            ]);
        });

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
        $customers = User::where('role', 'patient')->where('is_active', true)->get();
        return view('admin.patients.edit', compact('profile', 'customers'));
    }

    public function update(UpdatePatientRequest $request, $id)
    {
        $profile = PatientProfile::findOrFail($id);

        $validated = $request->validated();

        if ($validated['is_self'] && (!$profile->is_self || $profile->owner_id != $validated['owner_id'])) {
            $hasSelf = PatientProfile::where('owner_id', $validated['owner_id'])
                        ->where('is_self', 1)
                        ->where('id', '!=', $profile->id)
                        ->exists();
            if ($hasSelf) {
                return back()->withInput()->with('error', 'Khách hàng này đã có hồ sơ bản thân. Vui lòng chọn loại hồ sơ là "Người thân".');
            }
        }

        DB::transaction(function() use ($profile, $validated) {
            $profile->update([
                'owner_id'        => $validated['owner_id'],
                'is_self'         => $validated['is_self'],
                'full_name'       => $validated['full_name'],
                'date_of_birth'   => $validated['date_of_birth'],
                'gender'          => $validated['gender'],
                'id_card'         => $validated['id_card'] ?? null,
                'phone'           => $validated['phone'] ?? null,
                'address'         => $validated['address'] ?? null,
                'occupation'      => $validated['occupation'] ?? null,
                'ethnicity'       => $validated['ethnicity'] ?? null,
                'insurance_code'  => $validated['insurance_code'] ?? null,
                'insurance_place' => $validated['insurance_place'] ?? null,
                'insurance_expiry'=> $validated['insurance_expiry'] ?? null,
                'symptom_notes'   => $validated['symptom_notes'] ?? null,
            ]);

            SystemLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'PATIENT_PROFILE_UPDATED',
                'module'      => 'patients',
                'ref_type'    => 'patient_profiles',
                'ref_id'      => $profile->id,
                'description' => 'Cập nhật thông tin hồ sơ: ' . $validated['full_name'],
                'ip_address'  => request()->ip(),
            ]);
        });

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

            $user->update(['is_active' => !$user->is_active]);

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