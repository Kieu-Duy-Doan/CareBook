<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentLog extends Model
{
    public $timestamps = false;

    // Action type constants
    const ACTION_APPOINTMENT_CREATED = 'APPOINTMENT_CREATED';
    const ACTION_ADMIN_CREATE = 'ADMIN_CREATE';
    const ACTION_ADMIN_UPDATE = 'ADMIN_UPDATE';
    const ACTION_ADMIN_STATUS_CHANGE = 'ADMIN_STATUS_CHANGE';
    const ACTION_DOCTOR_UPDATE = 'DOCTOR_UPDATE';
    const ACTION_DOCTOR_STATUS_CHANGE = 'DOCTOR_STATUS_CHANGE';
    const ACTION_RECEPTIONIST_UPDATE = 'RECEPTIONIST_UPDATE';
    const ACTION_RECEPTIONIST_STATUS_CHANGE = 'RECEPTIONIST_STATUS_CHANGE';
    const ACTION_PATIENT_UPDATE = 'PATIENT_UPDATE';
    const ACTION_SYSTEM_UPDATE = 'SYSTEM_UPDATE';
    const ACTION_STATUS_CHANGED = 'STATUS_CHANGED';
    const ACTION_PAYMENT_COMPLETED = 'PAYMENT_COMPLETED';
    const ACTION_PAYMENT_FAILED = 'PAYMENT_FAILED';
    const ACTION_REFUND_REQUESTED = 'REFUND_REQUESTED';
    const ACTION_CLINICAL_VISIT_CREATED = 'CLINICAL_VISIT_CREATED';
    const ACTION_CLINICAL_VISIT_UPDATED = 'CLINICAL_VISIT_UPDATED';
    const ACTION_CLINICAL_VISIT_DELETED = 'CLINICAL_VISIT_DELETED';
    const ACTION_PRESCRIPTION_CREATED_OR_UPDATED = 'PRESCRIPTION_CREATED_OR_UPDATED';
    const ACTION_MEDICAL_RECORD_CREATED_OR_UPDATED = 'MEDICAL_RECORD_CREATED_OR_UPDATED';

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
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
            self::ACTION_APPOINTMENT_CREATED => 'Tạo lịch hẹn',
            self::ACTION_ADMIN_CREATE        => 'Quản trị viên tạo lịch hẹn',
            self::ACTION_ADMIN_UPDATE        => 'Quản trị viên cập nhật',
            self::ACTION_DOCTOR_UPDATE       => 'Bác sĩ cập nhật',
            self::ACTION_RECEPTIONIST_UPDATE => 'Lễ tân cập nhật',
            self::ACTION_PATIENT_UPDATE      => 'Bệnh nhân cập nhật',
            self::ACTION_SYSTEM_UPDATE       => 'Hệ thống cập nhật',
            self::ACTION_STATUS_CHANGED      => 'Đổi trạng thái',
            self::ACTION_ADMIN_STATUS_CHANGE => 'Quản trị viên đổi trạng thái',
            self::ACTION_DOCTOR_STATUS_CHANGE => 'Bác sĩ đổi trạng thái',
            self::ACTION_RECEPTIONIST_STATUS_CHANGE => 'Lễ tân đổi trạng thái',
            self::ACTION_PAYMENT_COMPLETED   => 'Thanh toán thành công',
            self::ACTION_PAYMENT_FAILED      => 'Thanh toán thất bại',
            self::ACTION_REFUND_REQUESTED    => 'Yêu cầu hoàn tiền',
            self::ACTION_CLINICAL_VISIT_CREATED => 'Chỉ định khám lâm sàng / cận lâm sàng',
            self::ACTION_CLINICAL_VISIT_UPDATED => 'Cập nhật trạng thái khám',
            self::ACTION_CLINICAL_VISIT_DELETED => 'Hủy chỉ định khám',
            self::ACTION_PRESCRIPTION_CREATED_OR_UPDATED => 'Cập nhật đơn thuốc',
            self::ACTION_MEDICAL_RECORD_CREATED_OR_UPDATED => 'Cập nhật kết luận bệnh án',
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
            'late'       => 'Đến muộn',
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
