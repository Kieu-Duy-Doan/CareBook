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
        // 1. Admin
        User::create([
            'full_name' => 'Nguyễn Quản Trị',
            'phone' => '0900000001',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('Admin@123'),
            'role' => 'admin',
            'id_card' => '001090123456', // Nam sinh 1990, Hà Nội
        ]);

        // 2. Lễ tân
        $letan = User::create([
            'full_name' => 'Trần Thị Lễ Tân',
            'phone' => '0900000002',
            'username' => 'letan',
            'email' => 'letan@gmail.com',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
            'id_card' => '001195123457', // Nữ sinh 1995
        ]);
        StaffProfile::create([
            'user_id' => $letan->id,
            'employee_code' => 'LT001',
            'position' => 'Lễ tân',
        ]);

        // 3. Doctors (10 Doctors)
        $doctors = [
            ['name' => 'Nguyễn Văn An', 'username' => 'bs_an', 'rank' => 'none', 'degree' => 'BSCK1', 'level' => 'BSCK1', 'pos' => 'ATTENDING'],
            ['name' => 'Trần Thị Bích', 'username' => 'bs_bich', 'rank' => 'none', 'degree' => 'ThS', 'level' => 'ThS', 'pos' => 'DEPARTMENT_HEAD'],
            ['name' => 'Lê Minh Tuấn', 'username' => 'bs_tuan', 'rank' => 'PGS', 'degree' => 'TS', 'level' => 'PGS', 'pos' => 'EXPERT'],
            ['name' => 'Hoàng Ngọc Hà', 'username' => 'bs_ha', 'rank' => 'none', 'degree' => 'BS', 'level' => 'BS', 'pos' => 'INTERN'],
            ['name' => 'Phạm Đức Đam', 'username' => 'bs_dam', 'rank' => 'none', 'degree' => 'BSCK2', 'level' => 'BSCK2', 'pos' => 'CONSULTANT'],
            ['name' => 'Ngô Bảo Châu', 'username' => 'bs_chau', 'rank' => 'GS', 'degree' => 'TS', 'level' => 'GS', 'pos' => 'EXPERT'],
            ['name' => 'Vũ Thu Thủy', 'username' => 'bs_thuy', 'rank' => 'none', 'degree' => 'BS', 'level' => 'BS', 'pos' => 'ATTENDING'],
            ['name' => 'Đinh Tuấn Anh', 'username' => 'bs_tuananh', 'rank' => 'none', 'degree' => 'BSCK1', 'level' => 'BSCK1', 'pos' => 'ATTENDING'],
            ['name' => 'Lý Thảo Tâm', 'username' => 'bs_tam', 'rank' => 'none', 'degree' => 'ThS', 'level' => 'ThS', 'pos' => 'ATTENDING'],
            ['name' => 'Châu Kiều Oanh', 'username' => 'bs_oanh', 'rank' => 'none', 'degree' => 'BSCK2', 'level' => 'BSCK2', 'pos' => 'DEPARTMENT_HEAD'],
        ];

        foreach ($doctors as $index => $doc) {
            $user = User::create([
                'full_name' => $doc['name'],
                'phone' => '09000001' . sprintf('%02d', $index),
                'username' => $doc['username'],
                'email' => $doc['username'] . '@gmail.com',
                'password' => Hash::make('Bacsi@123'),
                'role' => 'doctor',
                'id_card' => '0790851' . sprintf('%05d', $index), // Nam/Nữ sinh 1985, TP.HCM
            ]);

            DoctorProfile::create([
                'user_id' => $user->id,
                'doctor_code' => 'BS' . sprintf('%03d', $index + 1),
                'doctor_type' => 'clinical',
                'academic_rank' => $doc['rank'],
                'degree' => $doc['degree'],
                'current_position' => $doc['pos'],
                'level' => $doc['level'],
                'experience_years' => rand(5, 20),
                'expertise' => 'Khám và điều trị chuyên sâu',
            ]);
        }

        // 4. Patients (10 Patients)
        $patients = [
            ['name' => 'Nguyễn Thị Mai', 'gender' => 'female', 'dob' => '1990-05-15', 'cccd' => '001190123450', 'bhyt' => 'DN4011234567890'],
            ['name' => 'Trần Văn Hùng', 'gender' => 'male', 'dob' => '1985-08-22', 'cccd' => '079085123451', 'bhyt' => 'HT3791234567891'],
            ['name' => 'Lê Bích Ngọc', 'gender' => 'female', 'dob' => '1998-12-01', 'cccd' => '048198123452', 'bhyt' => '0123456789'], // Thẻ mới
            ['name' => 'Phạm Hoàng Long', 'gender' => 'male', 'dob' => '2000-02-14', 'cccd' => '001000123453', 'bhyt' => '0987654321'], // Thẻ mới
            ['name' => 'Võ Thị Sáu', 'gender' => 'female', 'dob' => '1975-04-30', 'cccd' => '079175123454', 'bhyt' => 'HC2791234567892'],
            ['name' => 'Đặng Kim Sơn', 'gender' => 'male', 'dob' => '1992-09-02', 'cccd' => '001092123455', 'bhyt' => 'DN4011234567893'],
            ['name' => 'Bùi Thu Hà', 'gender' => 'female', 'dob' => '1988-11-20', 'cccd' => '079188123456', 'bhyt' => 'DN4791234567894'],
            ['name' => 'Đỗ Xuân Trường', 'gender' => 'male', 'dob' => '1995-03-26', 'cccd' => '001095123457', 'bhyt' => '1122334455'],
            ['name' => 'Hoàng Phúc', 'gender' => 'male', 'dob' => '2010-06-01', 'cccd' => '079210123458', 'bhyt' => 'TE1791234567895'],
            ['name' => 'Ngô Phương Lan', 'gender' => 'female', 'dob' => '1982-10-10', 'cccd' => '001182123459', 'bhyt' => '5566778899'],
        ];

        foreach ($patients as $index => $pat) {
            $user = User::create([
                'full_name' => $pat['name'],
                'phone' => '09000002' . sprintf('%02d', $index),
                'username' => 'bn_' . ($index + 1),
                'email' => 'bn' . ($index + 1) . '@gmail.com',
                'password' => Hash::make('Patient@123'),
                'role' => 'patient',
                'id_card' => $pat['cccd'],
            ]);

            PatientProfile::create([
                'owner_id' => $user->id,
                'full_name' => $pat['name'],
                'date_of_birth' => $pat['dob'],
                'gender' => $pat['gender'],
                'id_card' => $pat['cccd'],
                'phone' => $user->phone,
                'insurance_code' => $pat['bhyt'],
                'is_self' => true,
            ]);
        }
    }
}
