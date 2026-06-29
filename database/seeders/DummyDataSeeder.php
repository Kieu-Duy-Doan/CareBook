<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\PatientProfile;
use App\Models\DoctorProfile;
use App\Models\Appointment;
use App\Models\Post;
use App\Models\Specialty;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        $password = Hash::make('123456');

        $specialties = Specialty::pluck('id')->toArray();
        if (empty($specialties)) {
            $specialties = [1]; // Fallback if no specialties exist
        }

        $this->command->info('Creating 15 Patients...');
        $patientIds = [];
        $userIds = [];
        for ($i = 0; $i < 15; $i++) {
            $phone = '09' . $faker->numerify('########');
            $user = User::create([
                'full_name' => $faker->name,
                'phone' => $phone,
                'username' => 'bn_' . $phone,
                'password' => $password,
                'role' => 'patient',
                'is_active' => true,
            ]);
            $userIds[] = $user->id;

            $patient = PatientProfile::create([
                'owner_id' => $user->id,
                'full_name' => $user->full_name,
                'phone' => $user->phone,
                'gender' => $faker->randomElement(['male', 'female']),
                'date_of_birth' => $faker->dateTimeBetween('-60 years', '-10 years')->format('Y-m-d'),
                'address' => $faker->address,
                'is_self' => true,
            ]);
            $patientIds[] = $patient->id;
        }

        $this->command->info('Creating 10 Doctors...');
        $doctorIds = [];
        
        $expertiseList = [
            'Nội khoa tổng hợp',
            'Ngoại khoa, phẫu thuật',
            'Nhi khoa, tư vấn dinh dưỡng',
            'Sản phụ khoa, chăm sóc mẹ và bé',
            'Tai mũi họng, phẫu thuật nội soi',
            'Răng hàm mặt, thẩm mỹ nha khoa'
        ];

        for ($i = 0; $i < 10; $i++) {
            $phone = '08' . $faker->numerify('########');
            $user = User::create([
                'full_name' => $faker->name,
                'phone' => $phone,
                'username' => 'bs_' . $phone,
                'password' => $password,
                'role' => 'doctor',
                'is_active' => true,
            ]);

            $doctor = DoctorProfile::create([
                'user_id' => $user->id,
                'doctor_code' => 'BS' . $faker->unique()->numerify('####'),
                'academic_title' => $faker->randomElement(['ThS.', 'TS.', 'PGS.TS.']),
                'level' => $faker->randomElement(['ThS', 'TS', 'PGS']),
                'expertise' => $faker->randomElement($expertiseList),
                'experience_years' => $faker->numberBetween(5, 25),
                'license_number' => 'CCHN' . $faker->numerify('######'),
                'bio' => 'Bác sĩ có nhiều năm kinh nghiệm trong lĩnh vực y tế, từng công tác tại các bệnh viện lớn. Luôn tận tâm và hết mình vì sức khỏe của người bệnh.',
            ]);
            $doctorIds[] = $doctor->id;

            if (!empty($specialties)) {
                $doctor->specialties()->attach($faker->randomElements($specialties, rand(1, 2)), ['is_primary' => 1]);
            }
        }

        $this->command->info('Creating 30 Appointments...');
        $rooms = \App\Models\Room::pluck('id')->toArray();
        if (empty($rooms)) {
            $rooms = [1];
        }
        $reasons = [
            'Đau đầu, chóng mặt kéo dài',
            'Kiểm tra sức khỏe tổng quát',
            'Đau dạ dày, ợ chua',
            'Mất ngủ, mệt mỏi',
            'Nhức mỏi vai gáy, tê tay',
            'Tư vấn dinh dưỡng cho người tiểu đường',
            'Khám thai định kỳ',
            'Sốt cao không giảm',
            'Đau bụng âm ỉ',
            'Khám tầm soát ung thư'
        ];

        for ($i = 0; $i < 30; $i++) {
            $date = $faker->dateTimeBetween('-1 month', '+1 month');
            $patientId = $faker->randomElement($patientIds);
            
            Appointment::create([
                'appointment_code' => 'LH' . strtoupper(Str::random(6)),
                'patient_profile_id' => $patientId,
                'booked_by_user_id' => $faker->randomElement($userIds),
                'doctor_profile_id' => $faker->randomElement($doctorIds),
                'specialty_id' => $faker->randomElement($specialties),
                'room_id' => $faker->randomElement($rooms),
                'appointment_date' => $date->format('Y-m-d'),
                'appointment_time' => $faker->randomElement(['08:00:00', '09:00:00', '10:00:00', '14:00:00', '15:00:00']),
                'reason' => $faker->randomElement($reasons),
                'status' => $faker->randomElement(['pending', 'checked_in', 'examining', 'completed', 'cancelled']),
                'source' => $faker->randomElement(['web', 'counter', 'chatbot']),
            ]);
        }

        $this->command->info('Creating 15 Posts...');
        $postTitles = [
            'Những thói quen tốt giúp bảo vệ hệ tiêu hóa',
            'Lịch tiêm phòng các mũi bắt buộc cho trẻ sơ sinh',
            'Chế độ dinh dưỡng cho người cao huyết áp',
            'Nhận biết sớm các dấu hiệu đột quỵ',
            'Cách phòng ngừa sốt xuất huyết trong mùa mưa',
            'Giải đáp những thắc mắc về vắc xin phòng cúm',
            'Bí quyết chăm sóc da mặt vào mùa đông',
            'Tại sao bạn nên kiểm tra sức khỏe định kỳ?',
            'Ưu đãi gói khám sức khỏe tổng quát tháng này',
            'Khai trương hệ thống máy xét nghiệm máu thế hệ mới',
            'Bệnh viện CareBook tổ chức hội thảo sức khỏe tim mạch',
            'Những bài tập nhẹ nhàng giúp giảm đau vai gáy',
            'Những lầm tưởng về việc ăn kiêng giảm cân',
            'Cách xử lý khi trẻ bị sốt cao tại nhà',
            'Vai trò của giấc ngủ đối với hệ miễn dịch'
        ];

        foreach ($postTitles as $title) {
            Post::create([
                'title' => $title,
                'slug' => Str::slug($title) . '-' . Str::random(4),
                'summary' => 'Đây là bài viết cung cấp các thông tin y khoa hữu ích, giúp bạn bảo vệ sức khỏe cho bản thân và gia đình.',
                'content' => '<p>Trong nhịp sống hiện đại, việc duy trì sức khỏe là điều vô cùng quan trọng...</p><p>Chúng tôi hy vọng bài viết này mang lại cho bạn những kiến thức bổ ích. Đừng quên thường xuyên theo dõi tin tức y tế từ hệ thống bệnh viện của chúng tôi.</p>',
                'post_type' => $faker->randomElement(['news', 'service', 'guide', 'announcement']),
                'view_count' => $faker->numberBetween(100, 5000),
                'is_published' => true,
                'published_at' => clone $faker->dateTimeBetween('-6 months', 'now'),
                'author_id' => 1,
            ]);
        }
        
        $this->command->info('Dummy data seeded successfully!');
    }
}
