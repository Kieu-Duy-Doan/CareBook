<x-layouts.patient-dashboard title="Chi tiết đơn thuốc" activeMenu="prescriptions">
    <div>
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Chi tiết đơn thuốc</h1>
                <p class="text-slate-500 mt-2 text-sm md:text-base">Kê ngày {{ $prescription->prescribed_date ? $prescription->prescribed_date->format('d/m/Y') : $prescription->created_at->format('d/m/Y') }}</p>
            </div>
            <a href="{{ route('patient.prescriptions.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold transition-colors">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Quay lại
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 pb-8 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Thông tin bệnh nhân</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Họ và tên:</span>
                            <span class="font-bold text-slate-800">{{ $prescription->medicalRecord->appointment->patientProfile->full_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Mã bệnh nhân:</span>
                            <span class="font-semibold text-slate-800">#{{ $prescription->medicalRecord->appointment->patientProfile->id }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Thông tin kê đơn</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Bác sĩ kê đơn:</span>
                            <span class="font-bold text-primary">{{ $prescription->medicalRecord->doctorProfile->full_title ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Chẩn đoán:</span>
                            <span class="font-semibold text-slate-800">{{ $prescription->diagnosis_note ?? $prescription->medicalRecord->diagnosis ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-slate-800 text-lg mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-pills text-primary"></i> Danh sách thuốc
                </h4>
                
                <div class="overflow-hidden rounded-2xl border border-slate-200">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Tên thuốc</th>
                                <th class="px-4 py-3 font-semibold">Số lượng</th>
                                <th class="px-4 py-3 font-semibold">Liều dùng / Cách dùng</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @if(is_array($prescription->items) && count($prescription->items) > 0)
                                @foreach($prescription->items as $item)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-4 font-semibold text-slate-800">{{ $item['name'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-4">{{ $item['quantity'] ?? 'N/A' }} {{ $item['unit'] ?? '' }}</td>
                                    <td class="px-4 py-4">{{ $item['usage'] ?? 'Theo chỉ định của bác sĩ' }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-slate-500">Không có danh sách thuốc cụ thể.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            @if($prescription->general_note)
            <div class="mt-8 bg-amber-50 border border-amber-100 rounded-xl p-4 flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation text-amber-500 mt-1"></i>
                <div>
                    <h4 class="font-bold text-amber-800">Lưu ý thêm từ bác sĩ</h4>
                    <p class="text-amber-700 mt-1">{{ $prescription->general_note }}</p>
                </div>
            </div>
            @endif
            
            <div class="mt-8 text-center">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-800 hover:bg-slate-900 text-white rounded-xl font-bold transition-colors">
                    <i class="fa-solid fa-print"></i> In đơn thuốc
                </button>
            </div>
        </div>
    </div>
</x-layouts.patient-dashboard>
