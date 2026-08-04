<?php

namespace Database\Seeders;

use App\Models\InsuranceType;
use Illuminate\Database\Seeder;

class InsuranceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['prefix' => 'TE', 'name' => 'Trẻ em dưới 6 tuổi', 'coverage_percent' => 100],
            ['prefix' => 'HT', 'name' => 'Hưu trí / Thương binh / Bệnh binh', 'coverage_percent' => 100],
            ['prefix' => 'BN', 'name' => 'Bảo trợ xã hội / Người nghèo', 'coverage_percent' => 100],
            ['prefix' => 'CK', 'name' => 'Cận nghèo', 'coverage_percent' => 95],
            ['prefix' => 'DN', 'name' => 'Doanh nghiệp / Lao động', 'coverage_percent' => 80],
            ['prefix' => 'HC', 'name' => 'Hành chính sự nghiệp', 'coverage_percent' => 80],
            ['prefix' => 'HN', 'name' => 'Hộ gia đình / Người dân tự đóng', 'coverage_percent' => 80],
            ['prefix' => 'HS', 'name' => 'Học sinh / Sinh viên', 'coverage_percent' => 80],
            ['prefix' => 'GD', 'name' => 'Hộ gia đình cận nghèo', 'coverage_percent' => 95],
            ['prefix' => 'ND', 'name' => 'Nông dân', 'coverage_percent' => 80],
        ];

        foreach ($types as $type) {
            InsuranceType::updateOrCreate(
                ['prefix' => $type['prefix']],
                $type
            );
        }
    }
}
