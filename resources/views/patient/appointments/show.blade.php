<x-layouts.patient-dashboard title="Chi tiết lịch hẹn" activeMenu="appointments">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Chi tiết lịch hẹn</h1>
                <p class="text-slate-500">Xem thông tin lịch khám, kết quả khám, đơn thuốc và phí dịch vụ.</p>
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

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-xl font-semibold text-slate-900">Kết quả khám</h2>
                        @if (! $appointment->medicalRecord)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Chưa có</span>
                        @endif
                    </div>

                    @if ($appointment->medicalRecord)
                        <div class="mt-5 grid gap-4">
                            <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-700">
                                <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Chẩn đoán</div>
                                <div class="mt-2 font-semibold text-slate-900">{{ $appointment->medicalRecord->diagnosis }}</div>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-700">
                                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Mã ICD-10</div>
                                    <div class="mt-2 font-semibold text-slate-900">{{ $appointment->medicalRecord->icd10_code ?? '—' }}</div>
                                </div>
                                <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-700">
                                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Ngày hẹn tái khám</div>
                                    <div class="mt-2 font-semibold text-slate-900">{{ $appointment->medicalRecord->followup_date?->format('d/m/Y') ?? 'Không hẹn' }}</div>
                                </div>
                            </div>
                            @if ($appointment->medicalRecord->conclusion)
                                <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-700">
                                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Kết luận</div>
                                    <p class="mt-2 text-slate-900">{{ $appointment->medicalRecord->conclusion }}</p>
                                </div>
                            @endif
                            @if ($appointment->medicalRecord->advice)
                                <div class="rounded-3xl bg-slate-50 p-4 text-sm text-slate-700">
                                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Dặn dò</div>
                                    <p class="mt-2 text-slate-900">{{ $appointment->medicalRecord->advice }}</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="mt-4 rounded-3xl border border-slate-100 bg-slate-50 p-6 text-sm text-slate-600 text-center">Chưa có kết quả khám cho lịch hẹn này.</div>
                    @endif
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-xl font-semibold text-slate-900">Đơn thuốc</h2>
                        @php
                            $prescription = optional($appointment->medicalRecord)->prescription;
                        @endphp
                        @if (! optional($prescription)->items)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Chưa có</span>
                        @endif
                    </div>

                    @php
                        $prescriptionItems = optional($prescription)->items;
                        if (is_string($prescriptionItems)) {
                            $prescriptionItems = json_decode($prescriptionItems, true);
                        }
                    @endphp

                    @if (!empty($prescriptionItems) && is_array($prescriptionItems))
                        <div class="mt-5 overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-500 uppercase tracking-[0.15em]">Thuốc</th>
                                        <th class="px-4 py-3 text-center font-semibold text-slate-500 uppercase tracking-[0.15em]">Số lượng</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-500 uppercase tracking-[0.15em]">Cách dùng</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @foreach ($prescriptionItems as $item)
                                        <tr>
                                            <td class="px-4 py-4 text-slate-900">{{ $item['medication_name'] ?? $item['name'] ?? '—' }}</td>
                                            <td class="px-4 py-4 text-center text-slate-700">{{ $item['quantity'] ?? '—' }}</td>
                                            <td class="px-4 py-4 text-slate-700">{{ $item['instructions'] ?? $item['usage'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($appointment->medicalRecord->prescription->general_note)
                            <div class="mt-4 rounded-3xl bg-amber-50 p-4 text-sm text-amber-900 border border-amber-100">
                                <div class="font-semibold text-slate-900">Ghi chú đơn thuốc</div>
                                <p class="mt-2 text-slate-700">{{ $appointment->medicalRecord->prescription->general_note }}</p>
                            </div>
                        @endif
                    @else
                        <div class="mt-4 rounded-3xl border border-slate-100 bg-slate-50 p-6 text-sm text-slate-600 text-center">Chưa có đơn thuốc cho lịch hẹn này.</div>
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

                    @if ($latestVisit)
                        <div class="mt-5 space-y-4 text-sm text-slate-700">
                            <div class="rounded-3xl bg-slate-50 p-4">
                                <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Tổng tiền</div>
                                <div class="mt-2 text-xl font-semibold text-slate-900">{{ number_format($latestVisit->payment_amount, 0, ',', '.') }}đ</div>
                            </div>
                            <div class="grid gap-3">
                                <div class="rounded-3xl bg-slate-50 p-4">
                                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Trạng thái thanh toán</div>
                                    <div class="mt-2 font-semibold text-slate-900">{{ $latestVisit->payment_status === 'paid' ? 'Đã thanh toán' : ($latestVisit->payment_status === 'pending' ? 'Chưa thanh toán' : 'Miễn phí') }}</div>
                                </div>
                                <div class="rounded-3xl bg-slate-50 p-4">
                                    <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Hình thức</div>
                                    <div class="mt-2 font-semibold text-slate-900">{{ $latestVisit->payment_method ? strtoupper($latestVisit->payment_method) : '—' }}</div>
                                </div>
                                @if ($latestVisit->collectedBy)
                                    <div class="rounded-3xl bg-slate-50 p-4">
                                        <div class="text-xs uppercase tracking-[0.25em] text-slate-500">Thu ngân</div>
                                        <div class="mt-2 font-semibold text-slate-900">{{ $latestVisit->collectedBy->full_name }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="mt-4 rounded-3xl border border-slate-100 bg-slate-50 p-6 text-sm text-slate-600 text-center">Thông tin phí dịch vụ sẽ được cập nhật khi có lượt khám.</div>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-layouts.patient-dashboard>
