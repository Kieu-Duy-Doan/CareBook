<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    protected $fillable = [
        'appointment_id',
        'doctor_profile_id',
        'assistant_id',
        'diagnosis',
        'icd10_code',
        'conclusion',
        'advice',
        'followup_date',
        'followup_reminded',
        'treatment_result',
        'result_files',
    ];

    protected function casts(): array
    {
        return [
            'followup_date'     => 'date',
            'followup_reminded' => 'boolean',
            'result_files'      => 'array',
        ];
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class);
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class);
    }

    public function assistant()
    {
        return $this->belongsTo(User::class, 'assistant_id');
    }
}
