<x-layouts.doctor>
    <x-slot:title>Thanh toán Phí bằng QR - Lịch hẹn #{{ $appointment->appointment_code }}</x-slot:title>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Thanh toán Phí dịch vụ (QR Code)</h2>
            <p class="text-gray-500 mt-1">Mã lịch hẹn: <span class="font-mono text-gray-900 font-bold">{{ $appointment->appointment_code }}</span></p>
        </div>
        <a href="{{ route('doctor.appointments.show', $appointment->id) }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại Hồ sơ Khám
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chi tiết hóa đơn (Cột trái) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900"><i class="fa-solid fa-file-invoice mr-2 text-emerald-600"></i> Thông tin Bệnh nhân & Dịch vụ</h3>
                </div>

                <div class="p-5">
                    <div class="flex flex-col sm:flex-row gap-6 mb-6 pb-6 border-b border-gray-100">
                        <div class="flex-1">
                            <p class="text-sm text-gray-500 mb-1">Bệnh nhân</p>
                            <p class="font-bold text-gray-900 text-lg">{{ $appointment->patientProfile->full_name }}</p>
                            <p class="text-sm font-mono text-gray-500">{{ $appointment->patientProfile->patient_code }}</p>
                        </div>
                    </div>

                    <!-- Danh sách dịch vụ đang chờ thanh toán (Giai đoạn hiện tại) -->
                    <h4 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wider">Chi tiết các khoản phí chờ thanh toán</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                                <tr>
                                    <th class="py-3 px-4 rounded-l-lg">Hạng mục</th>
                                    <th class="py-3 px-4 text-right rounded-r-lg text-gray-900">Chi phí</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary['pending_visits'] as $visit)
                                <tr class="border-b border-gray-50 last:border-0">
                                    <td class="py-3 px-4 font-medium text-gray-900">
                                        @if(isset($visit->items))
                                            Đơn Thuốc (Kê đơn)
                                        @else
                                            {{ $visit->is_origin ? 'Phí Khám Bệnh' : 'Dịch vụ Cận lâm sàng / Khác' }}
                                        @endif
                                        <span class="text-xs text-gray-400 block font-mono">#{{ $visit->id }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-gray-900">{{ number_format($visit->payment_amount, 0, ',', '.') }}đ</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 bg-gray-50 rounded-lg p-5 border border-gray-200">
                        <div class="flex justify-between items-center text-lg">
                            <span class="font-bold text-gray-900">Tổng cộng cần thanh toán đợt này:</span>
                            <span class="font-bold text-red-600 text-2xl">{{ number_format($summary['remaining_to_pay'], 0, ',', '.') }}đ</span>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-4 bg-blue-50 text-blue-700 text-sm rounded-lg flex items-start">
                        <i class="fa-solid fa-circle-info mt-0.5 mr-2"></i>
                        <div>
                            <strong>Lưu ý:</strong> Bệnh nhân quét mã QR để thanh toán tiền ngay tại phòng khám. <br>Nếu bệnh nhân muốn <strong>thanh toán bằng Tiền mặt</strong>, vui lòng hướng dẫn bệnh nhân ra quầy lễ tân để được hỗ trợ thanh toán.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code & Thanh toán (Cột phải) -->
        <div class="space-y-6">
            @if(isset($summary['amount_paid']) && $summary['amount_paid'] > 0 && $summary['remaining_to_pay'] > 0)
            <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl shadow-sm text-center">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-3xl mb-2"></i>
                <h3 class="font-bold text-amber-800 text-lg">Thanh toán chưa đủ</h3>
                <p class="text-amber-700 text-sm">Khách đã chuyển khoản <strong>{{ number_format($summary['amount_paid'], 0, ',', '.') }}đ</strong>. Còn thiếu <strong>{{ number_format($summary['remaining_to_pay'], 0, ',', '.') }}đ</strong>.</p>
                <p class="text-amber-600 text-xs mt-1">Vui lòng hướng dẫn khách quét lại mã QR mới với số tiền còn thiếu.</p>
            </div>
            @endif

            @if($summary['remaining_to_pay'] > 0)
            <!-- Thanh toán SePay QR -->
            <div class="bg-white rounded-xl border border-blue-100 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                <div class="p-6 text-center">
                    <div class="flex items-center justify-center gap-2 mb-4 opacity-60">
                        <i class="fa-solid fa-bolt text-blue-600 text-xl"></i>
                        <span class="font-black tracking-wider text-lg text-blue-600 uppercase">SePay</span>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Thanh toán chuyển khoản</h3>
                    <p class="text-sm text-gray-500 mb-6">Mời bệnh nhân quét mã QR</p>

                    <div id="qr-container" class="bg-gray-50 p-4 rounded-xl inline-block border border-gray-200 shadow-inner mb-4 relative overflow-hidden">
                        <!-- Hiệu ứng quét -->
                        <div class="absolute inset-0 border-2 border-blue-400 rounded-xl opacity-20 pointer-events-none animate-pulse"></div>
                        <img src="{{ $qrUrl }}" alt="QR Code" class="w-48 h-48 mx-auto rounded-lg transition-all duration-300" id="qr-image">

                        <!-- Overlay Hết hạn -->
                        <div id="qr-expired-overlay" class="hidden absolute inset-0 bg-white/90 backdrop-blur-sm rounded-xl flex flex-col items-center justify-center">
                            <i class="fa-solid fa-clock-rotate-left text-3xl text-gray-500 mb-2"></i>
                            <p class="font-bold text-gray-800">Mã QR hết hạn</p>
                            <button onclick="window.location.href = '?renew=1'" class="mt-2 px-3 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                                <i class="fa-solid fa-rotate-right mr-1"></i> Tạo mới
                            </button>
                        </div>
                    </div>

                    <div id="payment-status-banner" class="bg-blue-50 text-blue-800 text-sm p-3 rounded-lg flex items-center justify-between font-medium">
                        <div class="flex items-center">
                            <i class="fa-solid fa-circle-notch fa-spin mr-2 text-blue-600" id="payment-spinner"></i> <span id="payment-status-text">Đang chờ thanh toán...</span>
                        </div>
                        <div class="text-blue-700 font-mono font-bold bg-blue-100 px-2 py-0.5 rounded" id="qr-countdown">05:00</div>
                    </div>

                    <div class="mt-4">
                        <button onclick="window.location.href = '?renew=1'" class="w-full px-4 py-2 bg-white text-blue-600 hover:bg-blue-50 font-medium rounded-lg transition-colors border border-blue-200 text-sm shadow-sm flex items-center justify-center">
                            <i class="fa-solid fa-arrows-rotate mr-2"></i> Làm mới mã QR
                        </button>
                    </div>
                </div>
            </div>
            @else
            <!-- Đã thanh toán -->
            <div class="bg-white rounded-xl border border-emerald-100 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-600"></div>
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Thanh toán hoàn tất!</h3>
                    <p class="text-sm text-gray-500 mb-6">Các khoản phí hiện tại đã được thanh toán.</p>

                    <a href="{{ route('doctor.appointments.show', $appointment->id) }}"
                        class="w-full inline-flex bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-lg transition-colors justify-center items-center">
                        <i class="fa-solid fa-arrow-right mr-2"></i> Tiếp tục quy trình khám
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        const appointmentId = Number("{{ $appointment->id }}");
        const currentPaidAmount = Number("{{ $summary['amount_paid'] ?? 0 }}");
        const statusBanner = document.getElementById('payment-status-banner');

        // QR Countdown Timer (5 phút) đồng bộ server
        const serverStartTime = Number("{{ $startTime ?? time() }}");
        let qrExpired = false;

        function updateTimer() {
            if (qrExpired) return;

            let nowUnix = Math.floor(Date.now() / 1000);
            let elapsed = nowUnix - serverStartTime;
            let timeLeft = 300 - elapsed;

            if (timeLeft <= 0) {
                qrExpired = true;
                if (typeof checkPaymentInterval !== 'undefined') {
                    clearInterval(checkPaymentInterval); // Ngừng polling khi hết hạn
                }

                // Cập nhật UI hết hạn
                if (document.getElementById('qr-countdown')) document.getElementById('qr-countdown').innerText = '00:00';
                if (document.getElementById('qr-image')) document.getElementById('qr-image').classList.add('blur-[4px]', 'opacity-50');
                if (document.getElementById('qr-expired-overlay')) document.getElementById('qr-expired-overlay').classList.remove('hidden');

                if (statusBanner) {
                    statusBanner.classList.remove('bg-blue-50', 'text-blue-800');
                    statusBanner.classList.add('bg-gray-100', 'text-gray-600', 'border', 'border-gray-300');
                    if (document.getElementById('payment-spinner')) {
                        document.getElementById('payment-spinner').classList.replace('fa-circle-notch', 'fa-clock');
                        document.getElementById('payment-spinner').classList.replace('fa-spin', 'opacity-50');
                        document.getElementById('payment-spinner').classList.replace('text-blue-600', 'text-gray-500');
                    }
                    if (document.getElementById('payment-status-text')) document.getElementById('payment-status-text').innerText = 'Phiên thanh toán đã hết hạn';
                }
            } else {
                let m = Math.floor(timeLeft / 60).toString().padStart(2, '0');
                let s = (timeLeft % 60).toString().padStart(2, '0');
                if(document.getElementById('qr-countdown')) document.getElementById('qr-countdown').innerText = m + ':' + s;

                // Khi còn dưới 1 phút thì đổi màu cảnh báo
                if (timeLeft <= 60 && document.getElementById('qr-countdown')) {
                    document.getElementById('qr-countdown').classList.replace('bg-blue-100', 'bg-red-100');
                    document.getElementById('qr-countdown').classList.replace('text-blue-700', 'text-red-700');
                }
            }
        }

        updateTimer();
        const countdownTimer = setInterval(updateTimer, 1000);

        let checkPaymentInterval = setInterval(function() {
            if (qrExpired) return;

            fetch(`/api/payments/${appointmentId}/check-status`)
                .then(response => response.json())
                .then(data => {
                    if (data.paid === true) {
                        clearInterval(checkPaymentInterval);
                        clearInterval(countdownTimer); // Ngừng đếm ngược khi đã thanh toán xong

                        if (statusBanner) {
                            statusBanner.classList.remove('bg-blue-50', 'text-blue-800');
                            statusBanner.classList.add('bg-emerald-50', 'text-emerald-800', 'justify-center');
                            statusBanner.innerHTML = '<i class="fa-solid fa-circle-check mr-2 text-emerald-600"></i> Thanh toán thành công!';
                        }

                        // Redirect bình thường sau 1.5 giây để tiếp tục khám
                        setTimeout(function() {
                            window.location.href = "{{ route('doctor.appointments.show', $appointment->id) }}";
                        }, 1500);
                    } else if (data.paid_amount > currentPaidAmount) {
                        // Khách chuyển khoản nhưng thiếu tiền -> reload trang để cập nhật số tiền còn lại
                        window.location.reload();
                    }
                })
                .catch(err => {
                    // Ignore network errors, sẽ retry lần sau
                });
        }, 3000);
    </script>
</x-layouts.doctor>
