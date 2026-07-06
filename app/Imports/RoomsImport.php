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

                $id = trim($row[0] ?? '');
                $name = trim($row[1] ?? '');
                $roomNumber = trim($row[2] ?? '');
                $building = trim($row[3] ?? '');
                $floor = trim($row[4] ?? '');
                $roomTypeInput = trim($row[5] ?? '');
                $capacity = trim($row[6] ?? '');
                $statusInput = trim($row[7] ?? '');

                if (empty($name)) {
                    continue;
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

                if ($id) {
                    $room = Room::find($id);
                    if ($room) {
                        if ($name !== $room->name && Room::where('name', $name)->exists()) {
                            throw new \Exception("Dòng " . ($index + 1) . ": Tên phòng '$name' đã tồn tại trong hệ thống.");
                        }

                        $room->fill([
                            'name' => $name,
                            'room_number' => $roomNumber ?: null,
                            'building' => $building ?: null,
                            'floor' => $floor ?: null,
                            'room_type' => $roomType,
                            'capacity' => is_numeric($capacity) ? (int)$capacity : null,
                            'is_active' => $isActive,
                        ]);

                        if ($room->isDirty()) {
                            $room->save();
                        }
                        continue;
                    }
                }

                // Create
                if (Room::where('name', $name)->exists()) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Tên phòng '$name' đã tồn tại trong hệ thống.");
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
