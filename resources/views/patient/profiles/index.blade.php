<x-layouts.patient-dashboard :title="$title ?? 'Hồ sơ bệnh nhân'" :activeMenu="$activeMenu ?? 'profiles'">
    <div>
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">{{ $title ?? 'Hồ sơ bệnh nhân' }}</h1>
                <p class="text-slate-500 mt-2 text-sm md:text-base">{{ ($activeMenu ?? 'profiles') === 'family' ? 'Quản lý thông tin y tế của người thân' : 'Quản lý thông tin y tế của bạn và gia đình' }}</p>
            </div>
            <a href="{{ route('patient.profiles.create') }}" 
               class="group relative inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary text-white font-semibold rounded-2xl overflow-hidden transition-all hover:bg-primary-dark hover:shadow-lg hover:shadow-primary/30 active:scale-95">
                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                <i class="fa-solid fa-plus relative z-10"></i>
                <span class="hidden sm:inline relative z-10">Thêm hồ sơ</span>
            </a>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-700 p-4 rounded-2xl mb-8 flex items-center gap-3 border border-emerald-100 shadow-sm animate-fade-in-down">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-check text-emerald-600"></i>
                </div>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 text-rose-700 p-4 rounded-2xl mb-8 flex items-center gap-3 border border-rose-100 shadow-sm animate-fade-in-down">
                <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-xmark text-rose-600"></i>
                </div>
                <p class="font-medium">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($profiles as $profile)
                <div class="group bg-white rounded-3xl p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_12px_40px_-12px_rgba(29,111,164,0.15)] flex flex-col h-full relative border border-slate-100 shadow-sm overflow-hidden">
                    <!-- Decor pattern -->
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-primary/5 to-transparent rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>

                    @if($profile->is_self)
                        <div class="absolute top-5 right-5 flex items-center gap-1.5 bg-gradient-to-r from-primary/10 to-primary/5 text-primary text-xs font-bold px-3 py-1.5 rounded-xl border border-primary/10">
                            <i class="fa-solid fa-shield-heart"></i> Chính chủ
                        </div>
                    @endif
                    
                    <div class="flex items-start gap-4 mb-6 relative z-10">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-50 to-primary/10 flex items-center justify-center flex-shrink-0 border border-white shadow-inner">
                            <i class="fa-solid {{ $profile->gender == 'F' ? 'fa-person-dress' : 'fa-user' }} text-primary text-2xl opacity-80 group-hover:scale-110 transition-transform duration-300"></i>
                        </div>
                        <div class="pt-1">
                            <h3 class="font-bold text-xl text-slate-800 group-hover:text-primary transition-colors">{{ $profile->full_name }}</h3>
                            <div class="flex items-center gap-2 mt-1.5 text-sm text-slate-500 font-medium">
                                <span>{{ $profile->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth)->format('d/m/Y') : 'Chưa có NS' }}</span>
                                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                <span>@if($profile->gender == 'male') Nam @elseif($profile->gender == 'female') Nữ @else Khác @endif</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 mb-8 flex-1 relative z-10 bg-slate-50/50 p-4 rounded-2xl border border-slate-50">
                        <div class="flex items-center text-sm group/item">
                            <div class="w-8 flex justify-center text-slate-400 group-hover/item:text-primary transition-colors">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <span class="text-slate-600 font-medium">{{ $profile->phone ?? 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="flex items-center text-sm group/item">
                            <div class="w-8 flex justify-center text-slate-400 group-hover/item:text-primary transition-colors">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <span class="text-slate-600 font-medium">{{ $profile->id_card ?? 'Chưa cập nhật CCCD' }}</span>
                        </div>
                        <div class="flex items-start text-sm group/item">
                            <div class="w-8 flex justify-center text-slate-400 group-hover/item:text-primary transition-colors mt-0.5">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <span class="text-slate-600 font-medium line-clamp-2 leading-relaxed">{{ $profile->address ?? 'Chưa cập nhật địa chỉ' }}</span>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-auto relative z-10">
                        <a href="{{ route('patient.profiles.edit', $profile->id) }}" 
                           class="flex-1 flex items-center justify-center gap-2 py-2.5 text-sm font-semibold text-slate-600 hover:text-primary bg-slate-50 hover:bg-primary/5 rounded-xl transition-all active:scale-95">
                            <i class="fa-regular fa-pen-to-square"></i> Sửa
                        </a>
                        
                        @if(!$profile->is_self)
                            <form action="{{ route('patient.profiles.destroy', $profile->id) }}" method="POST" class="flex-1"
                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa hồ sơ này không?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full flex items-center justify-center gap-2 py-2.5 text-sm font-semibold text-rose-500 bg-rose-50 hover:bg-rose-100 hover:text-rose-600 rounded-xl transition-all active:scale-95">
                                    <i class="fa-regular fa-trash-can"></i> Xoá
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($profiles->isEmpty())
            <div class="text-center py-16 px-4 bg-white border border-slate-100 shadow-sm rounded-3xl relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-primary/5 via-transparent to-transparent"></div>
                <div class="relative z-10 w-20 h-20 bg-gradient-to-br from-blue-50 to-primary/10 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner rotate-3 hover:rotate-0 transition-transform duration-500">
                    <i class="fa-solid fa-folder-open text-primary text-3xl opacity-80"></i>
                </div>
                <h3 class="relative z-10 text-xl font-bold text-slate-800 mb-2">{{ $emptyMessageTitle ?? 'Chưa có hồ sơ nào' }}</h3>
                <p class="relative z-10 text-slate-500 mb-8 max-w-sm mx-auto">{{ $emptyMessageDesc ?? 'Thêm hồ sơ của bạn hoặc người thân để bắt đầu đặt lịch khám nhanh chóng.' }}</p>
                <a href="{{ route('patient.profiles.create') }}" 
                   class="relative z-10 inline-flex items-center gap-2 bg-primary hover:bg-primary-dark text-white px-8 py-3.5 rounded-2xl font-semibold transition-all hover:shadow-lg hover:shadow-primary/30 active:scale-95">
                    <i class="fa-solid fa-plus"></i> Thêm hồ sơ đầu tiên
                </a>
            </div>
        @endif
    </div>
</x-layouts.patient>
