<x-layouts.receptionist>
    <x-slot:title>Thủ tục Thanh toán</x-slot:title>

    <div class="mb-6">
        <a href="{{ route('receptionist.payments.index') }}" class="inline-flex items-center text-sm font-semibold text-emerald-600 hover:text-emerald-700 mb-2">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Quay lại danh sách hoá đơn
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Thủ tục Thanh toán</h2>
        <p class="text-gray-500 mt-1">Xác nhận thông tin dịch vụ khám và lựa chọn hình thức thanh toán cho bệnh nhân</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-medium">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Cột Trái & Giữa: Chi tiết hoá đơn -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Patient card -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="font-bold text-gray-950 text-base mb-4 flex items-center">
                    <i class="fa-solid fa-user-injured text-emerald-500 mr-2.5"></i> Thông tin bệnh nhân
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                    <div>
                        <span class="text-gray-500 block mb-0.5">Tên bệnh nhân</span>
                        <span class="font-bold text-gray-900">{{ $visit->appointment->patientProfile->full_name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-0.5">Mã bệnh nhân</span>
                        <span class="font-mono font-bold text-gray-900">{{ $visit->appointment->patientProfile->patient_code ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-0.5">Số điện thoại</span>
                        <span class="font-semibold text-gray-900">{{ $visit->appointment->patientProfile->phone ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block mb-0.5">Giới tính / Tuổi</span>
                        <span class="font-semibold text-gray-900">
                            {{ $visit->appointment->patientProfile->gender === 'male' ? 'Nam' : 'Nữ' }} / 
                            {{ $visit->appointment->patientProfile->age ?? '—' }} tuổi
                        </span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="text-gray-500 block mb-0.5">Lý do khám</span>
                        <p class="text-gray-800 italic font-medium">"{{ $visit->appointment->reason ?? 'Khám sức khoẻ tổng quát' }}"</p>
                    </div>
                </div>
            </div>

            <!-- Service Details -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="font-bold text-gray-950 text-base mb-4 flex items-center">
                    <i class="fa-solid fa-file-invoice text-emerald-500 mr-2.5"></i> Chi tiết dịch vụ
                </h3>
                <div class="border border-gray-150 rounded-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-150 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-gray-500 uppercase tracking-wider text-xs">Mô tả dịch vụ</th>
                                <th scope="col" class="px-6 py-3.5 text-left font-bold text-gray-500 uppercase tracking-wider text-xs">Bác sĩ / Phòng</th>
                                <th scope="col" class="px-6 py-3.5 text-right font-bold text-gray-500 uppercase tracking-wider text-xs w-36">Đơn giá</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-150 font-medium">
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-950">Khám chuyên khoa: {{ $visit->appointment->specialty->name ?? 'Tổng quát' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">Lượt khám ban đầu (Origin Visit)</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-900">{{ $visit->doctorProfile->user->name ?? '—' }}</div>
                                    <div class="text-xs text-purple-600 font-semibold mt-0.5">{{ $visit->room->name ?? '—' }}</div>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900">
                                    {{ number_format($visit->payment_amount, 0, ',', '.') }}đ
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cột Phải: Thanh toán -->
        <div class="space-y-6">
            <!-- Receipt Bill -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-100 px-6 py-4">
                    <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider"><i class="fa-solid fa-receipt mr-2 text-emerald-500"></i>Hoá đơn thu tiền</h3>
                </div>
                <div class="p-6 space-y-4 text-sm font-medium">
                    <div class="flex justify-between text-gray-500">
                        <span>Tiền khám bệnh</span>
                        <span class="text-gray-900">{{ number_format($visit->payment_amount, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="flex justify-between text-gray-500">
                        <span>Thuế VAT (0%)</span>
                        <span class="text-gray-900">0đ</span>
                    </div>
                    <hr class="border-gray-100">
                    <div class="flex justify-between items-center pt-2">
                        <span class="font-bold text-gray-900 text-base">Tổng số tiền thu</span>
                        <span class="font-extrabold text-xl text-emerald-600">{{ number_format($visit->payment_amount, 0, ',', '.') }}đ</span>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Form Selection -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-bold text-gray-950 text-sm uppercase tracking-wider mb-4"><i class="fa-solid fa-wallet mr-2 text-emerald-500"></i>Chọn hình thức thanh toán</h3>

                <div x-data="{ method: 'cash' }" class="space-y-4">
                    <!-- Option 1: Cash/Tiền mặt -->
                    <label class="relative flex items-center p-4 border rounded-xl cursor-pointer hover:bg-gray-50/50 transition-colors"
                        :class="method === 'cash' ? 'border-emerald-500 bg-emerald-50/20' : 'border-gray-200'">
                        <input type="radio" name="pay_type" value="cash" x-model="method" class="sr-only">
                        <div class="h-9 w-9 rounded bg-emerald-100 text-emerald-600 flex items-center justify-center mr-3 shrink-0">
                            <i class="fa-solid fa-money-bill-wave text-base"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-bold text-sm text-gray-900">Tiền mặt</div>
                            <div class="text-xs text-gray-500 mt-0.5">Bệnh nhân thanh toán tiền mặt tại quầy</div>
                        </div>
                        <i x-show="method === 'cash'" class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    </label>

                    <!-- Option 2: PayOS QR -->
                    <label class="relative flex items-center p-4 border rounded-xl cursor-pointer hover:bg-gray-50/50 transition-colors"
                        :class="method === 'qr' ? 'border-emerald-500 bg-emerald-50/20' : 'border-gray-200'">
                        <input type="radio" name="pay_type" value="qr" x-model="method" class="sr-only">
                        <div class="h-9 w-9 rounded bg-purple-100 text-purple-600 flex items-center justify-center mr-3 shrink-0">
                            <i class="fa-solid fa-qrcode text-base"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-bold text-sm text-gray-900">PayOS QR Code</div>
                            <div class="text-xs text-gray-500 mt-0.5">Quét QR chuyển khoản VietQR tự động</div>
                        </div>
                        <i x-show="method === 'qr'" class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    </label>

                    <!-- Option 3: Insurance/Bảo hiểm -->
                    <label class="relative flex items-center p-4 border rounded-xl cursor-pointer hover:bg-gray-50/50 transition-colors"
                        :class="method === 'insurance' ? 'border-emerald-500 bg-emerald-50/20' : 'border-gray-200'">
                        <input type="radio" name="pay_type" value="insurance" x-model="method" class="sr-only">
                        <div class="h-9 w-9 rounded bg-blue-100 text-blue-600 flex items-center justify-center mr-3 shrink-0">
                            <i class="fa-solid fa-shield-halved text-base"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-bold text-sm text-gray-900">Bảo hiểm chi trả</div>
                            <div class="text-xs text-gray-500 mt-0.5">Thanh toán bảo lãnh trực tiếp</div>
                        </div>
                        <i x-show="method === 'insurance'" class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    </label>

                    <!-- Option 4: Waived/Miễn giảm -->
                    <label class="relative flex items-center p-4 border rounded-xl cursor-pointer hover:bg-gray-50/50 transition-colors"
                        :class="method === 'waived' ? 'border-emerald-500 bg-emerald-50/20' : 'border-gray-200'">
                        <input type="radio" name="pay_type" value="waived" x-model="method" class="sr-only">
                        <div class="h-9 w-9 rounded bg-gray-100 text-gray-600 flex items-center justify-center mr-3 shrink-0">
                            <i class="fa-solid fa-percent text-base"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-bold text-sm text-gray-900">Miễn giảm phí khám</div>
                            <div class="text-xs text-gray-500 mt-0.5">Miễn phí khám (trường hợp đặc biệt)</div>
                        </div>
                        <i x-show="method === 'waived'" class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    </label>

                    <!-- Action Buttons based on select -->
                    <div class="pt-4">
                        <!-- Form Cash / Insurance / Waived -->
                        <form x-show="method !== 'qr'" action="{{ route('receptionist.payments.storeManual', $visit->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="payment_method" :value="method">
                            <button type="submit"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-sm flex items-center justify-center">
                                <i class="fa-solid fa-circle-check mr-2"></i> Xác nhận thanh toán thủ công
                            </button>
                        </form>

                        <!-- Form PayOS QR -->
                        <form x-show="method === 'qr'" action="{{ route('receptionist.payments.createPayOS', $visit->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-sm flex items-center justify-center">
                                <i class="fa-solid fa-qrcode mr-2"></i> Khởi tạo QR Code PayOS
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.receptionist>
