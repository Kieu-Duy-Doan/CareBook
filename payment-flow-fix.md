# Kế hoạch chỉnh sửa Luồng Thanh Toán (Payment Flow Fix)

## Overview
Giải quyết lỗ hổng mã QR tĩnh của SePay. Hiện tại QR quét mã thanh toán đang cố định và không bao giờ hết hạn ở phía Backend. Chúng ta sẽ triển khai cơ chế **Mã giao dịch dùng một lần (Intent Code)** có TTL 5 phút. Nếu bệnh nhân chuyển khoản vào QR đã hết hạn, giao dịch sẽ được ghi nhận vào DB nhưng bị đánh dấu là "Cần xử lý thủ công" (Needs Review) thay vì tự động gạch nợ.

## Project Type
BACKEND & FRONTEND (Web)

## Success Criteria
- [ ] Bấm "Tạo mới QR" sẽ sinh ra một mã QR hoàn toàn mới (ảnh mới, nội dung chuyển khoản mới).
- [ ] Quét mã QR hợp lệ trong 5 phút -> Tự động gạch nợ thành công.
- [ ] Quét mã QR đã hết hạn -> Tiền vẫn ghi nhận vào hệ thống nhưng trạng thái là `needs_review`, không gạch nợ, báo cảnh báo đỏ cho Lễ tân.

## Tech Stack
- Laravel Cache (quản lý thời gian sống TTL 5 phút của mã QR).
- Database MySQL (Bổ sung ENUM và cột mới để lưu vết lịch sử giao dịch rõ ràng hơn).
- JavaScript (cập nhật QR trên giao diện).

## File Structure
- `database/migrations/2026_07_14_000003_create_payments_table.php`
- `app/Http/Controllers/Receptionist/PaymentController.php`
- `app/Services/SePayService.php`
- `app/Services/PaymentService.php`

## Task Breakdown

### Task 1: Cập nhật Database Schema
- **Agent:** `database-architect` (hoặc `backend-specialist`)
- **Action:** Sửa file migration `create_payments_table.php` (vì DB chưa có dữ liệu quan trọng, ta có thể sửa trực tiếp migration).
- **INPUT → OUTPUT:**
  - Sửa `enum('status', ['pending', 'completed', 'refunded', 'needs_review'])`.
  - Thêm cột `$table->string('intent_code')->nullable()->after('transaction_code');` để dễ truy vết.
- **VERIFY:** Chạy lệnh `php artisan migrate:fresh --seed` (nếu có seed) hoặc `php artisan migrate:refresh` thành công.

### Task 2: Cập nhật hàm tạo QR
- **Agent:** `backend-specialist`
- **Action:** Sửa `PaymentController@create` và `SePayService@generateVietQrUrl`.
- **INPUT → OUTPUT:** 
  - Khi render trang checkout hoặc khi có request `?renew=1`, sinh mã `$intentCode = 'APT' . $appointment->id . '-' . Str::upper(Str::random(5));`.
  - Lưu vào `Cache::put('qr_intent_' . $intentCode, $appointment->id, now()->addMinutes(5));`.
  - Truyền `$intentCode` thay vì `$appointment->appointment_code` vào nội dung sinh ảnh VietQR.
- **VERIFY:** Giao diện hiển thị ảnh QR chứa nội dung chuyển khoản là mã Random. F5 hoặc bấm "Tạo mới" thì mã này thay đổi.

### Task 3: Cập nhật Webhook xử lý thanh toán
- **Agent:** `backend-specialist`
- **Action:** Sửa `PaymentService@processSePayWebhook`.
- **INPUT → OUTPUT:**
  - Trích xuất `intent_code` từ text chuyển khoản ngân hàng.
  - Kiểm tra `Cache::get('qr_intent_' . $intentCode)`.
  - **Nếu có Cache:** Lấy `appointment_id`, thanh toán `completed`, gạch nợ các `clinical_visits`, xoá Cache.
  - **Nếu KHÔNG có Cache (QR hết hạn):** Lấy `appointment_id` từ việc parse lại chuỗi (bỏ đoạn random đi). Tạo `Payment` với status `needs_review`, KHÔNG gạch nợ `clinical_visits`, gửi Notification cảnh báo lỗi cho Lễ tân.
- **VERIFY:** Bắn test webhook bằng Postman với mã hợp lệ và mã đã hết hạn.

## Phase X: Verification
- [ ] Chạy lại `php artisan migrate:refresh` không lỗi.
- [ ] Giả lập Webhook gửi request với nội dung QR hợp lệ -> Lịch hẹn chuyển sang Đã thanh toán.
- [ ] Giả lập Webhook gửi request với nội dung QR đã hết hạn (chờ quá 5 phút hoặc xoá cache) -> Trạng thái Payment là `needs_review`, không gạch nợ.

---
## ✅ PHASE X COMPLETE
- Lint: [ ]
- Build: [ ]
- Test: [ ]
- Date: [ ]
