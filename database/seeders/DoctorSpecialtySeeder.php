<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\DoctorProfile;
use App\Models\Specialty;

class DoctorSpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $mapping = [
            'BS001' => 'Tim mạch',
            'BS002' => 'Răng Hàm Mặt',
            'BS003' => 'Nội tiêu hoá',
        ];

        foreach ($mapping as $doctorCode => $specialtyName) {
            $doctor = DoctorProfile::where('doctor_code', $doctorCode)->first();
            $specialty = Specialty::where('name', $specialtyName)->first();

            if ($doctor && $specialty) {
                DB::table('doctor_specialties')->insert([
                    'doctor_profile_id' => $doctor->id,
                    'specialty_id' => $specialty->id,
                    'is_primary' => true,
                ]);
            }
        }
    }
}
