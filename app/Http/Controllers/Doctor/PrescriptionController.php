<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function create(MedicalRecord $medical_record)
    {
        if ($medical_record->prescription) {
            return redirect()->route('doctor.prescriptions.edit', $medical_record->prescription->id);
        }

        return view('doctor.prescriptions.create', compact('medical_record'));
    }

    public function store(Request $request, MedicalRecord $medical_record)
    {
        if ($medical_record->prescription) {
            return redirect()->route('doctor.medical-records.show', $medical_record->id)
                             ->with('error', 'Đơn thuốc đã tồn tại.');
        }

        $validated = $request->validate([
            'diagnosis_note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_name' => 'required|string',
            'items.*.quantity' => 'required|string',
            'items.*.dosage' => 'required|string',
            'items.*.instructions' => 'nullable|string',
            'general_note' => 'nullable|string',
        ]);

        $prescription = Prescription::create([
            'medical_record_id' => $medical_record->id,
            'prescribed_date' => now(),
            'diagnosis_note' => $validated['diagnosis_note'] ?? null,
            'items' => $validated['items'],
            'general_note' => $validated['general_note'] ?? null,
        ]);

        return redirect()->route('doctor.medical-records.show', $medical_record->id)
                         ->with('success', 'Tạo đơn thuốc thành công.');
    }

    public function edit(Prescription $prescription)
    {
        $prescription->load('medicalRecord');
        return view('doctor.prescriptions.edit', compact('prescription'));
    }

    public function update(Request $request, Prescription $prescription)
    {
        $validated = $request->validate([
            'diagnosis_note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_name' => 'required|string',
            'items.*.quantity' => 'required|string',
            'items.*.dosage' => 'required|string',
            'items.*.instructions' => 'nullable|string',
            'general_note' => 'nullable|string',
        ]);

        $prescription->update([
            'diagnosis_note' => $validated['diagnosis_note'] ?? null,
            'items' => $validated['items'],
            'general_note' => $validated['general_note'] ?? null,
        ]);

        return redirect()->route('doctor.medical-records.show', $prescription->medical_record_id)
                         ->with('success', 'Cập nhật đơn thuốc thành công.');
    }

    public function destroy(Prescription $prescription)
    {
        $medical_record_id = $prescription->medical_record_id;
        $prescription->delete();

        return redirect()->route('doctor.medical-records.show', $medical_record_id)
                         ->with('success', 'Xóa đơn thuốc thành công.');
    }
}
