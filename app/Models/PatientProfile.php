<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientProfile extends Model
{
    protected $fillable = [
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
}
