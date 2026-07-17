# BÁO CÁO CẬP NHẬT TÍNH NĂNG HỆ THỐNG CAREBOOK
**Ngày báo cáo:** 17/07/2026
**Tính năng cốt lõi:** Phân luồng Bác sĩ (Lâm sàng & Cận lâm sàng), Định giá phòng khám và Quản lý Giám sát Lâm sàng.

---

## I. TỔNG QUAN VỀ THAY ĐỔI
Hệ thống CareBook đã được tái cấu trúc quy trình khám chữa bệnh để phù hợp với thực tế tại các bệnh viện và phòng khám đa khoa. Cụ thể, chúng tôi đã tách biệt rõ vai trò của hai nhóm bác sĩ:
1. **Bác sĩ Lâm sàng (Clinical):** Là người tiếp nhận bệnh nhân từ đầu, thực hiện chẩn đoán tổng quát, kê đơn thuốc và có quyền **chỉ định** bệnh nhân đi làm các dịch vụ xét nghiệm, chụp chiếu.
2. **Bác sĩ Cận lâm sàng (Paraclinical):** Là các bác sĩ chuyên khoa làm việc tại phòng xét nghiệm, X-quang, nội soi... Họ **không** tiếp nhận lịch đặt khám từ khách hàng, mà chỉ thực hiện dịch vụ dựa trên chỉ định của Bác sĩ Lâm sàng, sau đó trả kết quả về.

---

## II. CHI TIẾT KỸ THUẬT VÀ MÃ NGUỒN (CODE CHANGES)

### 1. Cơ sở dữ liệu (Database)
- **Bảng `doctor_profiles`**: Thêm cột `doctor_type` (Enum: `clinical`, `paraclinical`) để phân loại bác sĩ.
- **Bảng `rooms`**: Thêm cột `price` (Integer) để cấu hình giá tiền dịch vụ cho các phòng cận lâm sàng.

### 2. Back-end Logic (Controllers & Models)
- **Model `DoctorProfile`, `Room`**: Khai báo các thuộc tính mới vào danh sách `$fillable`.
- **`Admin\DoctorController` & `Admin\RoomController`**: 
  - Cập nhật logic `store` và `update`.
  - Validate giá trị `doctor_type`.
  - Với Phòng khám, tự động bắt giá trị `price` nếu loại phòng là `diagnostic` (Cận lâm sàng), và set `null` với các loại phòng khác.
- **`Patient\BookingController` & `Services\BookingService`**: 
  - Sửa đổi query lấy danh sách bác sĩ/phòng cho App/Web của khách. Bệnh nhân giờ đây **chỉ nhìn thấy và đặt lịch được với các Bác sĩ Lâm sàng** (`doctor_type = clinical`).
- **`Doctor\AppointmentController`**: 
  - Sửa đổi hàm `index`. Danh sách lịch hẹn tự động lọc bỏ các lịch "Đã tiếp nhận" (`pending`), hiển thị danh sách của ngày hôm nay, và **sắp xếp ưu tiên các lịch "Đã check-in"** (`checked_in`) lên đầu danh sách để bác sĩ gọi khám trước.
  - Fix lỗi hiển thị chi tiết lịch hẹn (Lỗi `count() on null` khi lấy thông tin đơn thuốc cũ của hồ sơ bệnh án).
- **`Doctor\ClinicalVisitController`**:
  - Giao diện Chỉ định dịch vụ: Chỉ query ra các phòng thuộc loại `diagnostic`.

### 3. Front-end (Giao diện Blade & Alpine.js)
- Tích hợp Alpine.js (reactive state) vào các form Thêm/Sửa phòng của Admin. Khi chọn loại phòng "Cận lâm sàng", ô nhập Giá tiền dịch vụ sẽ tự động hiện ra.
- Tích hợp Alpine.js vào form "Chỉ định phòng khám chuyên sâu" của bác sĩ. Khi chọn một phòng dịch vụ, hệ thống **tự động điền giá tiền** của phòng đó vào ô "Chi phí dự kiến".
- Thêm giao diện cập nhật trạng thái, ghi chú, chi phí cho **Phòng khám chính** trong phần Giám sát lâm sàng.

---

## III. HƯỚNG DẪN SỬ DỤNG CHI TIẾT (USER MANUAL)

