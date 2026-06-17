<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'doctor_code',
        'academic_title',
        'level',
        'expertise',
        'experience_years',
        'license_number',
        'bio',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class, 'doctor_specialties', 'doctor_profile_id', 'specialty_id')
                    ->withPivot('is_primary');
    }

    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    // Accessors
    public function getFullTitleAttribute(): string
    {
        $title = $this->academic_title ? $this->academic_title . ' ' : '';
        return $title . ($this->user?->full_name ?? '');
    }

    public function getLevelLabelAttribute(): string
    {
        return match($this->level) {
            'BS'    => 'Bác sĩ',
            'BSCK1' => 'Bác sĩ CK1',
            'BSCK2' => 'Bác sĩ CK2',
            'ThS'   => 'Thạc sĩ',
            'TS'    => 'Tiến sĩ',
            'PGS'   => 'Phó Giáo sư',
            'GS'    => 'Giáo sư',
            default => $this->level,
        };
    }

    public function getPrimarySpecialtyAttribute(): ?Specialty
    {
        return $this->specialties->where('pivot.is_primary', 1)->first();
    }
}
