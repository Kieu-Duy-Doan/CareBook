# Kế Hoạch: Bổ sung màn hình Lịch Sử Khám Toàn Bệnh Viện

**Project Type:** WEB / BACKEND
**Overview:**
Xây dựng chức năng cho phép xem toàn bộ lịch sử khám của bệnh viện (Hospital Appointment History). Người dùng có thể lọc xem lịch sử theo thời gian (ngày, tháng, năm trước đó...). Danh sách sẽ hiển thị các ca khám với các thông tin chi tiết:

- Thông tin cơ bản: Bệnh nhân, Bác sĩ, Ngày giờ.
- Diễn biến khám (lịch đó khám như thế nào).
- Tổng thu của lịch khám đó.
- Các thông tin khác (đơn thuốc, trạng thái...).

## 🛑 Socratic Gate (Open Questions)

Vui lòng trả lời các câu hỏi sau để chốt yêu cầu:

1. **Phân quyền truy cập:** Màn hình này dành cho ai? (Chỉ Admin, hay cả Lễ tân / Quản lý chi nhánh)?-> danh cho admin
2. **Hiệu suất & Mặc định:** Vì dữ liệu toàn bệnh viện sẽ rất lớn, danh sách này có phân trang (pagination) không? Khi mới mở trang, dữ liệu mặc định hiển thị là của "Tháng hiện tại" hay "Ngày hôm nay" để tránh load chậm?-> phải có phân trang, và dữ liệu mặc định là ngày hôm nay.
3. **Xuất file (Export):** Với màn hình tổng hợp lịch sử và doanh thu như thế này, bạn có cần chức năng "Xuất Excel" (Export to CSV/Excel) không?-> cần có chức năng xuất excel.

## Success Criteria

- Có giao diện danh sách lịch sử khám toàn bệnh viện (Table/List).
- Có bộ lọc (Filter) theo khoảng thời gian (Từ ngày - Đến ngày, Tháng này, Tháng trước, v.v.).
- Mỗi dòng trong danh sách hiển thị đúng diễn biến khám (`clinicalVisits`), tổng thu (`payments`), và thông tin liên quan.
- Phân trang hoạt động tốt để tối ưu hiệu suất.

## Tech Stack

- Laravel (Backend)
- Frontend (Blade / Vue / React) tuỳ hệ thống.-> blade

## File Structure (Dự kiến)

- `app/Http/Controllers/HospitalHistoryController.php` (hoặc method mới trong `AppointmentController`).
- `resources/views/hospital/history/index.blade.php` (Giao diện hiển thị).

## Task Breakdown

| Task ID | Tên Task                       | Agent               | Skills          | Priority | Dependencies | INPUT → OUTPUT → VERIFY                                                                                                                                                               |
| ------- | ------------------------------ | ------------------- | --------------- | -------- | ------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1       | Viết Query & Lọc Dữ liệu       | backend-specialist  | api-patterns    | P1       | -            | INPUT: Param thời gian (date range). OUTPUT: Query lấy danh sách Appointment có eager load (`clinicalVisits`, `payments`) + logic lọc. VERIFY: Query trả về đúng data theo thời gian. |
| 2       | Tính Tổng Thu từng lịch        | backend-specialist  | clean-code      | P1       | Task 1       | INPUT: Danh sách Appointment. OUTPUT: Gắn thêm trường `total_revenue` vào mỗi lịch khám. VERIFY: Hiển thị đúng tổng doanh thu của từng lịch.                                          |
| 3       | Tạo View Danh Sách & Bộ Lọc    | frontend-specialist | frontend-design | P1       | Task 2       | INPUT: Dữ liệu Backend. OUTPUT: Giao diện bảng danh sách + Form bộ lọc thời gian. VERIFY: Chọn "Tháng trước" -> danh sách cập nhật đúng.                                              |
| 4       | Hiển thị Diễn biến khám & Khác | frontend-specialist | frontend-design | P2       | Task 3       | INPUT: Lịch khám. OUTPUT: Hiển thị tóm tắt hoặc nút "Xem thêm" cho diễn biến khám. VERIFY: Hiển thị thân thiện, không vỡ layout khi nội dung dài.                                     |

## ✅ Phase X: Verification

- [ ] Checklist: Layout bảng có respnsive không, bộ lọc hoạt động không.
- [ ] Scripts: Chạy `lint_runner.py` kiểm tra code.
- [ ] Build & Test: Mở màn hình, thử chọn thời gian 1 năm trước xem có bị chậm (N+1 query) không.
