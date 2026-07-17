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
        'doctor_type',
        'academic_rank',
        'degree',
        'current_position',
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
        $title = '';
        if ($this->academic_rank && $this->academic_rank !== 'none') {
            $title .= $this->academic_rank . '. ';
        }
        
        $degreeLabel = match($this->degree) {
            'BS' => 'BS',
            'ThS' => 'ThS',
            'TS' => 'TS',
            'BSCK1' => 'BSCK1',
            'BSCK2' => 'BSCK2',
            'BSNT' => 'BSNT',
            default => 'BS',
        };
        $title .= $degreeLabel . '. ';

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

    public function getPositionLabelAttribute(): string
    {
        return match($this->current_position) {
            'INTERN' => 'Bác sĩ Nội trú / Thực hành',
            'ATTENDING' => 'Bác sĩ Điều trị',
            'CONSULTANT' => 'Bác sĩ Hội chẩn / Ca trưởng',
            'DEPARTMENT_HEAD' => 'Phó khoa / Trưởng khoa Lâm sàng',
            'EXPERT' => 'Giám đốc Chuyên môn / Chuyên gia',
            default => 'Bác sĩ Điều trị',
        };
    }

    public function getPrimarySpecialtyAttribute(): ?Specialty
    {
        return $this->specialties->where('pivot.is_primary', 1)->first();
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->doctor_type) {
            'clinical' => 'Lâm sàng',
            'paraclinical' => 'Cận lâm sàng',
            default => 'Lâm sàng',
        };
    }
}
