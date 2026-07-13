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
            'academic_rank' => 'none',
            'degree' => 'TS',
            'current_position' => 'ATTENDING',
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
            'academic_rank' => 'PGS',
            'degree' => 'TS',
            'current_position' => 'CONSULTANT',
            'level' => 'TS',
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
            'academic_rank' => 'none',
            'degree' => 'ThS',
            'current_position' => 'ATTENDING',
            'level' => 'ThS',
        ]);
        $bs4 = User::create([
            'full_name' => 'Hoàng Ngọc Hà',
            'phone' => '0900000013',
            'username' => 'bs_ha',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs4->id, 'doctor_code' => 'BS004', 'academic_rank' => 'none', 'degree' => 'BSCK1', 'current_position' => 'ATTENDING', 'level' => 'BSCK1']);

        $bs5 = User::create([
            'full_name' => 'Phạm Đức Đam',
            'phone' => '0900000014',
            'username' => 'bs_dam',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs5->id, 'doctor_code' => 'BS005', 'academic_rank' => 'none', 'degree' => 'BSCK2', 'current_position' => 'CONSULTANT', 'level' => 'BSCK2']);

        $bs6 = User::create([
            'full_name' => 'Ngô Bảo Châu',
            'phone' => '0900000015',
            'username' => 'bs_chau',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs6->id, 'doctor_code' => 'BS006', 'academic_rank' => 'GS', 'degree' => 'TS', 'current_position' => 'EXPERT', 'level' => 'TS']);

        $bs7 = User::create([
            'full_name' => 'Vũ Thu Thủy',
            'phone' => '0900000016',
            'username' => 'bs_thuy',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs7->id, 'doctor_code' => 'BS007', 'academic_rank' => 'none', 'degree' => 'BS', 'current_position' => 'INTERN', 'level' => 'BS']);

        $bs8 = User::create([
            'full_name' => 'Đinh Tuấn Anh',
            'phone' => '0900000017',
            'username' => 'bs_tuananh',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs8->id, 'doctor_code' => 'BS008', 'academic_rank' => 'none', 'degree' => 'ThS', 'current_position' => 'ATTENDING', 'level' => 'ThS']);

        $bs9 = User::create([
            'full_name' => 'Lý Thảo Tâm',
            'phone' => '0900000018',
            'username' => 'bs_tam',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs9->id, 'doctor_code' => 'BS009', 'academic_rank' => 'none', 'degree' => 'TS', 'current_position' => 'DEPARTMENT_HEAD', 'level' => 'TS']);

        $bs10 = User::create([
            'full_name' => 'Châu Kiều Oanh',
            'phone' => '0900000019',
            'username' => 'bs_oanh',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs10->id, 'doctor_code' => 'BS010', 'academic_rank' => 'none', 'degree' => 'BS', 'current_position' => 'INTERN', 'level' => 'BS']);

        // Additional doctors for duplicate specialties (to test alternative doctor suggestions)
        $bs11 = User::create([
            'full_name' => 'Lê Thanh Bình',
            'phone' => '0900000031',
            'username' => 'bs_binh',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs11->id, 'doctor_code' => 'BS011', 'academic_rank' => 'none', 'degree' => 'ThS', 'current_position' => 'ATTENDING', 'level' => 'ThS']);

        $bs12 = User::create([
            'full_name' => 'Nguyễn Bích Phương',
            'phone' => '0900000032',
            'username' => 'bs_phuong',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs12->id, 'doctor_code' => 'BS012', 'academic_rank' => 'none', 'degree' => 'BS', 'current_position' => 'INTERN', 'level' => 'BS']);

        $bs13 = User::create([
            'full_name' => 'Võ Đình Tuấn',
            'phone' => '0900000033',
            'username' => 'bs_dinhtuan',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs13->id, 'doctor_code' => 'BS013', 'academic_rank' => 'none', 'degree' => 'BSCK1', 'current_position' => 'ATTENDING', 'level' => 'BSCK1']);

        $bs14 = User::create([
            'full_name' => 'Trịnh Hồng Ngọc',
            'phone' => '0900000034',
            'username' => 'bs_ngoc',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs14->id, 'doctor_code' => 'BS014', 'academic_rank' => 'none', 'degree' => 'TS', 'current_position' => 'DEPARTMENT_HEAD', 'level' => 'TS']);

        $bs15 = User::create([
            'full_name' => 'Phan Nhật Nam',
            'phone' => '0900000035',
            'username' => 'bs_nam',
            'password' => Hash::make('Bacsi@123'),
            'role' => 'doctor',
        ]);
        DoctorProfile::create(['user_id' => $bs15->id, 'doctor_code' => 'BS015', 'academic_rank' => 'none', 'degree' => 'BSCK2', 'current_position' => 'CONSULTANT', 'level' => 'BSCK2']);

        // Patients
        $bn1 = User::create([
            'full_name' => 'Nguyễn Thị Mai',
            'phone' => '0900000020',
            'username' => 'bn_mai',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn1->id,
            'full_name' => 'Nguyễn Thị Mai',
            'date_of_birth' => '1990-05-15',
            'gender' => 'female',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
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
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn2->id,
            'full_name' => 'Trần Văn Hùng',
            'date_of_birth' => '1985-08-22',
            'gender' => 'male',
            'is_self' => true,
        ]);
        $letan3 = User::create([
            'full_name' => 'An Song Hường',
            'phone' => '0800000003',
            'username' => 'letan3',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan3->id,
            'employee_code' => 'LT003',
            'position' => 'Lễ tân',
        ]);

        $letan4 = User::create([
            'full_name' => 'Bác. Cầm Chính Tín',
            'phone' => '0800000004',
            'username' => 'letan4',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan4->id,
            'employee_code' => 'LT004',
            'position' => 'Lễ tân',
        ]);

        $letan5 = User::create([
            'full_name' => 'An Khải',
            'phone' => '0800000005',
            'username' => 'letan5',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan5->id,
            'employee_code' => 'LT005',
            'position' => 'Lễ tân',
        ]);

        $letan6 = User::create([
            'full_name' => 'Cụ. Lưu Diễm',
            'phone' => '0800000006',
            'username' => 'letan6',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan6->id,
            'employee_code' => 'LT006',
            'position' => 'Lễ tân',
        ]);

        $letan7 = User::create([
            'full_name' => 'Nhữ Ninh',
            'phone' => '0800000007',
            'username' => 'letan7',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan7->id,
            'employee_code' => 'LT007',
            'position' => 'Lễ tân',
        ]);

        $letan8 = User::create([
            'full_name' => 'Âu Bổng',
            'phone' => '0800000008',
            'username' => 'letan8',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan8->id,
            'employee_code' => 'LT008',
            'position' => 'Lễ tân',
        ]);

        $letan9 = User::create([
            'full_name' => 'Chương Kim',
            'phone' => '0800000009',
            'username' => 'letan9',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan9->id,
            'employee_code' => 'LT009',
            'position' => 'Lễ tân',
        ]);

        $letan10 = User::create([
            'full_name' => 'Lữ Kiết Hoài',
            'phone' => '0800000010',
            'username' => 'letan10',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan10->id,
            'employee_code' => 'LT010',
            'position' => 'Lễ tân',
        ]);

        $letan11 = User::create([
            'full_name' => 'Anh. Đậu Khải',
            'phone' => '0800000011',
            'username' => 'letan11',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan11->id,
            'employee_code' => 'LT011',
            'position' => 'Lễ tân',
        ]);

        $letan12 = User::create([
            'full_name' => 'Bác. Tôn Lộc',
            'phone' => '0800000012',
            'username' => 'letan12',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan12->id,
            'employee_code' => 'LT012',
            'position' => 'Lễ tân',
        ]);

        $letan13 = User::create([
            'full_name' => 'Bồ Huệ Thu',
            'phone' => '0800000013',
            'username' => 'letan13',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan13->id,
            'employee_code' => 'LT013',
            'position' => 'Lễ tân',
        ]);

        $letan14 = User::create([
            'full_name' => 'Bác. Vũ Giao',
            'phone' => '0800000014',
            'username' => 'letan14',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan14->id,
            'employee_code' => 'LT014',
            'position' => 'Lễ tân',
        ]);

        $letan15 = User::create([
            'full_name' => 'Chú. Lư Ninh',
            'phone' => '0800000015',
            'username' => 'letan15',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan15->id,
            'employee_code' => 'LT015',
            'position' => 'Lễ tân',
        ]);

        $letan16 = User::create([
            'full_name' => 'Chị. Hy Uyên',
            'phone' => '0800000016',
            'username' => 'letan16',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan16->id,
            'employee_code' => 'LT016',
            'position' => 'Lễ tân',
        ]);

        $letan17 = User::create([
            'full_name' => 'Đinh Định',
            'phone' => '0800000017',
            'username' => 'letan17',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan17->id,
            'employee_code' => 'LT017',
            'position' => 'Lễ tân',
        ]);

        $letan18 = User::create([
            'full_name' => 'Ông. Trang Chiến Kiếm',
            'phone' => '0800000018',
            'username' => 'letan18',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan18->id,
            'employee_code' => 'LT018',
            'position' => 'Lễ tân',
        ]);

        $letan19 = User::create([
            'full_name' => 'Bác. Cấn Hiệp',
            'phone' => '0800000019',
            'username' => 'letan19',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan19->id,
            'employee_code' => 'LT019',
            'position' => 'Lễ tân',
        ]);

        $letan20 = User::create([
            'full_name' => 'Em. Lỡ Mi',
            'phone' => '0800000020',
            'username' => 'letan20',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan20->id,
            'employee_code' => 'LT020',
            'position' => 'Lễ tân',
        ]);

        $letan21 = User::create([
            'full_name' => 'Mộc Thương Phước',
            'phone' => '0800000021',
            'username' => 'letan21',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan21->id,
            'employee_code' => 'LT021',
            'position' => 'Lễ tân',
        ]);

        $letan22 = User::create([
            'full_name' => 'Mẫn Vỹ',
            'phone' => '0800000022',
            'username' => 'letan22',
            'password' => Hash::make('Letan@123'),
            'role' => 'receptionist',
        ]);
        StaffProfile::create([
            'user_id' => $letan22->id,
            'employee_code' => 'LT022',
            'position' => 'Lễ tân',
        ]);

        $bn3 = User::create([
            'full_name' => 'Kim Lộ',
            'phone' => '0900000103',
            'username' => 'bn3',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn3->id,
            'full_name' => 'Kim Lộ',
            'date_of_birth' => '1988-09-15',
            'gender' => 'female',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn3->id,
            'full_name' => 'Diệp Huy Nhu',
            'date_of_birth' => '2006-01-07',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn3->id,
            'full_name' => 'Bác. Ninh Băng',
            'date_of_birth' => '1994-04-04',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn3->id,
            'full_name' => 'Trịnh Tùng Cúc',
            'date_of_birth' => '1990-05-10',
            'gender' => 'male',
            'is_self' => false,
        ]);

        $bn4 = User::create([
            'full_name' => 'Ông. Tôn Tường',
            'phone' => '0900000104',
            'username' => 'bn4',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn4->id,
            'full_name' => 'Ông. Tôn Tường',
            'date_of_birth' => '1994-05-16',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn4->id,
            'full_name' => 'Đôn Ly',
            'date_of_birth' => '1998-10-12',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn4->id,
            'full_name' => 'Cụ. Phó Khanh',
            'date_of_birth' => '1984-08-20',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn4->id,
            'full_name' => 'Bà. Diệp Đào',
            'date_of_birth' => '1992-02-16',
            'gender' => 'male',
            'is_self' => false,
        ]);

        $bn5 = User::create([
            'full_name' => 'Tôn Lê Kim',
            'phone' => '0900000105',
            'username' => 'bn5',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn5->id,
            'full_name' => 'Tôn Lê Kim',
            'date_of_birth' => '1977-07-09',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn5->id,
            'full_name' => 'Bà. Ma Di',
            'date_of_birth' => '1973-03-15',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn5->id,
            'full_name' => 'Cự Liên Lễ',
            'date_of_birth' => '2001-04-11',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn5->id,
            'full_name' => 'Cụ. Quản Phương',
            'date_of_birth' => '1978-07-11',
            'gender' => 'female',
            'is_self' => false,
        ]);

        $bn6 = User::create([
            'full_name' => 'Từ Vũ',
            'phone' => '0900000106',
            'username' => 'bn6',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn6->id,
            'full_name' => 'Từ Vũ',
            'date_of_birth' => '1992-12-18',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn6->id,
            'full_name' => 'Tạ Bình Quyền',
            'date_of_birth' => '2009-01-07',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn6->id,
            'full_name' => 'Bế Triều',
            'date_of_birth' => '2004-03-01',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn6->id,
            'full_name' => 'La Trạch',
            'date_of_birth' => '1998-03-20',
            'gender' => 'female',
            'is_self' => false,
        ]);

        $bn7 = User::create([
            'full_name' => 'Em. Bùi Xuyến Đào',
            'phone' => '0900000107',
            'username' => 'bn7',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn7->id,
            'full_name' => 'Em. Bùi Xuyến Đào',
            'date_of_birth' => '1984-02-09',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn7->id,
            'full_name' => 'Đổng Phước',
            'date_of_birth' => '1984-06-16',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn7->id,
            'full_name' => 'Em. Cấn An',
            'date_of_birth' => '1973-09-23',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn7->id,
            'full_name' => 'Hoàng Canh',
            'date_of_birth' => '1986-08-21',
            'gender' => 'male',
            'is_self' => false,
        ]);

        $bn8 = User::create([
            'full_name' => 'Cô. Bành Châu',
            'phone' => '0900000108',
            'username' => 'bn8',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn8->id,
            'full_name' => 'Cô. Bành Châu',
            'date_of_birth' => '1978-07-10',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn8->id,
            'full_name' => 'Ung Đức',
            'date_of_birth' => '2008-04-25',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn8->id,
            'full_name' => 'Cô. Ánh Di',
            'date_of_birth' => '1979-05-29',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn8->id,
            'full_name' => 'Cụ. Dã Chuẩn Trọng',
            'date_of_birth' => '1979-12-13',
            'gender' => 'female',
            'is_self' => false,
        ]);

        $bn9 = User::create([
            'full_name' => 'Mộc Định',
            'phone' => '0900000109',
            'username' => 'bn9',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn9->id,
            'full_name' => 'Mộc Định',
            'date_of_birth' => '1979-05-22',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn9->id,
            'full_name' => 'Chu Thu Ái',
            'date_of_birth' => '2007-11-23',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn9->id,
            'full_name' => 'Phi Hạo Tấn',
            'date_of_birth' => '1972-02-14',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn9->id,
            'full_name' => 'Phi An',
            'date_of_birth' => '1998-11-08',
            'gender' => 'female',
            'is_self' => false,
        ]);

        $bn10 = User::create([
            'full_name' => 'Anh. Đào Thọ',
            'phone' => '0900000110',
            'username' => 'bn10',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn10->id,
            'full_name' => 'Anh. Đào Thọ',
            'date_of_birth' => '1993-12-29',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn10->id,
            'full_name' => 'Từ Triệu',
            'date_of_birth' => '1990-10-24',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn10->id,
            'full_name' => 'Chị. Bành Dương',
            'date_of_birth' => '1987-12-13',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn10->id,
            'full_name' => 'Trương Khánh',
            'date_of_birth' => '1993-04-11',
            'gender' => 'male',
            'is_self' => false,
        ]);

        $bn11 = User::create([
            'full_name' => 'Cụ. Biện Hoài Trình',
            'phone' => '0900000111',
            'username' => 'bn11',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn11->id,
            'full_name' => 'Cụ. Biện Hoài Trình',
            'date_of_birth' => '1978-06-22',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn11->id,
            'full_name' => 'Kha Nguyên Ngân',
            'date_of_birth' => '1975-08-03',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn11->id,
            'full_name' => 'Bác. Đậu Khánh Thống',
            'date_of_birth' => '2013-04-07',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn11->id,
            'full_name' => 'Em. Nghiêm Quỳnh',
            'date_of_birth' => '1980-05-10',
            'gender' => 'female',
            'is_self' => false,
        ]);

        $bn12 = User::create([
            'full_name' => 'Diệp Đồng Độ',
            'phone' => '0900000112',
            'username' => 'bn12',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn12->id,
            'full_name' => 'Diệp Đồng Độ',
            'date_of_birth' => '1971-07-16',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn12->id,
            'full_name' => 'Chị. Kha Ái',
            'date_of_birth' => '1982-11-02',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn12->id,
            'full_name' => 'Bà. Lạc Thuận',
            'date_of_birth' => '1970-02-17',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn12->id,
            'full_name' => 'Chú. Bạc Hội',
            'date_of_birth' => '1989-01-06',
            'gender' => 'male',
            'is_self' => false,
        ]);

        $bn13 = User::create([
            'full_name' => 'Cô. Ngô Quân',
            'phone' => '0900000113',
            'username' => 'bn13',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn13->id,
            'full_name' => 'Cô. Ngô Quân',
            'date_of_birth' => '1992-08-09',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn13->id,
            'full_name' => 'Lý Sơn Liêm',
            'date_of_birth' => '1973-11-01',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn13->id,
            'full_name' => 'Bác. Đới Sao Nguyệt',
            'date_of_birth' => '2009-12-24',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn13->id,
            'full_name' => 'Giả Bào',
            'date_of_birth' => '1996-07-22',
            'gender' => 'female',
            'is_self' => false,
        ]);

        $bn14 = User::create([
            'full_name' => 'Em. Vi Hoan',
            'phone' => '0900000114',
            'username' => 'bn14',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn14->id,
            'full_name' => 'Em. Vi Hoan',
            'date_of_birth' => '1973-02-13',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn14->id,
            'full_name' => 'Ông. Nhữ Đức',
            'date_of_birth' => '1996-03-16',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn14->id,
            'full_name' => 'Đôn Hiền',
            'date_of_birth' => '2006-07-27',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn14->id,
            'full_name' => 'Quản Phước',
            'date_of_birth' => '1997-11-02',
            'gender' => 'male',
            'is_self' => false,
        ]);

        $bn15 = User::create([
            'full_name' => 'Cô. Bạch Hiếu Sa',
            'phone' => '0900000115',
            'username' => 'bn15',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn15->id,
            'full_name' => 'Cô. Bạch Hiếu Sa',
            'date_of_birth' => '1984-01-10',
            'gender' => 'female',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn15->id,
            'full_name' => 'Chú. Tăng Nam Trân',
            'date_of_birth' => '1972-02-06',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn15->id,
            'full_name' => 'Bác. Xa Đông Thảo',
            'date_of_birth' => '2003-10-24',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn15->id,
            'full_name' => 'Đậu Hoàn Loan',
            'date_of_birth' => '2014-03-26',
            'gender' => 'male',
            'is_self' => false,
        ]);

        $bn16 = User::create([
            'full_name' => 'Em. Bửu Đan',
            'phone' => '0900000116',
            'username' => 'bn16',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn16->id,
            'full_name' => 'Em. Bửu Đan',
            'date_of_birth' => '1971-12-25',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn16->id,
            'full_name' => 'Khổng Khương Nhân',
            'date_of_birth' => '1991-01-24',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn16->id,
            'full_name' => 'Ánh Khải',
            'date_of_birth' => '2003-03-25',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn16->id,
            'full_name' => 'Xa Hiệp',
            'date_of_birth' => '1998-10-11',
            'gender' => 'male',
            'is_self' => false,
        ]);

        $bn17 = User::create([
            'full_name' => 'Cụ. Võ Chiến Thạch',
            'phone' => '0900000117',
            'username' => 'bn17',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn17->id,
            'full_name' => 'Cụ. Võ Chiến Thạch',
            'date_of_birth' => '1991-12-26',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn17->id,
            'full_name' => 'Đôn Tuyền',
            'date_of_birth' => '1986-06-30',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn17->id,
            'full_name' => 'Bà. Tông Nhân',
            'date_of_birth' => '2001-04-09',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn17->id,
            'full_name' => 'Khổng Hào Diệp',
            'date_of_birth' => '2010-12-14',
            'gender' => 'female',
            'is_self' => false,
        ]);

        $bn18 = User::create([
            'full_name' => 'Triệu Thanh',
            'phone' => '0900000118',
            'username' => 'bn18',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn18->id,
            'full_name' => 'Triệu Thanh',
            'date_of_birth' => '1970-05-14',
            'gender' => 'female',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn18->id,
            'full_name' => 'Bạc Phong',
            'date_of_birth' => '2007-04-02',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn18->id,
            'full_name' => 'Cao Dung',
            'date_of_birth' => '1991-08-02',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn18->id,
            'full_name' => 'Chị. Khưu Anh Hải',
            'date_of_birth' => '1975-01-16',
            'gender' => 'male',
            'is_self' => false,
        ]);

        $bn19 = User::create([
            'full_name' => 'Nhậm Nhất Nhi',
            'phone' => '0900000119',
            'username' => 'bn19',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn19->id,
            'full_name' => 'Nhậm Nhất Nhi',
            'date_of_birth' => '1971-11-13',
            'gender' => 'female',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn19->id,
            'full_name' => 'Bà. Sử Vi',
            'date_of_birth' => '2005-11-08',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn19->id,
            'full_name' => 'Bình Thái San',
            'date_of_birth' => '1971-12-03',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn19->id,
            'full_name' => 'Trương Yên Oanh',
            'date_of_birth' => '1975-06-27',
            'gender' => 'male',
            'is_self' => false,
        ]);

        $bn20 = User::create([
            'full_name' => 'Anh. Kha Cường Thạc',
            'phone' => '0900000120',
            'username' => 'bn20',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn20->id,
            'full_name' => 'Anh. Kha Cường Thạc',
            'date_of_birth' => '1981-11-03',
            'gender' => 'female',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn20->id,
            'full_name' => 'Cụ. Tôn Vũ',
            'date_of_birth' => '2013-07-09',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn20->id,
            'full_name' => 'Ấu Nhật Huyền',
            'date_of_birth' => '2002-05-04',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn20->id,
            'full_name' => 'Em. Thập Dụng',
            'date_of_birth' => '2013-08-10',
            'gender' => 'female',
            'is_self' => false,
        ]);

        $bn21 = User::create([
            'full_name' => 'Bác. Kha Mậu Minh',
            'phone' => '0900000121',
            'username' => 'bn21',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn21->id,
            'full_name' => 'Bác. Kha Mậu Minh',
            'date_of_birth' => '1970-08-20',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn21->id,
            'full_name' => 'Chị. Hồng Giao',
            'date_of_birth' => '2001-10-11',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn21->id,
            'full_name' => 'Chử Khởi Minh',
            'date_of_birth' => '1996-01-21',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn21->id,
            'full_name' => 'Mạch Bảo Vũ',
            'date_of_birth' => '2000-04-02',
            'gender' => 'female',
            'is_self' => false,
        ]);

        $bn22 = User::create([
            'full_name' => 'Hàng Bửu Yên',
            'phone' => '0900000122',
            'username' => 'bn22',
            'password' => Hash::make('Patient@123'),
            'role' => 'patient',
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn22->id,
            'full_name' => 'Hàng Bửu Yên',
            'date_of_birth' => '1972-09-24',
            'gender' => 'male',
            'is_self' => true,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn22->id,
            'full_name' => 'Chú. Cấn Hữu',
            'date_of_birth' => '2001-10-26',
            'gender' => 'female',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn22->id,
            'full_name' => 'Ung Lâm Luật',
            'date_of_birth' => '1974-02-01',
            'gender' => 'male',
            'is_self' => false,
        ]);
        PatientProfile::create([
            'medical_history' => ['https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf', 'https://res.cloudinary.com/dr4oef3ds/image/upload/v1751398321/xch039fwr8j6lq1c5f21.pdf'],
            'owner_id' => $bn22->id,
            'full_name' => 'Bà. Cung Giao Tiên',
            'date_of_birth' => '1977-08-23',
            'gender' => 'male',
            'is_self' => false,
        ]);

    }
}

