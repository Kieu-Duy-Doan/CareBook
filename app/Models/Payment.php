<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'transaction_code',
        'intent_code',
        'amount',
        'method',
        'status',
        'sepay_reference',
        'collected_by',
        'note',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function clinicalVisits(): BelongsToMany
    {
        return $this->belongsToMany(ClinicalVisit::class, 'payment_clinical_visit')
                    ->withPivot('amount_allocated')
                    ->withTimestamps();
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
