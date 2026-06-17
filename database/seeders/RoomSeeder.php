<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            ['name' => 'Khám Tim mạch', 'room_number' => 'P101', 'building' => 'K1'],
            ['name' => 'Khám Nhi', 'room_number' => 'P102', 'building' => 'K1'],
            ['name' => 'Khám Thần kinh', 'room_number' => 'P103', 'building' => 'K1'],
            ['name' => 'Khám Da liễu', 'room_number' => 'P104', 'building' => 'K1'],

            ['name' => 'Khám Răng Hàm Mặt', 'room_number' => 'P201', 'building' => 'K2'],
            ['name' => 'Khám Nội tiêu hoá', 'room_number' => 'P202', 'building' => 'K2'],
            ['name' => 'Khám Mắt', 'room_number' => 'P203', 'building' => 'K2'],
            ['name' => 'Khám Tai Mũi Họng', 'room_number' => 'P204', 'building' => 'K2'],

            ['name' => 'XQuang', 'room_number' => 'P301', 'building' => 'K3', 'room_type' => 'diagnostic'],
            ['name' => 'Xét nghiệm', 'room_number' => 'P302', 'building' => 'K3', 'room_type' => 'diagnostic'],
            ['name' => 'Siêu âm', 'room_number' => 'P303', 'building' => 'K3', 'room_type' => 'diagnostic'],
            ['name' => 'Khám Cơ xương khớp', 'room_number' => 'P304', 'building' => 'K3'],
        ];

        foreach ($rooms as $room) {
            Room::create([
                'name' => $room['name'],
                'room_number' => $room['room_number'],
                'building' => $room['building'],
                'room_type' => $room['room_type'] ?? 'examination',
                'floor' => substr($room['room_number'], 1, 1),
            ]);
        }
    }
}
