<x-layouts.patient-dashboard title="Chi tiết lịch hẹn" activeMenu="appointments">
    <div>
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('patient.appointments.index') }}" class="w-12 h-12 rounded-full bg-white shadow-sm border border-slate-100 flex items-center justify-center text-slate-500 hover:text-primary hover:bg-slate-50 transition-all active:scale-95 group">
                <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight">Chi tiết lịch hẹn</h1>
                <p class="text-sm md:text-base text-slate-500 mt-1">Mã lịch hẹn: <span class="font-bold text-slate-700">{{ $appointment->appointment_code }}</span></p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-700 p-4 rounded-2xl mb-8 flex items-center gap-3 border border-emerald-100 shadow-sm animate-fade-in-down">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-check text-emerald-600"></i>
                </div>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-3xl overflow-hidden mb-8 shadow-sm border border-slate-100">
            <div class="p-8 text-center relative overflow-hidden bg-slate-50/50 border-b border-slate-100">
                <!-- Decor subtle pattern -->
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-primary/5 via-transparent to-transparent"></div>
                
                <div class="relative z-10">
                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-bold mb-4 border shadow-sm
                        {{ $appointment->status == 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200 shadow-amber-100/50' : '' }}
                        {{ $appointment->status == 'completed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 shadow-emerald-100/50' : '' }}
                        {{ $appointment->status == 'cancelled' ? 'bg-rose-50 text-rose-700 border-rose-200 shadow-rose-100/50' : '' }}
                        {{ $appointment->status == 'examining' ? 'bg-blue-50 text-blue-700 border-blue-200 shadow-blue-100/50' : '' }}
                        {{ !in_array($appointment->status, ['pending', 'completed', 'cancelled', 'examining']) ? 'bg-slate-50 text-slate-700 border-slate-200' : '' }}">
                        @if($appointment->status == 'pending') <i class="fa-solid fa-clock"></i>
                        @elseif($appointment->status == 'completed') <i class="fa-solid fa-circle-check"></i>
                        @elseif($appointment->status == 'cancelled') <i class="fa-solid fa-circle-xmark"></i>
                        @elseif($appointment->status == 'examining') <i class="fa-solid fa-stethoscope"></i>
                        @endif
                        {{ $appointment->status_label }}
                    </span>
                    
                    <h2 class="text-5xl font-extrabold text-primary tracking-tight">{{ substr($appointment->appointment_time, 0, 5) }}</h2>
                    <p class="text-slate-500 font-semibold mt-2 text-lg">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, d/m/Y') }}</p>
                </div>
            </div>

            <div class="p-4 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Cột trái -->
                <div class="space-y-6">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Bệnh nhân</p>
                        <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-user text-primary/60"></i>
                            </div>
                            <span class="font-bold text-slate-800 text-lg">{{ $appointment->patientProfile->full_name }}</span>
                        </div>
                    </div>

                    @if($appointment->doctorProfile)
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Bác sĩ phụ trách</p>
                        <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-user-doctor text-primary/60"></i>
                            </div>
                            <span class="font-bold text-slate-800 uppercase">{{ $appointment->doctorProfile->full_title }}</span>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Cột phải -->
                <div class="space-y-6">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Chuyên khoa</p>
                        <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-stethoscope text-primary/60"></i>
                            </div>
                            <span class="font-semibold text-slate-800">{{ $appointment->specialty?->name }}</span>
                        </div>
                    </div>
                    
                    @if($appointment->room)
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Phòng khám</p>
                        <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                            <div class="w-10 h-10 rounded-full bg-white shadow-sm flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-location-dot text-primary/60"></i>
                            </div>
                            <span class="font-semibold text-slate-800">{{ $appointment->room->name }}</span>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Lý do khám full width -->
                <div class="md:col-span-2 pt-2">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Lý do khám bệnh</p>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 min-h-[80px]">
                        <span class="font-medium text-slate-700 leading-relaxed {{ !$appointment->reason ? 'italic text-slate-400' : '' }}">
                            {{ $appointment->reason ?: 'Không có ghi chú' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        @if($appointment->status === 'pending')
            <div x-data="{ openCancel: false }" class="mt-8 flex justify-center">
                <button @click="openCancel = true" class="group flex items-center justify-center gap-2 px-8 py-3.5 text-rose-500 font-semibold border-2 border-rose-100 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 rounded-2xl transition-all active:scale-95">
                    <i class="fa-solid fa-xmark group-hover:rotate-90 transition-transform duration-300"></i> Hủy lịch khám
                </button>

                <!-- Cancel Modal (Glassmorphism + Premium Feel) -->
                <div x-show="openCancel" x-cloak 
                     class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    
                    <!-- Backdrop blur -->
                    <div x-show="openCancel" x-transition.opacity duration.300ms 
                         class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="openCancel = false"></div>

                    <!-- Modal Content -->
                    <div x-show="openCancel" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                         class="relative bg-white rounded-3xl w-full max-w-md p-6 sm:p-8 shadow-2xl border border-white/20">
                        
                        <div class="w-16 h-16 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-6">
                            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-2xl"></i>
                        </div>
                        
                        <h3 class="text-2xl font-extrabold text-slate-800 mb-2 text-center">Xác nhận hủy lịch</h3>
                        <p class="text-slate-500 mb-6 text-center leading-relaxed">Bạn có chắc chắn muốn hủy lịch hẹn này? Sau khi hủy, suất khám sẽ được giải phóng cho người khác.</p>
                        
                        <form action="{{ route('patient.appointments.cancel', $appointment->id) }}" method="POST">
                            @csrf
                            <textarea name="reason" rows="3" 
                                      class="w-full border-slate-200 rounded-2xl p-4 text-sm focus:ring-rose-500/20 focus:border-rose-500 mb-6 bg-slate-50 placeholder:text-slate-400 transition-colors" 
                                      placeholder="Vui lòng nhập lý do hủy lịch (không bắt buộc)"></textarea>
                            
                            <div class="flex flex-col-reverse sm:flex-row gap-3">
                                <button type="button" @click="openCancel = false" 
                                        class="flex-1 py-3.5 bg-slate-100 text-slate-700 font-bold rounded-2xl hover:bg-slate-200 transition-colors active:scale-95">
                                    Giữ lại lịch
                                </button>
                                <button type="submit" 
                                        class="flex-1 py-3.5 bg-rose-500 text-white font-bold rounded-2xl hover:bg-rose-600 shadow-lg shadow-rose-500/30 transition-all active:scale-95">
                                    Xác nhận hủy
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-layouts.patient>
