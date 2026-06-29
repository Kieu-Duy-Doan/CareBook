# Tài liệu chức năng: Quản lý lịch sử đặt lịch / xem chi tiết lịch hẹn

## Mục đích
Chức năng này dùng để hiển thị thông tin chi tiết của một lịch hẹn sau khi người dùng hoặc admin bấm vào “xem chi tiết”. Trong phiên bản hiện tại, giao diện chính tập trung ở phần admin và có 3 nhóm thông tin quan trọng:
- Kết quả khám
- Đơn thuốc
- Phí dịch vụ

> Lưu ý: hiện tại phần “Phí dịch vụ” chưa có một mục riêng trong giao diện chi tiết lịch hẹn của appointment detail, nhưng có dữ liệu thanh toán liên quan ở module clinical visit.

---

## 1. File điều hướng / route liên quan

### 1.1 [routes/web.php](../routes/web.php)
- Định nghĩa các route cho quản trị lịch hẹn và bệnh án:
  - Route quản lý lịch hẹn: `admin.appointments.*`
  - Route quản lý lượt khám lâm sàng: `admin.clinical-visits.*`
  - Route lịch sử đặt khám phía bệnh nhân: `patient.appointments.*`
  - Route kết quả khám và đơn thuốc phía bệnh nhân: `patient.records.*`, `patient.prescriptions.*`
- Đây là điểm bắt đầu để truy cập vào chức năng xem chi tiết.

---

## 2. Controller xử lý chính

### 2.1 [app/Http/Controllers/Admin/AppointmentController.php](../app/Http/Controllers/Admin/AppointmentController.php)
- Chứa logic cho màn hình chi tiết lịch hẹn admin.
- Hàm `show($id)`:
  - Lấy thông tin appointment bằng `Appointment::with(...)`
  - Load các quan hệ liên quan: bệnh nhân, bác sĩ, chuyên khoa, phòng, clinical visits, medical record, prescription, lịch sử trạng thái
  - Trả dữ liệu cho view [resources/views/admin/appointments/show.blade.php](../resources/views/admin/appointments/show.blade.php)

### 2.2 [app/Http/Controllers/Admin/ClinicalVisitController.php](../app/Http/Controllers/Admin/ClinicalVisitController.php)
- Dùng cho màn hình “giám sát khám lâm sàng” và xem chi tiết hồ sơ bệnh án / đơn thuốc.
- Hàm `show($id)`:
  - Lấy thông tin lượt khám
  - Tìm medical record liên quan đến appointment
  - Tìm prescription liên quan đến medical record
  - Trả dữ liệu cho view [resources/views/admin/clinical-visits/show.blade.php](../resources/views/admin/clinical-visits/show.blade.php)

### 2.3 [app/Http/Controllers/Patient/AppointmentController.php](../app/Http/Controllers/Patient/AppointmentController.php)
- Hiện tại file này đang rỗng.
- Đây là nơi dự kiến xử lý màn hình lịch sử đặt lịch phía bệnh nhân, nhưng chưa được triển khai.

### 2.4 [app/Http/Controllers/Patient/MedicalRecordController.php](../app/Http/Controllers/Patient/MedicalRecordController.php)
- Hiện tại file này đang rỗng.
- Đây là nơi dự kiến xử lý trang kết quả khám phía bệnh nhân.

### 2.5 [app/Http/Controllers/Patient/PrescriptionController.php](../app/Http/Controllers/Patient/PrescriptionController.php)
- Hiện tại file này đang rỗng.
- Đây là nơi dự kiến xử lý trang đơn thuốc phía bệnh nhân.

---

## 3. View giao diện chính

### 3.1 [resources/views/admin/appointments/show.blade.php](../resources/views/admin/appointments/show.blade.php)
- Đây là file giao diện chính cho chức năng xem chi tiết lịch hẹn trong admin.
- Có 3 nhóm nội dung chính:
  1. Tổng quan lịch hẹn
  2. Bệnh án / Kết quả khám
  3. Đơn thuốc
- Trong phần “Tổng quan” hiển thị:
  - thông tin lịch hẹn
  - thông tin bệnh nhân
  - chỉ số sinh tồn
  - quy trình khám
- Trong phần “Bệnh án” hiển thị:
  - chẩn đoán
  - kết luận lâm sàng
  - kế hoạch điều trị / dặn dò
  - hẹn tái khám
- Trong phần “Đơn thuốc” hiển thị:
  - bảng thuốc
  - lời dặn
  - hỗ trợ in đơn thuốc

### 3.2 [resources/views/admin/clinical-visits/show.blade.php](../resources/views/admin/clinical-visits/show.blade.php)
- Giao diện chi tiết cho một lượt khám lâm sàng.
- Có phần riêng về thanh toán / phí dịch vụ:
  - chi phí khám
  - trạng thái thanh toán
  - hình thức thanh toán
  - người thu ngân
- Ngoài ra còn hiển thị:
  - ghi nhận lâm sàng
  - hồ sơ bệnh án
  - đơn thuốc

### 3.3 [resources/views/admin/clinical-visits/index.blade.php](../resources/views/admin/clinical-visits/index.blade.php)
- Trang danh sách lượt khám lâm sàng.
- Là màn hình chuyển tiếp để mở vào chi tiết bệnh án / khám lâm sàng.

