<x-layouts.app>
    {{-- Hero Section --}}
    <section class="relative bg-slate-900 overflow-hidden">
        {{-- Background Image / Gradient Mock --}}
        <div class="absolute inset-0 bg-gradient-to-r from-secondary-dark via-secondary to-teal-600 opacity-90"></div>
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 flex flex-col md:flex-row items-center">
            <div class="md:w-3/5 text-white mb-10 md:mb-0 z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 border border-white/30 backdrop-blur-md mb-6 animate-fade-in-down">
                    <span class="flex w-2 h-2 rounded-full bg-amber-400 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider text-white">Tin tức nổi bật</span>
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold leading-tight mb-6 tracking-tight drop-shadow-lg">
                    BỆNH VIỆN CAREBOOK <br>
                    <span class="text-amber-400">CHĂM SÓC SỨC KHỎE TOÀN DIỆN</span>
                </h1>
                <p class="text-lg md:text-xl text-white/90 mb-8 max-w-xl font-medium">
                    Hệ thống đặt lịch khám bệnh trực tuyến nhanh chóng, tiện lợi, giúp bạn chủ động thời gian và lựa chọn bác sĩ chuyên khoa phù hợp nhất.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('patient.booking.step1') }}" class="px-8 py-4 rounded-full bg-amber-400 text-slate-900 font-bold hover:bg-amber-300 hover:-translate-y-1 transition-all duration-300 shadow-lg shadow-amber-400/30 flex items-center gap-2">
                        <i class="fa-regular fa-calendar-check"></i> Đặt lịch ngay
                    </a>
                    <a href="#" class="px-8 py-4 rounded-full bg-white/10 text-white font-bold border border-white/30 hover:bg-white/20 hover:-translate-y-1 transition-all duration-300 backdrop-blur-md flex items-center gap-2">
                        <i class="fa-solid fa-circle-info"></i> Tìm hiểu thêm
                    </a>
                </div>
            </div>

            {{-- Countdown Widget (Mocking the Bach Mai Banner) --}}
            <div class="md:w-2/5 flex justify-center md:justify-end z-10 w-full">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 p-6 rounded-3xl shadow-2xl text-center w-full max-w-md">
                    <h3 class="text-white font-bold text-xl mb-4">Thời gian còn lại đến sự kiện:</h3>
                    <div class="flex justify-center gap-3 md:gap-4 text-white">
                        <div class="flex flex-col items-center bg-black/30 rounded-2xl p-3 md:p-4 w-20 md:w-24 border border-white/10 shadow-inner">
                            <span class="text-3xl md:text-4xl font-extrabold text-amber-400">00</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider mt-1 opacity-80">Ngày</span>
                        </div>
                        <div class="flex flex-col items-center bg-black/30 rounded-2xl p-3 md:p-4 w-20 md:w-24 border border-white/10 shadow-inner">
                            <span class="text-3xl md:text-4xl font-extrabold">01</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider mt-1 opacity-80">Giờ</span>
                        </div>
                        <div class="flex flex-col items-center bg-black/30 rounded-2xl p-3 md:p-4 w-20 md:w-24 border border-white/10 shadow-inner">
                            <span class="text-3xl md:text-4xl font-extrabold">39</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider mt-1 opacity-80">Phút</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions (Floating Overlap) --}}
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mb-16 z-20">
            <div class="bg-white rounded-3xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] p-2 md:p-4 flex flex-col md:flex-row divide-y md:divide-y-0 md:divide-x divide-slate-100 border border-slate-100">

                <a href="#" class="flex-1 flex items-start gap-4 p-4 md:p-6 hover:bg-slate-50 transition-colors rounded-2xl group">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-lg group-hover:text-amber-600 transition-colors">Gọi tổng đài</h4>
                        <p class="text-sm text-slate-500 mt-1 line-clamp-2">Đặt lịch khám nhanh qua tổng đài 1900.888.866</p>
                    </div>
                </a>

                <a href="{{ route('patient.booking.step1') }}" class="flex-1 flex items-start gap-4 p-4 md:p-6 hover:bg-slate-50 transition-colors rounded-2xl group">
                    <div class="w-12 h-12 rounded-2xl bg-secondary/10 text-secondary flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-regular fa-calendar-plus"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-lg group-hover:text-secondary transition-colors">Đặt lịch khám</h4>
                        <p class="text-sm text-slate-500 mt-1 line-clamp-2">Đặt lịch khám online chủ động qua Website</p>
                    </div>
                </a>

                <a href="#" class="flex-1 flex items-start gap-4 p-4 md:p-6 hover:bg-slate-50 transition-colors rounded-2xl group hidden lg:flex">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-stethoscope"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-lg group-hover:text-blue-600 transition-colors">Hỏi đáp chuyên gia</h4>
                        <p class="text-sm text-slate-500 mt-1 line-clamp-2">Giải đáp các thắc mắc về sức khỏe của bạn</p>
                    </div>
                </a>

                <a href="#" class="flex-1 flex items-start gap-4 p-4 md:p-6 hover:bg-slate-50 transition-colors rounded-2xl group hidden xl:flex">
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-microscope"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-lg group-hover:text-purple-600 transition-colors">Kết quả xét nghiệm</h4>
                        <p class="text-sm text-slate-500 mt-1 line-clamp-2">Tra cứu trực tuyến kết quả xét nghiệm</p>
                    </div>
                </a>

            </div>
        </div>
    </section>

    {{-- Chuyên khoa nổi bật --}}
    <section class="pt-32 pb-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-end mb-10 border-b-2 border-blue-600 pb-4">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-blue-800 uppercase tracking-wide">Chuyên khoa nổi bật</h2>
                    <div class="w-16 h-1 bg-amber-500 mt-2"></div>
                </div>
                <a href="#" class="text-blue-600 font-medium hover:text-amber-500 transition-colors hidden sm:inline-flex items-center gap-1">
                    Xem tất cả <i class="fa-solid fa-angle-right"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                @php
                    function getSpecialtyIcon($name) {
                        $name = mb_strtolower($name, 'UTF-8');
                        if (str_contains($name, 'tim')) return 'fa-solid fa-heart-pulse';
                        if (str_contains($name, 'răng') || str_contains($name, 'hàm')) return 'fa-solid fa-tooth';
                        if (str_contains($name, 'thần kinh')) return 'fa-solid fa-brain';
                        if (str_contains($name, 'xương') || str_contains($name, 'khớp')) return 'fa-solid fa-bone';
                        if (str_contains($name, 'mắt')) return 'fa-solid fa-eye';
                        if (str_contains($name, 'nhi')) return 'fa-solid fa-baby';
                        if (str_contains($name, 'tiêu hóa') || str_contains($name, 'tiêu hoá')) return 'fa-solid fa-bacterium';
                        if (str_contains($name, 'da liễu')) return 'fa-solid fa-spa';
                        if (str_contains($name, 'tai mũi họng')) return 'fa-solid fa-ear-listen';
                        if (str_contains($name, 'sản') || str_contains($name, 'phụ khoa')) return 'fa-solid fa-person-pregnant';
                        return 'fa-solid fa-stethoscope';
                    }
                @endphp
                @foreach($specialties->take(8) as $specialty)
                <div class="group bg-slate-50 border border-slate-100 rounded-2xl p-4 md:p-6 text-center hover:bg-blue-50 hover:border-blue-200 transition-all shadow-sm hover:shadow-md">
                    <div class="w-16 h-16 mx-auto bg-white border border-slate-100 text-blue-600 rounded-full flex items-center justify-center text-3xl mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors shadow-sm">
                        @if($specialty->image_url)
                            <img src="{{ $specialty->image_url }}" alt="{{ $specialty->name }}" class="w-full h-full object-cover rounded-full">
                        @else
                            <i class="{{ getSpecialtyIcon($specialty->name) }}"></i>
                        @endif
                    </div>
                    <h3 class="font-bold text-slate-800 text-base md:text-lg group-hover:text-blue-800">{{ $specialty->name }}</h3>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Đội ngũ bác sĩ --}}
    <section class="py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-end mb-10 border-b-2 border-blue-600 pb-4">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-blue-800 uppercase tracking-wide">Đội ngũ chuyên gia, Bác sĩ</h2>
                    <div class="w-16 h-1 bg-amber-500 mt-2"></div>
                </div>
                <a href="{{ route('doctors.directory') }}" class="text-blue-600 font-medium hover:text-amber-500 transition-colors hidden sm:inline-flex items-center gap-1">
                    Xem tất cả <i class="fa-solid fa-angle-right"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($doctors->take(4) as $doctor)
                <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-lg transition-all group flex flex-col h-full">
                    <div class="bg-slate-100 pt-6 px-6 relative flex justify-center">
                        <img src="{{ $doctor->user->avatar_url ?? 'https://api.dicebear.com/7.x/initials/svg?seed='.urlencode($doctor->user->full_name).'&backgroundColor=e0e7ff,c7d2fe,a5b4fc,818cf8&textColor=3730a3' }}" alt="{{ $doctor->user->full_name }}" class="w-32 h-32 object-cover rounded-full border-4 border-white shadow-md z-10 group-hover:scale-105 transition-transform duration-500 bg-white">
                        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-blue-600/5"></div>
                    </div>
                    <div class="p-5 text-center flex-1 flex flex-col justify-center">
                        <h3 class="font-bold text-lg text-blue-900 mb-1 leading-tight">{{ $doctor->user->full_name }}</h3>
                        <p class="text-sm font-medium text-amber-600 mb-2">{{ $doctor->full_title }}</p>
                        <p class="text-sm text-slate-500 bg-slate-50 py-1 px-3 rounded-full inline-block mx-auto border border-slate-100">
                            {{ $doctor->primary_specialty?->name ?? 'Đa khoa' }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-8 sm:hidden">
                 <a href="{{ route('doctors.directory') }}" class="inline-flex px-6 py-2 rounded-xl border border-blue-600 text-blue-600 font-bold hover:bg-blue-50">Xem tất cả Bác sĩ</a>
            </div>
        </div>
    </section>

    {{-- News & Services Content --}}
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-slate-800">Tin tức & Sự kiện nổi bật</h2>
                <div class="w-24 h-1 bg-secondary mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts as $post)
                <div class="bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group border border-slate-100 flex flex-col">
                    <div class="h-48 bg-slate-200 relative overflow-hidden">
                        @if($post->thumbnail_url)
                            <img src="{{ $post->thumbnail_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent z-10"></div>
                        <div class="absolute bottom-4 left-4 z-20">
                            @php
                                $badgeColor = 'bg-secondary';
                                if($post->post_type == 'medical' || $post->post_type == 'Y tế') $badgeColor = 'bg-amber-500';
                                elseif($post->post_type == 'announcement' || $post->post_type == 'Thông báo') $badgeColor = 'bg-blue-500';
                            @endphp
                            <span class="px-3 py-1 {{ $badgeColor }} text-white text-xs font-bold rounded-lg">{{ ucfirst($post->post_type ?? 'Tin tức') }}</span>
                        </div>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <p class="text-sm text-slate-500 mb-2"><i class="fa-regular fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($post->published_at)->format('d \T\h\á\n\g m, Y') }}</p>
                        <h3 class="text-xl font-bold text-slate-800 mb-3 group-hover:text-secondary transition-colors line-clamp-2">{{ $post->title }}</h3>
                        <p class="text-slate-600 line-clamp-3 mb-4 flex-1">{{ $post->summary }}</p>
                        <a href="{{ route('posts.show', $post->slug) }}" class="text-secondary font-bold text-sm inline-flex items-center gap-1 group/link">
                            Xem chi tiết <i class="fa-solid fa-arrow-right group-hover/link:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-10">
                <a href="{{ route('posts.index') }}" class="inline-flex px-8 py-3 rounded-xl border-2 border-secondary text-secondary font-bold hover:bg-secondary hover:text-white transition-colors">Xem tất cả tin tức</a>
            </div>
        </div>
    </section>
</x-layouts.app>
