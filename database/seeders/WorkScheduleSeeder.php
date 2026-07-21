<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DoctorProfile;
use App\Models\WorkSchedule;
use Illuminate\Support\Facades\DB;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = DoctorProfile::where('doctor_type', 'clinical')->get();

        foreach ($doctors as $doctor) {
            // Lấy chuyên khoa chính
            $specialtyId = DB::table('doctor_specialties')
                ->where('doctor_profile_id', $doctor->id)
                ->where('is_primary', true)
                ->value('specialty_id');

            if (!$specialtyId) continue;

            // Lấy phòng khám tương ứng
            $roomId = DB::table('specialty_rooms')
                ->where('specialty_id', $specialtyId)
                ->where('is_primary', true)
                ->value('room_id');

            if (!$roomId) continue;

            // Phân lịch từ T2 đến T7
            foreach ([2, 3, 4, 5, 6, 7] as $day) {
                // Sáng 7:30 - 11:30
                WorkSchedule::create([
                    'doctor_profile_id' => $doctor->id,
                    'room_id' => $roomId,
                    'day_of_week' => $day,
                    'start_time' => '07:30:00',
                    'end_time' => '11:30:00',
                ]);
                // Chiều 13:30 - 17:30
                WorkSchedule::create([
                    'doctor_profile_id' => $doctor->id,
                    'room_id' => $roomId,
                    'day_of_week' => $day,
                    'start_time' => '13:30:00',
                    'end_time' => '17:30:00',
                ]);
            }
        }
    }
}
