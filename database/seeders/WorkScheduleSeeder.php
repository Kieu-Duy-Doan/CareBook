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
        $bsAn = DoctorProfile::where('doctor_code', 'BS001')->first();
        $bsBich = DoctorProfile::where('doctor_code', 'BS002')->first();
        $bsTuan = DoctorProfile::where('doctor_code', 'BS003')->first();

        $p101 = Room::where('room_number', 'P101')->first();
        $p201 = Room::where('room_number', 'P201')->first();
        $p202 = Room::where('room_number', 'P202')->first();

        // BS An: Thứ 2 (1), 4 (3), 6 (5) — 07:30-11:30 — P101
        if ($bsAn && $p101) {
            foreach ([1, 3, 5] as $day) {
                WorkSchedule::create([
                    'doctor_profile_id' => $bsAn->id,
                    'room_id' => $p101->id,
                    'day_of_week' => $day,
                    'start_time' => '07:30:00',
                    'end_time' => '11:30:00',
                ]);
            }
        }

        // BS Bích: Thứ 3 (2), 5 (4) — 08:00-12:00 — P201
        if ($bsBich && $p201) {
            foreach ([2, 4] as $day) {
                WorkSchedule::create([
                    'doctor_profile_id' => $bsBich->id,
                    'room_id' => $p201->id,
                    'day_of_week' => $day,
                    'start_time' => '08:00:00',
                    'end_time' => '12:00:00',
                ]);
            }
        }

        // BS Tuấn: Thứ 2,3,4,5,6 — 07:00-11:00 — P202
        if ($bsTuan && $p202) {
            foreach ([1, 2, 3, 4, 5] as $day) {
                WorkSchedule::create([
                    'doctor_profile_id' => $bsTuan->id,
                    'room_id' => $p202->id,
                    'day_of_week' => $day,
                    'start_time' => '07:00:00',
                    'end_time' => '11:00:00',
                ]);
            }
        }
    }
}
