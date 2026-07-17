<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'medical_record_id',
        'prescribed_date',
        'diagnosis_note',
        'items',
        'general_note',
        'payment_amount',
        'payment_status',
        'payment_method',
        'collected_by',
        'paid_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'prescribed_date' => 'date',
            'items' => 'array',
            'payment_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'payment_prescription')
                    ->withPivot('amount_allocated')
                    ->withTimestamps();
    }
}
