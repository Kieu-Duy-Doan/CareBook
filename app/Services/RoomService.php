<?php

namespace App\Services;

use App\Models\Room;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoomService
{
    public function createRoom(array $data, array $specialtyIds = [])
    {
        return DB::transaction(function () use ($data, $specialtyIds) {
            $room = Room::create([
                'name' => $data['name'],
                'room_number' => $data['room_number'] ?? null,
                'building' => $data['building'] ?? null,
                'floor' => $data['floor'] ?? null,
                'room_type' => $data['room_type'],
                'price' => $data['room_type'] === 'diagnostic' ? ($data['price'] ?? null) : null,
                'capacity' => $data['capacity'] ?? null,
                'is_active' => $data['is_active'] ?? false,
            ]);

            if (!empty($specialtyIds)) {
                $syncData = [];
                foreach ($specialtyIds as $id) {
                    $syncData[$id] = ['is_primary' => false];
                }
                $room->specialties()->sync($syncData);
            }

            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'ROOM_CREATED',
                'module' => 'room_management',
                'ref_type' => 'room',
                'ref_id' => $room->id,
                'description' => 'Thêm mới phòng khám: ' . $room->name,
                'ip_address' => request()->ip()
            ]);
            
            return $room;
        });
    }

    public function updateRoom(Room $room, array $data, array $specialtyIds = [], bool $hasSpecialties = false)
    {
        return DB::transaction(function () use ($room, $data, $specialtyIds, $hasSpecialties) {
            $room->update([
                'name' => $data['name'],
                'room_number' => $data['room_number'] ?? null,
                'building' => $data['building'] ?? null,
                'floor' => $data['floor'] ?? null,
                'room_type' => $data['room_type'],
                'price' => $data['room_type'] === 'diagnostic' ? ($data['price'] ?? null) : null,
                'capacity' => $data['capacity'] ?? null,
                'is_active' => $data['is_active'] ?? false,
            ]);

            if ($hasSpecialties) {
                $syncData = [];
                foreach ($specialtyIds as $spId) {
                    $syncData[$spId] = ['is_primary' => false];
                }
                $room->specialties()->sync($syncData);
            } else {
                $room->specialties()->detach();
            }

            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'ROOM_UPDATED',
                'module' => 'room_management',
                'ref_type' => 'room',
                'ref_id' => $room->id,
                'description' => 'Cập nhật phòng khám: ' . $room->name,
                'ip_address' => request()->ip()
            ]);
            
            return $room;
        });
    }
}
