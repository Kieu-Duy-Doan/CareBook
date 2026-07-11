# Kế hoạch cập nhật và sửa lỗi tính năng Booking

## Overview
Cập nhật và sửa lỗi module Đặt lịch (Booking) dựa trên các góp ý của giảng viên sau buổi họp demo số 5. Các thay đổi chính bao gồm việc tái cấu trúc UX luồng đặt lịch, thêm phân cấp học vị và giá tiền cho bác sĩ, sửa lỗi logic gợi ý khi hủy lịch, và thêm validation giới hạn số lượng lịch khám đang hoạt động.

## Project Type
**WEB** (Laravel, Blade)

## Success Criteria
- [x] Tên gọi và UX các luồng đặt lịch hiển thị đúng yêu cầu (Đặt lịch cơ bản & Chỉ định bác sĩ).
- [x] Bác sĩ được phân loại theo học vị (Thạc sĩ, Tiến sĩ, PGS) và có các mức giá khám tương ứng.
- [x] Logic "Đặt lịch cơ bản" random được bác sĩ rảnh theo đúng chuyên khoa và học vị.
- [x] Hủy lịch gợi ý chính xác bác sĩ thay thế (cùng chuyên khoa, học vị, rảnh đúng giờ) và auto-fill dữ liệu.
- [x] Chặn thành công việc tạo lịch hẹn nếu hồ sơ bệnh nhân đang có lịch active.
- [x] Không có lỗi phát sinh trong toàn bộ quy trình booking.

## Tech Stack
- **Backend:** Laravel (PHP)
- **Frontend:** Blade Templates, HTML, CSS, JS
- **Database:** MySQL

## File Structure (Dự kiến bị ảnh hưởng)
- **Database:** Migrations mới cho bảng `doctors` hoặc bảng giá.
- **Models:** `Doctor`, `Appointment`/`Booking`, `PatientProfile`.
- **Controllers:** `BookingController`, API Controllers xử lý list bác sĩ.
- **Views:** Các file blade trong `resources/views/patient/booking/`.
- **Routes:** `routes/web.php` hoặc `routes/api.php`.

## Task Breakdown

### Phase 1: Database & Models (Foundation)
- **[x] Task 1.1:** Thêm cột học vị (`degree`) và giá cơ bản vào bảng `doctors` (hoặc tạo bảng giá riêng).
  - **Agent:** `backend-specialist`
  - **Skill:** `database-design`
  - **INPUT:** Chạy lệnh `make:migration` tạo migration cập nhật bảng.
  - **OUTPUT:** File migration và Model `Doctor` được cập nhật.
  - **VERIFY:** Chạy `php artisan migrate` thành công.
- **[x] Task 1.2:** Cập nhật seeders hoặc factories để có dữ liệu test cho học vị và giá.
  - **Agent:** `backend-specialist`
  - **INPUT:** Sửa file seeder của bác sĩ.
  - **OUTPUT:** Có đủ các cấp bậc bác sĩ khi chạy seed.
  - **VERIFY:** Database có dữ liệu đúng.

### Phase 2: Backend Logic & Validation
- **[x] Task 2.1:** Viết validation giới hạn 1 lịch active cho mỗi `patient_profile_id`.
  - **Agent:** `backend-specialist`
  - **INPUT:** Request tạo mới lịch khám.
  - **OUTPUT:** Trả về lỗi nếu profile đã có lịch chưa hoàn thành.
  - **VERIFY:** Đặt lịch thứ 2 cho cùng 1 hồ sơ báo lỗi. Đặt cho hồ sơ khác thành công.
- **Task 2.2:** API/Logic Lọc bác sĩ cho "Luồng Đặt lịch cơ bản" (chọn chuyên khoa + học vị -> random bác sĩ rảnh trong khung giờ).
  - **Agent:** `backend-specialist`
  - **OUTPUT:** Hàm trả về ID bác sĩ được chọn random hoặc thông báo hết chỗ.
  - **VERIFY:** Gọi API trả về random bác sĩ hợp lệ.
- **Task 2.3:** API/Logic Lấy danh sách bác sĩ cho "Luồng Chỉ định bác sĩ" (bắt buộc filter theo chuyên khoa trước).
  - **Agent:** `backend-specialist`
  - **OUTPUT:** API trả về list bác sĩ thuộc chuyên khoa (kèm mã bác sĩ và giá chỉ định).
- **Task 2.4:** Sửa logic "Gợi ý bác sĩ" khi hủy lịch.
  - **Agent:** `backend-specialist`
  - **OUTPUT:** API tìm bác sĩ thay thế cùng chuyên khoa, cùng học vị và rảnh đúng khung giờ đó.

### Phase 3: Frontend / UI Updates (Blade)
- **Task 3.1:** Cập nhật UI màn hình chọn luồng đặt lịch: "Đặt lịch cơ bản" và "Chỉ định bác sĩ".
  - **Agent:** `frontend-specialist`
  - **INPUT:** `resources/views/patient/booking/index.blade.php`
### Phase 3: Frontend Update (Booking Flow)
- **[x] Task 3.1:** Cập nhật UI chọn Bác sĩ theo UI/UX mới.
  - Sửa step 2 (chọn phương thức) để rõ ràng 2 luồng.
  - Cho phép chọn "Học vị" khi chọn "Đặt lịch cơ bản".
- **[x] Task 3.2:** Tích hợp logic giá tiền động.
  - Giao diện xác nhận (step 4) hiển thị đúng giá khám.
- **[x] Task 3.3:** Sửa logic Suggestion khi huỷ lịch.
  - Frontend gọi logic tìm bác sĩ thay thế và auto-fill.
  - Bypass form (nhảy thẳng sang step xác nhận) nếu đủ thông tin.

### Phase 4: Final Testing & QA
- **[x] Task 4.1:** Test luồng Đặt lịch cơ bản.
- **[x] Task 4.2:** Test luồng Chỉ định bác sĩ.
- **[x] Task 4.3:** Test luồng Bác sĩ gợi ý (khi bác sĩ cũ bận).
- **[x] Task 4.4:** Kiểm tra tổng quát và sửa lỗi.
- [ ] Chạy `python .agents/scripts/checklist.py .` để verify các vấn đề tiềm ẩn.
