<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'phone',
        'username',
        'id_card',
        'email',
        'password',
        'role',
        'avatar_url',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function patientProfiles()
    {
        return $this->hasMany(PatientProfile::class, 'owner_id');
    }

    public function doctorProfile()
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    public function isPatient(): bool
    {
        return $this->role === 'patient';
    }

    public function getAvatarInitialsAttribute(): string
    {
        if (!$this->full_name) {
            return '';
        }
        $words = explode(' ', $this->full_name);
        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr(end($words), 0, 1));
        }
        return mb_strtoupper(mb_substr($words[0], 0, 2));
    }

    public function getDisplayRoleAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Quản trị viên',
            'doctor' => 'Bác sĩ',
            'receptionist' => 'Lễ tân',
            'patient' => 'Bệnh nhân',
            default => 'Không xác định',
        };
    }
}
