<?php

namespace App\Services;

use App\Models\Specialty;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SpecialtyService
{
    public function createSpecialty(array $data, ?string $imagePath)
    {
        return DB::transaction(function() use ($data, $imagePath) {
            $specialtyData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'display_order' => $data['display_order'] ?? 0,
                'is_active' => $data['is_active'] ?? false,
            ];

            if ($imagePath) {
                $specialtyData['image_url'] = $imagePath;
            }

            $specialty = Specialty::create($specialtyData);

            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'SPECIALTY_CREATED',
                'module' => 'specialty_management',
                'ref_type' => 'specialty',
                'ref_id' => $specialty->id,
                'description' => 'Thêm mới chuyên khoa: ' . $specialty->name,
                'ip_address' => request()->ip()
            ]);

            return $specialty;
        });
    }

    public function updateSpecialty(Specialty $specialty, array $data, ?string $imagePath)
    {
        return DB::transaction(function() use ($specialty, $data, $imagePath) {
            $updateData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'display_order' => $data['display_order'] ?? 0,
                'is_active' => $data['is_active'] ?? false,
            ];

            if ($imagePath) {
                if ($specialty->image_url) {
                    Storage::disk('public')->delete($specialty->image_url);
                }
                $updateData['image_url'] = $imagePath;
            }

            $specialty->update($updateData);

            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => 'SPECIALTY_UPDATED',
                'module' => 'specialty_management',
                'ref_type' => 'specialty',
                'ref_id' => $specialty->id,
                'description' => 'Cập nhật chuyên khoa: ' . $specialty->name,
                'ip_address' => request()->ip()
            ]);

            return $specialty;
        });
    }
}
