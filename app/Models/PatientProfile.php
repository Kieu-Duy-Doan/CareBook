<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientProfile extends Model
{
    protected $fillable = [
        'patient_code',
        'owner_id',
        'full_name',
        'date_of_birth',
        'gender',
        'id_card',
        'phone',
        'address',
        'occupation',
        'ethnicity',
        'insurance_code',
        'insurance_place',
        'insurance_expiry',
        'medical_history',
        'symptom_notes',
        'is_self',
        'relationship',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'insurance_expiry' => 'date',
            'medical_history' => 'array',
            'is_self' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function getGenderLabelAttribute(): string
    {
        return match($this->gender) {
            'male' => 'Nam',
            'female' => 'Nữ',
            default => 'Chưa cập nhật',
        };
    }

    public function getDobAttribute()
    {
        return $this->date_of_birth;
    }
}
