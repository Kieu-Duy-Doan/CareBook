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
        $bs4 = User::create([
            'full_name' => 'Hoàng Ngọc Hà',
            'phone' => '0900000013',
            'username' => 'bs_ha',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs4->id, 'doctor_code' => 'BS004', 'academic_title' => 'BSCK1.', 'level' => 'BSCK1']);

        $bs5 = User::create([
            'full_name' => 'Phạm Đức Đam',
            'phone' => '0900000014',
            'username' => 'bs_dam',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs5->id, 'doctor_code' => 'BS005', 'academic_title' => 'BSCK2.', 'level' => 'BSCK2']);

        $bs6 = User::create([
            'full_name' => 'Ngô Bảo Châu',
            'phone' => '0900000015',
            'username' => 'bs_chau',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs6->id, 'doctor_code' => 'BS006', 'academic_title' => 'GS.', 'level' => 'GS']);

        $bs7 = User::create([
            'full_name' => 'Vũ Thu Thủy',
            'phone' => '0900000016',
            'username' => 'bs_thuy',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs7->id, 'doctor_code' => 'BS007', 'academic_title' => 'BS.', 'level' => 'BS']);

        $bs8 = User::create([
            'full_name' => 'Đinh Tuấn Anh',
            'phone' => '0900000017',
            'username' => 'bs_tuananh',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs8->id, 'doctor_code' => 'BS008', 'academic_title' => 'ThS.', 'level' => 'ThS']);

        $bs9 = User::create([
            'full_name' => 'Lý Thảo Tâm',
            'phone' => '0900000018',
            'username' => 'bs_tam',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs9->id, 'doctor_code' => 'BS009', 'academic_title' => 'TS.', 'level' => 'TS']);

        $bs10 = User::create([
            'full_name' => 'Châu Kiều Oanh',
            'phone' => '0900000019',
            'username' => 'bs_oanh',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs10->id, 'doctor_code' => 'BS010', 'academic_title' => 'BS.', 'level' => 'BS']);

        // Additional doctors for duplicate specialties (to test alternative doctor suggestions)
        $bs11 = User::create([
            'full_name' => 'Lê Thanh Bình',
            'phone' => '0900000031',
            'username' => 'bs_binh',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs11->id, 'doctor_code' => 'BS011', 'academic_title' => 'ThS.', 'level' => 'ThS']);

        $bs12 = User::create([
            'full_name' => 'Nguyễn Bích Phương',
            'phone' => '0900000032',
            'username' => 'bs_phuong',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs12->id, 'doctor_code' => 'BS012', 'academic_title' => 'BS.', 'level' => 'BS']);

        $bs13 = User::create([
            'full_name' => 'Võ Đình Tuấn',
            'phone' => '0900000033',
            'username' => 'bs_dinhtuan',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs13->id, 'doctor_code' => 'BS013', 'academic_title' => 'BSCK1.', 'level' => 'BSCK1']);

        $bs14 = User::create([
            'full_name' => 'Trịnh Hồng Ngọc',
            'phone' => '0900000034',
            'username' => 'bs_ngoc',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs14->id, 'doctor_code' => 'BS014', 'academic_title' => 'TS.', 'level' => 'TS']);

        $bs15 = User::create([
            'full_name' => 'Phan Nhật Nam',
            'phone' => '0900000035',
            'username' => 'bs_nam',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs15->id, 'doctor_code' => 'BS015', 'academic_title' => 'BSCK2.', 'level' => 'BSCK2']);

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
