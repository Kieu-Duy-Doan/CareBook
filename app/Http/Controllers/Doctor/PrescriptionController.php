<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\Doctor\StorePrescriptionRequest;
use App\Http\Requests\Doctor\UpdatePrescriptionRequest;

class PrescriptionController extends Controller
{
    public function create(MedicalRecord $medical_record)
    {
        if ($medical_record->prescription) {
            return redirect()->route('doctor.prescriptions.edit', $medical_record->prescription->id);
        }

        return view('doctor.prescriptions.create', compact('medical_record'));
    }

    public function store(StorePrescriptionRequest $request, MedicalRecord $medical_record, \App\Services\ClinicalService $clinicalService)
    {
        if ($medical_record->prescription) {
            return redirect()->route('doctor.medical-records.show', $medical_record->id)
                             ->with('error', 'Đơn thuốc đã tồn tại.');
        }

        $clinicalService->createPrescription($medical_record, $request->validated());

        return redirect()->route('doctor.medical-records.show', $medical_record->id)
                         ->with('success', 'Tạo đơn thuốc thành công.');
    }

    public function edit(Prescription $prescription)
    {
        $prescription->load('medicalRecord');
        return view('doctor.prescriptions.edit', compact('prescription'));
    }

    public function update(UpdatePrescriptionRequest $request, Prescription $prescription, \App\Services\ClinicalService $clinicalService)
    {
        $clinicalService->updatePrescription($prescription, $request->validated());

        return redirect()->route('doctor.medical-records.show', $prescription->medical_record_id)
                         ->with('success', 'Cập nhật đơn thuốc thành công.');
    }

    public function destroy(Prescription $prescription, \App\Services\ClinicalService $clinicalService)
    {
        $medical_record_id = $prescription->medical_record_id;
        $clinicalService->deletePrescription($prescription);

        return redirect()->route('doctor.medical-records.show', $medical_record_id)
                         ->with('success', 'Xóa đơn thuốc thành công.');
    }
}
