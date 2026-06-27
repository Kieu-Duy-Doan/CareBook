<x-layouts.patient-dashboard title="Kết quả khám bệnh" activeMenu="records">
    <div>
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Kết quả khám bệnh</h1>
            <p class="text-slate-500 mt-2 text-sm md:text-base">Tra cứu các phiếu kết quả xét nghiệm, chẩn đoán</p>
        </div>

        @if($records->isEmpty())
            <div class="text-center py-16 px-4 bg-white border border-slate-100 shadow-sm rounded-3xl relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-primary/5 via-transparent to-transparent"></div>
                <div class="relative z-10 w-20 h-20 bg-gradient-to-br from-blue-50 to-primary/10 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner rotate-3 hover:rotate-0 transition-transform duration-500">
                    <i class="fa-solid fa-file-medical text-primary text-3xl opacity-80"></i>
                </div>
                <h3 class="relative z-10 text-xl font-bold text-slate-800 mb-2">Chưa có kết quả khám</h3>
                <p class="relative z-10 text-slate-500 mb-8 max-w-sm mx-auto">Bạn chưa có hồ sơ bệnh án hoặc kết quả khám nào trên hệ thống.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($records as $record)
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-primary/30 hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary/10 text-primary rounded-xl flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-notes-medical text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg">Khám {{ $record->appointment->specialty->name ?? 'Tổng quát' }}</h3>
                                <p class="text-sm text-slate-500 mt-1">Bác sĩ: <span class="font-semibold">{{ $record->doctorProfile->full_title ?? 'N/A' }}</span></p>
                                <p class="text-sm text-slate-500">Bệnh nhân: <span class="font-semibold">{{ $record->appointment->patientProfile->full_name }}</span></p>
                            </div>
                        </div>
                        <div class="flex flex-col md:items-end gap-2 shrink-0">
                            <span class="text-sm font-medium bg-slate-100 text-slate-600 px-3 py-1 rounded-lg">
                                <i class="fa-regular fa-calendar mr-1"></i> {{ $record->created_at->format('d/m/Y') }}
                            </span>
                            <a href="{{ route('patient.records.show', $record->id) }}" class="text-primary font-semibold text-sm hover:underline mt-1">
                                Xem chi tiết <i class="fa-solid fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</x-layouts.patient-dashboard>
