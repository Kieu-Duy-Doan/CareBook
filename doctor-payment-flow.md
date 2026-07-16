# Project Plan: Luồng Thanh Toán 3 Giai Đoạn (Role Doctor)

## 1. Phân tích Luồng Nghiệp vụ (Business Workflow)

Dựa trên yêu cầu của bạn, hệ thống thanh toán cho `role doctor` được chia thành các giai đoạn linh hoạt trong hành trình của bệnh nhân:

### Giai đoạn 1: Check-in & Đo chỉ số sinh tồn

- **Nghiệp vụ:** Bệnh nhân đến phòng khám, thực hiện đăng ký khám (check-in) và đo sinh hiệu.
- **Hành động thanh toán (Phí khám ban đầu):**
    - **Quét mã QR:** Bệnh nhân thanh toán trực tiếp tại phòng khám (nếu bác sĩ/người phụ trách tự thao tác).
    - **Tiền mặt:** Bệnh nhân ra quầy Lễ tân đóng tiền.

### Giai đoạn 2: Khám bệnh (Chia thành 2 Luồng)

**👉 Luồng 1: Khám nhẹ, không cần Cận lâm sàng (Bỏ qua GĐ2, đi thẳng đến GĐ3)**

- **Nghiệp vụ:** Bác sĩ khám thấy không có bệnh gì nghiêm trọng, đưa ra kết luận ngay và kê đơn thuốc.
- **Hành động thanh toán:** Thanh toán tiền thuốc (Quét QR tại phòng bác sĩ HOẶC Tiền mặt tại quầy Lễ tân) -> In Đơn Thuốc.

**👉 Luồng 2: Cần Khám Cận lâm sàng (Clinical Visits)**

- **Nghiệp vụ:** Bác sĩ chỉ định bệnh nhân đi làm các xét nghiệm/siêu âm ở phòng khác.
- **Hành động thanh toán:** Bác sĩ tạo chỉ định -> Hệ thống sinh hóa đơn cận lâm sàng.
    - **Quét mã QR:** Thanh toán luôn tại phòng bác sĩ.
    - **Tiền mặt:** Ra quầy Lễ tân nộp tiền.
- **Kết quả:** Sau khi thanh toán thành công (qua QR tự động hoặc Lễ tân xác nhận), bác sĩ **In Phiếu Chỉ Định (kèm mộc đã thanh toán)**. Bệnh nhân cầm phiếu đi khám các phòng khác, sau đó quay lại phòng bác sĩ ban đầu để nghe kết luận và lấy đơn thuốc (Giai đoạn 3).

### Giai đoạn 3: Kết luận & Kê đơn thuốc (Dành cho Luồng 2)

- **Nghiệp vụ:** Bác sĩ đọc kết quả từ các phòng khác, kết luận bệnh và kê đơn thuốc.
- **Hành động thanh toán:** Tính tiền thuốc -> Thanh toán (QR tại phòng hoặc Tiền mặt tại Lễ tân) -> **In Phiếu Thuốc (kèm kết luận)**.
- **Kết quả:** Bệnh nhân xuống quầy thuốc để lấy thuốc.

---

## 2. Thiết kế Kiến trúc (Architecture & UI/UX)

### 2.1. Phân tách logic Thanh toán (QR vs Tiền mặt)

- **Nếu chọn Thanh toán QR (SePay):**
    - Hiển thị Popup mã QR tĩnh hoặc động trên màn hình Bác sĩ.
    - Sử dụng Polling/Websockets: Cứ 3-5 giây check trạng thái hóa đơn 1 lần. Khi khách quét xong, hệ thống (SePay webhook) tự cập nhật trạng thái "Đã thanh toán". Màn hình bác sĩ tự động hiển thị nút `[In Phiếu]`.
- **Nếu chọn Tiền mặt:**
    - Bác sĩ bấm `[Chuyển trạng thái: Chờ thanh toán tại quầy]`.
    - Màn hình Lễ tân sẽ nổi thông báo có hóa đơn chờ thu tiền. Khách ra nộp tiền, Lễ tân bấm `[Xác nhận đã thu tiền]`.
    - Màn hình Bác sĩ tự động cập nhật sang "Đã thanh toán" (bằng Polling) và hiện nút `[In Phiếu]`.

### 2.2. Giao diện (Blade Views - Role Doctor)

- Trên giao diện khám (`appointments.show`), với mỗi bước phát sinh chi phí, sẽ có 1 bảng tóm tắt chi phí và 2 nút:
    - `[Tạo QR Thanh toán]` (Thanh toán tại chỗ)
    - `[Gửi yêu cầu thu tiền mặt]` (Thanh toán qua Lễ tân)
- Sau khi thanh toán hoàn tất, UI thay đổi thành hiển thị nút `[In Phiếu Chỉ Định]` hoặc `[In Đơn Thuốc]`.

---

## 3. Các Câu hỏi còn lại (Chờ xác nhận)

Về Đơn giá & Cấu trúc Database:

- Hệ thống đã có sẵn bảng `services` (dịch vụ cận lâm sàng) và `medicines` (thuốc) kèm theo giá tiền chưa? Việc tính tổng tiền sẽ query trực tiếp từ các bảng này? -> tôi chưa rõ bnj hãy tự kiểm tra db.

Về In ấn:

- Việc "in phiếu" sẽ ưu tiên thiết kế cho khổ giấy máy in nhiệt (80mm) hay máy in A4/A5 thông thường? -> in kết quả thif in thường a4 còn in liên quan đế tiền thì in nhiệt.

---

## 4. Kế hoạch Triển khai (Task Breakdown)

- **Phase 1: Database & Logic**
    - Bổ sung cấu trúc lưu trữ phương thức thanh toán (`qr`, `cash`) và loại hóa đơn (`consultation`, `clinical`, `prescription`).
    - Viết API/Hàm xử lý tạo hóa đơn và luồng cho phép Lễ tân xác nhận thu tiền mặt.
- **Phase 2: UI Bác sĩ & Lễ tân (Xử lý thanh toán)**
    - Tích hợp QR SePay lên màn hình Bác sĩ.
    - Tích hợp Polling (Alpine.js) để real-time trạng thái thanh toán giữa Bác sĩ - SePay - Lễ tân.
- **Phase 3: Luồng Khám bệnh (Flow 1 & Flow 2)**
    - Tách luồng trên UI để xử lý mượt mà việc bỏ qua Cận lâm sàng hoặc bắt buộc đi khám Cận lâm sàng.
- **Phase 4: Chức năng In ấn (Print)**
    - Tạo template HTML In Phiếu Chỉ Định và In Đơn Thuốc.
