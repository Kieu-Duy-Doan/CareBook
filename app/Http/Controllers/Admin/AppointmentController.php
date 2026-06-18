<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use App\Models\DoctorProfile;
use App\Models\Specialty;
use App\Models\PatientProfile;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with([
            'patientProfile',
            'doctor.user',
            'specialty',
            'room',
            'bookedByUser'
        ])->latest('appointment_date')->latest('appointment_time');

        // Filter theo ngày từ
        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }
        // Filter theo ngày đến
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }
        // Filter theo bác sĩ
        if ($request->filled('doctor_id')) {
            $query->where('doctor_profile_id', $request->doctor_id);
        }
        // Filter theo chuyên khoa
        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }
        // Filter theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // Filter theo nguồn đặt
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        // Search theo mã lịch hẹn hoặc tên bệnh nhân
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('appointment_code', 'like', '%' . $request->search . '%')
                    ->orWhereHas(
                        'patientProfile',
                        fn($pq) =>
                        $pq->where('full_name', 'like', '%' . $request->search . '%')
                    );
            });
        }

        $appointments = $query->paginate(20)->withQueryString();

        // Data cho filter dropdowns
        $doctors = DoctorProfile::with('user')->whereHas('user', fn($q) => $q->where('is_active', true))->get();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        // Thống kê nhanh theo filter hiện tại (không paginate)
        $totalCount = Appointment::when($request->filled('date_from'), fn($q) => $q->whereDate('appointment_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('appointment_date', '<=', $request->date_to))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('doctor_id'), fn($q) => $q->where('doctor_profile_id', $request->doctor_id))
            ->when($request->filled('specialty_id'), fn($q) => $q->where('specialty_id', $request->specialty_id))
            ->when($request->filled('source'), fn($q) => $q->where('source', $request->source))
            ->when($request->filled('search'), fn($q) => $q->where(function ($sq) use ($request) {
                $sq->where('appointment_code', 'like', '%' . $request->search . '%')
                    ->orWhereHas(
                        'patientProfile',
                        fn($pq) =>
                        $pq->where('full_name', 'like', '%' . $request->search . '%')
                    );
            }))
            ->count();

        // Aggregate counts by status based on the exact same filters
        $statusCounts = DB::table('appointments')
            ->select('status', DB::raw('count(*) as count'))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('appointment_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('appointment_date', '<=', $request->date_to))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('doctor_id'), fn($q) => $q->where('doctor_profile_id', $request->doctor_id))
            ->when($request->filled('specialty_id'), fn($q) => $q->where('specialty_id', $request->specialty_id))
            ->when($request->filled('source'), fn($q) => $q->where('source', $request->source))
            ->when($request->filled('search'), fn($q) => $q->where(function ($sq) use ($request) {
                $sq->where('appointment_code', 'like', '%' . $request->search . '%')
                    ->orWhereExists(function ($pq) use ($request) {
                        $pq->select(DB::raw(1))
                            ->from('patient_profiles')
                            ->whereColumn('patient_profiles.id', 'appointments.patient_profile_id')
                            ->where('patient_profiles.full_name', 'like', '%' . $request->search . '%');
                    });
            }))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.appointments.index', compact('appointments', 'doctors', 'specialties', 'totalCount', 'statusCounts'));
    }
}
