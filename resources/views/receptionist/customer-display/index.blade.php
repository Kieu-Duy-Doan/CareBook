<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Màn hình Khách hàng - CareBook</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900 min-h-screen p-4 md:p-8 flex flex-col">

    <!-- Header Alert -->
    <div id="cd-header" class="bg-blue-600 text-white p-4 rounded-xl shadow-lg mb-6 flex justify-between items-center shrink-0">
        <div>
            <h1 class="text-xl font-bold"><i class="fa-solid fa-display mr-2"></i> Chế độ Màn hình Phụ</h1>
            <p class="text-sm opacity-90 mt-1">Vui lòng kéo cửa sổ này sang màn hình thứ 2 quay về phía khách hàng. Nhấn F11 để bật toàn màn hình.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="document.documentElement.requestFullscreen(); document.getElementById('cd-header').classList.add('hidden');" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-bold transition-colors">
                <i class="fa-solid fa-expand mr-2"></i> Toàn màn hình
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-red-500 hover:bg-red-600 rounded-lg text-sm font-bold transition-colors">
                <i class="fa-solid fa-xmark mr-2"></i> Đóng
            </button>
        </div>
    </div>

    <!-- Main Display Area -->
    <div id="customer-display-area" class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden flex-1 flex items-center justify-center relative p-8">
        
        <!-- Loading / Idle State -->
        <div id="state-idle" class="text-center transition-all duration-500">
            <div class="w-40 h-40 mx-auto mb-8 opacity-20">
                <i class="fa-solid fa-hospital-user text-9xl text-emerald-600"></i>
            </div>
            <h2 class="text-4xl font-black text-gray-900 mb-4">Xin chào Quý khách!</h2>
            <p class="text-2xl text-gray-500">Vui lòng chờ trong giây lát, Lễ tân đang xử lý thông tin...</p>
        </div>

        <!-- Checkout State -->
        <div id="state-checkout" class="hidden w-full max-w-6xl transition-all duration-500">
            <div class="text-center mb-12">
                <h2 class="text-5xl font-black text-gray-900 mb-4">Thông tin Thanh toán</h2>
                <p class="text-2xl text-gray-500">Bệnh nhân: <span id="cd-patient-name" class="font-bold text-gray-800 uppercase"></span></p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-stretch">
                <!-- Left: Bill Summary -->
                <div class="bg-gray-50 rounded-3xl p-10 border border-gray-200 flex flex-col justify-center shadow-inner">
                    <h3 class="text-3xl font-bold text-gray-900 mb-8 border-b border-gray-200 pb-6">Chi tiết Chi phí</h3>
                    
                    <div class="space-y-6 text-2xl">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Tổng chi phí:</span>
                            <span id="cd-total-amount" class="font-bold text-gray-900">0đ</span>
                        </div>
                        <div class="flex justify-between items-center pb-6 border-b border-gray-200">
                            <span class="text-emerald-600">BHYT chi trả:</span>
                            <span id="cd-insurance" class="font-bold text-emerald-600">- 0đ</span>
                        </div>
                        <div class="flex justify-between items-center pt-4">
                            <span class="text-2xl font-bold text-gray-900">CẦN THANH TOÁN:</span>
                            <span id="cd-remaining" class="text-5xl font-black text-red-600">0đ</span>
                        </div>
                    </div>

                    <div id="cd-overpaid-alert" class="hidden mt-10 bg-amber-100 border border-amber-300 text-amber-800 p-6 rounded-2xl flex items-center shadow-sm">
                        <i class="fa-solid fa-hand-holding-dollar text-5xl mr-6"></i>
                        <div>
                            <div class="font-bold text-2xl">Quý khách đã chuyển dư tiền!</div>
                            <div class="text-lg mt-2">Lễ tân sẽ hoàn trả lại phần tiền thừa là <b id="cd-overpaid-amount" class="text-2xl text-amber-900"></b>. Xin cảm ơn!</div>
                        </div>
                    </div>
                </div>

                <!-- Right: QR Code -->
                <div class="bg-white rounded-3xl border-2 border-blue-100 p-10 text-center shadow-xl relative overflow-hidden flex flex-col justify-center items-center min-h-[450px]">
                    <div class="absolute top-0 inset-x-0 h-3 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                    
                    <div id="cd-qr-area" class="w-full flex flex-col items-center justify-center">
                        <div class="flex items-center justify-center gap-3 mb-8 opacity-60">
                            <i class="fa-solid fa-bolt text-blue-600 text-3xl"></i>
                            <span class="font-black tracking-widest text-2xl text-blue-600 uppercase">SePay</span>
                        </div>
                        <p class="text-gray-600 mb-8 text-xl font-medium">Quét mã QR bằng App Ngân hàng để thanh toán</p>
                        
                        <div id="cd-qr-wrapper" class="bg-gray-50 p-6 rounded-3xl inline-block border border-gray-200 shadow-inner mb-8 relative overflow-hidden">
                            <div class="absolute inset-0 border-4 border-blue-400 rounded-3xl opacity-20 pointer-events-none animate-pulse"></div>
                            <img id="cd-qr-image" src="" alt="QR Code" class="w-80 h-80 mx-auto rounded-2xl object-contain bg-white transition-all duration-300">
                            
                            <!-- Overlay Hết hạn -->
                            <div id="cd-qr-expired-overlay" class="hidden absolute inset-0 bg-white/90 backdrop-blur-sm rounded-3xl flex flex-col items-center justify-center">
                                <i class="fa-solid fa-clock-rotate-left text-5xl text-gray-400 mb-4"></i>
                                <p class="font-bold text-gray-800 text-2xl mb-2">Mã QR hết hạn</p>
                                <p class="text-gray-500 text-lg">Vui lòng chờ Lễ tân tạo lại mã</p>
                            </div>
                        </div>
                        
                        <div id="cd-payment-status-banner" class="bg-blue-50 text-blue-800 p-5 rounded-2xl flex items-center justify-between font-bold text-xl w-full border border-blue-100 transition-colors">
                            <div class="flex items-center">
                                <i class="fa-solid fa-circle-notch fa-spin mr-3 text-blue-600 text-2xl" id="cd-payment-spinner"></i> 
                                <span id="cd-payment-status-text">Đang chờ thanh toán...</span>
                            </div>
                            <div class="text-blue-700 font-mono bg-blue-100 px-3 py-1 rounded-lg" id="cd-qr-countdown">05:00</div>
                        </div>
                    </div>

                    <div id="cd-success-area" class="hidden w-full py-12 flex flex-col items-center justify-center">
                        <div class="w-32 h-32 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-8 text-6xl shadow-inner">
                            <i class="fa-solid fa-check-double"></i>
                        </div>
                        <h3 class="text-4xl font-black text-gray-900 mb-4">Thanh toán Thành công!</h3>
                        <p class="text-gray-500 text-xl">Cảm ơn Quý khách. Chúc Quý khách nhiều sức khỏe.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function formatMoney(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
        }

        // Tự động ẩn thanh công cụ nếu ấn phím Esc thoát Fullscreen
        document.addEventListener('fullscreenchange', (event) => {
            if (!document.fullscreenElement) {
                document.getElementById('cd-header').classList.remove('hidden');
            }
        });

        // Cập nhật Timer từ Server Time
        let serverStartTime = 0;
        let qrExpired = false;

        function updateTimer() {
            if (qrExpired || serverStartTime === 0) return;
            
            let nowUnix = Math.floor(Date.now() / 1000);
            let elapsed = nowUnix - serverStartTime;
            let timeLeft = 300 - elapsed; // 5 phút (300s)

            if (timeLeft <= 0) {
                qrExpired = true;
                
                // Cập nhật UI hết hạn
                document.getElementById('cd-qr-countdown').innerText = '00:00';
                document.getElementById('cd-qr-image').classList.add('blur-sm', 'opacity-50');
                document.getElementById('cd-qr-expired-overlay').classList.remove('hidden');
                
                const statusBanner = document.getElementById('cd-payment-status-banner');
                if (statusBanner) {
                    statusBanner.classList.replace('bg-blue-50', 'bg-gray-100');
                    statusBanner.classList.replace('text-blue-800', 'text-gray-600');
                    statusBanner.classList.replace('border-blue-100', 'border-gray-300');
                    
                    document.getElementById('cd-payment-spinner').classList.replace('fa-circle-notch', 'fa-clock');
                    document.getElementById('cd-payment-spinner').classList.replace('fa-spin', 'opacity-50');
                    document.getElementById('cd-payment-spinner').classList.replace('text-blue-600', 'text-gray-500');
                    document.getElementById('cd-payment-status-text').innerText = 'Phiên thanh toán đã hết hạn';
                }
            } else {
                let m = Math.floor(timeLeft / 60).toString().padStart(2, '0');
                let s = (timeLeft % 60).toString().padStart(2, '0');
                document.getElementById('cd-qr-countdown').innerText = m + ':' + s;
                
                if (timeLeft <= 60) {
                    document.getElementById('cd-qr-countdown').classList.replace('bg-blue-100', 'bg-red-100');
                    document.getElementById('cd-qr-countdown').classList.replace('text-blue-700', 'text-red-700');
                } else {
                    // Reset class in case of new session
                    document.getElementById('cd-qr-countdown').classList.replace('bg-red-100', 'bg-blue-100');
                    document.getElementById('cd-qr-countdown').classList.replace('text-red-700', 'text-blue-700');
                }
            }
        }

        setInterval(updateTimer, 1000);

        setInterval(function() {
            if (qrExpired) return;

            fetch('/receptionist/customer-display/status')
                .then(response => response.json())
                .then(data => {
                    const stateIdle = document.getElementById('state-idle');
                    const stateCheckout = document.getElementById('state-checkout');
                    
                    if (data.status === 'idle') {
                        stateIdle.classList.remove('hidden');
                        stateCheckout.classList.add('hidden');
                        serverStartTime = 0; // Reset
                        qrExpired = false;
                        
                        // Khôi phục UI QR
                        document.getElementById('cd-qr-image').classList.remove('blur-sm', 'opacity-50');
                        document.getElementById('cd-qr-expired-overlay').classList.add('hidden');
                        
                        const statusBanner = document.getElementById('cd-payment-status-banner');
                        if (statusBanner) {
                            statusBanner.classList.replace('bg-gray-100', 'bg-blue-50');
                            statusBanner.classList.replace('text-gray-600', 'text-blue-800');
                            statusBanner.classList.replace('border-gray-300', 'border-blue-100');
                            
                            document.getElementById('cd-payment-spinner').classList.replace('fa-clock', 'fa-circle-notch');
                            document.getElementById('cd-payment-spinner').classList.replace('opacity-50', 'fa-spin');
                            document.getElementById('cd-payment-spinner').classList.replace('text-gray-500', 'text-blue-600');
                            document.getElementById('cd-payment-status-text').innerText = 'Đang chờ thanh toán...';
                        }
                    } else if (data.status === 'checkout') {
                        stateIdle.classList.add('hidden');
                        stateCheckout.classList.remove('hidden');

                        if (serverStartTime !== data.checkout_start_time) {
                            serverStartTime = data.checkout_start_time; // Bắt đầu đếm ngược phiên mới
                            qrExpired = false;
                            
                            // Khôi phục UI QR
                            document.getElementById('cd-qr-image').classList.remove('blur-sm', 'opacity-50');
                            document.getElementById('cd-qr-expired-overlay').classList.add('hidden');
                        }

                        document.getElementById('cd-patient-name').innerText = data.patient_name;
                        document.getElementById('cd-total-amount').innerText = formatMoney(data.total_amount);
                        document.getElementById('cd-insurance').innerText = '- ' + formatMoney(data.insurance_covers);
                        document.getElementById('cd-remaining').innerText = formatMoney(data.remaining_to_pay);
                        
                        const qrArea = document.getElementById('cd-qr-area');
                        const successArea = document.getElementById('cd-success-area');
                        const overpaidAlert = document.getElementById('cd-overpaid-alert');

                        if (data.is_paid) {
                            qrArea.classList.add('hidden');
                            successArea.classList.remove('hidden');
                            serverStartTime = 0; // Ngừng đếm
                            
                            if (data.overpaid_amount > 0) {
                                overpaidAlert.classList.remove('hidden');
                                document.getElementById('cd-overpaid-amount').innerText = formatMoney(data.overpaid_amount);
                            } else {
                                overpaidAlert.classList.add('hidden');
                            }
                        } else {
                            qrArea.classList.remove('hidden');
                            successArea.classList.add('hidden');
                            overpaidAlert.classList.add('hidden');
                            document.getElementById('cd-qr-image').src = data.qr_url;
                        }
                    }
                })
                .catch(err => console.log('Polling error:', err));
        }, 1500);
    </script>
</body>
</html>