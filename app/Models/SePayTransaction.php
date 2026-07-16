<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SePayTransaction extends Model
{
    use HasFactory;

    protected $table = 'sepay_transactions';

    protected $fillable = [
        'transaction_id',
        'gateway',
        'transaction_date',
        'account_number',
        'sub_account',
        'amount_in',
        'amount_out',
        'accumulated',
        'transaction_content',
        'reference_number',
        'code',
        'is_synced',
        'matched_payment_id',
        'reconciliation_status',
        'reconciliation_note',
    ];

    protected $casts = [
        'is_synced' => 'boolean',
        'amount_in' => 'decimal:2',
        'amount_out' => 'decimal:2',
        'accumulated' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    public function matchedPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'matched_payment_id');
    }

    /**
     * Tình trạng đối soát hiển thị
     */
    public function getReconciliationBadgeAttribute(): array
    {
        return match($this->reconciliation_status) {
            'matched'          => ['label' => 'Đã khớp',     'color' => 'green'],
            'amount_mismatch'  => ['label' => 'Sai số tiền', 'color' => 'yellow'],
            'manual'           => ['label' => 'Thủ công',    'color' => 'blue'],
            default            => ['label' => 'Chưa khớp',   'color' => 'gray'],
        };
    }
}