### 1. Dành cho Quản trị viên (Admin)
**A. Quản lý Bác sĩ:**
1. Truy cập: `Admin -> Quản lý Bác sĩ`.
2. Khi tạo mới hoặc chỉnh sửa Bác sĩ, bạn sẽ thấy trường **"Loại Bác sĩ"**.
3. Vui lòng chọn đúng loại:
   - *Bác sĩ Lâm sàng*: Khám chính, được bệnh nhân đặt lịch trực tiếp.
   - *Bác sĩ Cận lâm sàng*: Nhận chỉ định từ bác sĩ lâm sàng (Xét nghiệm, X-Quang,...).

**B. Quản lý Phòng khám:**
1. Truy cập: `Admin -> Quản lý Phòng khám`.
2. Tại trường **"Loại phòng"**, nếu bạn chọn "Cận lâm sàng", một ô nhập liệu **"Giá tiền dịch vụ (VNĐ)"** sẽ xuất hiện.
3. Điền giá dịch vụ vào ô này để hệ thống tự động tính toán chi phí khi Bác sĩ Lâm sàng chỉ định.

---

### 2. Dành cho Khách hàng / Bệnh nhân (Patient)
- Trải nghiệm đặt lịch của khách hàng không bị xáo trộn. Tuy nhiên, khách hàng sẽ không còn thấy các bác sĩ Xét nghiệm, Siêu âm... trên danh sách đặt lịch nữa, tránh tình trạng đặt nhầm lịch.

---

### 3. Dành cho Bác sĩ Lâm sàng (Clinical Doctor)
**A. Xem lịch hẹn:**
1. Vào `Quản lý Lịch hẹn`. Hệ thống tự động lọc danh sách bệnh nhân của ngày hôm nay đã có mặt ở viện (Đã check-in hoặc đang khám).
2. Bệnh nhân nào Đã check-in sẽ được đẩy lên trên cùng theo thứ tự giờ ưu tiên.

**B. Khám và Chỉ định Cận lâm sàng:**
1. Bấm vào chi tiết lịch hẹn -> Chọn "Khám bệnh".
2. Chuyển sang màn hình **Giám sát lâm sàng**.
3. Tại phần **Chỉ định phòng khám chuyên sâu**, chọn dịch vụ cần thực hiện (ví dụ: Siêu âm 4D).
4. Hệ thống sẽ **tự động điền Giá tiền** của dịch vụ đó. Bác sĩ có thể nhập thêm ghi chú và bấm "Chỉ định". Lập tức, dữ liệu ca khám này sẽ được gửi sang màn hình của Bác sĩ Cận lâm sàng.

**C. Theo dõi và Kết luận:**
1. Trên màn hình **Giám sát lâm sàng**, bác sĩ sẽ thấy danh sách các phòng Cận lâm sàng đang thực hiện.
2. Khi Bác sĩ Cận lâm sàng hoàn tất và upload kết quả (PDF/Hình ảnh), Bác sĩ Lâm sàng có thể xem trực tiếp kết quả này ngay tại đây.
3. Sau khi xem kết quả, Bác sĩ Lâm sàng có thể sử dụng form **"Cập nhật phòng khám chính"** để chốt trạng thái thành "Hoàn thành", cập nhật chi phí tổng và lưu hồ sơ bệnh án.

---

### 4. Dành cho Bác sĩ Cận lâm sàng (Paraclinical Doctor)
1. Bác sĩ Cận lâm sàng **không có** danh sách hẹn trực tiếp từ khách hàng.
2. Thay vào đó, họ theo dõi phần màn hình làm việc của phòng mình. Bất cứ khi nào Bác sĩ Lâm sàng gửi lệnh chỉ định tới, hệ thống sẽ đẩy bệnh nhân vào danh sách chờ của phòng.
3. Bác sĩ thực hiện dịch vụ, sau đó vào form cập nhật của hệ thống để:
   - Điền kết luận / nhận xét dịch vụ.
   - Upload file kết quả xét nghiệm, X-quang,...
   - Chuyển trạng thái thành "Hoàn thành".
4. Ngay lập tức, kết quả này được đồng bộ ngược lại về cho Bác sĩ Lâm sàng gốc xem.

---

*Báo cáo được lập tự động bởi hệ thống Trợ lý ảo Antigravity. Vui lòng liên hệ bộ phận kỹ thuật nếu cần hỗ trợ thêm về các luồng nghiệp vụ mới!*
