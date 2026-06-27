<x-layouts.patient-dashboard title="Lịch sử đặt khám" activeMenu="appointments">
    <div>
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Lịch hẹn của tôi</h1>
            <p class="text-slate-500 mt-2 text-sm md:text-base">Theo dõi và quản lý lịch khám bệnh</p>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-700 p-4 rounded-2xl mb-8 flex items-center gap-3 border border-emerald-100 shadow-sm animate-fade-in-down">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-check text-emerald-600"></i>
                </div>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Premium Tabs -->
        <div class="bg-slate-100 p-1.5 rounded-2xl flex mb-8 max-w-sm">
            <a href="{{ route('patient.appointments.index', ['tab' => 'upcoming']) }}" 
               class="flex-1 text-center py-2.5 px-4 rounded-xl font-semibold text-sm transition-all duration-300 {{ $tab == 'upcoming' ? 'bg-white text-primary shadow-sm ring-1 ring-slate-900/5' : 'text-slate-500 hover:text-slate-700' }}">
                Sắp tới
            </a>
            <a href="{{ route('patient.appointments.index', ['tab' => 'history']) }}" 
               class="flex-1 text-center py-2.5 px-4 rounded-xl font-semibold text-sm transition-all duration-300 {{ $tab == 'history' ? 'bg-white text-primary shadow-sm ring-1 ring-slate-900/5' : 'text-slate-500 hover:text-slate-700' }}">
                Đã qua / Hủy
            </a>
        </div>

        <!-- Appointment List -->
        <div class="space-y-5">
            @forelse($appointments as $appointment)
                <a href="{{ route('patient.appointments.show', $appointment->id) }}" class="group block bg-white rounded-3xl p-5 md:p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_40px_-12px_rgba(29,111,164,0.15)] relative border border-slate-100 shadow-sm overflow-hidden">
                    <!-- Hover subtle decor -->
                    <div class="absolute inset-y-0 left-0 w-1 bg-transparent group-hover:bg-primary transition-colors duration-300"></div>

                    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-4 relative z-10">
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold mb-3 border
                                {{ $appointment->status == 'pending' ? 'bg-amber-50 text-amber-700 border-amber-100' : '' }}
                                {{ $appointment->status == 'completed' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : '' }}
                                {{ $appointment->status == 'cancelled' ? 'bg-rose-50 text-rose-700 border-rose-100' : '' }}
                                {{ $appointment->status == 'examining' ? 'bg-blue-50 text-blue-700 border-blue-100' : '' }}
                                {{ !in_array($appointment->status, ['pending', 'completed', 'cancelled', 'examining']) ? 'bg-slate-50 text-slate-700 border-slate-100' : '' }}">
                                @if($appointment->status == 'pending') <i class="fa-solid fa-clock text-amber-500"></i>
                                @elseif($appointment->status == 'completed') <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                @elseif($appointment->status == 'cancelled') <i class="fa-solid fa-circle-xmark text-rose-500"></i>
                                @elseif($appointment->status == 'examining') <i class="fa-solid fa-stethoscope text-blue-500"></i>
                                @endif
                                {{ $appointment->status_label }}
                            </span>
                            <h3 class="font-bold text-slate-800 text-xl group-hover:text-primary transition-colors">{{ $appointment->patientProfile->full_name }}</h3>
                        </div>
                        
                        <!-- Lịch (Desktop thì float right, Mobile thì nằm dưới tên) -->
                        <div class="md:text-right flex items-center md:flex-col gap-3 md:gap-0 bg-slate-50 md:bg-transparent p-3 md:p-0 rounded-2xl">
                            <div class="w-12 h-12 md:w-auto md:h-auto rounded-xl bg-primary/10 md:bg-transparent flex items-center justify-center text-primary md:text-inherit shrink-0">
                                <i class="fa-regular fa-calendar md:hidden text-xl"></i>
                            </div>
                            <div>
                                <p class="text-2xl font-extrabold text-primary tracking-tight">{{ substr($appointment->appointment_time, 0, 5) }}</p>
                                <p class="text-sm font-medium text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 rounded-2xl p-4 flex flex-col md:flex-row flex-wrap gap-x-8 gap-y-3 text-sm text-slate-600 mt-2 relative z-10">
                        @if($appointment->doctorProfile)
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-white shadow-sm flex items-center justify-center shrink-0 border border-slate-100">
                                <i class="fa-solid fa-user-doctor text-primary/80 text-xs"></i>
                            </div>
                            <span class="uppercase font-semibold text-slate-700">{{ $appointment->doctorProfile->full_title }}</span>
                        </div>
                        @endif
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-white shadow-sm flex items-center justify-center shrink-0 border border-slate-100">
                                <i class="fa-solid fa-stethoscope text-primary/80 text-xs"></i>
                            </div>
                            <span class="font-medium">{{ $appointment->specialty?->name }}</span>
                        </div>
                        @if($appointment->room)
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-white shadow-sm flex items-center justify-center shrink-0 border border-slate-100">
                                <i class="fa-solid fa-location-dot text-primary/80 text-xs"></i>
                            </div>
                            <span class="font-medium">{{ $appointment->room->name }}</span>
                        </div>
                        @endif
                    </div>
                </a>
            @empty
                <div class="text-center py-16 px-4 bg-white border border-slate-100 shadow-sm rounded-3xl relative overflow-hidden">
                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-primary/5 via-transparent to-transparent"></div>
                    <div class="relative z-10 w-20 h-20 bg-gradient-to-br from-blue-50 to-primary/10 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner rotate-3 hover:rotate-0 transition-transform duration-500">
                        <i class="fa-regular fa-calendar-xmark text-primary text-3xl opacity-80"></i>
                    </div>
                    <h3 class="relative z-10 text-xl font-bold text-slate-800 mb-2">Chưa có lịch hẹn nào</h3>
                    <p class="relative z-10 text-slate-500 max-w-sm mx-auto">Bạn chưa có lịch hẹn nào ở mục này. Hãy đặt lịch ngay để được chăm sóc sức khỏe tốt nhất.</p>
                    @if($tab == 'upcoming')
                    <div class="mt-8 relative z-10">
                        <a href="{{ route('patient.booking.index') }}" 
                           class="inline-flex items-center gap-2 bg-primary hover:bg-primary-dark text-white px-8 py-3.5 rounded-2xl font-semibold transition-all hover:shadow-lg hover:shadow-primary/30 active:scale-95">
                            Đặt lịch ngay
                        </a>
                    </div>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $appointments->appends(['tab' => $tab])->links() }}
        </div>
    </div>
</x-layouts.patient>
