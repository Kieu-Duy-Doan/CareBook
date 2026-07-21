# CareBook - Hệ Thống Quản Lý Phòng Khám / Bệnh Viện

CareBook là một nền tảng quản trị và vận hành cơ sở y tế toàn diện được xây dựng trên framework **Laravel** kết hợp với giao diện hiện đại sử dụng **Tailwind CSS** và **Alpine.js**. Dự án cung cấp các công cụ mạnh mẽ để quản lý nhân sự (bác sĩ, lễ tân), hồ sơ bệnh nhân, lên lịch làm việc, đặt lịch hẹn trực tuyến và quản lý kết quả lâm sàng.

---

## 🚀 1. Yêu cầu hệ thống (Prerequisites)

Để cài đặt và chạy dự án này, máy tính của bạn cần được cài đặt sẵn:

- **PHP** >= 8.2
- **Composer** (Trình quản lý thư viện của PHP)
- **MySQL** (Hoặc MariaDB, phiên bản từ 8.x trở lên)
- **Node.js** & **NPM** (Để build giao diện frontend)
- **Git**

---

## 🛠 2. Hướng dẫn cài đặt (Installation Guide)

Vui lòng làm theo các bước dưới đây để triển khai dự án trên môi trường Local:

### Bước 1: Clone mã nguồn

Mở Terminal/Command Prompt và chạy lệnh sau để tải source code về:

```bash
git clone <url-repo-cua-ban>
cd carebook-ui
```

### Bước 2: Cài đặt các thư viện PHP

Chạy lệnh composer để tải các dependency của Laravel:

```bash
composer install
```

### Bước 3: Cấu hình môi trường (.env)

Tạo file cấu hình môi trường `.env` từ file mẫu:

```bash
cp .env.example .env
```

Mở file `.env` vừa tạo và cập nhật cấu hình kết nối Database (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) sao cho khớp với cấu hình MySQL trên máy của bạn:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=carebook_db  # Tạo một database có tên này trong MySQL
DB_USERNAME=root         # Tên đăng nhập DB của bạn
DB_PASSWORD=             # Mật khẩu DB của bạn
```

### Bước 4: Khởi tạo Application Key

```bash
php artisan key:generate
```

### Bước 5: Chạy Migration & Dữ liệu mẫu (Seeder)

Lệnh này sẽ tạo các bảng trong database và tự động đổ dữ liệu mẫu ban đầu (danh mục, tài khoản admin, cấu hình,...):

```bash
php artisan migrate --seed
```

### Bước 6: Cài đặt thư viện Frontend và Build giao diện

```bash
npm install
npm run build
```

_(Nếu bạn đang code và muốn giao diện tự cập nhật khi lưu file, hãy chạy lệnh `npm run dev`)_

### Bước 7: Liên kết thư mục chứa ảnh/tệp tải lên

Để các hình ảnh avatar, hồ sơ y tế có thể hiển thị được ra trình duyệt:

```bash
php artisan storage:link
```

---

## 💻 3. Hướng dẫn chạy dự án

Sau khi hoàn tất cài đặt, bạn khởi động server ảo của Laravel bằng lệnh:

```bash
php artisan serve
```

Hệ thống sẽ chạy ở địa chỉ mặc định: [http://localhost:8000](http://localhost:8000)

---

## 🔑 4. Tài khoản kiểm thử mặc định (Dữ liệu mẫu)

Sau khi chạy lệnh `--seed` ở Bước 5, hệ thống sẽ cung cấp một số tài khoản mặc định để bạn đăng nhập thử (Lưu ý hệ thống sử dụng **Số điện thoại** để đăng nhập thay vì Email):

- **Tài khoản Admin (Quản trị viên hệ thống):**
    - SĐT: `0900000001`
    - Tên đăng nhập: `admin`
    - Mật khẩu: `Admin@123`

- **Tài khoản Lễ tân:**
    - SĐT: `0900000002`
    - Tên đăng nhập: `letan`
    - Mật khẩu: `Letan@123`

- **Tài khoản Bác sĩ (10 tài khoản, mật khẩu chung `Bacsi@123`):**
    - `0900000100` (`bs_an`), `0900000101` (`bs_bich`), `0900000102` (`bs_tuan`), ...

- **Tài khoản Bệnh nhân (10 tài khoản, mật khẩu chung `Patient@123`):**
    - `0900000200` (`bn_1`), `0900000201` (`bn_2`), `0900000202` (`bn_3`), ...

---

## 📁 5. Cấu trúc thư mục chính

- `app/Http/Controllers/`: Chứa các bộ điều khiển xử lý logic.
- `app/Models/`: Chứa các Model kết nối cơ sở dữ liệu.
- `database/migrations/`: Chứa các file khởi tạo và thay đổi cấu trúc bảng CSDL.
- `resources/views/`: Chứa các file giao diện Blade (HTML/Tailwind).
- `routes/web.php`: Nơi định nghĩa các đường dẫn (URL) của trang web.

---

**Chúc bạn có một trải nghiệm tuyệt vời với CareBook!** Mọi thắc mắc hoặc báo lỗi vui lòng tạo Issue trên hệ thống quản lý mã nguồn.
