<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StaffProfile;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'full_name' => 'Nguyễn Quản Trị',
            'phone' => '0900000001',
            'username' => 'admin',
            'password' => Hash::make('Admin@123'),
            'role' => 'admin',
        ]);

        // Receptionists
        $letan1 = User::create([
            'full_name' => 'Trần Thị Lễ Tân',
            'phone' => '0900000002',
            'username' => 'letan1',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan1->id,
            'employee_code' => 'LT001',
            'position' => 'Lễ tân',
        ]);

        $letan2 = User::create([
            'full_name' => 'Phạm Văn Tiếp Tân',
            'phone' => '0900000003',
            'username' => 'letan2',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan2->id,
            'employee_code' => 'LT002',
            'position' => 'Lễ tân',
        ]);

        // Doctors
        $bs1 = User::create([
            'full_name' => 'Nguyễn Văn An',
            'phone' => '0900000010',
            'username' => 'bs_an',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create([
            'user_id' => $bs1->id,
            'doctor_code' => 'BS001',
            'academic_title' => 'TS.',
            'level' => 'TS',
        ]);

        $bs2 = User::create([
            'full_name' => 'Trần Thị Bích',
            'phone' => '0900000011',
            'username' => 'bs_bich',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create([
            'user_id' => $bs2->id,
            'doctor_code' => 'BS002',
            'academic_title' => 'PGS.TS.',
            'level' => 'PGS',
        ]);

        $bs3 = User::create([
            'full_name' => 'Lê Minh Tuấn',
            'phone' => '0900000012',
            'username' => 'bs_tuan',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create([
            'user_id' => $bs3->id,
            'doctor_code' => 'BS003',
            'academic_title' => 'ThS.',
            'level' => 'ThS',
        ]);

        // Patients
        $bn1 = User::create([
            'full_name' => 'Nguyễn Thị Mai',
            'phone' => '0900000020',
            'username' => 'bn_mai',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'owner_id' => $bn1->id,
            'full_name' => 'Nguyễn Thị Mai',
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'owner_id' => $bn1->id,
            'full_name' => 'Nguyễn Bé Ngoan',
            'date_of_birth' => '2015-10-20',
            'gender' => 'female',
            'is_self' => false,
        ]);

        $bn2 = User::create([
            'full_name' => 'Trần Văn Hùng',
            'phone' => '0900000021',
            'username' => 'bn_hung',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'owner_id' => $bn2->id,
            'full_name' => 'Trần Văn Hùng',
            'date_of_birth' => '1985-08-22',
            'gender' => 'male',
            'is_self' => true,
        ]);
    }
}
