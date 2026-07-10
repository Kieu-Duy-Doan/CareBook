<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\DoctorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicalRecordController extends Controller
{
    public function create(Appointment $appointment)
    {
        // Check if medical record already exists
        if ($appointment->medicalRecord) {
            return redirect()->route('doctor.medical-records.show', $appointment->medicalRecord->id);
        }

        return view('doctor.medical-records.create', compact('appointment'));
    }

    public function store(Request $request, Appointment $appointment)
    {
        if ($appointment->medicalRecord) {
            return redirect()->route('doctor.medical-records.show', $appointment->medicalRecord->id)
                             ->with('error', 'Hồ sơ bệnh án đã tồn tại.');
        }

        $validated = $request->validate([
            'diagnosis' => 'required|string',
            'icd10_code' => 'nullable|string|max:20',
            'conclusion' => 'nullable|string',
            'advice' => 'nullable|string',
            'followup_date' => 'nullable|date',
            'treatment_result' => 'required|in:outpatient,admitted,monitoring',
            'result_files.*' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $resultFiles = [];
        if ($request->hasFile('result_files')) {
            foreach ($request->file('result_files') as $file) {
                $path = $file->store('medical_records', 'public');
                $resultFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ];
            }
        }

        $doctorProfile = DoctorProfile::where('user_id', auth()->id())->first();

        $medicalRecord = MedicalRecord::create([
            'appointment_id' => $appointment->id,
            'doctor_profile_id' => $doctorProfile->id,
            'diagnosis' => $validated['diagnosis'],
            'icd10_code' => $validated['icd10_code'],
            'conclusion' => $validated['conclusion'],
            'advice' => $validated['advice'],
            'followup_date' => $validated['followup_date'],
            'treatment_result' => $validated['treatment_result'],
            'result_files' => empty($resultFiles) ? null : $resultFiles,
        ]);

        return redirect()->route('doctor.medical-records.show', $medicalRecord->id)
                         ->with('success', 'Tạo hồ sơ bệnh án thành công.');
    }

    public function show(MedicalRecord $medical_record)
    {
        $medical_record->load(['appointment.patientProfile', 'prescription']);
        return view('doctor.medical-records.show', compact('medical_record'));
    }

    public function edit(MedicalRecord $medical_record)
    {
        $medical_record->load('appointment');
        return view('doctor.medical-records.edit', compact('medical_record'));
    }

    public function update(Request $request, MedicalRecord $medical_record)
    {
        $validated = $request->validate([
            'diagnosis' => 'required|string',
            'icd10_code' => 'nullable|string|max:20',
            'conclusion' => 'nullable|string',
            'advice' => 'nullable|string',
            'followup_date' => 'nullable|date',
            'treatment_result' => 'required|in:outpatient,admitted,monitoring',
            'result_files.*' => 'nullable|file|mimes:pdf|max:10240',
            'remove_files' => 'nullable|array',
        ]);

        $resultFiles = $medical_record->result_files ?? [];

        // Handle deletions
        if ($request->has('remove_files')) {
            foreach ($request->remove_files as $pathToRemove) {
                Storage::disk('public')->delete($pathToRemove);
                $resultFiles = array_filter($resultFiles, function ($file) use ($pathToRemove) {
                    return $file['path'] !== $pathToRemove;
                });
            }
            $resultFiles = array_values($resultFiles); // Re-index array
        }

        // Handle new uploads
        if ($request->hasFile('result_files')) {
            foreach ($request->file('result_files') as $file) {
                $path = $file->store('medical_records', 'public');
                $resultFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ];
            }
        }

        $validated['result_files'] = empty($resultFiles) ? null : $resultFiles;

        $medical_record->update($validated);

        return redirect()->route('doctor.medical-records.show', $medical_record->id)
                         ->with('success', 'Cập nhật hồ sơ bệnh án thành công.');
    }
}
