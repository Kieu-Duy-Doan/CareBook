<x-layouts.receptionist>
    <x-slot:title>Thanh toán Phí - Lịch hẹn #{{ $appointment->appointment_code }}</x-slot:title>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Thanh toán Phí dịch vụ</h2>
            <p class="text-gray-500 mt-1">Mã lịch hẹn: <span class="font-mono text-gray-900 font-bold">{{ $appointment->appointment_code }}</span></p>
        </div>
        <a href="{{ route('receptionist.payments.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
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
                        <div class="flex-1">
                            <p class="text-sm text-gray-500 mb-1">Bác sĩ chỉ định</p>
                            <p class="font-bold text-gray-900">{{ $appointment->doctorProfile->user->name ?? '—' }}</p>
                            <p class="text-sm text-gray-500">{{ $appointment->specialty->name ?? '—' }}</p>
                        </div>
                    </div>

                    <!-- Danh sách dịch vụ -->
                    <h4 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wider">Chi tiết các khoản phí</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                                <tr>
                                    <th class="py-3 px-4 rounded-l-lg">Dịch vụ (Mã lượt)</th>
                                    <th class="py-3 px-4 text-right rounded-r-lg text-gray-900">Chi phí</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary['all_visits'] as $visit)
                                <tr class="border-b border-gray-50 last:border-0">
                                    <td class="py-3 px-4 font-medium text-gray-900">
                                        {{ $visit->is_origin ? 'Phí Khám Bệnh' : 'Dịch vụ Cận lâm sàng / Khác' }}
                                        <span class="text-xs text-gray-400 block font-mono">#{{ $visit->id }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-gray-900">{{ number_format($visit->payment_amount, 0, ',', '.') }}đ</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 bg-gray-50 rounded-lg p-5">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Tổng phí ({{ count($summary['all_visits']) }} mục):</span>
                            <span class="font-bold text-gray-900">{{ number_format($summary['total_amount'], 0, ',', '.') }}đ</span>
                        </div>

                        @if($summary['is_expired'])
                        <div class="mb-2 p-2 bg-red-50 text-red-700 text-xs rounded border border-red-100">
                            {{ $summary['warning_message'] }}
                        </div>
                        @else
                        <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-200">
                            <span class="text-emerald-600 font-medium">
                                BHYT chi trả ({{ $summary['insurance_rate'] * 100 }}%):
                            </span>
                            <span class="font-bold text-emerald-600">- {{ number_format($summary['insurance_covers'], 0, ',', '.') }}đ</span>
                        </div>
                        @endif

                        @if(isset($summary['amount_paid']) && $summary['amount_paid'] > 0)
                        <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-200">
                            <span class="text-blue-600 font-medium">Khách đã trả một phần:</span>
                            <span class="font-bold text-blue-600">- {{ number_format($summary['amount_paid'], 0, ',', '.') }}đ</span>
                        </div>
                        @endif

                        <div class="flex justify-between items-center text-lg mt-2 pt-2 border-t border-gray-200">
                            <span class="font-bold text-gray-900">Khách cần thanh toán:</span>
                            <span class="font-bold text-red-600 text-2xl">{{ number_format($summary['remaining_to_pay'], 0, ',', '.') }}đ</span>
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
                <p class="text-amber-600 text-xs mt-1">Vui lòng quét mã QR mới hoặc thu tiền mặt phần còn lại.</p>
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
                    <p class="text-sm text-gray-500 mb-6">Mở App Ngân hàng và quét mã QR</p>

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

            <!-- Thanh toán Tiền mặt -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900">Thanh toán Tiền mặt</h3>
                </div>
                <div class="p-5">
                    <form action="{{ route('receptionist.payments.storeManual', $appointment->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tổng tiền cần thu (VNĐ)</label>
                            <input type="text" value="{{ number_format($summary['remaining_to_pay'], 0, ',', '.') }}đ" readonly
                                class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-mono text-lg font-bold text-gray-900 cursor-not-allowed">
                        </div>
                        <button type="submit" onclick="return confirm('Xác nhận đã thu đủ tiền mặt từ khách?')"
                            class="w-full bg-gray-900 hover:bg-gray-800 text-white font-bold py-3 px-4 rounded-lg transition-colors flex justify-center items-center">
                            <i class="fa-solid fa-money-bill-wave mr-2"></i> Xác nhận Thu tiền mặt
                        </button>
                    </form>
                </div>
            </div>
            @else
            @if($summary['patient_pays'] > 0)
            <!-- Đã thanh toán (có thể có tiền thừa) -->
            <div class="bg-white rounded-xl border border-emerald-100 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-600"></div>
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                        <i class="fa-solid fa-check-double"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Thanh toán hoàn tất!</h3>
                    <p class="text-sm text-gray-500 mb-4">Hồ sơ này đã được thanh toán đủ chi phí.</p>

                    <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left text-sm">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Khách cần trả:</span>
                            <span class="font-bold">{{ number_format($summary['patient_pays'], 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Khách đã trả:</span>
                            <span class="font-bold text-emerald-600">{{ number_format($summary['amount_paid'], 0, ',', '.') }}đ</span>
                        </div>
                        @if($summary['overpaid_amount'] > 0)
                        <div class="flex justify-between pt-2 border-t border-gray-200 mt-2">
                            <span class="font-bold text-amber-600">Tiền thừa cần thối:</span>
                            <span class="font-bold text-amber-600">{{ number_format($summary['overpaid_amount'], 0, ',', '.') }}đ</span>
                        </div>
                        @endif
                    </div>

                    @if($summary['overpaid_amount'] > 0)
                    <a href="{{ route('receptionist.payments.show', $appointment->id) }}"
                        class="w-full inline-flex bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-4 rounded-lg transition-colors justify-center items-center">
                        <i class="fa-solid fa-hand-holding-dollar mr-2"></i> Đã hoàn trả tiền thừa & Chuyển tới In Hóa Đơn
                    </a>
                    @else
                    <a href="{{ route('receptionist.payments.show', $appointment->id) }}"
                        class="w-full inline-flex bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-lg transition-colors justify-center items-center">
                        <i class="fa-solid fa-print mr-2"></i> Chuyển tới trang In Hóa Đơn
                    </a>
                    @endif
                </div>
            </div>
            @else
            <!-- Miễn phí / BHYT 100% -->
            <div class="bg-white rounded-xl border border-emerald-100 shadow-sm overflow-hidden relative">
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-600"></div>
                <div class="p-6 text-center">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                        <i class="fa-solid fa-shield-check"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Bảo hiểm / Miễn phí</h3>
                    <p class="text-sm text-gray-500 mb-6">Hồ sơ này không phát sinh chi phí cần thu từ bệnh nhân.</p>

                    <form action="{{ route('receptionist.payments.storeManual', $appointment->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-lg transition-colors flex justify-center items-center">
                            <i class="fa-solid fa-check-circle mr-2"></i> Xác nhận Hoàn tất
                        </button>
                    </form>
                </div>
            </div>
            @endif
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
                document.getElementById('qr-countdown').innerText = '00:00';
                document.getElementById('qr-image').classList.add('blur-[4px]', 'opacity-50');
                document.getElementById('qr-expired-overlay').classList.remove('hidden');

                if (statusBanner) {
                    statusBanner.classList.remove('bg-blue-50', 'text-blue-800');
                    statusBanner.classList.add('bg-gray-100', 'text-gray-600', 'border', 'border-gray-300');
                    document.getElementById('payment-spinner').classList.replace('fa-circle-notch', 'fa-clock');
                    document.getElementById('payment-spinner').classList.replace('fa-spin', 'opacity-50');
                    document.getElementById('payment-spinner').classList.replace('text-blue-600', 'text-gray-500');
                    document.getElementById('payment-status-text').innerText = 'Phiên thanh toán đã hết hạn';
                }
            } else {
                let m = Math.floor(timeLeft / 60).toString().padStart(2, '0');
                let s = (timeLeft % 60).toString().padStart(2, '0');
                document.getElementById('qr-countdown').innerText = m + ':' + s;

                // Khi còn dưới 1 phút thì đổi màu cảnh báo
                if (timeLeft <= 60) {
                    document.getElementById('qr-countdown').classList.replace('bg-blue-100', 'bg-red-100');
                    document.getElementById('qr-countdown').classList.replace('text-blue-700', 'text-red-700');
                }
            }
        }

        updateTimer(); // Chạy ngay lập tức để đè lên 05:00 mặc định
        const countdownTimer = setInterval(updateTimer, 1000);

        let checkPaymentInterval = setInterval(function() {
            if (qrExpired) return;

            fetch(`/api/payments/${appointmentId}/check-status`)
                .then(response => response.json())
                .then(data => {
                    if (data.paid === true) {
                        clearInterval(checkPaymentInterval);
                        clearInterval(countdownTimer); // Ngừng đếm ngược khi đã thanh toán xong

                        let surplus = 0;
                        if (data.remaining_to_pay < 0) {
                            surplus = Math.abs(data.remaining_to_pay);
                        }

                        if (surplus > 0) {
                            // Khách chuyển dư, reload lại trang để hiện UI "Tiền thừa cần thối" và nút xác nhận
                            window.location.reload();
                        } else {
                            if (statusBanner) {
                                statusBanner.classList.remove('bg-blue-50', 'text-blue-800');
                                statusBanner.classList.add('bg-emerald-50', 'text-emerald-800', 'justify-center');
                                statusBanner.innerHTML = '<i class="fa-solid fa-circle-check mr-2 text-emerald-600"></i> Thanh toán thành công!';
                            }

                            // Redirect bình thường sau 2 giây
                            setTimeout(function() {
                                window.location.href = "{{ route('receptionist.payments.show', $appointment->id) }}";
                            }, 2000);
                        }
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
</x-layouts.receptionist>