<x-layouts.patient-dashboard title="Chi tiết lịch hẹn" activeMenu="appointments">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Chi tiết lịch hẹn</h1>
                <p class="text-slate-500">Xem thông tin lịch khám .</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('patient.appointments.index') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition"><i class="fa-solid fa-chevron-left"></i> Quay lại</a>
                @if ($appointment->status === 'pending')
                    <form action="{{ route('patient.appointments.cancel', $appointment->id) }}" method="POST" class="inline-block">@csrf<button type="submit" class="inline-flex items-center gap-2 rounded-full bg-rose-600 px-5 py-3 text-sm font-semibold text-white hover:bg-rose-700 transition"><i class="fa-solid fa-xmark"></i> Huỷ lịch hẹn</button></form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-3xl border border-rose-200 bg-rose-50 p-4 text-rose-800 shadow-sm">{{ session('error') }}</div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.5fr_0.9fr]">
            <div class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Mã lịch hẹn</div>
                            <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $appointment->appointment_code }}</h2>
                        </div>
                        <span class="rounded-full px-4 py-2 text-sm font-semibold {{ $appointment->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($appointment->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700') }}">{{ $appointment->status_label }}</span>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-700">
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Ngày khám</div>
                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $appointment->appointment_date?->format('d/m/Y') ?? '—' }}</div>
                        </div>
                        <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-700">
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Giờ khám</div>
                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $appointment->appointment_time ? substr($appointment->appointment_time, 0, 5) : '—' }}</div>
                        </div>
                        <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-700">
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Nguồn đặt</div>
                            <div class="mt-2 text-lg font-semibold text-slate-900">{{ $appointment->source_label }}</div>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-700">
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Bác sĩ</div>
                            <div class="mt-2 font-semibold text-slate-900">{{ $appointment->doctorProfile->full_title ?? 'Chưa chỉ định' }}</div>
                        </div>
                        <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-700">
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Chuyên khoa</div>
                            <div class="mt-2 font-semibold text-slate-900">{{ $appointment->specialty->name ?? '—' }}</div>
                        </div>
                        <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-700">
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Phòng khám</div>
                            <div class="mt-2 font-semibold text-slate-900">{{ $appointment->room->name ?? '—' }}</div>
                        </div>
                    </div>

                    @if ($appointment->reason)
                        <div class="mt-6 rounded-3xl bg-amber-50 p-4 text-sm text-amber-900 border border-amber-100">
                            <div class="font-semibold text-slate-900">Lý do khám</div>
                            <p class="mt-2 text-slate-700">{{ $appointment->reason }}</p>
                        </div>
                    @endif
                </section>
            </div>

            <aside class="space-y-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">Thông tin bệnh nhân</h3>
                    <div class="mt-4 space-y-3 text-sm text-slate-700">
                        <div><span class="font-semibold text-slate-900">Tên:</span> {{ $appointment->patientProfile->full_name ?? '—' }}</div>
                        <div><span class="font-semibold text-slate-900">Năm sinh:</span> {{ $appointment->patientProfile->date_of_birth?->format('d/m/Y') ?? '—' }}</div>
                        <div><span class="font-semibold text-slate-900">Giới tính:</span> {{ $appointment->patientProfile->gender === 'M' ? 'Nam' : ($appointment->patientProfile->gender === 'F' ? 'Nữ' : 'Khác') }}</div>
                        <div><span class="font-semibold text-slate-900">SĐT:</span> {{ $appointment->patientProfile->phone ?? '—' }}</div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-lg font-semibold text-slate-900">Phí dịch vụ</h3>
                        @if (! $latestVisit)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Chưa cập nhật</span>
                        @endif
                    </div>

                    @if ($appointment->payments->where('status', 'completed')->isNotEmpty())
                        <div class="mt-5 space-y-4">
                            @foreach ($appointment->payments->where('status', 'completed') as $payment)
                                <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Mã hóa đơn</div>
                                        <div class="font-semibold text-slate-900">{{ $payment->transaction_code }}</div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Số tiền</div>
                                        <div class="text-lg font-bold text-slate-900">{{ number_format($payment->amount, 0, ',', '.') }}đ</div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Thời gian</div>
                                        <div class="text-sm text-slate-700">{{ $payment->paid_at ? $payment->paid_at->format('H:i d/m/Y') : '—' }}</div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Hình thức</div>
                                        <div class="text-sm font-semibold text-slate-900">{{ strtoupper($payment->method) }}</div>
                                    </div>
                                    <div class="pt-3 mt-3 border-t border-slate-100">
                                        <div class="text-xs uppercase tracking-[0.25em] text-slate-500 mb-2">Chi tiết dịch vụ</div>
                                        <ul class="text-sm text-slate-700 list-disc list-inside space-y-1">
                                            @foreach ($payment->clinicalVisits as $visit)
                                                <li>{{ $visit->is_origin ? 'Phí Khám Bệnh' : 'Dịch vụ Cận lâm sàng / Khác' }}</li>
                                            @endforeach
                                            @foreach ($payment->prescriptions as $prescription)
                                                <li>Phí thuốc theo đơn</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="pt-4 text-right">
                                        <a href="{{ route('patient.appointments.payments.print', ['appointment_id' => $appointment->id, 'payment_id' => $payment->id]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-full bg-slate-800 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-700 transition">
                                            <i class="fa-solid fa-download"></i> Tải hóa đơn (PDF)
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-4 rounded-3xl border border-slate-100 bg-slate-50 p-6 text-sm text-slate-600 text-center">Chưa có hóa đơn thanh toán nào.</div>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-layouts.patient-dashboard>
