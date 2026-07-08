# Receptionist Features Plan

## Overview
Xây dựng các chức năng dành riêng cho vai trò Lễ tân (Receptionist) trong hệ thống phòng khám, đảm bảo tách biệt hoàn toàn về giao diện và Controller so với các vai trò khác (như Admin, Patient, Doctor) để tránh xung đột.

## Project Type
WEB

## Success Criteria
- [ ] Lễ tân có thể đăng nhập, quên mật khẩu tại trang riêng biệt.
- [ ] Lễ tân có thể quản lý lịch hẹn (CRUD) và xem danh sách khách hàng / bệnh nhân. Không cần làm chức năng export CSV.
- [ ] Lễ tân có thể giám sát lâm sàng (theo dõi `clinical_visits`).
- [ ] Lễ tân có thể quản lý thông tin cá nhân của bản thân.
- [ ] Lễ tân có thể xử lý thanh toán trực tiếp trên các bản ghi của `clinical_visits` (cập nhật `payment_status`, `payment_method`, `paid_at`).
- [ ] Giao diện và logic (Controller/Route) hoàn toàn độc lập với Admin.

## Tech Stack
- **Backend:** Laravel 12 (Blade, Controllers, Middleware).
- **Frontend:** TailwindCSS v4, FontAwesome.
- **Database:** MySQL (Sử dụng các bảng hiện có như `users`, `appointments`, `patient_profiles`, `clinical_visits`).

## File Structure
```text
├── app/
│   └── Http/
│       ├── Controllers/
│       │   └── Receptionist/
│       │       ├── AuthController.php
│       │       ├── DashboardController.php
│       │       ├── AppointmentController.php
│       │       ├── PatientController.php
│       │       ├── ClinicalVisitController.php
│       │       ├── PaymentController.php
│       │       └── ProfileController.php
│       └── Middleware/
│           └── EnsureIsReceptionist.php (nếu chưa có)
├── resources/
│   └── views/
│       └── receptionist/
│           ├── layouts/
│           │   └── app.blade.php
│           ├── auth/
│           │   ├── login.blade.php
│           │   ├── register.blade.php
│           │   └── forgot-password.blade.php
│           ├── dashboard.blade.php
│           ├── appointments/
│           ├── patients/
│           ├── clinical_visits/
│           ├── payments/
│           └── profile/
└── routes/
    └── receptionist.php (được include trong web.php hoặc bootstrap/app.php)
```

## Task Breakdown

### Task 1: Thiết lập Routing & Middleware
- **Agent:** `backend-specialist`
- **INPUT:** Cấu trúc role hiện tại.
- **OUTPUT:** File `routes/receptionist.php`, `EnsureIsReceptionist` middleware.
- **VERIFY:** Truy cập `/receptionist` bị chặn nếu chưa đăng nhập hoặc không phải role receptionist.

### Task 2: Chức năng Xác thực (Auth)
- **Agent:** `backend-specialist` / `frontend-specialist`
- **INPUT:** `users` table.
- **OUTPUT:** `Receptionist/AuthController`, views `receptionist.auth.*`.
- **VERIFY:** Có thể login/logout thành công bằng tài khoản lễ tân. Đăng ký/Quên mật khẩu hoạt động.

### Task 3: Quản lý lịch hẹn (Appointments) & Dashboard
- **Agent:** `backend-specialist` / `frontend-specialist`
- **INPUT:** `appointments` table.
- **OUTPUT:** `Receptionist/AppointmentController`, views CRUD lịch hẹn.
- **VERIFY:** Lễ tân có thể xem, thêm, sửa, hủy lịch hẹn.

### Task 4: Quản lý Bệnh nhân/Khách hàng
- **Agent:** `backend-specialist` / `frontend-specialist`
- **INPUT:** `users`, `patient_profiles` tables.
- **OUTPUT:** `Receptionist/PatientController`, views danh sách & chi tiết.
- **VERIFY:** Lễ tân có thể xem và quản lý thông tin bệnh nhân tương tự Admin.

### Task 5: Giám sát lâm sàng (Clinical Monitoring)
- **Agent:** `backend-specialist` / `frontend-specialist`
- **INPUT:** `clinical_visits` table.
- **OUTPUT:** `Receptionist/ClinicalVisitController`, views giám sát.
- **VERIFY:** Hiển thị danh sách các lượt khám trong ngày, trạng thái (waiting, in_progress, completed...).

### Task 6: Thanh toán (Payment)
- **Agent:** `backend-specialist` / `frontend-specialist`
- **INPUT:** Các bản ghi `clinical_visits` có phí (`payment_amount` > 0).
- **OUTPUT:** `Receptionist/PaymentController`, chức năng cập nhật thanh toán.
- **VERIFY:** Lễ tân có thể xác nhận thanh toán (cập nhật `payment_status`, `payment_method`...).

### Task 7: Quản lý Thông tin cá nhân
- **Agent:** `backend-specialist` / `frontend-specialist`
- **INPUT:** `users`, `staff_profiles` table.
- **OUTPUT:** `Receptionist/ProfileController`.
- **VERIFY:** Lễ tân cập nhật được profile của mình.

## Phase X: Verification
- [ ] Chạy `npm run build` không lỗi giao diện.
- [ ] Các URL `/receptionist/*` phải độc lập, không dùng chung Controller với Admin.
- [ ] Kiểm tra phân quyền: Không role nào khác được truy cập route Lễ tân ngoài chính Lễ tân (và có thể Super Admin nếu quy định).
- [ ] Các chức năng CRUD hoạt động bình thường, không lỗi DB.
