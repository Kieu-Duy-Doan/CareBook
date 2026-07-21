<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = [
        'doctor_profile_id',
        'room_id',
        'shift_label',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration_minutes',
        'max_slots',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class);
    }

    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_profile_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function getDayNameAttribute(): string
    {
        $days = [1=>'Chủ Nhật', 2=>'Thứ Hai', 3=>'Thứ Ba', 4=>'Thứ Tư', 5=>'Thứ Năm', 6=>'Thứ Sáu', 7=>'Thứ Bảy'];
        return $days[$this->day_of_week] ?? 'Không xác định';
    }

    public function getTimeRangeAttribute(): string
    {
        return substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5);
    }
}
