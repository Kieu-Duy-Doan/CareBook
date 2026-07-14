<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicalVisit extends Model
{
    protected $fillable = [
        'appointment_id',
        'parent_visit_id',
        'doctor_profile_id',
        'room_id',
        'visit_order',
        'is_origin',
        'findings',
        'result_files',
        'refusal_reason',
        'status',
        'payment_amount',
        'payment_status',
        'payment_method',
        'collected_by',
        'paid_at',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_origin' => 'boolean',
            'result_files' => 'array',
            'payment_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function parentVisit()
    {
        return $this->belongsTo(ClinicalVisit::class, 'parent_visit_id');
    }

    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'payment_clinical_visit')
                    ->withPivot('amount_allocated')
                    ->withTimestamps();
    }
}
