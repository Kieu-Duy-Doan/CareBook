# Kế hoạch xử lý tài khoản Spam (Hủy lịch nhiều lần)

## 1. Mục tiêu

Ngăn chặn các hành vi đặt lịch ảo, đặt xong hủy liên tục gây ảnh hưởng đến hệ thống và lịch làm việc của bác sĩ. Áp dụng biện pháp khóa tài khoản và yêu cầu liên hệ bộ phận hỗ trợ (Admin/Lễ tân) để mở lại.

## 1.2 Trước khi làm thì kiểm tra dữ liệu từ migrations tráng trường hợp thừa trường dữ liệu. Hệ thống hiện tại chưa đấy lên domain nên có thể sửa trực tiếp bảng đó.

## 1.3 Cần đảm bảo nếu trường hợp lịch do người quản lý tạo thì không bị áp dụng. Chỉ áp dụng cho user tự đặt.

## 1.4 vì powershell và cmd của bạn thỉnh thoảng lỗi nên nếu bạn không chạy được lệnh thì hãy thông báo cho tôi tự chay rồi báo cho bạn.

## 2. Câu hỏi cần xác nhận (Socratic Gate)

Trước khi bắt đầu triển khai, tôi cần bạn xác nhận một số quy tắc kinh doanh (Business Rules) sau:

1. **Ngưỡng vi phạm (Threshold):** Bạn muốn quy định số lần hủy là bao nhiêu thì sẽ bị khóa? (Ví dụ: Hủy 3 lần trong vòng 7 ngày, hay 5 lần trong 1 tháng?) -> 3 lần 1 ngày thì khóa lun dù hủy ở bất kì hồ sơ nào (bản thân + gia đình cứ đủ 3 lần thì khóa). Nếu trong 1 ngày 1 người hủy 3 lần thì khóa (áp dụng cho tất cả tài khoản đăng nhập bằng số điện thoại đó).
2. **Phạm vi khóa:** Khi khóa, tài khoản sẽ bị đăng xuất ngay lập tức và không thể đăng nhập vào hệ thống được nữa, hay vẫn đăng nhập được nhưng bị chặn tính năng Đặt lịch? (Hệ thống đã có cột `is_active` trên bảng `users`, tận dụng cột này để chặn đăng nhập là cách hiệu quả nhất). -> Đăng xuất lun.
3. **Hiển thị thông báo:** Khi người dùng bị khóa cố gắng đăng nhập, hệ thống sẽ hiện thông báo cảnh báo. Bạn có muốn hiển thị số điện thoại hotline cụ thể nào của Lễ tân/Admin trong câu thông báo này không? -> Có hiển thị số điện thoại admin và lễ tân để được hỗ trợ mở khóa. Khi khóa thì nêu rõ lí do cho người dùng biết.

## 3. Các thay đổi dự kiến (Task Breakdown)

### Phase 1: Database & Cấu hình

- Thêm cột `locked_reason` (string, nullable) vào bảng `users` qua Migration để phân biệt tài khoản bị khóa do spam hủy lịch với các lý do khóa khác. (Cột `is_active` đã có sẵn).
- Thêm hằng số ngưỡng spam (có thể hardcode trong class hoặc bỏ vào `config/booking.php`). -> không được hardcode.

### Phase 2: Logic xử lý Hủy lịch (Backend)

- Cập nhật hàm hủy lịch (`cancel`) trong `app/Http/Controllers/Patient/AppointmentController.php`.
- Sau khi bệnh nhân hủy lịch thành công, đếm tổng số lịch mà user này (`booked_by_user_id`) đã hủy trong N ngày qua.
- Nếu số lượng >= Ngưỡng cho phép:
    - Cập nhật User `is_active = false`, `locked_reason = 'spam_cancellation'`.
    - Tự động đăng xuất user hiện tại (ví dụ: `Auth::logout()`, `request()->session()->invalidate()`).

### Phase 3: Cập nhật thông báo Đăng nhập

- Cập nhật logic Login để hiển thị câu báo lỗi thân thiện thay vì thông báo chung chung.
- Câu thông báo dự kiến: _"Tài khoản của bạn đã bị khóa do hủy lịch khám quá nhiều lần. Vui lòng liên hệ Admin hoặc Lễ tân để được hỗ trợ mở khóa."_

### Phase 4: Quản trị viên & Lễ tân (Mở khóa)

- Kiểm tra danh sách khách hàng (`CustomerController`) phía Admin/Lễ tân.
- Bổ sung nút **"Mở khóa tài khoản"** (Unban) cho các user đang có `is_active = false`.
- API/Action mở khóa sẽ đặt lại `is_active = true` và `locked_reason = null`.

## 4. Phân công Agent

- **backend-specialist:** Thực hiện các xử lý DB, logic đếm số lần hủy lịch khóa tài khoản, đăng xuất user và làm API mở khóa cho Admin.
- **frontend-specialist:** Hiển thị thông báo lỗi khi đăng nhập, cập nhật giao diện hiển thị badge (Bị khóa) và nút "Mở khóa" trong trang quản trị.

## 5. Tiêu chí nghiệm thu (Verification Checklist)

- [ ] Bệnh nhân hủy lịch dưới ngưỡng cho phép: Mọi thứ vẫn hoạt động bình thường.
- [ ] Bệnh nhân hủy lịch đạt ngưỡng vi phạm: Tài khoản ngay lập tức bị văng ra (đăng xuất) và bị khóa.
- [ ] Đăng nhập lại bằng tài khoản vi phạm: Bị chặn và nhìn thấy thông báo hướng dẫn liên hệ hỗ trợ.
- [ ] Admin / Lễ tân thấy user bị khóa trên Dashboard và click mở khóa thành công.
- [ ] Sau khi được mở khóa, bệnh nhân có thể đăng nhập và tiếp tục sử dụng hệ thống.
