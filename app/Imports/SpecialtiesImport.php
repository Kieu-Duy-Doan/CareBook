<?php

namespace App\Imports;

use App\Models\Specialty;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class SpecialtiesImport implements ToCollection
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
                $description = trim($row[1] ?? '');
                $displayOrder = trim($row[2] ?? '');
                $statusInput = trim($row[3] ?? '');

                if (empty($name)) {
                    continue;
                }

                // Kiểm tra trùng lặp
                if (Specialty::where('name', $name)->exists()) {
                    throw new \Exception("Dòng " . ($index + 1) . ": Tên chuyên khoa '$name' đã tồn tại trong hệ thống.");
                }

                $isActive = true;
                if ($statusInput !== '') {
                    $val = mb_strtolower($statusInput);
                    $isActive = ($val === 'đang hoạt động' || $val === '1' || $val === 'true' || $val === 'active');
                }

                Specialty::create([
                    'name' => $name,
                    'description' => $description ?: null,
                    'display_order' => is_numeric($displayOrder) ? (int)$displayOrder : 0,
                    'is_active' => $isActive,
                ]);
            }
        });
    }
}
