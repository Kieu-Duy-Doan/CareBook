<x-layouts.receptionist>
    <x-slot:title>Lịch sử Thanh toán - Lịch hẹn #{{ $appointment->appointment_code }}</x-slot:title>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Chi tiết Thanh toán & In Hóa đơn</h2>
            <p class="text-gray-500 mt-1">Mã lịch hẹn: <span class="font-mono text-gray-900 font-bold">{{ $appointment->appointment_code }}</span></p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(isset($summary) && $summary['remaining_to_pay'] <= 0)
                <a href="{{ route('receptionist.payments.printVat', $appointment->id) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-bold text-sm shadow-sm flex items-center">
                <i class="fa-solid fa-file-invoice mr-2"></i> In Hóa đơn VAT
                </a>
                <a href="{{ route('receptionist.payments.printDeposit', $appointment->id) }}" target="_blank" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-bold text-sm shadow-sm flex items-center">
                    <i class="fa-solid fa-receipt mr-2"></i> In Phiếu Tạm Ứng
                </a>
                @endif
                <a href="{{ route('receptionist.payments.index', ['tab' => 'history']) }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
                </a>
        </div>
    </div>

    @if(isset($summary) && $summary['overpaid_amount'] > 0)
    @php
    $hasRefunded = $appointment->payments->where('status', 'refunded')->isNotEmpty();
    @endphp
    @if(!$hasRefunded)
    <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-center justify-between shadow-sm print:hidden">
        <div class="flex items-center">
            <div class="h-10 w-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xl mr-4">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <h3 class="font-bold text-amber-900">Có khoản tiền thừa cần thối lại</h3>
                <p class="text-amber-700 text-sm">Bệnh nhân đã chuyển dư <span class="font-bold">{{ number_format($summary['overpaid_amount'], 0, ',', '.') }}đ</span>. Vui lòng thối lại bằng tiền mặt.</p>
            </div>
        </div>
        <form action="{{ route('receptionist.payments.refund', $appointment->id) }}" method="POST">
            @csrf
            <button type="submit" onclick="return confirm('Xác nhận đã thối lại {{ number_format($summary['overpaid_amount'], 0, ',', '.') }}đ cho bệnh nhân?')" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg shadow-sm transition-colors">
                <i class="fa-solid fa-check mr-2"></i> Đã thối tiền
            </button>
        </form>
    </div>
    @else
    <div class="mb-6 bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center shadow-sm print:hidden">
        <i class="fa-solid fa-circle-check text-emerald-500 text-xl mr-3"></i>
        <p class="text-emerald-800 font-medium">Đã ghi nhận thối lại tiền thừa <span class="font-bold">{{ number_format($summary['overpaid_amount'], 0, ',', '.') }}đ</span> cho bệnh nhân.</p>
    </div>
    @endif
    @endif

    <!-- Tiêu đề dành riêng cho bản in -->
    <div class="hidden print:block mb-8 text-center border-b pb-4">
        <h1 class="text-2xl font-bold uppercase mb-1">BỆNH VIỆN ĐA KHOA CAREBOOK</h1>
        <p class="text-sm">HÓA ĐƠN ĐIỆN TỬ / PHIẾU THU DỊCH VỤ</p>
        <p class="text-xs mt-2">Mã KH: {{ $appointment->appointment_code }} | Ngày in: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 print:block print:w-full">

        <!-- Các khoản phí đã thanh toán -->
        <div class="space-y-6">
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

                    <h4 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wider">Các khoản phí ĐÃ THANH TOÁN</h4>
                    <div class="overflow-x-auto mb-6">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                                <tr>
                                    <th class="py-3 px-4 rounded-l-lg">Dịch vụ (Mã lượt)</th>
                                    <th class="py-3 px-4 text-right rounded-r-lg text-gray-900">Chi phí</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($appointment->clinicalVisits as $visit)
                                <tr class="border-b border-gray-50 last:border-0">
                                    <td class="py-3 px-4 font-medium text-gray-900">
                                        {{ $visit->is_origin ? 'Phí Khám Bệnh' : 'Dịch vụ Cận lâm sàng / Khác' }}
                                        <span class="text-xs text-gray-400 block font-mono">#{{ $visit->id }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-gray-900">{{ number_format($visit->payment_amount, 0, ',', '.') }}đ</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" class="py-3 px-4 text-center text-gray-500 text-sm">Không có khoản phí nào (hoặc đã được BHYT chi trả 100%)</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lịch sử giao dịch -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900"><i class="fa-solid fa-money-bill-transfer mr-2 text-blue-600"></i> Lịch sử Giao dịch</h3>
                </div>
                <div class="p-5">
                    @forelse($appointment->payments as $payment)
                    <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-100 last:border-0 last:mb-0 last:pb-0">
                        <div>
                            <div class="font-bold text-gray-900 text-lg mb-1">{{ number_format($payment->amount, 0, ',', '.') }}đ</div>
                            <div class="text-xs text-gray-500 flex items-center gap-2">
                                <span>
                                    @if($payment->method === 'cash')
                                    <i class="fa-solid fa-money-bill text-emerald-600"></i> Tiền mặt
                                    @elseif($payment->method === 'qr')
                                    <i class="fa-solid fa-qrcode text-purple-600"></i> QR Code (SePay)
                                    @else
                                    <i class="fa-solid fa-credit-card text-blue-600"></i> Cà thẻ/Khác
                                    @endif
                                </span>
                                <span>&bull;</span>
                                <span>{{ $payment->transaction_code ?? 'N/A' }}</span>
                            </div>
                            @if($payment->collectedBy)
                            <div class="text-[10px] text-gray-400 mt-1 uppercase tracking-wider">
                                Thu bởi: {{ $payment->collectedBy->name }}
                            </div>
                            @endif
                        </div>
                        <div class="text-right">
                            @if($payment->status === 'completed')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                THÀNH CÔNG
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-800">
                                {{ strtoupper($payment->status) }}
                            </span>
                            @endif
                            <div class="text-xs text-gray-500 font-mono mt-1">{{ Carbon\Carbon::parse($payment->paid_at)->format('H:i d/m/Y') }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-gray-500 text-sm">Chưa có giao dịch thanh toán nào.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.receptionist>