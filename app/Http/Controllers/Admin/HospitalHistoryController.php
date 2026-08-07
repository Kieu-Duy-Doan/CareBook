<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\Specialty;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HospitalHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with([
            'patientProfile',
            'doctor.user',
            'specialty',
            'room',
            'clinicalVisits',
            'medicalRecord',
            'payments' => function ($q) {
                $q->where('status', 'paid');
            }
        ])->where('status', 'completed')
          ->latest('appointment_date')->latest('appointment_time');

        // Mặc định là ngày hôm nay nếu không có filter date
        if (!$request->has('date_from') && !$request->has('date_to') && !$request->has('clear_filter')) {
            $request->merge([
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString()
            ]);
        }

        if (Auth::user()->isDoctor() && Auth::user()->doctorProfile) {
            $query->where('doctor_profile_id', Auth::user()->doctorProfile->id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }
        if ($request->filled('doctor_id')) {
            $query->where('doctor_profile_id', $request->doctor_id);
        }
        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }
        if ($request->filled('search')) {
            $search = AppointmentService::escapeLikeWildcards($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'like', '%' . $search . '%')
                    ->orWhereHas(
                        'patientProfile',
                        fn($pq) => $pq->where('full_name', 'like', '%' . $search . '%')
                    );
            });
        }

        $appointments = $query->paginate(20)->withQueryString();

        $doctors = DoctorProfile::with('user')->whereHas('user', fn($q) => $q->where('is_active', true))->get();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        return view('admin.hospital-history.index', compact('appointments', 'doctors', 'specialties'));
    }

    public function exportCsv(Request $request)
    {
        $query = Appointment::with([
            'patientProfile',
            'doctor.user',
            'specialty',
            'room',
            'clinicalVisits',
            'payments' => function ($q) {
                $q->where('status', 'paid');
            }
        ])->where('status', 'completed')->latest('appointment_date')->latest('appointment_time');

        // Mặc định là ngày hôm nay nếu không có filter date
        if (!$request->has('date_from') && !$request->has('date_to') && !$request->has('clear_filter')) {
            $request->merge([
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString()
            ]);
        }

        if (Auth::user()->isDoctor() && Auth::user()->doctorProfile) {
            $query->where('doctor_profile_id', Auth::user()->doctorProfile->id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }
        if ($request->filled('doctor_id')) {
            $query->where('doctor_profile_id', $request->doctor_id);
        }
        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }
        if ($request->filled('search')) {
            $search = AppointmentService::escapeLikeWildcards($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('appointment_code', 'like', '%' . $search . '%')
                    ->orWhereHas(
                        'patientProfile',
                        fn($pq) => $pq->where('full_name', 'like', '%' . $search . '%')
                    );
            });
        }

        $appointments = $query->get();

        $filename = 'lich-su-kham-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($appointments) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM
            fputcsv($file, [
                'Mã LH', 'Bệnh nhân', 'Bác sĩ', 'Chuyên khoa', 'Ngày khám', 'Giờ khám', 'Tổng thu', 'Diễn biến khám'
            ]);
            
            foreach ($appointments as $a) {
                $totalRevenue = $a->payments->sum('amount');
                $clinicalNotes = $a->clinicalVisits->pluck('clinical_notes')->filter()->implode('; ');
                $diagnosis = $a->clinicalVisits->pluck('diagnosis')->filter()->implode('; ');
                $fullClinicalDetails = trim($diagnosis . ' - ' . $clinicalNotes, ' -');
                
                fputcsv($file, [
                    $a->appointment_code,
                    $a->patientProfile->full_name ?? '',
                    $a->doctor->full_title ?? '',
                    $a->specialty->name ?? '',
                    $a->appointment_date ? $a->appointment_date->format('d/m/Y') : '',
                    $a->appointment_time ? substr($a->appointment_time, 0, 5) : '',
                    $totalRevenue,
                    $fullClinicalDetails
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show($id)
    {
        $appointment = Appointment::with([
            'patientProfile',
            'doctor.user',
            'doctor.specialties',
            'specialty',
            'room',
            'bookedByUser',
            'clinicalVisits.doctorProfile.user',
            'clinicalVisits.room',
            'medicalRecord.prescription',
            'logs.changedBy',
            'payments'
        ])->findOrFail($id);

        $clinicSettings = \App\Models\SystemSetting::whereIn('key', ['clinic_name', 'clinic_address', 'clinic_phone'])
            ->pluck('value', 'key')->toArray();

        return view('admin.hospital-history.show', compact('appointment', 'clinicSettings'));
    }
}
