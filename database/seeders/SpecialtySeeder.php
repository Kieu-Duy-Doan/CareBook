<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Specialty;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            'Tim mạch', 'Răng Hàm Mặt', 'Nội tiêu hoá', 'Nhi khoa',
            'Thần kinh', 'Cơ xương khớp', 'Da liễu', 'Mắt',
            'Tai Mũi Họng', 'Nội tiết'
        ];

        foreach ($specialties as $index => $name) {
            Specialty::create([
                'name' => $name,
                'display_order' => $index + 1,
            ]);
        }
    }
}
