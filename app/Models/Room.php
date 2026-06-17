<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'room_number',
        'building',
        'floor',
        'room_type',
        'capacity',
        'is_active',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class, 'specialty_rooms')->withPivot('is_primary');
    }

    public function workSchedules()
    {
        return $this->hasMany(WorkSchedule::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
