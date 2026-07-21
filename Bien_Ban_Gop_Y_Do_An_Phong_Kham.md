# BIÊN BẢN ĐÁNH GIÁ VÀ GÓP Ý ĐỒ ÁN HỆ THỐNG QUẢN LÝ PHÒNG KHÁM / BỆNH VIỆN

**Tài liệu tham khảo:** Dựa trên nội dung bóc băng buổi bảo vệ đồ án (File: FPT Polytechnic.m4a).
**Mục đích:** Tổng hợp chi tiết các lỗi, lỗ hổng logic và các yêu cầu cải thiện từ giảng viên/hội đồng phản biện dành cho nhóm phát triển.

---

## 1. TỔNG QUAN VẤN ĐỀ TRONG HỆ THỐNG HIỆN TẠI
Hệ thống hiện tại đã xây dựng được các luồng cơ bản của một phòng khám (đặt lịch, check-in, khám bệnh). Tuy nhiên, luồng nghiệp vụ (business flow) còn nhiều điểm chưa thực tế, thiếu tính chặt chẽ. Đặc biệt, trải nghiệm người dùng (UI/UX) đang gặp vấn đề lớn: thao tác bị phân mảnh, bắt người dùng phải click và chuyển đổi qua lại giữa nhiều màn hình (menu) không cần thiết.

---

## 2. CHI TIẾT CÁC LỖ HỔNG VÀ YÊU CẦU CẢI THIỆN

### 2.1. Trải nghiệm người dùng (UI/UX) và Tối ưu Luồng thao tác
* **Vấn đề:** Các chức năng bị tách rời một cách máy móc. Ví dụ: Đang xem chi tiết lịch hẹn muốn thanh toán lại phải thoát ra, tìm đến menu "Thanh toán". Đang khám muốn chuyển phòng lại phải vào menu khác.
* **Yêu cầu sửa đổi:**
  * **Tích hợp nút thao tác trực tiếp:** Trong màn hình chi tiết của một ca khám (hoặc lịch hẹn), cần đặt sẵn các nút chức năng (Action buttons) như: `Check-in`, `Thanh toán`, `Chuyển phòng khám`, `Hủy lịch`.
  * **Chuyển đổi trạng thái tự động:** Khi thực hiện xong một hành động (ví dụ thanh toán xong), hệ thống cần tự động chuyển trạng thái ca khám và làm mới dữ liệu (refresh) hoặc đưa ra gợi ý bước tiếp theo, tránh bắt người dùng tự tìm đường đi tiếp.
  * **Cửa sổ hiển thị (Popup/Modal):** Hạn chế chuyển trang hoàn toàn (redirect) gây mất bối cảnh. Nên dùng các modal hoặc luồng liền mạch để giữ chân người dùng ở màn hình chính.

### 2.2. Quy trình Quản lý Tiếp nhận & Trạng thái Lịch hẹn
* **Vấn đề:** Chưa xử lý tốt các tình huống ngoại lệ trong thực tế (khách đến muộn, quá giờ) và chưa lưu vết lịch sử thay đổi.
* **Yêu cầu sửa đổi:**
  * **Xử lý đến muộn (Late Arrival):** Nếu khách hàng đặt lịch nhưng đến quá giờ, hệ thống không được tự động xóa mà phải chuyển sang trạng thái `Đến muộn` hoặc `Quá giờ`. Khi đó, lễ tân có thể sắp xếp lùi giờ hoặc chuyển bác sĩ khác.
  * **Lưu vết lịch sử (Tracking History):** Bất kỳ ca khám nào cũng cần có phần "Lịch sử cập nhật" ghi rõ: Ai đổi lịch, đổi khi nào, trạng thái từ gì sang gì (Ví dụ: Chờ xác nhận -> Đã xác nhận -> Check-in -> Đang khám -> Hoàn thành).

### 2.3. Quy trình Thanh toán, Viện phí & Bảo hiểm y tế
* **Vấn đề:** Quy trình thanh toán chưa rõ ràng, thiếu hóa đơn và hoàn toàn bỏ ngỏ logic tính toán bảo hiểm.
* **Yêu cầu sửa đổi:**
  * **Quản lý Hóa đơn (Invoicing):** Bất kỳ giao dịch nào (thu tiền cọc 100k, tiền khám, tiền thuốc) đều phải phát sinh Hóa đơn. Cần bổ sung tính năng xem lại hóa đơn, in hóa đơn ngay trong chi tiết bệnh án.
  * **Luồng thanh toán Cận lâm sàng:** Khi bác sĩ chỉ định đi chụp X-quang, bệnh nhân phải ra quầy thanh toán tiền X-quang trước. Thanh toán thành công (có hóa đơn) thì phòng X-quang mới nhận được thông tin để chụp.
  * **Tích hợp Bảo hiểm Y tế (BHYT):** Phải bổ sung mã BHYT cho bệnh nhân. Hệ thống cần logic tự động tính toán mức miễn giảm (ví dụ mã sinh viên được giảm 80%, mã doanh nghiệp giảm khác, bệnh nhân không có bảo hiểm trả 100%).

