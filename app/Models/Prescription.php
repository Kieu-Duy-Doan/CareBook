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
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'prescribed_date' => 'date',
            'items' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}
