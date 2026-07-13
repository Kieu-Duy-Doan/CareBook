<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DoctorLevelFee;

class DoctorLevelFeeSeeder extends Seeder
{
    public function run(): void
    {
        $fees = [
            ['level' => 'BS', 'base_price' => 150000, 'specific_price' => 200000],
            ['level' => 'BSCK1', 'base_price' => 200000, 'specific_price' => 250000],
            ['level' => 'BSCK2', 'base_price' => 250000, 'specific_price' => 300000],
            ['level' => 'ThS', 'base_price' => 250000, 'specific_price' => 300000],
            ['level' => 'TS', 'base_price' => 300000, 'specific_price' => 400000],
            ['level' => 'PGS', 'base_price' => 400000, 'specific_price' => 500000],
            ['level' => 'GS', 'base_price' => 500000, 'specific_price' => 700000],
        ];

        foreach ($fees as $fee) {
            DoctorLevelFee::updateOrCreate(
                ['level' => $fee['level']],
                $fee
            );
        }
    }
}
