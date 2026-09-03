<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    public $timestamps = false;

    public const MODULES = [
        'auth',
        'users',
        'doctors',
        'specialties',
        'rooms',
        'work-schedules',
        'appointments',
        'cms',
        'faq',
        'chatbot',
        'notifications',
        'settings'
    ];

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'ref_type',
        'ref_id',
        'description',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'old_data' => 'array',
            'new_data' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'DOCTOR_CREATED'             => 'Thêm bác sĩ mới',
            'DOCTOR_UPDATED'             => 'Cập nhật thông tin bác sĩ',
            'DOCTOR_LOCKED'              => 'Khóa tài khoản bác sĩ',
            'DOCTOR_UNLOCKED'            => 'Mở khóa tài khoản bác sĩ',
            'DOCTOR_IMPORTED'            => 'Import danh sách bác sĩ',
            'DOCTOR_EXPORTED'            => 'Export danh sách bác sĩ',
            'RECEPTIONIST_CREATED'       => 'Thêm lễ tân mới',
            'RECEPTIONIST_UPDATED'       => 'Cập nhật thông tin lễ tân',
            'RECEPTIONIST_LOCKED'        => 'Khóa tài khoản lễ tân',
            'RECEPTIONIST_UNLOCKED'      => 'Mở khóa tài khoản lễ tân',
            'RECEPTIONIST_LOGOUT'        => 'Lễ tân đăng xuất',
            'USER_CREATED'               => 'Tạo tài khoản người dùng',
            'USER_UPDATED'               => 'Cập nhật người dùng',
            'USER_LOCKED'                => 'Khóa người dùng',
            'USER_UNLOCKED'              => 'Mở khóa người dùng',
            'USER_DELETED'               => 'Xóa người dùng',
            'USER_LOGIN'                 => 'Đăng nhập',
            'USER_LOGOUT'                => 'Đăng xuất',
            'CUSTOMER_DELETED'           => 'Xóa khách hàng',
            'CUSTOMER_LOCKED'            => 'Khóa khách hàng',
            'CUSTOMER_UNLOCKED'          => 'Mở khóa khách hàng',
            'SPECIALTY_CREATED'          => 'Thêm chuyên khoa',
            'SPECIALTY_UPDATED'          => 'Cập nhật chuyên khoa',
            'SPECIALTY_DELETED'          => 'Xóa chuyên khoa',
            'ROOM_CREATED'               => 'Thêm phòng khám',
            'ROOM_UPDATED'               => 'Cập nhật phòng khám',
            'ROOM_DELETED'               => 'Xóa phòng khám',
            'WORK_SCHEDULE_CREATED'      => 'Tạo lịch làm việc',
            'WORK_SCHEDULE_UPDATED'      => 'Cập nhật lịch làm việc',
            'WORK_SCHEDULE_DELETED'      => 'Xóa lịch làm việc',
            'WORK_SCHEDULE_TRANSFERRED'  => 'Điều chuyển lịch làm việc',
            'SCHEDULE_OVERRIDE_CREATED'  => 'Tạo lịch làm việc ngoại lệ',
            'PATIENT_PROFILE_CREATED'    => 'Tạo hồ sơ bệnh nhân',
            'PATIENT_PROFILE_UPDATED'    => 'Cập nhật hồ sơ bệnh nhân',
            'PATIENT_PROFILE_DELETED'    => 'Xóa hồ sơ bệnh nhân',
            'APPOINTMENT_CREATED'        => 'Tạo lịch hẹn khám',
            'PAYMENT_COMPLETED'          => 'Thanh toán hoàn tất',
            'SETTING_UPDATED'            => 'Cập nhật cài đặt hệ thống',
            'FAQ_CREATED'                => 'Tạo câu hỏi thường gặp',
            'FAQ_UPDATED'                => 'Cập nhật câu hỏi thường gặp',
            'FAQ_DELETED'                => 'Xóa câu hỏi thường gặp',
            'POST_CREATED'               => 'Tạo bài viết',
            'POST_UPDATED'               => 'Cập nhật bài viết',
            'POST_DELETED'               => 'Xóa bài viết',
            default                      => $this->action,
        };
    }

    public function getDescriptionAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        return $this->action_label;
    }

    public function getActionColorAttribute(): string
    {
        return match (true) {
            str_starts_with($this->action, 'USER_')         => 'blue',
            str_starts_with($this->action, 'DOCTOR_')       => 'purple',
            str_starts_with($this->action, 'RECEPTIONIST_') => 'orange',
            str_starts_with($this->action, 'APPOINTMENT_')  => 'green',
            str_starts_with($this->action, 'SPECIALTY_')    => 'orange',
            str_starts_with($this->action, 'ROOM_')         => 'orange',
            str_starts_with($this->action, 'WORK_SCHEDULE_')=> 'orange',
            str_starts_with($this->action, 'SETTING_')      => 'teal',
            default => 'gray',
        };
    }
}
