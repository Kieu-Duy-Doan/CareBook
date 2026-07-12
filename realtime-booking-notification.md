# Kế hoạch nâng cấp Real-time (Đặt lịch & Thông báo)

## 0. Socratic Gate (Các câu hỏi làm rõ trước khi code)

Trước khi chúng ta bắt tay vào viết code vào ngày mai, hãy suy nghĩ và trả lời các câu hỏi sau để chốt phương án kiến trúc:

1. **Phiên bản Laravel của bạn:** Bạn đang sử dụng phiên bản Laravel 11 (dùng được Reverb) hay bản thấp hơn (chỉ dùng được Laravel WebSockets)? -> tôi đang dùng laravel 12. Dùng Reverb
2. **Lựa chọn Hàng đợi (Queue):** Bạn muốn cấu hình Hàng đợi (Queue) bằng `database` để mô phỏng hệ thống lớn và ghi điểm tối đa với hội đồng, hay muốn dùng cấu hình `sync` (không cần chạy `queue:work`) cho dễ demo? -> dùng database để mô phỏng hệ thống lớn và ghi điểm tối đa với hội đồng.
3. **Hiệu ứng Giao diện (UI) cho Đặt lịch:** Ở Bước 3 (Chọn Giờ), khi 1 khung giờ vừa bị người khác "đặt cọc" (khoá), bạn muốn ô giờ đó mờ đi và không bấm được (disabled) trên giao diện hay ẩn nó đi hoàn toàn? -> mờ đi và không bấm được (disabled) trên giao diện

---

## 1. Mục tiêu (Goals)

- Triển khai một máy chủ WebSocket nội bộ chạy hoàn toàn offline trên thiết bị Local (Laragon).
- Nâng cấp luồng Thông báo (Notifications) sang Real-time: Đẩy thẳng xuống trình duyệt người dùng mà không cần gọi API (Polling).
- Nâng cấp Bước 3 Đặt lịch (Chọn Giờ) sang Real-time: Ngăn chặn triệt để lỗi Double-booking (2 người đặt trùng 1 giờ) và hiển thị trực quan trạng thái khoá giờ.
- **TUYỆT ĐỐI BẢO TOÀN LOGIC NGHIỆP VỤ CŨ:** Việc nâng cấp Real-time chỉ thay đổi về mặt giao tiếp thời gian thực, mọi logic nghiệp vụ cốt lõi từ đợt nâng cấp trước (`booking-update.md`) bắt buộc phải được giữ nguyên:
  - Logic phân loại luồng "Đặt lịch cơ bản" vs "Chỉ định bác sĩ".
  - Phân cấp học vị, giá khám động, random bác sĩ.
  - Chức năng gợi ý bác sĩ thay thế (Fast-track bypass form).
  - Validation giới hạn 1 lịch active.

---

## 2. Các bước triển khai (Phases)

### Phase 1: Cài đặt và Cấu hình Hệ thống (Infrastructure)

- **Backend:** Cài đặt Laravel Broadcasting bằng lệnh `php artisan install:broadcasting`.
- **Môi trường:** Cấu hình file `.env` sử dụng `BROADCAST_DRIVER=reverb` (hoặc `pusher`), và `QUEUE_CONNECTION=database`.
- **Database:** Khởi tạo bảng jobs cho hàng đợi (`php artisan queue:table` & `php artisan migrate`).
- **Frontend:** Cài đặt thư viện `laravel-echo` và `pusher-js` thông qua NPM. Biên dịch lại tài sản bằng Vite.

### Phase 2: Triển khai Real-time Thông báo (Notifications)

- **Tạo Event:** Tạo class `NotificationCreatedEvent` (implements `ShouldBroadcast`).
- **Phân quyền (Auth):** Khai báo và xác thực Private Channel `user.{id}` trong `routes/channels.php`. Đảm bảo chỉ chủ nhân thông báo mới nhận được.
- **Trigger Event:** Sửa logic tại `NotificationController` và các Background Service để phát (fire) sự kiện mỗi khi bản ghi Notification được tạo vào database.
- **Giao diện (Blade):** Chèn đoạn mã JS sử dụng `window.Echo.private()` vào layout tổng (`patient.blade.php`). Xử lý logic tăng số thông báo chưa đọc và hiển thị Toast Alert.

### Phase 3: Triển khai Real-time Khung Giờ Đặt Lịch (Booking Slots)

- **Database Logic:** Cập nhật `BookingController@store` thêm logic Pessimistic Locking (Sử dụng `DB::transaction` và `lockForUpdate()`) để chống xung đột dữ liệu.
- **Tạo Event:** Tạo `SlotBookedEvent` phát thông tin về ngày, giờ, chuyên khoa vừa bị đặt.
- **Giao diện (Blade):** Ở file `step3.blade.php`, nhúng JS lắng nghe kênh Public `booking.date.{date}`. Nếu nhận tín hiệu có khung giờ vừa bị đặt, dùng JS tìm phần tử `<input type="radio">` của giờ đó và vô hiệu hoá (disabled) nó.

### Phase 4: Kiểm thử (Testing & Verification)

- Khởi động 2 cửa sổ Terminal:
    1. `php artisan reverb:start`
    2. `php artisan queue:work`
- Mở 2 cửa sổ ẩn danh để đóng vai 2 Bệnh nhân khác nhau:
    - **Kiểm thử Booking:** Bệnh nhân A vừa chốt giờ X, màn hình Bệnh nhân B tự động mờ ô giờ X.
    - **Kiểm thử Notification:** Tạo 1 hành động tác động tới Bệnh nhân A (VD: Bác sĩ đồng ý lịch), Bệnh nhân A lập tức nảy popup thông báo.

### Phase 5: Báo cáo Tổng kết (Reporting)

- Tạo một bản báo cáo chi tiết (`walkthrough.md`) trình bày cụ thể:
  - Các thay đổi đã thực hiện về cấu hình và kiến trúc.
  - Hướng dẫn chi tiết cách chạy demo trước giảng viên (cách bật terminal, luồng hoạt động).
  - Giải thích tóm tắt về WebSockets và Queue để bạn dễ dàng đưa vào báo cáo Word đồ án tốt nghiệp.

---

_Bản kế hoạch này tuân thủ đúng chuẩn MVC và kiến trúc Event-Driven của Laravel. Rất phù hợp để báo cáo thành tích vào Đồ án tốt nghiệp._
