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
        // =========================================================
        // 1. Admin
        // =========================================================
        User::create([
            'full_name' => 'Nguyễn Quản Trị',
            'phone'     => '0900000001',
            'username'  => 'admin',
            'email'     => 'admin@gmail.com',
            'password'  => Hash::make('Admin@123'),
            'role'      => 'admin',
            'id_card'   => '001090123456',
        ]);

        // =========================================================
        // 2. Lễ tân
        // =========================================================
        $letan = User::create([
            'full_name' => 'Trần Thị Lễ Tân',
            'phone'     => '0900000002',
            'username'  => 'letan',
            'email'     => 'letan@gmail.com',
            'password'  => Hash::make('Letan@123'),
            'role'      => 'receptionist',
            'id_card'   => '001195123457',
        ]);
        StaffProfile::create([
            'user_id'       => $letan->id,
            'employee_code' => 'LT001',
            'position'      => 'Lễ tân',
        ]);

        // =========================================================
        // 3. Bác sĩ LÂM SÀNG (10 bác sĩ — BS001 → BS010)
        //    Ca xen kẽ: lẻ = sáng, chẵn = chiều
        // =========================================================
        $clinicalDoctors = [
            // index 0 → BS001 → Ca sáng
            ['name' => 'Nguyễn Văn An',    'username' => 'bs_an',      'rank' => 'none', 'degree' => 'BSCK1', 'level' => 'BSCK1', 'pos' => 'ATTENDING'],
            // index 1 → BS002 → Ca chiều
            ['name' => 'Trần Thị Bích',    'username' => 'bs_bich',    'rank' => 'none', 'degree' => 'ThS',   'level' => 'ThS',   'pos' => 'DEPARTMENT_HEAD'],
            // index 2 → BS003 → Ca sáng
            ['name' => 'Lê Minh Tuấn',     'username' => 'bs_tuan',    'rank' => 'PGS',  'degree' => 'TS',    'level' => 'PGS',   'pos' => 'EXPERT'],
            // index 3 → BS004 → Ca chiều
            ['name' => 'Hoàng Ngọc Hà',    'username' => 'bs_ha',      'rank' => 'none', 'degree' => 'BS',    'level' => 'BS',    'pos' => 'INTERN'],
            // index 4 → BS005 → Ca sáng
            ['name' => 'Phạm Đức Đam',     'username' => 'bs_dam',     'rank' => 'none', 'degree' => 'BSCK2', 'level' => 'BSCK2', 'pos' => 'CONSULTANT'],
            // index 5 → BS006 → Ca chiều
            ['name' => 'Ngô Bảo Châu',     'username' => 'bs_chau',    'rank' => 'GS',   'degree' => 'TS',    'level' => 'GS',    'pos' => 'EXPERT'],
            // index 6 → BS007 → Ca sáng
            ['name' => 'Vũ Thu Thủy',      'username' => 'bs_thuy',    'rank' => 'none', 'degree' => 'BS',    'level' => 'BS',    'pos' => 'ATTENDING'],
            // index 7 → BS008 → Ca chiều
            ['name' => 'Đinh Tuấn Anh',    'username' => 'bs_tuananh', 'rank' => 'none', 'degree' => 'BSCK1', 'level' => 'BSCK1', 'pos' => 'ATTENDING'],
            // index 8 → BS009 → Ca sáng
            ['name' => 'Lý Thảo Tâm',      'username' => 'bs_tam',     'rank' => 'none', 'degree' => 'ThS',   'level' => 'ThS',   'pos' => 'ATTENDING'],
            // index 9 → BS010 → Ca chiều
            ['name' => 'Châu Kiều Oanh',   'username' => 'bs_oanh',    'rank' => 'none', 'degree' => 'BSCK2', 'level' => 'BSCK2', 'pos' => 'DEPARTMENT_HEAD'],
        ];

        foreach ($clinicalDoctors as $index => $doc) {
            $user = User::create([
                'full_name' => $doc['name'],
                'phone'     => '09000001' . sprintf('%02d', $index),
                'username'  => $doc['username'],
                'email'     => $doc['username'] . '@gmail.com',
                'password'  => Hash::make('Bacsi@123'),
                'role'      => 'doctor',
                'id_card'   => '0790851' . sprintf('%05d', $index),
            ]);

            DoctorProfile::create([
                'user_id'          => $user->id,
                'doctor_code'      => 'BS' . sprintf('%03d', $index + 1),
                'doctor_type'      => 'clinical',
                'academic_rank'    => $doc['rank'],
                'degree'           => $doc['degree'],
                'current_position' => $doc['pos'],
                'level'            => $doc['level'],
                'experience_years' => rand(5, 20),
                'expertise'        => 'Khám và điều trị chuyên sâu',
                'bio'              => 'Bác sĩ nhiều năm kinh nghiệm, tận tâm vì sức khỏe người bệnh.',
            ]);
        }

        // =========================================================
        // 4. Bác sĩ CẬN LÂM SÀNG (6 bác sĩ — BS011 → BS016)
        //    2 bác sĩ / phòng: 1 ca sáng, 1 ca chiều
        // =========================================================
        $paraclinicalDoctors = [
            // Siêu âm 4D
            ['name' => 'Trương Minh Quang', 'username' => 'bs_sa_s',  'degree' => 'BSCK1', 'level' => 'BSCK1', 'expertise' => 'Siêu âm chẩn đoán hình ảnh'],
            ['name' => 'Nguyễn Thị Hồng',   'username' => 'bs_sa_c',  'degree' => 'ThS',   'level' => 'ThS',   'expertise' => 'Siêu âm sản khoa và 4D'],
            // Xét nghiệm Máu
            ['name' => 'Lê Thanh Sơn',       'username' => 'bs_xn_s',  'degree' => 'BS',    'level' => 'BS',    'expertise' => 'Xét nghiệm huyết học lâm sàng'],
            ['name' => 'Phan Thị Nga',        'username' => 'bs_xn_c',  'degree' => 'BSCK1', 'level' => 'BSCK1', 'expertise' => 'Xét nghiệm sinh hóa và miễn dịch'],
            // XQuang
            ['name' => 'Đoàn Văn Khánh',     'username' => 'bs_xq_s',  'degree' => 'BSCK2', 'level' => 'BSCK2', 'expertise' => 'Chẩn đoán hình ảnh X-Quang'],
            ['name' => 'Mai Phương Thảo',     'username' => 'bs_xq_c',  'degree' => 'ThS',   'level' => 'ThS',   'expertise' => 'X-Quang kỹ thuật số và CT-Scan'],
        ];

        foreach ($paraclinicalDoctors as $index => $doc) {
            $codeNum = $index + 11; // BS011 → BS016
            $user = User::create([
                'full_name' => $doc['name'],
                'phone'     => '09000003' . sprintf('%02d', $index),
                'username'  => $doc['username'],
                'email'     => $doc['username'] . '@gmail.com',
                'password'  => Hash::make('Bacsi@123'),
                'role'      => 'doctor',
                'id_card'   => '0790852' . sprintf('%05d', $index),
            ]);

            DoctorProfile::create([
                'user_id'          => $user->id,
                'doctor_code'      => 'BS' . sprintf('%03d', $codeNum),
                'doctor_type'      => 'paraclinical',
                'academic_rank'    => 'none',
                'degree'           => $doc['degree'],
                'current_position' => 'ATTENDING',
                'level'            => $doc['level'],
                'experience_years' => rand(3, 15),
                'expertise'        => $doc['expertise'],
                'bio'              => 'Chuyên gia cận lâm sàng với nhiều năm kinh nghiệm chẩn đoán chính xác.',
            ]);
        }

        // =========================================================
        // 5. Bệnh nhân (10 bệnh nhân — thông tin đầy đủ)
        //    Mã BHYT chuẩn 15 ký tự: [2 ký tự tỉnh][2 số đối tượng][11 số định danh]
        // =========================================================
        $patients = [
            [
                'name'             => 'Nguyễn Thị Mai',
                'gender'           => 'female',
                'dob'              => '1990-05-15',
                'cccd'             => '001190123450',
                'username'         => 'bn_mai',
                'phone'            => '0900000201',
                'address'          => '45 Lý Thường Kiệt, P.14, Q.10, TP.HCM',
                'occupation'       => 'Giáo viên',
                'ethnicity'        => 'Kinh',
                'bhyt'             => 'DN4011234500001',
                'insurance_place'  => 'BV Nhân Dân 115',
                'insurance_expiry' => '2027-12-31',
            ],
            [
                'name'             => 'Trần Văn Hùng',
                'gender'           => 'male',
                'dob'              => '1985-08-22',
                'cccd'             => '079085123451',
                'username'         => 'bn_hung',
                'phone'            => '0900000202',
                'address'          => '12 Nguyễn Trãi, P.Bến Thành, Q.1, TP.HCM',
                'occupation'       => 'Kỹ sư xây dựng',
                'ethnicity'        => 'Kinh',
                'bhyt'             => 'HT3791234500002',
                'insurance_place'  => 'BV Quân Y 175',
                'insurance_expiry' => '2026-06-30',
            ],
            [
                'name'             => 'Lê Bích Ngọc',
                'gender'           => 'female',
                'dob'              => '1998-12-01',
                'cccd'             => '048198123452',
                'username'         => 'bn_ngoc',
                'phone'            => '0900000203',
                'address'          => '78 Trần Hưng Đạo, P.Cầu Kho, Q.1, TP.HCM',
                'occupation'       => 'Sinh viên',
                'ethnicity'        => 'Kinh',
                'bhyt'             => 'HC2791234500003',
                'insurance_place'  => 'BV Đại Học Y Dược TP.HCM',
                'insurance_expiry' => '2027-08-31',
            ],
            [
                'name'             => 'Phạm Hoàng Long',
                'gender'           => 'male',
                'dob'              => '2000-02-14',
                'cccd'             => '001000123453',
                'username'         => 'bn_long',
                'phone'            => '0900000204',
                'address'          => '200 Lê Lai, P.Bến Thành, Q.1, TP.HCM',
                'occupation'       => 'Nhân viên văn phòng',
                'ethnicity'        => 'Kinh',
                'bhyt'             => 'DN4011234500004',
                'insurance_place'  => 'BV Chợ Rẫy',
                'insurance_expiry' => '2026-12-31',
            ],
            [
                'name'             => 'Võ Thị Sáu',
                'gender'           => 'female',
                'dob'              => '1975-04-30',
                'cccd'             => '079175123454',
                'username'         => 'bn_sau',
                'phone'            => '0900000205',
                'address'          => '33 Cách Mạng Tháng 8, P.5, Q.3, TP.HCM',
                'occupation'       => 'Nội trợ',
                'ethnicity'        => 'Kinh',
                'bhyt'             => 'HC2791234500005',
                'insurance_place'  => 'BV Từ Dũ',
                'insurance_expiry' => '2027-03-31',
            ],
            [
                'name'             => 'Đặng Kim Sơn',
                'gender'           => 'male',
                'dob'              => '1992-09-02',
                'cccd'             => '001092123455',
                'username'         => 'bn_son',
                'phone'            => '0900000206',
                'address'          => '56 Nguyễn Đình Chiểu, P.3, Q.3, TP.HCM',
                'occupation'       => 'Lập trình viên',
                'ethnicity'        => 'Kinh',
                'bhyt'             => 'DN4011234500006',
                'insurance_place'  => 'BV Bình Dân',
                'insurance_expiry' => '2027-09-30',
            ],
            [
                'name'             => 'Bùi Thu Hà',
                'gender'           => 'female',
                'dob'              => '1988-11-20',
                'cccd'             => '079188123456',
                'username'         => 'bn_ha',
                'phone'            => '0900000207',
                'address'          => '91 Điện Biên Phủ, P.15, Bình Thạnh, TP.HCM',
                'occupation'       => 'Kế toán',
                'ethnicity'        => 'Hoa',
                'bhyt'             => 'DN4791234500007',
                'insurance_place'  => 'BV Gia An 115',
                'insurance_expiry' => '2026-08-31',
            ],
            [
                'name'             => 'Đỗ Xuân Trường',
                'gender'           => 'male',
                'dob'              => '1995-03-26',
                'cccd'             => '001095123457',
                'username'         => 'bn_truong',
                'phone'            => '0900000208',
                'address'          => '14 Võ Văn Tần, P.6, Q.3, TP.HCM',
                'occupation'       => 'Bác sĩ thú y',
                'ethnicity'        => 'Kinh',
                'bhyt'             => 'DN4011234500008',
                'insurance_place'  => 'BV 30/4',
                'insurance_expiry' => '2027-06-30',
            ],
            [
                'name'             => 'Hoàng Phúc',
                'gender'           => 'male',
                'dob'              => '2010-06-01',
                'cccd'             => '079210123458',
                'username'         => 'bn_phuc',
                'phone'            => '0900000209',
                'address'          => '22 Hoàng Văn Thụ, P.8, Phú Nhuận, TP.HCM',
                'occupation'       => 'Học sinh',
                'ethnicity'        => 'Kinh',
                'bhyt'             => 'TE1791234500009',
                'insurance_place'  => 'BV Nhi Đồng 1',
                'insurance_expiry' => '2027-12-31',
            ],
            [
                'name'             => 'Ngô Phương Lan',
                'gender'           => 'female',
                'dob'              => '1982-10-10',
                'cccd'             => '001182123459',
                'username'         => 'bn_lan',
                'phone'            => '0900000210',
                'address'          => '5 Phan Xích Long, P.2, Phú Nhuận, TP.HCM',
                'occupation'       => 'Dược sĩ',
                'ethnicity'        => 'Tày',
                'bhyt'             => 'DN4011234500010',
                'insurance_place'  => 'BV Phú Nhuận',
                'insurance_expiry' => '2026-10-31',
            ],
        ];

        foreach ($patients as $pat) {
            $user = User::create([
                'full_name' => $pat['name'],
                'phone'     => $pat['phone'],
                'username'  => $pat['username'],
                'email'     => $pat['username'] . '@gmail.com',
                'password'  => Hash::make('Patient@123'),
                'role'      => 'patient',
                'id_card'   => $pat['cccd'],
            ]);

            PatientProfile::create([
                'owner_id'         => $user->id,
                'full_name'        => $pat['name'],
                'date_of_birth'    => $pat['dob'],
                'gender'           => $pat['gender'],
                'id_card'          => $pat['cccd'],
                'phone'            => $pat['phone'],
                'address'          => $pat['address'],
                'occupation'       => $pat['occupation'],
                'ethnicity'        => $pat['ethnicity'],
                'insurance_code'   => $pat['bhyt'],
                'insurance_place'  => $pat['insurance_place'],
                'insurance_expiry' => $pat['insurance_expiry'],
                'is_self'          => true,
            ]);
        }
    }
}
