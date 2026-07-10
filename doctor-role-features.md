# Kế hoạch phát triển chức năng Role Bác sĩ (Doctor Role)

## Overview (Tổng quan)
Xây dựng các chức năng dành riêng cho role Bác sĩ (Doctor) trong hệ thống CareBook, đảm bảo tách biệt hoàn toàn về route, controller và view so với các role khác (admin, patient, receptionist) để dễ dàng quản lý và tránh xung đột.

## Project Type
WEB

## Success Criteria (Tiêu chí thành công)
- Bác sĩ có thể đăng nhập, đăng xuất và khôi phục mật khẩu.
- Bác sĩ có thể xem và quản lý lịch hẹn (thay đổi trạng thái lịch hẹn).
- Bác sĩ có thể quản lý giám sát lâm sàng (CRUD trên bảng `clinical_visits`), thanh toán nếu cần, và chỉ định bệnh nhân đi khám ở các phòng khác (nhóm theo lịch hẹn).
- Bác sĩ có thể xem lịch sử khám bệnh của bệnh nhân (bệnh án, đơn thuốc).
- Bác sĩ có thể cập nhật thông tin cá nhân (Profile).
- Mọi logic được đặt trong `App\Http\Controllers\Doctor\`, view trong `resources/views/doctor/` và route được group với prefix `/doctor` và middleware `role:doctor`.

## Tech Stack
- Backend: Laravel 12 (PHP)
- Frontend: Blade Templates, Bootstrap/Tailwind (theo UI hiện có), jQuery/Alpine.js
- Database: MySQL (Eloquent ORM)

## File Structure (Cấu trúc file dự kiến)
```text
routes/
└── web.php (Thêm group route prefix '/doctor')

app/Http/Controllers/Doctor/
├── AuthController.php (Đăng nhập, Quên mật khẩu)
├── DashboardController.php (Trang chủ bác sĩ)
├── AppointmentController.php (Quản lý lịch hẹn)
├── ClinicalVisitController.php (Giám sát lâm sàng)
├── PatientHistoryController.php (Lịch sử khám bệnh nhân)
└── ProfileController.php (Quản lý thông tin cá nhân)

resources/views/doctor/
├── auth/
│   ├── login.blade.php
│   └── forgot-password.blade.php
├── layouts/
│   └── doctor.blade.php
├── dashboard/
│   └── index.blade.php
├── appointments/
│   ├── index.blade.php
│   └── show.blade.php
│   └── history.blade.php
├── clinical_visits/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
└── profile/
    └── index.blade.php
