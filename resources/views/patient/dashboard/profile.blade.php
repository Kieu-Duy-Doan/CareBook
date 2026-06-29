<x-layouts.patient-dashboard title="Thông tin cá nhân" activeMenu="profiles">
    <div>
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Thông tin cá nhân</h1>
                <p class="text-slate-500 mt-2 text-sm md:text-base">Quản lý thông tin tài khoản và thông tin y tế cá nhân của bạn</p>
            </div>
            <a href="{{ $profile ? route('patient.profiles.edit', $profile->id) : route('patient.profiles.create', ['is_self' => 1]) }}" 
               class="group relative inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary text-white font-semibold rounded-2xl overflow-hidden transition-all hover:bg-primary-dark hover:shadow-lg hover:shadow-primary/30 active:scale-95">
                <i class="fa-regular fa-pen-to-square relative z-10"></i>
                <span class="hidden sm:inline relative z-10">Cập nhật thông tin</span>
            </a>
        </div>

        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 relative overflow-hidden mb-6">
            <div class="absolute top-0 right-0 w-64 h-64 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-primary/5 to-transparent rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

            <div class="flex flex-col md:flex-row gap-8 relative z-10">
                <!-- Avatar column -->
                <div class="flex flex-col items-center shrink-0">
                    <div class="w-32 h-32 rounded-3xl bg-gradient-to-br from-primary/10 to-blue-50 border-4 border-white shadow-lg flex items-center justify-center mb-4 overflow-hidden">
                        @if($user->avatar_url)
                            <img src="{{ asset('storage/' . $user->avatar_url) }}" alt="Avatar" class="w-full h-full object-cover">
                        @else
                            <span class="text-4xl font-black text-primary/40">{{ $user->avatar_initials }}</span>
                        @endif
                    </div>
                    <div class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Đang hoạt động
                    </div>
                </div>

                <!-- Info columns -->
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                    <!-- Account Info -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-user-shield text-primary"></i> Thông tin tài khoản
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-slate-500 font-medium mb-1">Họ và tên</p>
                                <p class="font-semibold text-slate-800 text-lg">{{ $user->full_name }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-slate-500 font-medium mb-1">Số điện thoại</p>
                                <p class="font-semibold text-slate-800">{{ $user->phone ?? 'Chưa cập nhật' }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-slate-500 font-medium mb-1">Email</p>
                                <p class="font-semibold text-slate-800">{{ $user->email ?? 'Chưa cập nhật' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-slate-500 font-medium mb-1">Căn cước công dân</p>
                                <p class="font-semibold text-slate-800">{{ $user->id_card ?? 'Chưa cập nhật' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Profile Info -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-notes-medical text-primary"></i> Thông tin y tế cá nhân
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-slate-500 font-medium mb-1">Ngày sinh</p>
                                    <p class="font-semibold text-slate-800">{{ $profile && $profile->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth)->format('d/m/Y') : 'Chưa cập nhật' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 font-medium mb-1">Giới tính</p>
                                    <p class="font-semibold text-slate-800">
                                        @if($profile && $profile->gender == 'male') Nam 
                                        @elseif($profile && $profile->gender == 'female') Nữ 
                                        @elseif($profile && $profile->gender == 'other') Khác
                                        @else Chưa cập nhật @endif
                                    </p>
                                </div>
                            </div>
                            
                            <div>
                                <p class="text-sm text-slate-500 font-medium mb-1">Địa chỉ</p>
                                <p class="font-semibold text-slate-800">{{ $profile->address ?? 'Chưa cập nhật' }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-slate-500 font-medium mb-1">Dân tộc</p>
                                    <p class="font-semibold text-slate-800">{{ $profile->ethnicity ?? 'Chưa cập nhật' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 font-medium mb-1">Nghề nghiệp</p>
                                    <p class="font-semibold text-slate-800">{{ $profile->occupation ?? 'Chưa cập nhật' }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-slate-500 font-medium mb-1">Mã BHYT</p>
                                    <p class="font-semibold text-slate-800">{{ $profile->insurance_code ?? 'Chưa cập nhật' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 font-medium mb-1">Hạn thẻ BHYT</p>
                                    <p class="font-semibold text-slate-800">{{ $profile && $profile->insurance_expiry ? \Carbon\Carbon::parse($profile->insurance_expiry)->format('d/m/Y') : 'Chưa cập nhật' }}</p>
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t border-slate-100">
                                <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                                    <i class="fa-solid fa-file-waveform text-rose-500"></i> Tiền sử y tế
                                </h4>
                                
                                <div>
                                    @if($profile && $profile->medical_history)
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            @php
                                                $historyArray = is_string($profile->medical_history) ? json_decode($profile->medical_history, true) : $profile->medical_history;
                                            @endphp
                                            @if(is_array($historyArray) && count($historyArray) > 0)
                                                @foreach($historyArray as $history)
                                                    <span class="px-3 py-1 bg-rose-50 text-rose-600 rounded-lg text-sm font-medium border border-rose-100">{{ $history }}</span>
                                                @endforeach
                                            @else
                                                <p class="font-semibold text-slate-800">Không có</p>
                                            @endif
                                        </div>
                                    @else
                                        <p class="font-semibold text-slate-800">Chưa cập nhật</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.patient-dashboard>