---

## 4. Model dữ liệu

### 4.1 [app/Models/Appointment.php](../app/Models/Appointment.php)
- Đại diện cho một lịch hẹn.
- Chứa các quan hệ:
  - `patientProfile()`
  - `doctor()`
  - `clinicalVisits()`
  - `medicalRecord()`
  - `logs()`
- Là model trung tâm cho chức năng xem chi tiết lịch hẹn.

### 4.2 [app/Models/ClinicalVisit.php](../app/Models/ClinicalVisit.php)
- Đại diện cho một lượt khám lâm sàng.
- Chứa các trường thanh toán quan trọng:
  - `payment_amount`
  - `payment_status`
  - `payment_method`
  - `collected_by`
  - `paid_at`
- Đây là model liên quan trực tiếp tới phần “Phí dịch vụ”.

### 4.3 [app/Models/MedicalRecord.php](../app/Models/MedicalRecord.php)
- Đại diện cho hồ sơ bệnh án / kết quả khám.
- Chứa các thông tin:
  - chẩn đoán
  - mã ICD-10
  - kết luận
  - lời khuyên
  - ngày tái khám
- Đây là model cho phần “Kết quả khám”.

### 4.4 [app/Models/Prescription.php](../app/Models/Prescription.php)
- Đại diện cho đơn thuốc.
- Chứa:
  - `items` (danh sách thuốc)
  - `general_note` (lưu ý dùng thuốc)
  - `diagnosis_note`
- Đây là model cho phần “Đơn thuốc”.

---

## 5. Migration / database

### 5.1 [database/migrations/2026_00_11_000000_create_appointments_table.php](../database/migrations/2026_00_11_000000_create_appointments_table.php)
- Tạo bảng `appointments`.
- Lưu thông tin lịch hẹn, trạng thái, thời gian, tình trạng khám.

### 5.2 [database/migrations/2026_00_12_000000_create_clinical_visits_table.php](../database/migrations/2026_00_12_000000_create_clinical_visits_table.php)
- Tạo bảng `clinical_visits`.
- Lưu lượt khám lâm sàng và các thông tin phí dịch vụ.

### 5.3 [database/migrations/2026_00_13_000000_create_medical_records_table.php](../database/migrations/2026_00_13_000000_create_medical_records_table.php)
- Tạo bảng `medical_records`.
- Lưu hồ sơ bệnh án / kết quả khám.

### 5.4 [database/migrations/2026_00_14_000000_create_prescriptions_table.php](../database/migrations/2026_00_14_000000_create_prescriptions_table.php)
- Tạo bảng `prescriptions`.
- Lưu đơn thuốc.

---

## 6. Mapping theo yêu cầu: Kết quả khám / Đơn thuốc / Phí dịch vụ

### A. Kết quả khám
- Chịu trách nhiệm chính bởi:
  - [app/Models/MedicalRecord.php](../app/Models/MedicalRecord.php)
  - [resources/views/admin/appointments/show.blade.php](../resources/views/admin/appointments/show.blade.php)
  - [resources/views/admin/clinical-visits/show.blade.php](../resources/views/admin/clinical-visits/show.blade.php)
- Hiển thị: chẩn đoán, kết luận, kế hoạch điều trị, hẹn tái khám.

### B. Đơn thuốc
- Chịu trách nhiệm chính bởi:
  - [app/Models/Prescription.php](../app/Models/Prescription.php)
  - [resources/views/admin/appointments/show.blade.php](../resources/views/admin/appointments/show.blade.php)
  - [resources/views/admin/clinical-visits/show.blade.php](../resources/views/admin/clinical-visits/show.blade.php)
- Hiển thị: danh sách thuốc, số lượng, cách dùng, lưu ý.

### C. Phí dịch vụ
- Chịu trách nhiệm chính bởi:
  - [app/Models/ClinicalVisit.php](../app/Models/ClinicalVisit.php)
  - [resources/views/admin/clinical-visits/show.blade.php](../resources/views/admin/clinical-visits/show.blade.php)
- Hiển thị: chi phí khám, trạng thái thanh toán, phương thức thanh toán.
- Hiện tại chưa có một section riêng bằng tên “Phí dịch vụ” trong màn hình chi tiết lịch hẹn của appointment detail.

---

## 7. Tóm tắt luồng hoạt động
1. Admin hoặc bệnh nhân mở màn hình chi tiết lịch hẹn.
2. Route chuyển đến controller tương ứng.
3. Controller load dữ liệu appointment + medical record + prescription + clinical visit.
4. View render các phần:
   - thông tin lịch hẹn
   - kết quả khám
   - đơn thuốc
   - phí dịch vụ (nếu có dữ liệu thanh toán)

---

## 8. Ghi chú triển khai hiện tại
- Chức năng này đã có triển khai đầy đủ phần admin cho lịch hẹn và hồ sơ khám.
- Phần phía bệnh nhân hiện đang có route nhưng controller chưa được triển khai (rỗng).
- Nếu cần mở rộng, nên bổ sung một section “Phí dịch vụ” trực tiếp trong [resources/views/admin/appointments/show.blade.php](../resources/views/admin/appointments/show.blade.php) để thống nhất với các mục Kết quả khám và Đơn thuốc.
