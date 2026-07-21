<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\PatientProfile;
use App\Models\DoctorProfile;
use App\Models\ClinicalVisit;
use App\Models\MedicalRecord;
use App\Models\DoctorLevelFee;
use Illuminate\Support\Facades\DB;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $patients = PatientProfile::all();
        $doctors = DoctorProfile::where('doctor_type', 'clinical')->get();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'checked_in', 'examining', 'completed', 'cancelled'];

        $order = 1;
        foreach ($patients as $index => $patient) {
            $doc = $doctors->random();
            $specialtyId = DB::table('doctor_specialties')->where('doctor_profile_id', $doc->id)->value('specialty_id');
            $roomId = DB::table('specialty_rooms')->where('specialty_id', $specialtyId)->value('room_id');
            $fee = DoctorLevelFee::where('level', $doc->level)->first();
            $totalFee = $fee ? $fee->specific_price : 0;

            $status = $statuses[$index % count($statuses)];

            $appointment = Appointment::create([
                'appointment_code' => 'APT' . time() . str_pad($index, 3, '0', STR_PAD_LEFT),
                'patient_profile_id' => $patient->id,
                'booked_by_user_id' => $patient->owner_id,
                'specialty_id' => $specialtyId,
                'doctor_level' => $doc->level,
                'room_id' => $roomId,
                'doctor_profile_id' => $doc->id,
                'appointment_date' => now()->toDateString(),
                'appointment_time' => sprintf('%02d:30:00', 8 + ($index % 8)),
                'reason' => 'Khám tổng quát định kỳ',
                'status' => $status,
                'source' => 'web',
                'booking_method' => 'doctor',
                'total_fee' => $totalFee,
                'checked_in_at' => in_array($status, ['checked_in', 'examining', 'completed']) ? now() : null,
                'completed_at' => $status === 'completed' ? now() : null,
            ]);

            if (in_array($status, ['checked_in', 'examining', 'completed'])) {
                $visit = ClinicalVisit::create([
                    'appointment_id' => $appointment->id,
                    'doctor_profile_id' => $doc->id,
                    'room_id' => $roomId,
                    'visit_order' => $order++,
                    'is_origin' => true,
                    'status' => $status === 'completed' ? 'completed' : ($status === 'examining' ? 'in_progress' : 'waiting'),
                    'payment_amount' => $totalFee,
                    'payment_status' => 'pending',
                ]);

                if ($status === 'completed') {
                    MedicalRecord::create([
                        'appointment_id' => $appointment->id,
                        'doctor_profile_id' => $doc->id,
                        'diagnosis' => 'Sức khỏe bình thường, không có dấu hiệu bệnh lý nghiêm trọng.',
                        'conclusion' => 'Bệnh nhân khỏe mạnh',
                        'advice' => 'Nghỉ ngơi, uống nhiều nước',
                        'treatment_result' => 'outpatient',
                    ]);
                }
            }
        }
    }
}
