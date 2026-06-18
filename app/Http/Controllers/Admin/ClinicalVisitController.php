<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicalVisit;
use App\Models\DoctorProfile;
use App\Models\Room;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use Illuminate\Http\Request;

class ClinicalVisitController extends Controller
{
    /**
     * Hiển thị danh sách giám sát khám lâm sàng
     */
    public function index(Request $request)
    {
        $query = ClinicalVisit::with([
            'appointment.patientProfile',
            'doctorProfile.user',
            'room'
        ]);

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by Payment Status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        // Filter by Doctor
        if ($request->filled('doctor_profile_id')) {
            $query->where('doctor_profile_id', $request->input('doctor_profile_id'));
        }

        // Filter by Room
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
        }

        // Filter by Date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        } else {
            // Default to today if no date selected
            $query->whereDate('created_at', today());
        }

        $visits = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $doctors = DoctorProfile::with('user')->get();
        $rooms = Room::where('is_active', true)->get();

        return view('admin.clinical-visits.index', compact('visits', 'doctors', 'rooms'));
    }

    /**
     * Xem chi tiết hồ sơ bệnh án của lượt khám (Read-Only)
     */
    public function show($id)
    {
        $visit = ClinicalVisit::with([
            'appointment.patientProfile.user',
            'doctorProfile.user',
            'room',
            'collectedBy'
        ])->findOrFail($id);

        // Fetch Medical Record linked to this visit's appointment
        $medicalRecord = MedicalRecord::where('appointment_id', $visit->appointment_id)->first();

        $prescription = null;
        if ($medicalRecord) {
            $prescription = Prescription::where('medical_record_id', $medicalRecord->id)->first();
        }

        return view('admin.clinical-visits.show', compact('visit', 'medicalRecord', 'prescription'));
    }
}
