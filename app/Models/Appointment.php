<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'appointment_code',
        'patient_profile_id',
        'booked_by_user_id',
        'specialty_id',
        'doctor_level',
        'room_id',
        'doctor_profile_id',
        'appointment_date',
        'appointment_time',
        'reason',
        'status',
        'source',
        'booking_method',
        'total_fee',
        'receptionist_note',
        'vital_pulse',
        'vital_systolic_bp',
        'vital_diastolic_bp',
        'vital_temperature',
        'vital_respiratory',
        'vital_spo2',
        'vital_weight_kg',
        'vital_height_cm',
        'vital_bmi',
        'vital_note',
        'measured_by',
        'checked_in_at',
        'completed_at',
        'reminded_2h',
        'reminded_30m',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'vital_temperature' => 'decimal:1',
            'vital_spo2' => 'decimal:1',
            'vital_weight_kg' => 'decimal:2',
            'vital_height_cm' => 'decimal:2',
            'vital_bmi' => 'decimal:2',
            'checked_in_at' => 'datetime',
            'completed_at' => 'datetime',
            'reminded_2h' => 'boolean',
            'reminded_30m' => 'boolean',
        ];
    }

    public function patientProfile()
    {
        return $this->belongsTo(PatientProfile::class);
    }

    public function bookedByUser()
    {
        return $this->belongsTo(User::class, 'booked_by_user_id');
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class);
    }

    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_profile_id');
    }

    public function measuredBy()
    {
        return $this->belongsTo(User::class, 'measured_by');
    }

    public function clinicalVisits()
    {
        return $this->hasMany(ClinicalVisit::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function logs()
    {
        return $this->hasMany(AppointmentLog::class)->orderBy('created_at', 'desc');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'Đã tiếp nhận',
            'checked_in' => 'Đã checkin',
            'examining'  => 'Đang khám',
            'completed'  => 'Hoàn thành',
            'cancelled'  => 'Đã huỷ',
            'absent'     => 'Vắng mặt',
            default      => 'Không xác định',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'yellow',
            'checked_in' => 'blue',
            'examining'  => 'purple',
            'completed'  => 'green',
            'cancelled'  => 'red',
            'absent'     => 'gray',
            default      => 'gray',
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match($this->source) {
            'web'     => 'Web',
            'counter' => 'Quầy lễ tân',
            'chatbot' => 'Chatbot',
            default   => 'Không xác định',
        };
    }
}
