<?php

namespace Database\Seeders;

use App\Models\ChatbotIntent;
use App\Models\ChatbotResponse;
use App\Enums\ChatbotActionEnum;
use Illuminate\Database\Seeder;

class ChatbotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $intents = [
            [
                'intent_name' => 'ask_price',
                'description' => 'Khách hỏi về giá khám bệnh cơ bản',
                'action' => ChatbotActionEnum::FAQ_LOOKUP->value,
                'example_phrases' => "Giá khám là bao nhiêu | Khám tốn bao tiền | Bảng giá khám | Cho tôi xin bảng giá",
                'is_active' => true,
                'responses' => [
                    [
                        'content' => "Chào bạn, chi phí khám lâm sàng ban đầu tại phòng khám là 150.000 VNĐ. Nếu có làm thêm các xét nghiệm cận lâm sàng (như thử máu, siêu âm, X-quang,...), bác sĩ sẽ tư vấn cụ thể chi phí cho bạn dựa trên tình trạng sức khỏe thực tế nhé. Bạn có muốn đặt lịch khám luôn không?",
                        'priority' => 1,
                        'is_active' => true,
                    ]
                ]
            ],
            [
                'intent_name' => 'guide_booking_general',
                'description' => 'Khách muốn hướng dẫn đặt lịch khám',
                'action' => ChatbotActionEnum::GUIDE_BOOKING->value,
                'example_phrases' => "Làm sao để đặt lịch | Hướng dẫn đặt khám | Tôi muốn đăng ký khám | Đặt lịch như thế nào",
                'is_active' => true,
                'responses' => [
                    [
                        'content' => "Để đặt lịch khám, bạn vui lòng đăng nhập vào hệ thống, sau đó chọn mục 'Đặt lịch hẹn' trên menu chính nhé. Bạn có thể tự do chọn chuyên khoa, bác sĩ và khung giờ phù hợp với mình. Hệ thống sẽ gửi thông báo xác nhận ngay khi bạn đặt thành công!",
                        'priority' => 1,
                        'is_active' => true,
                    ]
                ]
            ],
            [
                'intent_name' => 'ask_working_hours',
                'description' => 'Khách hỏi về giờ làm việc của phòng khám',
                'action' => ChatbotActionEnum::FAQ_LOOKUP->value,
                'example_phrases' => "Mấy giờ mở cửa | Lịch làm việc | Phòng khám làm việc đến mấy giờ | Có khám chủ nhật không",
                'is_active' => true,
                'responses' => [
                    [
                        'content' => "Phòng khám CareBook mở cửa từ Thứ 2 đến Thứ 7 hàng tuần.\nBuổi sáng: 7h30 - 11h30\nBuổi chiều: 13h30 - 17h30\nPhòng khám nghỉ Chủ nhật và các ngày lễ tết theo quy định của nhà nước ạ.",
                        'priority' => 1,
                        'is_active' => true,
                    ]
                ]
            ],
            [
                'intent_name' => 'ask_address',
                'description' => 'Khách hỏi địa chỉ phòng khám',
                'action' => ChatbotActionEnum::FAQ_LOOKUP->value,
                'example_phrases' => "Địa chỉ ở đâu | Phòng khám nằm ở đâu | Xin địa chỉ | Cho tôi xin địa chỉ",
                'is_active' => true,
                'responses' => [
                    [
                        'content' => "Phòng khám đa khoa CareBook nằm tại địa chỉ: Số 123 Đường Y Tế, Phường Chăm Sóc, Quận Sức Khỏe, Thành phố Hồ Chí Minh. Có bãi đỗ xe rộng rãi cho cả xe máy và ô tô bạn nhé.",
                        'priority' => 1,
                        'is_active' => true,
                    ]
                ]
            ],
            [
                'intent_name' => 'introduce_cardiology',
                'description' => 'Khách hỏi về khoa tim mạch',
                'action' => ChatbotActionEnum::INTRODUCE_SPECIALTY->value,
                'example_phrases' => "Khám tim | Khoa tim mạch | Bác sĩ tim mạch | Đau ngực khám ở đâu",
                'is_active' => true,
                'responses' => [
                    [
                        'content' => "Chuyên khoa Tim mạch của chúng tôi quy tụ đội ngũ y bác sĩ đầu ngành, được trang bị máy siêu âm tim màu 4D, máy đo điện tâm đồ thế hệ mới. Bạn có thể hoàn toàn yên tâm khi đến khám và tầm soát các bệnh lý tim mạch tại đây.",
                        'priority' => 1,
                        'is_active' => true,
                    ]
                ]
            ],
            [
                'intent_name' => 'need_human',
                'description' => 'Khách cần gặp nhân viên tư vấn',
                'action' => ChatbotActionEnum::TRANSFER_STAFF->value,
                'example_phrases' => "Gặp nhân viên | Nói chuyện với người | Tư vấn viên | Chuyển máy",
                'is_active' => true,
                'responses' => [
                    [
                        'content' => "Mình đã ghi nhận yêu cầu. Vui lòng đợi trong giây lát, mình đang kết nối bạn với nhân viên y tế để được tư vấn chuyên sâu hơn nhé...",
                        'priority' => 1,
                        'is_active' => true,
                    ]
                ]
            ],
            [
                'intent_name' => 'greeting',
                'description' => 'Khách chào hỏi mở đầu',
                'action' => ChatbotActionEnum::FAQ_LOOKUP->value,
                'example_phrases' => "Xin chào | Alo | Hi chatbot | Chào phòng khám",
                'is_active' => true,
                'responses' => [
                    [
                        'content' => "Dạ chào bạn! Mình là trợ lý ảo của phòng khám CareBook. Mình có thể giúp gì cho bạn hôm nay ạ? (Ví dụ: xem bảng giá, xem lịch làm việc, hướng dẫn đặt khám...)",
                        'priority' => 1,
                        'is_active' => true,
                    ]
                ]
            ],
        ];

        foreach ($intents as $intentData) {
            $responses = $intentData['responses'];
            unset($intentData['responses']);

            $intent = ChatbotIntent::firstOrCreate(
                ['intent_name' => $intentData['intent_name']],
                $intentData
            );

            foreach ($responses as $response) {
                $intent->responses()->firstOrCreate(
                    ['content' => $response['content']],
                    $response
                );
            }
        }
    }
}