```

## Task Breakdown (Chi tiết công việc)

### 1. Thiết lập Cấu trúc và Xác thực (Auth)
- **Agent**: `backend-specialist`
- **Skills**: `clean-code`, `backend-architecture`
- **Priority**: P1
- **INPUT→OUTPUT→VERIFY**: 
  - *Input*: Tạo route group prefix `/doctor`, middleware `role:doctor`. Tạo `Doctor/AuthController`.
  - *Output*: Màn hình login/forgot password, logic xác thực Bác sĩ.
  - *Verify*: Đăng nhập thành công với tài khoản có role `doctor`, không cho phép role khác truy cập.

### 2. Quản lý Lịch hẹn (Appointments)
- **Agent**: `backend-specialist`, `frontend-specialist`
- **Skills**: `clean-code`
- **Priority**: P1
- **INPUT→OUTPUT→VERIFY**: 
  - *Input*: Lấy danh sách `appointments` của bác sĩ hiện tại.
  - *Output*: Trang danh sách lịch hẹn và chức năng cập nhật trạng thái (Chờ khám, Đang khám, Hoàn thành...).
  - *Verify*: Bác sĩ chỉ xem được lịch hẹn của mình, đổi trạng thái thành công lưu vào DB.

### 3. Giám sát Lâm sàng (Clinical Visits)
- **Agent**: `backend-specialist`
- **Skills**: `clean-code`
- **Priority**: P1
- **INPUT→OUTPUT→VERIFY**: 
  - *Input*: Tạo CRUD trong `Doctor/ClinicalVisitController`. Liên kết với `appointments`.
  - *Output*: Chức năng thêm phòng khám mới (nhóm theo lịch hẹn), cập nhật kết quả/thanh toán trên bảng `clinical_visits`.
  - *Verify*: Bản ghi `clinical_visits` được tạo/cập nhật đúng với `appointment_id`, `doctor_profile_id`, `room_id`.

### 4. Xem Lịch sử Bệnh nhân (Patient History)
- **Agent**: `backend-specialist`, `frontend-specialist`
- **Skills**: `clean-code`
- **Priority**: P2
- **INPUT→OUTPUT→VERIFY**: 
  - *Input*: Dựa vào `patient_id` từ lịch hẹn, truy xuất `MedicalRecord` và `Prescription`.
  - *Output*: Giao diện xem chi tiết lịch sử khám và đơn thuốc của bệnh nhân.
  - *Verify*: Hiển thị đúng dữ liệu quá khứ của bệnh nhân, không cho phép chỉnh sửa nếu không có quyền.

### 5. Quản lý Thông tin cá nhân (Profile)
- **Agent**: `backend-specialist`, `frontend-specialist`
- **Skills**: `clean-code`
- **Priority**: P2
- **INPUT→OUTPUT→VERIFY**: 
  - *Input*: Lấy dữ liệu `DoctorProfile` của user hiện tại.
  - *Output*: Form cập nhật thông tin cá nhân (tên, avatar, chuyên khoa, v.v.).
  - *Verify*: Cập nhật thành công lưu vào DB và hiển thị đúng thông tin mới.

## User Review Required & Open Questions (Cần xác nhận & Câu hỏi làm rõ)

> [!IMPORTANT]
> Vui lòng xác nhận một số điểm dưới đây trước khi bắt đầu triển khai code:

1. **Vị trí Route**: Hiện tại tôi định thêm `Route::prefix('doctor')` trực tiếp vào `routes/web.php` để giống với admin và receptionist. Bạn có muốn tách riêng ra file `routes/doctor.php` không?
Hãy tách riêng ra file `routes/doctor.php`.
2. **Layout Bác Sĩ**: Chúng ta sẽ tạo một layout dashboard hoàn toàn mới cho Bác sĩ (sidebar, header riêng) dựa trên thiết kế của Admin/Receptionist hiện tại, đúng không?
Hãy tạo 1 layout riêng cho bác sĩ dựa trên thiết kế của Admin/Receptionist hiện tại. Thêm 1 sidebar cho bác sĩ.
3. **Thanh toán tại Clinical Visits**: Bác sĩ có quyền thực hiện thu tiền trực tiếp (cập nhật trạng thái `payment_status` thành đã thanh toán trong bản ghi) hay việc này nên chuyển qua cho lễ tân thực hiện? (Nếu bác sĩ cũng có quyền thu tiền, tôi sẽ thêm chức năng thanh toán/cập nhật này vào view của bác sĩ).
Hãy thêm chức năng thanh toán cho bác sĩ tại clinical visits. Thêm bảng payments có khóa ngoại liên kết với bảng `appointments`. Bạn hãy gom tất cả những bản ghi trong `clinical_visits` với `appointment_id` giống nhau thành 1, nếu có payments thì sum payments lại. Và hiển thị tổng tiền đã thanh toán và lưu vào bảng payments. Đồng thời đổi trạng thái trong bảng `clinical_visits` thành paid.
4. **Các thay đổi cơ sở dữ liệu (Database Schema)**
    - Cập nhật bảng `appointments` để lưu trữ `payment_status` (paid, unpaid).
    - Tạo bảng `payments` lưu trữ lịch sử thanh toán liên quan đến `appointment_id`, số tiền, phương thức thanh toán, và ngày thanh toán.

## ✅ PHASE X COMPLETE
- Lint: [ ] Pass
- Security: [ ] No critical issues
- Build: [ ] Success
- Date: [Pending]
