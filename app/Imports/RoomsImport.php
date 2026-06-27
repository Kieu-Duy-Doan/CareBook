<?php

namespace App\Imports;

use App\Models\Room;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class RoomsImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('File import không có dữ liệu (file rỗng).');
        }

        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue;
                }

                $name = trim($row[0] ?? '');
                $roomNumber = trim($row[1] ?? '');
                $building = trim($row[2] ?? '');
                $floor = trim($row[3] ?? '');
                $roomTypeInput = trim($row[4] ?? '');
                $capacity = trim($row[5] ?? '');
                $statusInput = trim($row[6] ?? '');

                if (empty($name)) {
                    continue;
                }

                // Kiểm tra trùng lặp Tên phòng
                if (Room::where('name', $name)->exists()) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Tên phòng '$name' đã tồn tại trong hệ thống.");
                }

                $isActive = true;
                if ($statusInput !== '') {
                    $val = mb_strtolower($statusInput);
                    $isActive = ($val === 'đang hoạt động' || $val === '1' || $val === 'true' || $val === 'active');
                }
                
                $roomType = 'examination';
                if (in_array(strtolower($roomTypeInput), ['examination', 'diagnostic', 'surgery', 'other'])) {
                    $roomType = strtolower($roomTypeInput);
                }

                Room::create([
                    'name' => $name,
                    'room_number' => $roomNumber ?: null,
                    'building' => $building ?: null,
                    'floor' => $floor ?: null,
                    'room_type' => $roomType,
                    'capacity' => is_numeric($capacity) ? (int)$capacity : null,
                    'is_active' => $isActive,
                ]);
            }
        });
    }
}
