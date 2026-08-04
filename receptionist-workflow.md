# Kế hoạch thực hiện: Luồng Lễ tân / Thu ngân (Receptionist Workflow)

Tài liệu này mô tả chi tiết kế hoạch thực hiện các tính năng dành cho Lễ tân và Thu ngân theo yêu cầu của bạn.

> [!WARNING]
> **Cần Review & Phản hồi**
> Vui lòng xem xét các câu hỏi mở (Open Questions) dưới đây và trả lời trước khi tôi tiến hành lập trình.

## 1. Open Questions (Câu hỏi cần làm rõ)

1. **Xác định thời gian "Đến muộn" (Late Check-in):**
    - Theo bạn, bệnh nhân đến muộn bao nhiêu phút so với giờ hẹn (`appointment_time`) thì hệ thống mới xếp vào diện "Đến muộn" và đẩy xuống cuối hàng đợi? (Ví dụ: 15 phút, hay 30 phút, hay trễ 1 phút cũng tính là muộn?)-> 30 phút
2. **Gộp hóa đơn (Invoice Consolidation):**
    - Bạn muốn gộp thành 2 loại: "Khám lâm sàng ban đầu" và "Dịch vụ kỹ thuật/chuyên sâu". Vậy còn **Tiền thuốc (Prescriptions)** thì sao? Tiền thuốc nên được gộp chung vào Hóa đơn dịch vụ kỹ thuật, hay nên tách thành 1 loại Hóa đơn riêng (Hóa đơn nhà thuốc)? -> 3 loại đi, 1: Khám, 2: CLS & dịch vụ, 3: Thuốc

---

## 2. Proposed Changes (Các thay đổi đề xuất)

## 3. Data model (mô hình hóa dữ liệu): kiểm tra bảng dữ liệu để viết đầu ra code rõ ràng chuẩn logic.

### A. Xử lý luồng Check-in & Đến muộn

- Thêm trường `is_late` (boolean) vào bảng `appointments` (hoặc tính toán realtime lúc truy vấn).
- Cập nhật logic Check-in (ví dụ trong `AppointmentController` hoặc `CheckInService`): Khi lễ tân nhấn Check-in, so sánh giờ hiện tại với `appointment_date` + `appointment_time`. Nếu trễ hơn mốc cho phép -> đánh dấu `is_late = true`.
- Cập nhật câu truy vấn lấy danh sách hàng đợi (Queue) của Bác sĩ: Sắp xếp theo `is_late ASC` (đúng giờ lên trước, đến muộn xuống dưới), sau đó mới tới `checked_in_at ASC`.

### B. Tối ưu hóa & Gộp hóa đơn

- Chỉnh sửa `PaymentService` hoặc luồng tạo thanh toán (`PaymentController`):
- Thay vì xuất hóa đơn cho từng dịch vụ riêng lẻ, hệ thống sẽ gom nhóm các khoản phí của 1 mã lịch hẹn (`appointment_id`) thành tối đa 2-3 phiếu thu (Payment record):
    1. Loại 1: Khám lâm sàng ban đầu.
    2. Loại 2: Cận lâm sàng, dịch vụ kỹ thuật chuyên sâu.

### C. Thao tác thanh toán nhanh

- Cập nhật View chi tiết lịch hẹn của lễ tân (có thể là `resources/views/receptionist/appointments/show.blade.php`).
- Nhúng component hoặc modal thanh toán (Tiền mặt / Quét mã QR) ngay tại màn hình này. Gọi trực tiếp API thanh toán mà không cần chuyển hướng sang module Payment rườm rà.

### D. Hỗ trợ tư vấn BHYT (Tooltip / Modal)

- Truy xuất toàn bộ dữ liệu từ bảng `insurance_types` (đã xây dựng trước đó).
- Hiển thị một nút "Tra cứu BHYT" hoặc biểu tượng dấu chấm hỏi `(?)` cạnh ô nhập Mã BHYT của bệnh nhân. Khi click vào, hiển thị Modal hoặc Tooltip giải thích chi tiết mức hưởng (Ví dụ: TE -> 100%, HT -> 95%, v.v.).

---

## 3. Verification Plan (Kế hoạch kiểm thử)

### Automated Tests / Data Checks

- Kiểm tra lại logic tính thời gian đến muộn (ví dụ hẹn 09:00, check-in 09:20 -> xếp dưới).
- Kiểm tra logic gom nhóm hóa đơn không làm sai lệch tổng doanh thu.

### Manual Verification

- Đăng nhập tài khoản Lễ tân (`letan` / `Letan@123`).
- Thực hiện Check-in 1 bệnh nhân trễ giờ và xem hàng đợi ở màn hình Bác sĩ có bị đẩy xuống cuối không.
- Xem giao diện thanh toán nhanh ở màn hình chi tiết lịch hẹn có hoạt động (tạo phiếu thu) thành công không.
- Click thử Tooltip tra cứu mức hưởng BHYT.
