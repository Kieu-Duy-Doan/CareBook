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

- **Tài khoản Bác sĩ Lâm sàng (10 tài khoản, mật khẩu chung `Bacsi@123`):**

    | Mã BS | Tên bác sĩ | SĐT | Tên đăng nhập | Ca làm việc | Chuyên khoa |
    |-------|-----------|-----|---------------|-------------|-------------|
    | BS001 | Nguyễn Văn An | `0900000100` | `bs_an` | ☀️ Ca sáng (7:00–11:00) | Tim mạch |
    | BS002 | Trần Thị Bích | `0900000101` | `bs_bich` | 🌤️ Ca chiều (13:00–17:00) | Răng Hàm Mặt |
    | BS003 | Lê Minh Tuấn | `0900000102` | `bs_tuan` | ☀️ Ca sáng (7:00–11:00) | Nội tiêu hoá |
    | BS004 | Hoàng Ngọc Hà | `0900000103` | `bs_ha` | 🌤️ Ca chiều (13:00–17:00) | Nhi khoa |
    | BS005 | Phạm Đức Đam | `0900000104` | `bs_dam` | ☀️ Ca sáng (7:00–11:00) | Thần kinh |
    | BS006 | Ngô Bảo Châu | `0900000105` | `bs_chau` | 🌤️ Ca chiều (13:00–17:00) | Cơ xương khớp |
    | BS007 | Vũ Thu Thủy | `0900000106` | `bs_thuy` | ☀️ Ca sáng (7:00–11:00) | Da liễu |
    | BS008 | Đinh Tuấn Anh | `0900000107` | `bs_tuananh` | 🌤️ Ca chiều (13:00–17:00) | Mắt |
    | BS009 | Lý Thảo Tâm | `0900000108` | `bs_tam` | ☀️ Ca sáng (7:00–11:00) | Tai Mũi Họng |
    | BS010 | Châu Kiều Oanh | `0900000109` | `bs_oanh` | 🌤️ Ca chiều (13:00–17:00) | Nội tiết |

- **Tài khoản Bác sĩ Cận lâm sàng (6 tài khoản, mật khẩu chung `Bacsi@123`):**

    **🔬 Phòng Siêu âm 4D (SA01)**

    | Ca | Mã BS | Tên bác sĩ | SĐT | Tên đăng nhập |
    |----|-------|-----------|-----|---------------|
    | ☀️ Ca sáng (7:00–11:00) | BS011 | Trương Minh Quang | `0900000300` | `bs_sa_s` |
    | 🌤️ Ca chiều (13:00–17:00) | BS012 | Nguyễn Thị Hồng | `0900000301` | `bs_sa_c` |

    **🩸 Phòng Xét nghiệm Máu (XN01)**

    | Ca | Mã BS | Tên bác sĩ | SĐT | Tên đăng nhập |
    |----|-------|-----------|-----|---------------|
    | ☀️ Ca sáng (7:00–11:00) | BS013 | Lê Thanh Sơn | `0900000302` | `bs_xn_s` |
    | 🌤️ Ca chiều (13:00–17:00) | BS014 | Phan Thị Nga | `0900000303` | `bs_xn_c` |

    **🩻 Phòng XQuang (XQ01)**

    | Ca | Mã BS | Tên bác sĩ | SĐT | Tên đăng nhập |
    |----|-------|-----------|-----|---------------|
    | ☀️ Ca sáng (7:00–11:00) | BS015 | Đoàn Văn Khánh | `0900000304` | `bs_xq_s` |
    | 🌤️ Ca chiều (13:00–17:00) | BS016 | Mai Phương Thảo | `0900000305` | `bs_xq_c` |

- **Tài khoản Bệnh nhân (10 tài khoản, mật khẩu chung `Patient@123`):**

    | SĐT | Tên đăng nhập | Họ tên |
    |-----|---------------|--------|
    | `0900000201` | `bn_mai` | Nguyễn Thị Mai |
    | `0900000202` | `bn_hung` | Trần Văn Hùng |
    | `0900000203` | `bn_ngoc` | Lê Bích Ngọc |
    | `0900000204` | `bn_long` | Phạm Hoàng Long |
    | `0900000205` | `bn_sau` | Võ Thị Sáu |
    | `0900000206` | `bn_son` | Đặng Kim Sơn |
    | `0900000207` | `bn_ha` | Bùi Thu Hà |
    | `0900000208` | `bn_truong` | Đỗ Xuân Trường |
    | `0900000209` | `bn_phuc` | Hoàng Phúc |
    | `0900000210` | `bn_lan` | Ngô Phương Lan |


---

## 📁 5. Cấu trúc thư mục chính

- `app/Http/Controllers/`: Chứa các bộ điều khiển xử lý logic.
- `app/Models/`: Chứa các Model kết nối cơ sở dữ liệu.
- `database/migrations/`: Chứa các file khởi tạo và thay đổi cấu trúc bảng CSDL.
- `resources/views/`: Chứa các file giao diện Blade (HTML/Tailwind).
- `routes/web.php`: Nơi định nghĩa các đường dẫn (URL) của trang web.

---

**Chúc bạn có một trải nghiệm tuyệt vời với CareBook!** Mọi thắc mắc hoặc báo lỗi vui lòng tạo Issue trên hệ thống quản lý mã nguồn.
