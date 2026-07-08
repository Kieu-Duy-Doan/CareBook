<x-layouts.receptionist>
    <x-slot:title>Xử lý thanh toán</x-slot:title>

    <div class="mb-6">
        <a href="{{ route('receptionist.payments.index') }}" class="text-emerald-600 hover:text-emerald-700 flex items-center gap-2 mb-4">
            <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Xử lý thanh toán</h2>
        <p class="text-gray-500 text-sm mt-1">Cập nhật trạng thái thanh toán cho lượt khám lâm sàng #{{ $payment->id }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Thông tin chi tiết -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Thông tin lượt khám</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Bệnh nhân</dt>
                            <dd class="mt-1 text-base text-gray-900 font-semibold">{{ $payment->appointment->patientProfile->full_name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Mã bệnh nhân</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $payment->appointment->patientProfile->patient_code ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Bác sĩ khám</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $payment->doctorProfile->user->full_name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Trạng thái khám</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ ucfirst($payment->status) }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Phí khám bệnh</dt>
                            <dd class="mt-1 text-2xl font-bold text-emerald-600">{{ number_format($payment->payment_amount, 0, ',', '.') }} VNĐ</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Form thanh toán -->
        <div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden sticky top-24">
                <div class="px-6 py-4 border-b border-gray-200 bg-emerald-50">
                    <h3 class="text-lg font-bold text-emerald-800">Cập nhật thanh toán</h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('receptionist.payments.update', $payment->id) }}" method="POST" x-data="{ status: '{{ old('payment_status', $payment->payment_status) }}' }">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái thanh toán <span class="text-red-500">*</span></label>
                            <select name="payment_status" x-model="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="pending" {{ $payment->payment_status === 'pending' ? 'selected' : '' }}>Chưa thanh toán</option>
                                <option value="paid" {{ $payment->payment_status === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                                <option value="waived" {{ $payment->payment_status === 'waived' ? 'selected' : '' }}>Miễn phí/Hủy</option>
                            </select>
                            @error('payment_status')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6" x-show="status === 'paid'" x-transition>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phương thức thanh toán <span class="text-red-500">*</span></label>
                            <select name="payment_method" :required="status === 'paid'"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                <option value="">Chọn phương thức...</option>
                                <option value="cash" {{ old('payment_method', $payment->payment_method) === 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                                <option value="qr" {{ old('payment_method', $payment->payment_method) === 'qr' ? 'selected' : '' }}>Chuyển khoản (QR)</option>
                                <option value="insurance" {{ old('payment_method', $payment->payment_method) === 'insurance' ? 'selected' : '' }}>Bảo hiểm</option>
                            </select>
                            @error('payment_method')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="flex-1 bg-emerald-600 text-white font-medium py-2.5 px-4 rounded-lg hover:bg-emerald-700 transition-colors">
                                Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.receptionist>
