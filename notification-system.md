# Kế hoạch Triển khai Hệ thống Thông báo Tự động (Notification System)

## 1. Phân tích Yêu cầu (Analysis)
- **Mục tiêu:** Tự động hoá hoàn toàn việc thông báo qua Email và Web UI cho bệnh nhân khi đặt lịch, nhắc lịch, và khi bị huỷ lịch.
- **Đối tượng:** Bệnh nhân lớn tuổi/ít dùng công nghệ -> Email cần thiết kế chữ to, rõ ràng (1 cột, nút to, tiếng Việt đơn giản).
- **Công nghệ:** Laravel 12 (Queue, Job, Task Scheduling, Mailable, Service Pattern).

---

## 2. Các Phase triển khai (Task Breakdown)

### Phase 1: Chuẩn bị Cơ sở dữ liệu & Cấu hình
- **Tác vụ 1.1:** Tạo migration bổ sung cột `reminded_2h` và `reminded_30m` (boolean, default 0) vào bảng `appointments`.
- **Tác vụ 1.2:** Định nghĩa các Relationship trong Model `Appointment` (`PatientProfile`, `DoctorProfile`, `Specialty`, `Room`, `User`).
- **Tác vụ 1.3:** Cấu hình Queue Driver trong `.env` (`QUEUE_CONNECTION=database`) và chạy `php artisan queue:table` & `migrate` (nếu chưa có bảng jobs).

### Phase 2: Xây dựng Tầng Service (Business Logic)
- **Tác vụ 2.1:** Tạo `App\Services\NotificationService` đảm nhiệm việc ghi bản ghi vào bảng `notifications` (channel `in_web`). Controller/Job tuyệt đối không được ghi Notification trực tiếp.
- **Tác vụ 2.2:** Tạo `App\Services\AlternativeDoctorService` với hàm `findAlternatives(Appointment $appointment)`.
  - *Logic Tầng 1:* Tìm bác sĩ cùng chuyên khoa, có slot trống trong cùng ngày bị huỷ. Nếu không có, mở rộng tìm trong 3 ngày tiếp theo.
  - *Logic Tầng 2:* Nếu cả 3 ngày tới vẫn không có ai, trả về rỗng để Email hiển thị nút bấm dẫn thẳng ra trang danh sách bác sĩ theo chuyên khoa đó.
- **Tác vụ 2.3:** Update `BookingService::createAppointment()`: Kiểm tra nếu thời điểm đặt lịch cách giờ khám `< 2 tiếng`, set luôn cờ `reminded_2h = 1` trước khi lưu vào DB (bỏ qua mốc gửi nhắc 2h để tránh rối cho bệnh nhân).

### Phase 3: Xây dựng Giao diện Email (Blade Templates)
- **Tác vụ 3.1:** Tạo Layout chung `resources/views/emails/layouts/base.blade.php` (chuẩn responsive 1 cột, chữ body >= 16px, title >= 22px, nút xanh đậm >= 48px bo góc, có hotline ở footer). Tiếng Việt đơn giản, ngắn gọn.
- **Tác vụ 3.2:** Tạo 3 file view cho 3 loại email: `booking-confirmation.blade.php`, `appointment-reminder.blade.php`, `cancellation.blade.php` (kèm theo thông tin gợi ý tối đa 3 bác sĩ có nút bấm dẫn link điền sẵn ngày).

### Phase 4: Tầng Mail & Job (Asynchronous Logic)
- **Tác vụ 4.1:** Tạo 3 lớp Mailable trong `App\Mail`: `BookingConfirmationMail`, `AppointmentReminderMail`, `CancellationMail`. Không dùng markdown mail.
- **Tác vụ 4.2:** Tạo 3 lớp Job (implement `ShouldQueue`) trong `App\Jobs`. Job không chứa business logic, chỉ gọi `NotificationService` và `Mail::to()->send()`.
  - `SendBookingConfirmationJob`, `SendAppointmentReminderJob`, `SendCancellationNotificationJob`.
  - *Cấu hình Retry (SMTP Lỗi):* Cấu hình public `$tries = 3` và public `$backoff = [300, 300, 300]` (cách nhau 5 phút). Quá 3 lần sẽ rớt vào bảng `failed_jobs`.

### Phase 5: Tích hợp Controller & Scheduler
- **Tác vụ 5.1:** Update `BookingController`: dispatch `SendBookingConfirmationJob` sau khi đặt lịch thành công.
- **Tác vụ 5.2:** Update `AppointmentController`: dispatch `SendCancellationNotificationJob` khi huỷ lịch.
- **Tác vụ 5.3:** Tạo `App\Console\Commands\RemindAppointmentsCommand` có logging chi tiết số lịch được nhắc.
- **Tác vụ 5.4:** Khai báo Command chạy mỗi 5 phút trong `routes/console.php`. Truy vấn các appointment (trạng thái pending/checked_in) sắp tới giờ (2h hoặc 30m), chưa gửi nhắc, sau đó dispatch `SendAppointmentReminderJob` và update cờ `reminded_*`.

---

## 3. Phân công Agent
- **backend-specialist**: Xử lý toàn bộ logic Queue, Job, Service, Command, Mail, và tích hợp Controller.
- **frontend-specialist**: Chịu trách nhiệm code UI cho các file Blade template email theo chuẩn thiết kế người lớn tuổi.
- **database-architect**: Viết Migration và tối ưu hoá câu query trong Scheduler Command để đảm bảo quét hàng nghìn lịch mỗi 5 phút không bị thắt cổ chai.

---

## 4. Tiêu chí nghiệm thu (Verification)
- [ ] Tính năng Đặt lịch dispatch thành công Job và gửi email chuẩn UI.
- [ ] Cronjob chạy mỗi 5 phút phát hiện đúng lịch sắp diễn ra, update cờ reminded và gửi email.
- [ ] Bệnh nhân bị huỷ lịch nhận được email gợi ý tối đa 3 bác sĩ chính xác.
- [ ] Log hệ thống không có lỗi kẹt Queue hoặc gửi lặp email.
