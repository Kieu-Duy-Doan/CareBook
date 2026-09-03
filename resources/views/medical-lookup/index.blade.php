<x-layouts.app title="Tra cứu Hồ sơ Bệnh án" metaDescription="Tra cứu kết quả khám bệnh, chẩn đoán, đơn thuốc và cận lâm sàng trực tuyến tại Bệnh viện CareBook.">
    <div class="bg-gradient-to-b from-slate-50 via-white to-slate-50 py-10 md:py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                <a href="{{ route('home') }}" class="hover:text-primary transition-colors">Trang chủ</a>
                <i class="fa-solid fa-chevron-right text-[10px] text-slate-400"></i>
                <span class="text-slate-800 font-semibold">Tra cứu hồ sơ bệnh án</span>
            </nav>

            <!-- Header Section -->
            <div class="text-center max-w-2xl mx-auto mb-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-4">
                    <i class="fa-solid fa-file-medical"></i> Dịch vụ tiện ích bệnh nhân
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Tra cứu Hồ sơ Bệnh án
                </h1>
                <p class="text-slate-600 mt-3 text-base md:text-lg">
                    Nhập số điện thoại và họ tên để tra cứu nhanh kết quả khám bệnh, chẩn đoán, đơn thuốc và xét nghiệm mà không cần đăng nhập.
                </p>
            </div>

            <!-- Form Tra Cứu Card -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 p-6 md:p-10 mb-10">
                @if(session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg mt-0.5 shrink-0"></i>
                        <div class="text-sm font-medium leading-relaxed">{{ session('error') }}</div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 flex items-start gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg mt-0.5 shrink-0"></i>
                        <div class="text-sm font-medium leading-relaxed">{{ session('warning') }}</div>
                    </div>
                @endif

                <form action="{{ route('medical-lookup.search') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Số điện thoại -->
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-slate-700 mb-2">
                                Số điện thoại đăng ký khám <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-phone"></i>
                                </div>
                                <input type="tel" 
                                       name="phone" 
                                       id="phone" 
                                       value="{{ old('phone', $searchPhone ?? '') }}"
                                       placeholder="Ví dụ: 0912345678"
                                       required
                                       class="w-full pl-11 pr-4 py-3.5 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-slate-900 placeholder-slate-400 text-base transition-all @error('phone') border-rose-300 ring-1 ring-rose-300 @enderror">
                            </div>
                            @error('phone')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Họ và tên -->
                        <div>
                            <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-2">
                                Họ và tên bệnh nhân <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <input type="text" 
                                       name="full_name" 
                                       id="full_name" 
                                       value="{{ old('full_name', $searchName ?? '') }}"
                                       placeholder="Ví dụ: Nguyễn Văn A"
                                       required
                                       class="w-full pl-11 pr-4 py-3.5 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 text-slate-900 placeholder-slate-400 text-base transition-all @error('full_name') border-rose-300 ring-1 ring-rose-300 @enderror">
                            </div>
                            @error('full_name')
                                <p class="text-rose-600 text-xs mt-1.5 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Ghi chú bảo mật & Nút tra cứu -->
                    <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                            <span>Bảo mật y tế: Đường link chi tiết bệnh án có hiệu lực <strong>45 phút</strong> sau khi tra cứu.</span>
                        </div>
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/25 transition-all duration-200 text-base cursor-pointer">
                            <i class="fa-solid fa-magnifying-glass"></i> Tra cứu hồ sơ
                        </button>
                    </div>
                </form>
            </div>

            <!-- Kết quả tra cứu (Nếu có) -->
            @if(isset($searched) && $searched)
                <div class="space-y-6 animate-fade-in" id="search-results">
                    <!-- Patient Summary Card -->
                    <div class="bg-blue-50/80 border border-blue-100 rounded-3xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-2xl font-bold shadow-md shadow-blue-600/20">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">{{ $patientName }}</h3>
                                <p class="text-sm text-slate-600 mt-0.5">
                                    Số điện thoại: <span class="font-semibold text-slate-800">{{ $maskedPhone }}</span>
                                    <span class="mx-2 text-slate-300">|</span>
                                    Tìm thấy: <span class="font-bold text-blue-600">{{ $appointments->count() }}</span> lượt khám có kết quả
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 px-4 py-2 rounded-2xl bg-amber-50 border border-amber-200/80 text-amber-800 text-xs font-semibold self-start md:self-center">
                            <i class="fa-regular fa-clock text-amber-600"></i>
                            <span>Link xem chi tiết hết hạn sau 45 phút</span>
                        </div>
                    </div>

                    <!-- Danh sách lượt khám -->
                    <div class="space-y-4">
                        @foreach($appointments as $appointment)
                            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-all">
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                                    <div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-xs font-extrabold uppercase px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 tracking-wider">
                                                Mã: {{ $appointment->appointment_code }}
                                            </span>
                                            <span class="text-xs font-semibold text-slate-500">
                                                <i class="fa-regular fa-calendar text-slate-400 mr-1"></i>
                                                {{ $appointment->appointment_date?->format('d/m/Y') }}
                                                @if($appointment->appointment_time)
                                                    lúc {{ substr($appointment->appointment_time, 0, 5) }}
                                                @endif
                                            </span>
                                        </div>
                                        <h4 class="text-lg font-bold text-slate-900 mt-2">
                                            {{ $appointment->specialty->name ?? 'Khám tổng quát' }}
                                        </h4>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <a href="{{ $appointment->signed_url }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition shadow-sm hover:shadow">
                                            <i class="fa-solid fa-eye"></i> Xem chi tiết bệnh án
                                        </a>
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="text-xs uppercase text-slate-400 font-semibold tracking-wider block">Bác sĩ phụ trách</span>
                                        <span class="font-medium text-slate-800 mt-0.5 block">
                                            {{ $appointment->doctorProfile->full_title ?? 'Chưa cập nhật' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-xs uppercase text-slate-400 font-semibold tracking-wider block">Phòng khám</span>
                                        <span class="font-medium text-slate-800 mt-0.5 block">
                                            {{ $appointment->room->name ?? 'Phòng khám bệnh' }}
                                        </span>
                                    </div>
                                    <div class="sm:col-span-2 lg:col-span-1">
                                        <span class="text-xs uppercase text-slate-400 font-semibold tracking-wider block">Chẩn đoán kết luận</span>
                                        <span class="font-medium text-slate-800 mt-0.5 block truncate" title="{{ $appointment->medicalRecord->diagnosis ?? 'Đã hoàn tất' }}">
                                            {{ $appointment->medicalRecord->diagnosis ?? 'Đã hoàn tất kết luận' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <!-- Hướng dẫn 3 bước khi chưa tìm kiếm -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm text-center">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold mx-auto mb-4">
                            1
                        </div>
                        <h4 class="font-bold text-slate-800 text-base mb-1.5">Nhập thông tin</h4>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            Cung cấp chính xác Số điện thoại và Họ tên của người bệnh đã dùng khi đăng ký khám.
                        </p>
                    </div>

                    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm text-center">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold mx-auto mb-4">
                            2
                        </div>
                        <h4 class="font-bold text-slate-800 text-base mb-1.5">Hệ thống đối soát</h4>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            Hệ thống kiểm tra bảo mật và tổng hợp danh sách các lượt khám có kết luận y khoa.
                        </p>
                    </div>

                    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm text-center">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold mx-auto mb-4">
                            3
                        </div>
                        <h4 class="font-bold text-slate-800 text-base mb-1.5">Xem kết quả & Đơn thuốc</h4>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            Nhận link xem kết quả, đơn thuốc và tệp xét nghiệm đính kèm an toàn trong 45 phút.
                        </p>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-layouts.app>
