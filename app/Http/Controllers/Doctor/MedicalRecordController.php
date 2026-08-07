<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\DoctorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Http\Requests\Doctor\StoreMedicalRecordRequest;
use App\Http\Requests\Doctor\UpdateMedicalRecordRequest;

class MedicalRecordController extends Controller
{
    public function create(Appointment $appointment)
    {
        // Check if medical record already exists
        if ($appointment->medicalRecord) {
            return redirect()->route('doctor.medical-records.show', $appointment->medicalRecord->id);
        }

        $assistants = \App\Models\User::where('role', 'receptionist')->get();

        return view('doctor.medical-records.create', compact('appointment', 'assistants'));
    }

    public function store(StoreMedicalRecordRequest $request, Appointment $appointment, \App\Services\ClinicalService $clinicalService)
    {
        if ($appointment->medicalRecord) {
            return redirect()->route('doctor.medical-records.show', $appointment->medicalRecord->id)
                             ->with('error', 'Hồ sơ bệnh án đã tồn tại.');
        }

        $doctorProfile = DoctorProfile::where('user_id', Auth::id())->first();

        $medicalRecord = $clinicalService->createMedicalRecord(
            $appointment,
            $doctorProfile->id,
            $request->validated(),
            $request->file('result_files')
        );

        return redirect()->route('doctor.medical-records.show', $medicalRecord->id)
                         ->with('success', 'Tạo hồ sơ bệnh án thành công.');
    }

    public function show(MedicalRecord $medical_record, \App\Services\PaymentService $paymentService)
    {
        $medical_record->load(['appointment.patientProfile', 'prescription']);
        
        $summary = $paymentService->calculateSummary($medical_record->appointment);
        $unpaidAmount = $summary['remaining_to_pay'];
        
        return view('doctor.medical-records.show', compact('medical_record', 'summary', 'unpaidAmount'));
    }

    public function edit(MedicalRecord $medical_record)
    {
        $medical_record->load('appointment');
        $assistants = \App\Models\User::where('role', 'receptionist')->get();
        return view('doctor.medical-records.edit', compact('medical_record', 'assistants'));
    }

    public function update(UpdateMedicalRecordRequest $request, MedicalRecord $medical_record, \App\Services\ClinicalService $clinicalService)
    {
        $clinicalService->updateMedicalRecord(
            $medical_record,
            $request->validated(),
            $request->file('result_files'),
            $request->input('remove_files', [])
        );

        return redirect()->route('doctor.medical-records.show', $medical_record->id)
                         ->with('success', 'Cập nhật hồ sơ bệnh án thành công.');
    }
}
