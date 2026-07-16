<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    protected $fillable = [
        'appointment_id',
        'payment_id',
        'amount',
        'reason',
        'status',
        'refund_method',
        'bank_account',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'approved'   => ['label' => 'Đã duyệt',   'color' => 'green'],
            'completed'  => ['label' => 'Đã hoàn',    'color' => 'blue'],
            'rejected'   => ['label' => 'Từ chối',    'color' => 'red'],
            default      => ['label' => 'Chờ duyệt',  'color' => 'yellow'],
        };
    }
}
