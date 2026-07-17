<x-layouts.doctor>
    <x-slot:title>Chi tiết Thanh toán - LH #{{ $appointment->appointment_code }}</x-slot:title>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Chi tiết Thanh toán & In Phiếu</h2>
            <p class="text-gray-500 mt-1">Mã lịch hẹn: <span class="font-mono font-bold text-gray-900">{{ $appointment->appointment_code }}</span></p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{-- Nút In Phiếu Chỉ Định (chỉ hiện nếu có sub-visits) --}}
            @if($appointment->clinicalVisits->where('is_origin', false)->count() > 0)
            <a href="{{ route('doctor.payments.print-referral', $appointment->id) }}" target="_blank"
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-bold text-sm shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-print"></i> In Phiếu Chỉ Định
            </a>
            @endif

            {{-- Nút In Đơn Thuốc (chỉ hiện nếu đã có đơn thuốc và đã thanh toán xong) --}}
            @if($appointment->medicalRecord?->prescription && $summary['remaining_to_pay'] <= 0)
            <a href="{{ route('doctor.payments.print-prescription', $appointment->id) }}" target="_blank"
               class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors font-bold text-sm shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-pills"></i> In Đơn Thuốc
            </a>
            @endif

            <a href="{{ route('doctor.payments.index') }}"
               class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" style="display:none"
         class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-lg mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-green-500"></i>{{ session('success') }}</div>
        <button @click="show=false" class="text-green-500 hover:text-green-700"><i class="fa-solid fa-xmark"></i></button>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Chi tiết dịch vụ --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-900"><i class="fa-solid fa-file-invoice mr-2 text-emerald-600"></i>Thông tin Bệnh nhân & Dịch vụ</h3>
                </div>
                <div class="p-5">
                    <div class="mb-5 pb-5 border-b border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">Bệnh nhân</p>
                        <p class="font-bold text-gray-900 text-lg">{{ $appointment->patientProfile->full_name }}</p>
                        <p class="text-sm font-mono text-gray-500">{{ $appointment->patientProfile->patient_code }}</p>
                    </div>

                    <h4 class="font-bold text-gray-900 mb-3 text-xs uppercase tracking-wider text-gray-500">Chi tiết dịch vụ</h4>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="py-2 px-3 text-left rounded-l-lg">Hạng mục</th>
                                <th class="py-2 px-3 text-center">Trạng thái</th>
                                <th class="py-2 px-3 text-right rounded-r-lg">Chi phí</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($appointment->clinicalVisits as $visit)
                            <tr>
                                <td class="py-2.5 px-3 font-medium text-gray-900">
                                    {{ $visit->is_origin ? 'Phí Khám Bệnh' : ($visit->room->name ?? 'Dịch vụ Cận lâm sàng') }}
                                    <span class="text-xs text-gray-400 block font-normal">#{{ $visit->id }}</span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if($visit->payment_status === 'paid')
                                        <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">Đã thu</span>
                                    @else
                                        <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">Chờ thu</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-right font-bold text-gray-900">{{ number_format($visit->payment_amount, 0, ',', '.') }}đ</td>
                            </tr>
                            @endforeach

                            @if($appointment->medicalRecord?->prescription)
                            <tr>
                                <td class="py-2.5 px-3 font-medium text-gray-900">
                                    Đơn Thuốc (Kê đơn)
                                    <span class="text-xs text-gray-400 block font-normal">#{{ $appointment->medicalRecord->prescription->id }}</span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if($appointment->medicalRecord->prescription->payment_status === 'paid')
                                        <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">Đã thu</span>
                                    @else
                                        <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">Chờ thu</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-right font-bold text-gray-900">
                                    {{ number_format($appointment->medicalRecord->prescription->payment_amount ?? 0, 0, ',', '.') }}đ
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>

                    <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Tổng chi phí:</span>
                            <span class="font-bold text-gray-900">{{ number_format($summary['total_amount'], 0, ',', '.') }}đ</span>
                        </div>
                        @if($summary['insurance_covers'] > 0)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">BHYT chi trả ({{ $summary['insurance_rate'] * 100 }}%):</span>
                            <span class="font-bold text-blue-600">-{{ number_format($summary['insurance_covers'], 0, ',', '.') }}đ</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Đã thanh toán:</span>
                            <span class="font-bold text-emerald-600">{{ number_format($summary['amount_paid'], 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex justify-between items-center text-base pt-2 border-t border-gray-100">
                            <span class="font-bold text-gray-900">Còn lại:</span>
                            <span class="font-black text-xl {{ $summary['remaining_to_pay'] > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ number_format($summary['remaining_to_pay'], 0, ',', '.') }}đ
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lịch sử giao dịch --}}
        <div class="space-y-6">
            {{-- Trạng thái tổng --}}
            @if($summary['remaining_to_pay'] > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-amber-900 mb-1"><i class="fa-solid fa-circle-exclamation mr-2"></i>Chưa thanh toán đủ</h3>
                    <p class="text-amber-700 text-sm">Còn <strong>{{ number_format($summary['remaining_to_pay'], 0, ',', '.') }}đ</strong> chưa thu.</p>
                </div>
                <a href="{{ route('doctor.payments.checkout', $appointment->id) }}"
                   class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-colors text-sm flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-qrcode"></i> Tạo QR
                </a>
            </div>
            @else
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-5 flex items-center gap-4">
                <div class="h-12 w-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-2xl shrink-0">
                    <i class="fa-solid fa-check-double"></i>
                </div>
                <div>
                    <h3 class="font-bold text-emerald-900">Đã thanh toán hoàn tất</h3>
                    <p class="text-emerald-700 text-sm">Tất cả khoản phí đã được thu đầy đủ.</p>
                </div>
            </div>
            @endif

            {{-- Lịch sử giao dịch --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-900"><i class="fa-solid fa-money-bill-transfer mr-2 text-blue-600"></i>Lịch sử Giao dịch</h3>
                </div>
                <div class="p-5">
                    @forelse($appointment->payments as $payment)
                    <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-100 last:border-0 last:mb-0 last:pb-0">
                        <div>
                            <div class="font-bold text-gray-900 text-lg mb-1">{{ number_format($payment->amount, 0, ',', '.') }}đ</div>
                            <div class="text-xs text-gray-500 flex items-center gap-2">
                                @if($payment->method === 'cash')
                                    <i class="fa-solid fa-money-bill text-emerald-600"></i> Tiền mặt
                                @elseif($payment->method === 'qr')
                                    <i class="fa-solid fa-qrcode text-purple-600"></i> QR (SePay)
                                @elseif($payment->method === 'insurance')
                                    <i class="fa-solid fa-shield-heart text-blue-600"></i> BHYT
                                @else
                                    <i class="fa-solid fa-credit-card text-gray-500"></i> Khác
                                @endif
                                <span>&bull;</span>
                                <span class="font-mono">{{ $payment->transaction_code ?? 'N/A' }}</span>
                            </div>
                            @if($payment->collectedBy)
                            <div class="text-[10px] text-gray-400 mt-1 uppercase tracking-wider">
                                Thu bởi: {{ $payment->collectedBy->name }}
                            </div>
                            @endif
                            @if($payment->note)
                            <div class="text-xs text-amber-700 mt-1 bg-amber-50 px-2 py-0.5 rounded">
                                {{ $payment->note }}
                            </div>
                            @endif
                        </div>
                        <div class="text-right">
                            @if($payment->status === 'completed')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">THÀNH CÔNG</span>
                            @elseif($payment->status === 'needs_review')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-800">CẦN XEM XÉT</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">{{ strtoupper($payment->status) }}</span>
                            @endif
                            <div class="text-xs text-gray-500 font-mono mt-1">{{ \Carbon\Carbon::parse($payment->paid_at)->format('H:i d/m/Y') }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-400 text-sm italic">
                        <i class="fa-solid fa-receipt text-3xl mb-2 opacity-30 block"></i>
                        Chưa có giao dịch thanh toán nào.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.doctor>
