<?php

namespace App\Imports;

use App\Models\WorkSchedule;
use App\Models\DoctorProfile;
use App\Models\Room;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class WorkSchedulesImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('File import không có dữ liệu (file rỗng).');
        }

        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue; // Skip heading
                }

                $doctorCode = trim($row[0] ?? '');
                $roomName = trim($row[1] ?? '');
                $dayOfWeekInput = trim($row[2] ?? '');
                $shiftInput = trim($row[3] ?? '');
                $duration = trim($row[4] ?? '');
                $maxSlots = trim($row[5] ?? '');
                $statusInput = trim($row[6] ?? '');

                if (empty($doctorCode) || empty($roomName) || empty($dayOfWeekInput) || empty($shiftInput)) {
                    continue;
                }

                $doctor = DoctorProfile::where('doctor_code', $doctorCode)->first();
                if (!$doctor) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Không tìm thấy Bác sĩ có mã '$doctorCode'.");
                }

                $room = Room::where('name', $roomName)->first();
                if (!$room) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Không tìm thấy Phòng có tên '$roomName'.");
                }

                $dayOfWeek = (int)$dayOfWeekInput;
                if ($dayOfWeek < 1 || $dayOfWeek > 7) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Thứ '$dayOfWeekInput' không hợp lệ (phải từ 1-7).");
                }

                $shift = mb_strtolower($shiftInput);
                if ($shift !== 'sáng' && $shift !== 'chiều') {
                    throw new \Exception("Dòng " . ($index + 1) . ": Ca làm việc '$shiftInput' không hợp lệ (chỉ nhận Sáng/Chiều).");
                }

                $startTime = $shift === 'sáng' ? '07:00:00' : '13:00:00';
                $endTime = $shift === 'sáng' ? '11:00:00' : '17:00:00';

                // Kiểm tra trùng lặp Bác sĩ ở một phòng vào ngày này
                $existsRoom = WorkSchedule::where('doctor_profile_id', $doctor->id)
                    ->where('room_id', $room->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->exists();

                if ($existsRoom) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Bác sĩ '$doctorCode' đã có lịch tại phòng '$roomName' vào Thứ $dayOfWeek.");
                }

                // Kiểm tra trùng lặp thời gian của Bác sĩ
                $existsTime = WorkSchedule::where('doctor_profile_id', $doctor->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('is_active', true)
                    ->where(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<', $endTime)
                              ->where('end_time', '>', $startTime);
                    })->exists();
                
                if ($existsTime) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Bác sĩ '$doctorCode' đã có ca $shiftInput vào Thứ $dayOfWeek.");
                }

                // Kiểm tra trùng lặp phòng 
                $existsRoomOverlap = WorkSchedule::where('room_id', $room->id)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('is_active', true)
                    ->where(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<', $endTime)
                              ->where('end_time', '>', $startTime);
                    })->exists();

                if ($existsRoomOverlap) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Phòng '$roomName' đã có người trực ca $shiftInput vào Thứ $dayOfWeek.");
                }

                $isActive = true;
                if ($statusInput !== '') {
                    $val = mb_strtolower($statusInput);
                    $isActive = ($val === 'đang hoạt động' || $val === '1' || $val === 'true' || $val === 'active');
                }

                WorkSchedule::create([
                    'doctor_profile_id' => $doctor->id,
                    'room_id' => $room->id,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'slot_duration_minutes' => is_numeric($duration) ? (int)$duration : 15,
                    'max_slots' => is_numeric($maxSlots) ? (int)$maxSlots : 16,
                    'is_active' => $isActive,
                ]);
            }
        });
    }
}
