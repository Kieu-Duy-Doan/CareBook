<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'type',
        'channel',
        'scheduled_at',
        'is_sent',
        'ref_type',
        'ref_id',
        'is_read',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'is_sent' => 'boolean',
            'is_read' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'appointment' => 'Lịch hẹn',
            'result'      => 'Kết quả',
            'system'      => 'Hệ thống',
            'reminder'    => 'Nhắc nhở',
            default       => 'Khác',
        };
    }

    public function getChannelLabelAttribute(): string
    {
        return match($this->channel) {
            'in_web' => 'Trong web',
            'email'  => 'Email',
            'zalo'   => 'Zalo',
            default  => 'Khác',
        };
    }
}
