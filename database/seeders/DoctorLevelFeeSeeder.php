<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DoctorLevelFee;

class DoctorLevelFeeSeeder extends Seeder
{
    public function run(): void
    {
        $fees = [
            ['level' => 'BS', 'base_price' => 2000, 'specific_price' => 3000],
            ['level' => 'BSCK1', 'base_price' => 3000, 'specific_price' => 4000],
            ['level' => 'BSCK2', 'base_price' => 4000, 'specific_price' => 5000],
            ['level' => 'ThS', 'base_price' => 5000, 'specific_price' => 6000],
            ['level' => 'TS', 'base_price' => 6000, 'specific_price' => 7000],
            ['level' => 'PGS', 'base_price' => 7000, 'specific_price' => 8000],
            ['level' => 'GS', 'base_price' => 8000, 'specific_price' => 10000],
        ];

        foreach ($fees as $fee) {
            DoctorLevelFee::updateOrCreate(
                ['level' => $fee['level']],
                $fee
            );
        }
    }
}
