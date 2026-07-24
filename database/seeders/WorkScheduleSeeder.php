<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DoctorProfile;
use App\Models\Room;
use App\Models\WorkSchedule;
use Illuminate\Support\Facades\DB;

class WorkScheduleSeeder extends Seeder
{
    // Ca làm việc
    private const MORNING   = ['start' => '07:00:00', 'end' => '11:00:00', 'label' => 'morning'];
    private const AFTERNOON = ['start' => '13:00:00', 'end' => '17:00:00', 'label' => 'afternoon'];

    // T2 đến T7
    private const WORK_DAYS = [2, 3, 4, 5, 6, 7];

    public function run(): void
    {
        // =========================================================
        // 1. Bác sĩ LÂM SÀNG — ca xen kẽ theo doctor_code
        //    BS001, BS003, BS005, BS007, BS009 → Ca SÁNG
        //    BS002, BS004, BS006, BS008, BS010 → Ca CHIỀU
        // =========================================================
        $clinicalDoctors = DoctorProfile::where('doctor_type', 'clinical')
            ->orderBy('doctor_code')
            ->get();

        foreach ($clinicalDoctors as $index => $doctor) {
            // Lấy phòng khám từ chuyên khoa chính
            $specialtyId = DB::table('doctor_specialties')
                ->where('doctor_profile_id', $doctor->id)
                ->where('is_primary', true)
                ->value('specialty_id');

            if (!$specialtyId) continue;

            $roomId = DB::table('specialty_rooms')
                ->where('specialty_id', $specialtyId)
                ->where('is_primary', true)
                ->value('room_id');

            if (!$roomId) continue;

            // Ca xen kẽ: index chẵn (0,2,4,...) = sáng, lẻ (1,3,5,...) = chiều
            $shift = ($index % 2 === 0) ? self::MORNING : self::AFTERNOON;

            foreach (self::WORK_DAYS as $day) {
                WorkSchedule::create([
                    'doctor_profile_id'    => $doctor->id,
                    'room_id'              => $roomId,
                    'shift_label'          => $shift['label'],
                    'day_of_week'          => $day,
                    'start_time'           => $shift['start'],
                    'end_time'             => $shift['end'],
                    'slot_duration_minutes' => 15,
                    'max_slots'            => 20,
                    'is_active'            => true,
                ]);
            }
        }

        // =========================================================
        // 2. Bác sĩ CẬN LÂM SÀNG — gán trực tiếp vào phòng xét nghiệm
        //    Mỗi phòng: BS _S = ca sáng, BS _C = ca chiều
        // =========================================================
        $paraclinicalMapping = [
            // [doctor_code, room_number, shift]
            ['BS011', 'SA01', self::MORNING],   // Siêu âm 4D — sáng
            ['BS012', 'SA01', self::AFTERNOON], // Siêu âm 4D — chiều
            ['BS013', 'XN01', self::MORNING],   // Xét nghiệm Máu — sáng
            ['BS014', 'XN01', self::AFTERNOON], // Xét nghiệm Máu — chiều
            ['BS015', 'XQ01', self::MORNING],   // XQuang — sáng
            ['BS016', 'XQ01', self::AFTERNOON], // XQuang — chiều
        ];

        foreach ($paraclinicalMapping as [$doctorCode, $roomNumber, $shift]) {
            $doctor = DoctorProfile::where('doctor_code', $doctorCode)->first();
            $room   = Room::where('room_number', $roomNumber)->first();

            if (!$doctor || !$room) continue;

            foreach (self::WORK_DAYS as $day) {
                WorkSchedule::create([
                    'doctor_profile_id'    => $doctor->id,
                    'room_id'              => $room->id,
                    'shift_label'          => $shift['label'],
                    'day_of_week'          => $day,
                    'start_time'           => $shift['start'],
                    'end_time'             => $shift['end'],
                    'slot_duration_minutes' => 15,
                    'max_slots'            => 20,
                    'is_active'            => true,
                ]);
            }
        }
    }
}
