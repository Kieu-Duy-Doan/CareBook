<x-layouts.receptionist title="Thanh toán & Thu ngân">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('receptionist.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <a href="{{ route('receptionist.clinical-visits.index') }}" class="hover:text-blue-600 transition-colors">Giám sát LS</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <span class="text-gray-800 font-medium">Thanh toán</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Thanh toán cho Lượt khám #{{ $clinical_visit->id }}</h2>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-800 p-4 rounded-lg mb-6 border border-green-200">
            <i class="fa-solid fa-circle-check text-green-500 mr-2"></i>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 text-red-800 p-4 rounded-lg mb-6 border border-red-200">
            <i class="fa-solid fa-circle-xmark text-red-500 mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Thông tin bệnh nhân -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">
                    Thông tin Bệnh nhân
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Họ và tên</p>
                        <p class="font-medium">{{ $clinical_visit->appointment->patientProfile->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Số điện thoại</p>
                        <p class="font-medium">{{ $clinical_visit->appointment->patientProfile->phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Bác sĩ khám</p>
                        <p class="font-medium">{{ $clinical_visit->appointment->doctorProfile->full_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Trạng thái thanh toán hiện tại</p>
                        <p class="font-medium">
                            @if ($clinical_visit->payment_status == 'paid')
                                <span class="text-green-600 font-bold bg-green-50 px-2 py-1 rounded">Đã thanh toán</span>
                            @else
                                <span class="text-yellow-600 font-bold bg-yellow-50 px-2 py-1 rounded">Chưa thanh toán</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phương thức thanh toán -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">
                    Lựa chọn thanh toán
                </h3>

                @if ($clinical_visit->payment_status == 'paid')
                    <div class="text-center py-8">
                        <i class="fa-solid fa-circle-check text-5xl text-green-500 mb-4"></i>
                        <h4 class="text-xl font-bold text-gray-900">Lượt khám này đã được thanh toán!</h4>
                        <p class="text-gray-500 mt-2">Bệnh nhân có thể tiếp tục vào phòng khám.</p>
                        <a href="{{ route('receptionist.clinical-visits.index') }}" class="mt-4 inline-block bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200">
                            Quay lại danh sách
                        </a>
                    </div>
                @else
                    <div x-data="{ tab: 'manual' }">
                        <div class="flex border-b border-gray-200 mb-4">
                            <button @click="tab = 'manual'" :class="tab == 'manual' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                                Tiền mặt / BHYT
                            </button>
                            <button @click="tab = 'payos'" :class="tab == 'payos' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center">
                                Chuyển khoản VietQR
                                <span class="ml-2 bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-bold">Auto</span>
                            </button>
                        </div>

                        <!-- Manual Tab -->
                        <div x-show="tab == 'manual'">
                            <form action="{{ route('receptionist.payments.storeManual', $clinical_visit->id) }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Số tiền thanh toán (VNĐ)</label>
                                        <input type="text" value="{{ number_format($clinical_visit->payment_amount, 0, ',', '.') }}" readonly class="block w-full py-2 px-3 border border-gray-300 bg-gray-100 text-gray-600 rounded-lg cursor-not-allowed font-medium">
                                        <input type="hidden" name="amount" value="{{ floatval($clinical_visit->payment_amount) }}">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Phương thức</label>
                                        <select name="payment_method" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                            <option value="cash">Tiền mặt</option>
                                            <option value="insurance">Bảo hiểm y tế</option>
                                        </select>
                                    </div>
                                    <div class="pt-4">
                                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg flex justify-center items-center gap-2">
                                            <i class="fa-solid fa-money-bill-wave"></i> Xác nhận đã thu tiền
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- PayOS Tab -->
                        <div x-show="tab == 'payos'" style="display: none;">
                            <form action="{{ route('receptionist.payments.createPayOS', $clinical_visit->id) }}" method="POST">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Số tiền thanh toán (VNĐ) - Tối thiểu 2.000đ</label>
                                        <input type="text" value="{{ number_format($clinical_visit->payment_amount, 0, ',', '.') }}" readonly class="block w-full py-2 px-3 border border-gray-300 bg-gray-100 text-gray-600 rounded-lg cursor-not-allowed font-medium">
                                        <input type="hidden" name="amount" value="{{ floatval($clinical_visit->payment_amount) }}">
                                    </div>
                                    <div class="bg-blue-50 p-4 rounded-lg text-sm text-blue-800 border border-blue-100">
                                        <i class="fa-solid fa-circle-info mr-2"></i>
                                        Hệ thống sẽ chuyển hướng sang Cổng thanh toán PayOS để tạo mã QR. Sau khi bệnh nhân quét mã và chuyển khoản thành công, hệ thống sẽ tự động ghi nhận thanh toán.
                                    </div>
                                    <div class="pt-4 flex items-center justify-between">
                                        <a href="javascript:window.location.reload(true)" class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center">
                                            <i class="fa-solid fa-rotate-right mr-1"></i> Làm mới trạng thái
                                        </a>
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg flex justify-center items-center gap-2">
                                            <i class="fa-solid fa-qrcode"></i> Tạo mã QR Thanh toán
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.receptionist>
