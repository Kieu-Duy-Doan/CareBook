<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'changed_by',
        'old_status',
        'new_status',
        'action',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'APPOINTMENT_CREATED' => 'Tạo lịch hẹn',
            'ADMIN_UPDATE'        => 'Quản trị viên cập nhật',
            'DOCTOR_UPDATE'       => 'Bác sĩ cập nhật',
            'RECEPTIONIST_UPDATE' => 'Lễ tân cập nhật',
            'PATIENT_UPDATE'      => 'Bệnh nhân cập nhật',
            'SYSTEM_UPDATE'       => 'Hệ thống cập nhật',
            'STATUS_CHANGED'      => 'Đổi trạng thái',
            'PAYMENT_COMPLETED'   => 'Thanh toán thành công',
            'PAYMENT_FAILED'      => 'Thanh toán thất bại',
            'CLINICAL_VISIT_CREATED' => 'Chỉ định cận lâm sàng',
            'PRESCRIPTION_CREATED' => 'Kê đơn thuốc',
            default               => $this->action,
        };
    }

    private function translateStatus(?string $status): ?string
    {
        if (!$status) return null;
        return match ($status) {
            'pending'    => 'Đã tiếp nhận',
            'checked_in' => 'Đã checkin',
            'examining'  => 'Đang khám',
            'completed'  => 'Hoàn thành',
            'cancelled'  => 'Đã huỷ',
            'absent'     => 'Vắng mặt',
            default      => $status,
        };
    }

    public function getOldStatusLabelAttribute(): ?string
    {
        return $this->translateStatus($this->old_status);
    }

    public function getNewStatusLabelAttribute(): ?string
    {
        return $this->translateStatus($this->new_status);
    }
}
