<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ClinicalVisit;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\WorkSchedule;
use App\Models\AppointmentLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ClinicalService
{
    /**
     * Chỉ định phòng khám lâm sàng / cận lâm sàng.
     * Thuật toán tìm bác sĩ: 4 bước fallback.
     */
    public function assignClinicalVisit(Appointment $appointment, ClinicalVisit $originVisit, array $data): ClinicalVisit
    {
        $roomId = $data['room_id'];
        $appointmentDayOfWeek = Carbon::parse($appointment->appointment_date)->dayOfWeek;
        $appointmentTime      = $appointment->appointment_time;

        // 1. Khớp ngày và bao phủ khung giờ hẹn
        $assignedDoctorProfileId = WorkSchedule::where('room_id', $roomId)
            ->where('day_of_week', $appointmentDayOfWeek)
            ->where('start_time', '<=', $appointmentTime)
            ->where('end_time',   '>=', $appointmentTime)
            ->where('is_active', true)
            ->value('doctor_profile_id');

        // 2. Fallback 1: Khớp ca Sáng / Chiều
        if (!$assignedDoctorProfileId) {
            $shiftLabel = Carbon::parse($appointmentTime)->hour < 12 ? 'morning' : 'afternoon';
            $assignedDoctorProfileId = WorkSchedule::where('room_id', $roomId)
                ->where('day_of_week', $appointmentDayOfWeek)
                ->where('shift_label', $shiftLabel)
                ->where('is_active', true)
                ->value('doctor_profile_id');
        }

        // 3. Fallback 2: Khớp ngày
        if (!$assignedDoctorProfileId) {
            $assignedDoctorProfileId = WorkSchedule::where('room_id', $roomId)
                ->where('day_of_week', $appointmentDayOfWeek)
                ->where('is_active', true)
                ->value('doctor_profile_id');
        }

        // 4. Fallback 3: Khớp phòng
        if (!$assignedDoctorProfileId) {
            $assignedDoctorProfileId = WorkSchedule::where('room_id', $roomId)
                ->where('is_active', true)
                ->value('doctor_profile_id');
        }

        if (!$assignedDoctorProfileId) {
            throw new \Exception('Không thể chỉ định: Phòng này hiện chưa có bác sĩ nào được phân công làm việc trong hệ thống.');
        }

        $maxOrder  = ClinicalVisit::where('appointment_id', $appointment->id)->max('visit_order');
        $nextOrder = $maxOrder ? $maxOrder + 1 : 2;

        $visit = ClinicalVisit::create([
            'appointment_id'    => $appointment->id,
            'parent_visit_id'   => $originVisit->id,
            'doctor_profile_id' => $assignedDoctorProfileId,
            'room_id'           => $roomId,
            'visit_order'       => $nextOrder,
            'is_origin'         => false,
            'status'            => 'waiting',
            'payment_status'    => 'pending',
            'payment_amount'    => $data['payment_amount'] ?? 0,
            'findings'          => $data['findings'] ?? null,
        ]);

        $roomName = \App\Models\Room::find($roomId)?->name ?? 'Phòng không xác định';
        $assignedDoctor = \App\Models\DoctorProfile::with('user')->find($assignedDoctorProfileId)?->user->full_name ?? 'Bác sĩ không xác định';

        AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'action'         => 'CLINICAL_VISIT_CREATED',
            'old_status'     => null,
            'new_status'     => $appointment->status,
            'changed_by'     => Auth::id(),
            'reason'         => "Chỉ định bệnh nhân thực hiện dịch vụ tại phòng $roomName do bác sĩ $assignedDoctor phụ trách."
        ]);

        return $visit;
    }

    /**
     * Cập nhật kết quả khám cận lâm sàng
     */
    public function updateClinicalVisit(ClinicalVisit $visit, array $data, $uploadedFiles = null): ClinicalVisit
    {
        // Kiểm tra thanh toán trước khi thực hiện
        if (in_array($data['status'], ['in_progress', 'completed']) && $visit->payment_status !== 'paid' && $visit->payment_amount > 0) {
            throw new \Exception('Không thể thực hiện: Bệnh nhân chưa thanh toán dịch vụ cận lâm sàng này. Vui lòng yêu cầu thanh toán trước.');
        }

        $visit->findings       = $data['findings'] ?? $visit->findings;
        $visit->status         = $data['status'];
        $visit->payment_amount = $data['payment_amount'] ?? $visit->payment_amount;

        if (!empty($data['refusal_reason'])) {
            $visit->refusal_reason = $data['refusal_reason'];
        }

        if ($uploadedFiles) {
            $files = $visit->result_files ?? [];
            foreach ($uploadedFiles as $file) {
                $path   = $file->store('clinical_results', 'public');
                $files[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }
            $visit->result_files = $files;
        }

        if ($data['status'] === 'in_progress' && is_null($visit->started_at)) {
            $visit->started_at = now();
        }

        if (in_array($data['status'], ['completed', 'refused']) && is_null($visit->completed_at)) {
            $visit->completed_at = now();
        }

        $visit->save();

        $roomName = $visit->room->name ?? 'phòng không xác định';
        $reason = "Đã cập nhật trạng thái thực hiện dịch vụ tại $roomName.";
        if ($data['status'] === 'in_progress') {
            $reason = "Bệnh nhân bắt đầu thực hiện dịch vụ tại $roomName.";
        } elseif ($data['status'] === 'completed') {
            $reason = "Bệnh nhân đã thực hiện xong dịch vụ tại $roomName.";
        } elseif ($data['status'] === 'refused') {
            $reason = "Bệnh nhân từ chối thực hiện dịch vụ tại $roomName với lý do: " . ($data['refusal_reason'] ?? 'Không có');
        }

        AppointmentLog::create([
            'appointment_id' => $visit->appointment_id,
            'action'         => 'CLINICAL_VISIT_UPDATED',
            'old_status'     => null,
            'new_status'     => $visit->appointment->status,
            'changed_by'     => Auth::id(),
            'reason'         => $reason
        ]);

        return $visit;
    }

    /**
     * Xóa chỉ định dịch vụ cận lâm sàng
     */
    public function deleteClinicalVisit(ClinicalVisit $visit)
    {
        $roomName = $visit->room->name ?? 'phòng không xác định';
        $appointmentId = $visit->appointment_id;
        $appointmentStatus = $visit->appointment->status;

        $visit->delete();

        AppointmentLog::create([
            'appointment_id' => $appointmentId,
            'action'         => AppointmentLog::ACTION_CLINICAL_VISIT_DELETED,
            'old_status'     => null,
            'new_status'     => $appointmentStatus,
            'changed_by'     => Auth::id(),
            'reason'         => "Hủy chỉ định thực hiện dịch vụ tại $roomName."
        ]);
    }

    /**
     * Tạo hồ sơ bệnh án
     */
    public function createMedicalRecord(Appointment $appointment, int $doctorProfileId, array $data, $uploadedFiles = null): MedicalRecord
    {
        $resultFiles = [];
        if ($uploadedFiles) {
            foreach ($uploadedFiles as $file) {
                $path = $file->store('medical_records', 'public');
                $resultFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ];
            }
        }

        $medicalRecord = MedicalRecord::create([
            'appointment_id'    => $appointment->id,
            'doctor_profile_id' => $doctorProfileId,
            'assistant_id'      => $data['assistant_id'] ?? null,
            'diagnosis'         => $data['diagnosis'],
            'icd10_code'        => $data['icd10_code'] ?? null,
            'conclusion'        => $data['conclusion'] ?? null,
            'advice'            => $data['advice'] ?? null,
            'followup_date'     => $data['followup_date'] ?? null,
            'treatment_result'  => $data['treatment_result'],
            'result_files'      => empty($resultFiles) ? null : $resultFiles,
        ]);

        AppointmentLog::create([
            'appointment_id' => $appointment->id,
            'action'         => 'MEDICAL_RECORD_CREATED_OR_UPDATED',
            'old_status'     => null,
            'new_status'     => $appointment->status,
            'changed_by'     => Auth::id(),
            'reason'         => "Bác sĩ " . Auth::user()->full_name . " đã khởi tạo bệnh án với chẩn đoán: " . $data['diagnosis']
        ]);

        return $medicalRecord;
    }

    /**
     * Cập nhật hồ sơ bệnh án
     */
    public function updateMedicalRecord(MedicalRecord $medicalRecord, array $data, $uploadedFiles = null, $removeFiles = []): MedicalRecord
    {
        $resultFiles = $medicalRecord->result_files ?? [];

        // Xóa file cũ
        if (!empty($removeFiles)) {
            foreach ($removeFiles as $pathToRemove) {
                Storage::disk('public')->delete($pathToRemove);
                $resultFiles = array_filter($resultFiles, function ($file) use ($pathToRemove) {
                    return $file['path'] !== $pathToRemove;
                });
            }
            $resultFiles = array_values($resultFiles);
        }

        // Upload file mới
        if ($uploadedFiles) {
            foreach ($uploadedFiles as $file) {
                $path = $file->store('medical_records', 'public');
                $resultFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                ];
            }
        }

        $data['result_files'] = empty($resultFiles) ? null : $resultFiles;
        $medicalRecord->update($data);

        AppointmentLog::create([
            'appointment_id' => $medicalRecord->appointment_id,
            'action'         => 'MEDICAL_RECORD_CREATED_OR_UPDATED',
            'old_status'     => null,
            'new_status'     => $medicalRecord->appointment->status ?? null,
            'changed_by'     => Auth::id(),
            'reason'         => "Bác sĩ " . Auth::user()->full_name . " đã cập nhật kết luận khám bệnh."
        ]);

        return $medicalRecord;
    }

    /**
     * Tạo đơn thuốc
     */
    public function createPrescription(MedicalRecord $medicalRecord, array $data): Prescription
    {
        $prescription = Prescription::create([
            'medical_record_id' => $medicalRecord->id,
            'prescribed_date'   => now(),
            'diagnosis_note'    => $data['diagnosis_note'] ?? null,
            'items'             => $data['items'],
            'general_note'      => $data['general_note'] ?? null,
        ]);

        AppointmentLog::create([
            'appointment_id' => $medicalRecord->appointment_id,
            'action'         => 'PRESCRIPTION_CREATED_OR_UPDATED',
            'old_status'     => null,
            'new_status'     => $medicalRecord->appointment->status ?? null,
            'changed_by'     => Auth::id(),
            'reason'         => "Bác sĩ " . Auth::user()->full_name . " đã kê đơn thuốc mới (gồm " . count($data['items']) . " loại thuốc)."
        ]);

        return $prescription;
    }

    /**
     * Cập nhật đơn thuốc
     */
    public function updatePrescription(Prescription $prescription, array $data): Prescription
    {
        $prescription->update([
            'diagnosis_note' => $data['diagnosis_note'] ?? null,
            'items'          => $data['items'],
            'general_note'   => $data['general_note'] ?? null,
        ]);

        AppointmentLog::create([
            'appointment_id' => $prescription->medicalRecord->appointment_id,
            'action'         => 'PRESCRIPTION_CREATED_OR_UPDATED',
            'old_status'     => null,
            'new_status'     => $prescription->medicalRecord->appointment->status ?? null,
            'changed_by'     => Auth::id(),
            'reason'         => "Bác sĩ " . Auth::user()->full_name . " đã chỉnh sửa đơn thuốc (gồm " . count($data['items']) . " loại thuốc)."
        ]);

        return $prescription;
    }

    /**
     * Xóa đơn thuốc
     */
    public function deletePrescription(Prescription $prescription)
    {
        $appointmentId = $prescription->medicalRecord->appointment_id;
        $prescription->delete();

        AppointmentLog::create([
            'appointment_id' => $appointmentId,
            'action'         => 'PRESCRIPTION_CREATED_OR_UPDATED',
            'old_status'     => null,
            'new_status'     => null,
            'changed_by'     => Auth::id(),
            'reason'         => "Bác sĩ " . Auth::user()->full_name . " đã xóa đơn thuốc."
        ]);
    }
}
