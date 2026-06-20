<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\Specialty;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $doctor = User::where('role', 'doctor')->first() ?? User::factory()->create(['role' => 'doctor']);
        $specialties = Specialty::all();

        $posts = [
            [
                'title' => 'Phòng khám CareBook chính thức khai trương hệ thống đặt lịch trực tuyến',
                'summary' => 'Hệ thống đặt lịch khám trực tuyến giúp bệnh nhân tiết kiệm thời gian, chọn đúng bác sĩ chuyên khoa và chủ động sắp xếp thời gian đi khám.',
                'content' => '<p>Nhằm nâng cao chất lượng dịch vụ và đáp ứng nhu cầu khám chữa bệnh ngày càng cao của người dân, Phòng khám CareBook chính thức ra mắt hệ thống đặt lịch khám bệnh trực tuyến.</p><p>Hệ thống cung cấp các tính năng nổi bật:</p><ul><li>Đặt lịch 24/7 ở bất kỳ đâu</li><li>Chọn chuyên khoa và bác sĩ theo mong muốn</li><li>Xem trước lịch làm việc của từng bác sĩ</li><li>Nhận thông báo nhắc nhở trước giờ khám</li></ul><p>Quý khách có thể truy cập website hoặc tải ứng dụng di động để trải nghiệm dịch vụ mới này.</p>',
                'post_type' => 'announcement',
                'author_id' => $admin->id,
                'specialty_id' => null,
            ],
            [
                'title' => 'Dấu hiệu nhận biết sớm bệnh đau dạ dày và cách phòng ngừa',
                'summary' => 'Đau dạ dày là căn bệnh phổ biến hiện nay. Nhận biết sớm các dấu hiệu sẽ giúp quá trình điều trị hiệu quả và nhanh chóng hơn.',
                'content' => '<p>Bệnh đau dạ dày (hay còn gọi là đau bao tử) là tình trạng dạ dày bị tổn thương chủ yếu do viêm loét. Người bệnh thường cảm thấy đau âm ỉ hoặc dữ dội ở vùng thượng vị.</p><h3>Các dấu hiệu nhận biết điển hình:</h3><ol><li>Đau vùng thượng vị (vùng bụng trên rốn)</li><li>Ăn kém, chán ăn, ăn không tiêu</li><li>Ợ hơi, ợ chua, ợ nóng</li><li>Buồn nôn và nôn</li><li>Chảy máu tiêu hóa (trường hợp nặng)</li></ol><h3>Cách phòng ngừa hiệu quả:</h3><p>Để bảo vệ dạ dày, bạn nên duy trì chế độ ăn uống khoa học, tránh ăn quá no hoặc để quá đói, hạn chế đồ cay nóng, chất kích thích. Đồng thời, cần giữ tinh thần thoải mái, tránh stress kéo dài và nên đi khám sức khỏe định kỳ.</p>',
                'post_type' => 'news',
                'author_id' => $doctor->id,
                'specialty_id' => $specialties->where('name', 'Nội tiêu hóa')->first()?->id ?? ($specialties->first()->id ?? null),
            ],
            [
                'title' => 'Hướng dẫn quy trình khám bệnh tại CareBook',
                'summary' => 'Quy trình 5 bước khám bệnh nhanh chóng, tiện lợi dành cho tất cả bệnh nhân khi đến thăm khám tại Phòng khám CareBook.',
                'content' => '<p>Để tiết kiệm thời gian cho người bệnh, CareBook áp dụng quy trình khám chữa bệnh khoa học gồm 5 bước:</p><h3>Bước 1: Đặt lịch hẹn và đăng ký khám</h3><p>Bệnh nhân có thể đặt lịch trước qua website hoặc đến trực tiếp quầy lễ tân để đăng ký và nhận số thứ tự.</p><h3>Bước 2: Khám lâm sàng</h3><p>Di chuyển đến phòng khám chuyên khoa theo hướng dẫn. Bác sĩ sẽ thăm khám lâm sàng và chỉ định các xét nghiệm cận lâm sàng nếu cần.</p><h3>Bước 3: Thực hiện cận lâm sàng (nếu có)</h3><p>Bệnh nhân đóng phí và thực hiện các xét nghiệm, siêu âm, X-quang theo chỉ định của bác sĩ.</p><h3>Bước 4: Nhận kết quả và nghe tư vấn</h3><p>Mang kết quả xét nghiệm quay lại phòng khám ban đầu để bác sĩ đọc kết quả, chẩn đoán và kê đơn thuốc.</p><h3>Bước 5: Mua thuốc và ra về</h3><p>Bệnh nhân đến nhà thuốc của phòng khám để mua thuốc theo đơn và nhận hướng dẫn sử dụng từ dược sĩ.</p>',
                'post_type' => 'guide',
                'author_id' => $admin->id,
                'specialty_id' => null,
            ],
            [
                'title' => 'Gói khám tầm soát sức khỏe tổng quát',
                'summary' => 'Phát hiện sớm các mầm mống gây bệnh để có phương án điều trị kịp thời, bảo vệ sức khỏe cho bạn và người thân.',
                'content' => '<p>Tầm soát sức khỏe định kỳ là "chìa khóa vàng" để bảo vệ sức khỏe, giúp phát hiện sớm các bất thường trong cơ thể ngay cả khi chưa có biểu hiện ra bên ngoài.</p><h3>Gói khám tổng quát tại CareBook bao gồm:</h3><ul><li>Khám nội tổng quát</li><li>Xét nghiệm máu cơ bản (Công thức máu, đường huyết, mỡ máu, chức năng gan thận)</li><li>Siêu âm ổ bụng tổng quát</li><li>Chụp X-quang tim phổi thẳng</li><li>Điện tâm đồ (ECG)</li></ul><p>Tất cả các danh mục đều được thực hiện bởi đội ngũ bác sĩ chuyên khoa giàu kinh nghiệm cùng hệ thống trang thiết bị hiện đại, cho kết quả nhanh chóng và chính xác.</p>',
                'post_type' => 'service',
                'author_id' => $admin->id,
                'specialty_id' => null,
            ],
            [
                'title' => 'Chăm sóc sức khỏe răng miệng cho trẻ em đúng cách',
                'summary' => 'Răng sữa đóng vai trò quan trọng trong việc nhai, phát âm và định hình cho răng vĩnh viễn sau này. Bố mẹ cần lưu ý chăm sóc đúng cách.',
                'content' => '<p>Nhiều phụ huynh cho rằng răng sữa sẽ rụng đi nên không cần chăm sóc kỹ. Đây là một quan niệm hoàn toàn sai lầm. Răng sữa sâu không chỉ gây đau đớn cho trẻ mà còn ảnh hưởng đến việc mọc răng vĩnh viễn.</p><h3>Hướng dẫn chăm sóc răng miệng theo độ tuổi:</h3><h4>Trẻ dưới 1 tuổi</h4><p>Dùng gạc mềm nhúng nước muối sinh lý để lau sạch nướu và lưỡi cho bé sau mỗi cữ bú hoặc ăn dặm.</p><h4>Trẻ từ 1-3 tuổi</h4><p>Bắt đầu cho trẻ làm quen với bàn chải đánh răng lông mềm và kem đánh răng không chứa flour (hoặc lượng rất ít).</p><h4>Trẻ từ 3-6 tuổi</h4><p>Hướng dẫn trẻ tự đánh răng dưới sự giám sát của người lớn, sử dụng kem đánh răng có flour dành riêng cho trẻ em với lượng bằng hạt đậu.</p><p>Đừng quên đưa trẻ đi khám răng định kỳ 6 tháng/lần để bác sĩ theo dõi và xử lý kịp thời các vấn đề về răng miệng nhé!</p>',
                'post_type' => 'news',
                'author_id' => $doctor->id,
                'specialty_id' => $specialties->where('name', 'Nha khoa')->first()?->id ?? ($specialties->first()->id ?? null),
            ]
        ];

        foreach ($posts as $index => $postData) {
            Post::create([
                'title' => $postData['title'],
                'slug' => Str::slug($postData['title']),
                'summary' => $postData['summary'],
                'content' => $postData['content'],
                'thumbnail_url' => 'https://ui-avatars.com/api/?name=Post+' . ($index + 1) . '&background=random&size=400',
                'specialty_id' => $postData['specialty_id'],
                'post_type' => $postData['post_type'],
                'view_count' => rand(50, 1000),
                'author_id' => $postData['author_id'],
                'is_published' => true,
                'published_at' => now()->subDays(rand(1, 30)),
            ]);
        }
    }
}
