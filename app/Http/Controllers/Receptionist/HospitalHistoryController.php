<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Models\Specialty;
use App\Services\AppointmentService;
use Illuminate\Http\Request;

class HospitalHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with([
            'patientProfile',
            'doctor.user',
            'specialty',
            'room',
            'payments' => fn($q) => $q->where('status', 'paid'),
            'medicalRecord',
        ])->where('status', 'completed')
          ->latest('appointment_date')
          ->latest('appointment_time');

        // Default: today
        if (!$request->has('date_from') && !$request->has('date_to') && !$request->has('clear_filter')) {
            $request->merge([
                'date_from' => now()->toDateString(),
                'date_to'   => now()->toDateString(),
            ]);
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
                  ->orWhereHas('patientProfile', fn($pq) => $pq->where('full_name', 'like', '%' . $search . '%'));
            });
        }

        $appointments = $query->paginate(20)->withQueryString();
        $doctors = DoctorProfile::with('user')->whereHas('user', fn($q) => $q->where('is_active', true))->get();
        $specialties = Specialty::where('is_active', true)->orderBy('name')->get();

        return view('receptionist.hospital-history.index', compact('appointments', 'doctors', 'specialties'));
    }
}
