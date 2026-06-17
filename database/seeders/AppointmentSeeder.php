<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\PatientProfile;
use App\Models\DoctorProfile;
use App\Models\User;
use App\Models\Specialty;
use App\Models\Room;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $patient = PatientProfile::first();
        $user = User::where('role', 'patient')->first();
        
        $bsAn = DoctorProfile::where('doctor_code', 'BS001')->first();
        $bsBich = DoctorProfile::where('doctor_code', 'BS002')->first();
        
        $tm = Specialty::where('name', 'Tim mạch')->first();
        $rhm = Specialty::where('name', 'Răng Hàm Mặt')->first();
        
        $p101 = Room::where('room_number', 'P101')->first();
        $p201 = Room::where('room_number', 'P201')->first();

        $today = date('Y-m-d');

        $appointments = [
            [
                'code' => 'APT' . time() . '1',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '08:00:00', 'status' => 'pending'
            ],
            [
                'code' => 'APT' . time() . '2',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '08:30:00', 'status' => 'checked_in'
            ],
            [
                'code' => 'APT' . time() . '3',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '09:00:00', 'status' => 'examining'
            ],
            [
                'code' => 'APT' . time() . '4',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '09:30:00', 'status' => 'completed'
            ],
            [
                'code' => 'APT' . time() . '5',
                'doc' => $bsAn, 'spec' => $tm, 'room' => $p101,
                'time' => '10:00:00', 'status' => 'cancelled'
            ],
            [
                'code' => 'APT' . time() . '6',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '08:15:00', 'status' => 'pending'
            ],
            [
                'code' => 'APT' . time() . '7',
                'doc' => $bsBich, 'spec' => $rhm, 'room' => $p201,
                'time' => '08:45:00', 'status' => 'absent'
            ],
        ];

        foreach ($appointments as $appt) {
            Appointment::create([
                'appointment_code' => $appt['code'],
                'patient_profile_id' => $patient->id,
                'booked_by_user_id' => $user->id,
                'specialty_id' => $appt['spec']->id,
                'doctor_level' => $appt['doc']->level,
                'room_id' => $appt['room']->id,
                'doctor_profile_id' => $appt['doc']->id,
                'appointment_date' => $today,
                'appointment_time' => $appt['time'],
                'reason' => 'Khám tổng quát định kỳ',
                'status' => $appt['status'],
                'source' => 'web',
            ]);
        }
    }
}
