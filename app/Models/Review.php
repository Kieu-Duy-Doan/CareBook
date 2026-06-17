<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'patient_profile_id',
        'doctor_profile_id',
        'specialty_id',
        'rating',
        'comment',
        'is_visible',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patientProfile()
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class);
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }
}
