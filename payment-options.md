# Kế hoạch Triển khai Chức năng Thanh toán (Payment Integration)

## 🎯 Mục tiêu
Mở rộng và hệ thống hóa chức năng thanh toán (Payment) cho các lượt khám bệnh (`clinical_visits`) và dịch vụ trong hệ thống CareBook, cho phép người dùng/lễ tân xử lý thanh toán thông qua nhiều phương thức khác nhau.

---

## 💡 Các lựa chọn thanh toán (Payment Options)

### 1. Thanh toán Thủ công (Manual)
Phương pháp Lễ tân thu tiền trực tiếp và xác nhận trên phần mềm.
- **Tiền mặt (Cash):** Khách hàng trả tiền mặt, lễ tân nhận tiền và bấm "Đã thanh toán".
- **Bảo hiểm y tế (Insurance):** Chi trả 100% bằng thẻ BHYT hoặc đồng chi trả.
- **Chuyển khoản (Mã QR tĩnh):** Khách hàng quét mã QR tĩnh của phòng khám (in trên quầy hoặc hiển thị trên màn hình). Lễ tân kiểm tra app ngân hàng và bấm "Đã thanh toán".

### 2. Tích hợp VietQR Động (Dynamic VietQR - Miễn phí)
- **Cơ chế:** Sử dụng API của VietQR (như `vietqr.io` hoặc `PayOS`) để tự động tạo mã QR có chứa sẵn số tiền và nội dung chuyển khoản: Thanh toan tien kham + mã lịch hẹn (VD: `Thanh toan tien kham APT1783301669001`).
- **Xác nhận:** 
  - *Tự động (PayOS/SePay):* Hệ thống bắt webhook khi tiền vào tài khoản và tự động cập nhật trạng thái `paid`.

## ❓ Socratic Gate (Câu hỏi xác định yêu cầu)

Để lên kế hoạch code chi tiết (`Task Breakdown`), bạn vui lòng trả lời các câu hỏi sau để chúng ta chốt phương án:

> [!IMPORTANT]
> 1. **Bạn muốn áp dụng phương thức nào?** (Chỉ dùng Tiền mặt/QR tĩnh, hay muốn tích hợp API tạo mã QR động, hay dùng hẳn VNPAY/MoMo?)
### 1. Thanh toán Thủ công (Manual)
Phương pháp Lễ tân thu tiền trực tiếp và xác nhận trên phần mềm.
- **Tiền mặt (Cash):** Khách hàng trả tiền mặt, lễ tân nhận tiền và bấm "Đã thanh toán".
- **Bảo hiểm y tế (Insurance):** Chi trả 100% bằng thẻ BHYT hoặc đồng chi trả.
- **Chuyển khoản (Mã QR tĩnh):** Khách hàng quét mã QR tĩnh của phòng khám (in trên quầy hoặc hiển thị trên màn hình). Lễ tân kiểm tra app ngân hàng và bấm "Đã thanh toán".

### 2. Tích hợp VietQR Động (Dynamic VietQR - Miễn phí)
- **Cơ chế:** Sử dụng API của VietQR (như `vietqr.io` hoặc `PayOS`) để tự động tạo mã QR có chứa sẵn số tiền và nội dung chuyển khoản: Thanh toan tien kham + mã lịch hẹn (VD: `Thanh toan tien kham APT1783301669001`).
- **Xác nhận:** 
  - *Tự động (PayOS/SePay):* Hệ thống bắt webhook khi tiền vào tài khoản và tự động cập nhật trạng thái `paid`.
> 2. **Luồng thanh toán diễn ra ở đâu?** (Chỉ dành cho Lễ tân thao tác ở quầy, hay Bệnh nhân có thể tự thanh toán qua ứng dụng/website của Bệnh nhân?)
=> Bệnh nhân sau khi đặt lịch khám xong và có dữ liệu khám lâm sàn (do bác sĩ thêm trong bảng `clinical_visits`) thì có thể thanh toán tiền khám ngay, lễ tân sẽ check thông tin và xác nhận thanh toán. Sau đó người bệnh mới có thể đi khám. Tức là phải thanh toán trước khi vào từng phòng khám theo chỉ định.

> 3. **Bạn có cần hệ thống tự động xác nhận (Webhook)?** Hay lễ tân sẽ tự thao tác xác nhận bằng tay sau khi nhận được tiền?
=> Tự động

---

## 📝 Task Breakdown (Dự kiến)

*Lưu ý: Các bước này sẽ được điều chỉnh tùy thuộc vào lựa chọn của bạn ở trên.*

- `[ ]` **Phase 1: Database & Model**
  - Cập nhật bảng `clinical_visits` đồng thời tạo bảng `payments` riêng để lưu trữ giao dịch (Transaction ID, Cổng thanh toán, Thời gian).
- `[ ]` **Phase 2: Service Provider & API Integration**
  - Cài đặt SDK hoặc viết các HTTP Request gọi API (VietQR).
  - Tạo logic sinh mã QR/URL thanh toán.
- `[ ]` **Phase 3: Webhook/IPN Handler**
  - Tạo endpoint nhận thông báo (Callback) từ cổng thanh toán.
  - Cập nhật tự động trạng thái `payment_status`.
- `[ ]` **Phase 4: Giao diện (UI)**
  - Hiển thị QR Code trên màn hình của Lễ tân / Bệnh nhân.
  - Hiển thị trạng thái giao dịch (Loading, Success, Failed).
- `[ ]` **Phase X: Verification & Testing**
  - Test môi trường Sandbox (VNPAY/MoMo test).
  - Xác nhận luồng hoàn tất giao dịch.

---

**Vui lòng xem xét các lựa chọn và cho tôi biết quyết định của bạn để tôi bắt đầu viết code (`/create`) nhé!**
