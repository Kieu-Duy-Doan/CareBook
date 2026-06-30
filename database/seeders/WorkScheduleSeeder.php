<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DoctorProfile;
use App\Models\Room;
use App\Models\WorkSchedule;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = DoctorProfile::all();
        $rooms = Room::all();

        if ($doctors->isEmpty() || $rooms->isEmpty()) {
            return;
        }

        foreach ($doctors as $i => $doctor) {
            $room = $rooms[$i % $rooms->count()];

            // Phân lịch từ T2 đến T6 cho mỗi bác sĩ
            foreach ([2, 3, 4, 5, 6] as $day) {
                // Sáng
                WorkSchedule::create([
                    'doctor_profile_id' => $doctor->id,
                    'room_id' => $room->id,
                    'day_of_week' => $day, // 2=T2, 3=T3,..., 7=T7, 1=CN
                    'start_time' => '07:00:00',
                    'end_time' => '11:00:00',
                ]);
                // Chiều
                WorkSchedule::create([
                    'doctor_profile_id' => $doctor->id,
                    'room_id' => $room->id,
                    'day_of_week' => $day,
                    'start_time' => '13:00:00',
                    'end_time' => '17:00:00',
                ]);
            }
        }
    }
}
