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
        $days = [1 => 'Chủ Nhật', 2 => 'Thứ Hai', 3 => 'Thứ Ba', 4 => 'Thứ Tư', 5 => 'Thứ Năm', 6 => 'Thứ Sáu', 7 => 'Thứ Bảy'];
        return $days[$this->day_of_week] ?? 'Không xác định';
    }

    public function getTimeRangeAttribute(): string
    {
        return substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5);
    }

    public function getShiftBadgeAttribute(): string
    {
        $label = $this->shift_label;
        if (!$label) {
            $hour = (int) substr($this->start_time, 0, 2);
            $label = $hour < 12 ? 'morning' : 'afternoon';
        }

        return match ($label) {
            'morning' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">Ca Sáng</span>',
            'afternoon' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-50 text-orange-700 border border-orange-200">Ca Chiều</span>',
            'evening' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">Ca Tối</span>',
            'full_day' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">Cả ngày</span>',
            default => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200">Chưa xếp ca</span>',
        };
    }
}
