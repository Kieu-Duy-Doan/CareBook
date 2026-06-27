<x-layouts.patient-dashboard title="Chi tiết kết quả khám" activeMenu="records">
    <div>
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Chi tiết kết quả khám</h1>
                <p class="text-slate-500 mt-2 text-sm md:text-base">Hồ sơ bệnh án ngày {{ $record->created_at->format('d/m/Y') }}</p>
            </div>
            <a href="{{ route('patient.records.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold transition-colors">
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
                            <span class="font-bold text-slate-800">{{ $record->appointment->patientProfile->full_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Mã bệnh nhân:</span>
                            <span class="font-semibold text-slate-800">#{{ $record->appointment->patientProfile->id }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Thông tin khám bệnh</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Bác sĩ khám:</span>
                            <span class="font-bold text-primary">{{ $record->doctorProfile->full_title ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Chuyên khoa:</span>
                            <span class="font-semibold text-slate-800">{{ $record->appointment->specialty->name ?? 'Tổng quát' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div>
                    <h4 class="font-bold text-slate-800 text-lg mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-stethoscope text-primary"></i> Chẩn đoán
                    </h4>
                    <p class="text-slate-700 bg-slate-50 p-4 rounded-xl leading-relaxed">{{ $record->diagnosis ?? 'Không có thông tin chẩn đoán' }}</p>
                </div>
                
                @if($record->conclusion)
                <div>
                    <h4 class="font-bold text-slate-800 text-lg mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-check text-emerald-500"></i> Kết luận
                    </h4>
                    <p class="text-slate-700 bg-emerald-50 p-4 rounded-xl leading-relaxed border border-emerald-100">{{ $record->conclusion }}</p>
                </div>
                @endif
                
                @if($record->advice)
                <div>
                    <h4 class="font-bold text-slate-800 text-lg mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-comment-medical text-amber-500"></i> Lời khuyên của Bác sĩ
                    </h4>
                    <p class="text-slate-700 bg-amber-50 p-4 rounded-xl leading-relaxed border border-amber-100">{{ $record->advice }}</p>
                </div>
                @endif
                
                @if($record->followup_date)
                <div class="bg-primary/5 border border-primary/10 rounded-xl p-4 flex items-start gap-3">
                    <i class="fa-solid fa-calendar-plus text-primary mt-1"></i>
                    <div>
                        <h4 class="font-bold text-slate-800">Lịch tái khám</h4>
                        <p class="text-slate-700 mt-1">Bác sĩ yêu cầu tái khám vào ngày <strong class="text-primary">{{ \Carbon\Carbon::parse($record->followup_date)->format('d/m/Y') }}</strong></p>
                    </div>
                </div>
                @endif
                
                @if($record->prescription)
                <div class="mt-8 pt-8 border-t border-slate-100 text-center">
                    <a href="{{ route('patient.prescriptions.show', $record->prescription->id) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary/10 hover:bg-primary/20 text-primary rounded-xl font-bold transition-colors">
                        <i class="fa-solid fa-pills"></i> Xem đơn thuốc liên quan
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.patient-dashboard>
