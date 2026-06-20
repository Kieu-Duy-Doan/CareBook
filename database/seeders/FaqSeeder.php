<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;
use App\Models\Specialty;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Làm thế nào để tôi đặt lịch khám trực tuyến?',
                'answer' => 'Để đặt lịch khám, bạn vui lòng đăng nhập vào hệ thống, sau đó chọn mục "Đặt lịch hẹn" trên menu chính. Bạn có thể chọn chuyên khoa, bác sĩ và khung giờ phù hợp với mình. Hệ thống sẽ gửi thông báo xác nhận khi đặt thành công.',
                'keywords' => 'đặt lịch, hẹn khám, trực tuyến, online',
                'is_active' => true,
                'view_count' => rand(10, 100),
                'specialty_id' => null,
            ],
            [
                'question' => 'Chi phí khám bệnh lâm sàng ban đầu là bao nhiêu?',
                'answer' => 'Chi phí khám lâm sàng ban đầu (chưa bao gồm xét nghiệm, siêu âm) tại phòng khám là 150.000 VNĐ đối với tất cả các chuyên khoa.',
                'keywords' => 'chi phí, giá khám, bảng giá, tiền khám',
                'is_active' => true,
                'view_count' => rand(10, 100),
                'specialty_id' => null,
            ],
            [
                'question' => 'Phòng khám có làm việc vào ngày cuối tuần không?',
                'answer' => 'Phòng khám hoạt động từ Thứ 2 đến Thứ 7. Buổi sáng từ 7h30 - 11h30, buổi chiều từ 13h30 - 17h30. Chủ nhật và các ngày lễ tết phòng khám nghỉ.',
                'keywords' => 'giờ làm việc, cuối tuần, chủ nhật, thời gian mở cửa',
                'is_active' => true,
                'view_count' => rand(10, 100),
                'specialty_id' => null,
            ],
            [
                'question' => 'Tôi có thể sử dụng thẻ Bảo hiểm Y tế (BHYT) tại đây không?',
                'answer' => 'Có, phòng khám có tiếp nhận bệnh nhân khám chữa bệnh theo thẻ BHYT theo đúng quy định của Nhà nước. Vui lòng mang theo CCCD và Thẻ BHYT khi đến khám.',
                'keywords' => 'bảo hiểm y tế, BHYT, bảo hiểm',
                'is_active' => true,
                'view_count' => rand(10, 100),
                'specialty_id' => null,
            ],
        ];

        // Lấy danh sách ID của một vài khoa để gán cho các câu hỏi chuyên khoa
        $specialties = Specialty::where('is_active', true)->get();
        
        if ($specialties->count() > 0) {
            // Câu hỏi cho Khoa Tim mạch (nếu có)
            $timMach = $specialties->where('name', 'Khoa Tim mạch')->first() ?? $specialties->first();
            if ($timMach) {
                $faqs[] = [
                    'question' => 'Khoa Tim mạch có dịch vụ đo điện tâm đồ Holter 24h không?',
                    'answer' => 'Có, Khoa Tim mạch của chúng tôi trang bị đầy đủ máy Holter điện tâm đồ 24h và Holter huyết áp 24h để phục vụ quá trình chẩn đoán bệnh lý tim mạch.',
                    'keywords' => 'điện tâm đồ, tim mạch, holter',
                    'is_active' => true,
                    'view_count' => rand(5, 50),
                    'specialty_id' => $timMach->id,
                ];
            }

            // Câu hỏi cho Khoa Nhi (nếu có)
            $nhi = $specialties->where('name', 'Khoa Nhi')->first() ?? $specialties->last();
            if ($nhi && $nhi->id !== $timMach->id) {
                $faqs[] = [
                    'question' => 'Khoa Nhi có khám tiêm chủng vaccine không?',
                    'answer' => 'Hiện tại Khoa Nhi có khám sàng lọc trước tiêm chủng và cung cấp các gói tiêm vaccine cơ bản cho trẻ nhỏ. Bạn có thể gọi hotline để hỏi tình trạng vaccine cụ thể.',
                    'keywords' => 'khoa nhi, trẻ em, tiêm chủng, vaccine',
                    'is_active' => true,
                    'view_count' => rand(5, 50),
                    'specialty_id' => $nhi->id,
                ];
            }
        }

        foreach ($faqs as $faq) {
            Faq::firstOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }
    }
}
