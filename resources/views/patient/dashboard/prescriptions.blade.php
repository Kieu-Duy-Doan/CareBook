<x-layouts.patient-dashboard title="Đơn thuốc" activeMenu="prescriptions">
    <div>
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Đơn thuốc của bạn</h1>
            <p class="text-slate-500 mt-2 text-sm md:text-base">Tra cứu các đơn thuốc đã được bác sĩ kê</p>
        </div>

        @if($prescriptions->isEmpty())
            <div class="text-center py-16 px-4 bg-white border border-slate-100 shadow-sm rounded-3xl relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-primary/5 via-transparent to-transparent"></div>
                <div class="relative z-10 w-20 h-20 bg-gradient-to-br from-primary/5 to-primary/10 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner rotate-3 hover:rotate-0 transition-transform duration-500">
                    <i class="fa-solid fa-pills text-primary text-3xl opacity-80"></i>
                </div>
                <h3 class="relative z-10 text-xl font-bold text-slate-800 mb-2">Chưa có đơn thuốc</h3>
                <p class="relative z-10 text-slate-500 mb-8 max-w-sm mx-auto">Bạn chưa có đơn thuốc nào được kê trên hệ thống.</p>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($prescriptions as $prescription)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-primary/30 hover:shadow-md transition-all flex flex-col h-full">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-prescription text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-slate-800 text-lg line-clamp-1">{{ $prescription->diagnosis_note ?? 'Đơn thuốc khám bệnh' }}</h3>
                                <p class="text-sm text-slate-500 mt-1">Bác sĩ: <span class="font-semibold">{{ $prescription->medicalRecord->doctorProfile->full_title ?? 'N/A' }}</span></p>
                            </div>
                        </div>
                        
                        <div class="bg-slate-50 rounded-xl p-3 mb-4 text-sm flex-1">
                            <div class="flex justify-between mb-2">
                                <span class="text-slate-500">Bệnh nhân:</span>
                                <span class="font-semibold text-slate-700">{{ $prescription->medicalRecord->appointment->patientProfile->full_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Ngày kê đơn:</span>
                                <span class="font-semibold text-slate-700">{{ $prescription->prescribed_date ? $prescription->prescribed_date->format('d/m/Y') : $prescription->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-auto">
                            <span class="text-xs font-semibold bg-primary/10 text-primary px-2.5 py-1 rounded-full border border-primary/20">
                                {{ is_array($prescription->items) ? count($prescription->items) : 0 }} loại thuốc
                            </span>
                            <a href="{{ route('patient.prescriptions.show', $prescription->id) }}" class="text-primary font-semibold text-sm hover:underline flex items-center gap-1">
                                Xem đơn thuốc <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $prescriptions->links() }}
            </div>
        @endif
    </div>
</x-layouts.patient-dashboard>