### 2.4. Quản lý Nhân sự, Ca trực và Chuyển phòng khám
* **Vấn đề:** Hệ thống đang gán cứng 1 bác sĩ làm việc cả ngày (fix cứng) và không có cơ chế chuyển giao linh hoạt.
* **Yêu cầu sửa đổi:**
  * **Chia ca làm việc (Shift Management):** Bác sĩ làm việc theo ca (Sáng, Chiều). Không được gán mặc định từ 8h sáng đến 12h đêm.
  * **Luân chuyển Bác sĩ linh hoạt:** Trong trường hợp bác sĩ đã hết ca hoặc có việc đột xuất nghỉ, hệ thống phải cho phép Lễ tân hoặc Quản lý (Admin) chuyển toàn bộ lịch khám của bác sĩ đó sang một bác sĩ khác đang rảnh trong hệ thống.
  * **Luồng dữ liệu Cận lâm sàng:** Bác sĩ A chỉ định bệnh nhân đi xét nghiệm. Bệnh nhân đi xét nghiệm xong, kết quả phải được đẩy về tài khoản của bác sĩ A (để bác sĩ A đọc kết quả và kê đơn), không được để dữ liệu trôi nổi hoặc chuyển nhầm bác sĩ khác.

### 2.5. Hệ thống Thống kê & Báo cáo (Dashboard)
* **Vấn đề:** Thống kê đang làm chung chung, không tách biệt vai trò (Role-based).
* **Yêu cầu sửa đổi:**
  * **Tách biệt Dashboard theo quyền:**
    * *Admin/Quản lý:* Xem biểu đồ doanh thu, tổng số ca khám toàn viện, hiệu suất phòng ban.
    * *Lễ tân:* Xem số ca đang chờ, số ca đã đến, số ca hủy trong ngày để điều phối.
    * *Bác sĩ:* Xem tổng số ca mình đã khám, số bệnh nhân đang chờ ngoài cửa phòng khám của mình.
  * **Cần có biểu đồ có ý nghĩa:** Không làm biểu đồ lấy lệ, dữ liệu trên biểu đồ phải hỗ trợ trực tiếp cho quyết định của người xem (ví dụ: giờ cao điểm để xếp thêm người).

---

## 3. DANH SÁCH CÔNG VIỆC CẦN THỰC HIỆN GẤP (ACTION ITEMS)

**Ưu tiên Cao (P1 - Cần sửa ngay để luồng chạy đúng):**
- [ ] Bổ sung module Hóa đơn (Invoice) cho mọi giao dịch thanh toán.
- [ ] Thêm các nút thao tác nhanh (Thanh toán, Check-in, Chuyển phòng, Chỉ định dịch vụ) vào trang chi tiết khám bệnh.
- [ ] Thiết lập logic "Thanh toán dịch vụ cận lâm sàng xong mới được thực hiện dịch vụ".
- [ ] Thêm tính năng phân ca trực (Sáng/Chiều) cho Bác sĩ.

**Ưu tiên Trung bình (P2 - Cải thiện nghiệp vụ):**
- [ ] Bổ sung trường thông tin Bảo hiểm y tế và logic tính toán giảm trừ viện phí (% giảm trừ theo mã).
- [ ] Cập nhật luồng xử lý bệnh nhân đến muộn / quá giờ thay vì xóa lịch.
- [ ] Tính năng đổi bác sĩ khám (cho toàn bộ lịch chờ) khi bác sĩ được chỉ định vắng mặt.
- [ ] Thêm phần Lịch sử thay đổi (Activity log) cho từng đơn khám.

**Ưu tiên Thấp (P3 - Hoàn thiện UI & Thống kê):**
- [ ] Chia tách Dashboard thành 3 giao diện riêng cho Admin, Lễ tân, và Bác sĩ.
- [ ] Chuẩn bị sẵn bộ dữ liệu mẫu (Dummy data) phong phú để demo (chạy mượt các luồng chờ, hoàn thành, hủy).

---
*Tài liệu được trích xuất và tổng hợp nhằm hỗ trợ nhóm phát triển có cái nhìn tổng quan và sắp xếp kế hoạch Sprint tiếp theo một cách hiệu quả.*
