<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'ref_type',
        'ref_id',
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

    public function getActionColorAttribute(): string
    {
        return match (true) {
            str_starts_with($this->action, 'USER_')       => 'blue',
            str_starts_with($this->action, 'DOCTOR_')     => 'purple',
            str_starts_with($this->action, 'APPOINTMENT_') => 'green',
            str_starts_with($this->action, 'SPECIALTY_')  => 'orange',
            str_starts_with($this->action, 'ROOM_')       => 'orange',
            str_starts_with($this->action, 'WORK_SCHEDULE_') => 'orange',
            str_starts_with($this->action, 'SETTING_')    => 'teal',
            default => 'gray',
        };
    }
}
