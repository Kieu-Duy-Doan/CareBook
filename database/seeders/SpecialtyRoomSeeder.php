<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Specialty;
use App\Models\Room;

class SpecialtyRoomSeeder extends Seeder
{
    public function run(): void
    {
        $mapping = [
            'Tim mạch' => 'P101',
            'Nhi khoa' => 'P102',
            'Thần kinh' => 'P103',
            'Da liễu' => 'P104',
            'Răng Hàm Mặt' => 'P201',
            'Nội tiêu hoá' => 'P202',
            'Mắt' => 'P203',
            'Tai Mũi Họng' => 'P204',
            'Nội tiết' => 'P301',
            'Cơ xương khớp' => 'P304',
        ];

        foreach ($mapping as $specialtyName => $roomNumber) {
            $specialty = Specialty::where('name', $specialtyName)->first();
            $room = Room::where('room_number', $roomNumber)->first();

            if ($specialty && $room) {
                DB::table('specialty_rooms')->insert([
                    'specialty_id' => $specialty->id,
                    'room_id' => $room->id,
                    'is_primary' => true,
                ]);
            }
        }
    }
}
