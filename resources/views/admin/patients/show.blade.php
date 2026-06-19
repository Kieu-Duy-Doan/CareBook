<x-layouts.admin title="Hồ sơ bệnh nhân — {{ $patient->full_name }}">
    <div class="space-y-6">
        <!-- Breadcrumbs & Actions -->
        <div class="flex items-center justify-between">
            <nav class="flex text-sm text-gray-500 font-medium">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition">Dashboard</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('admin.patients.index') }}" class="hover:text-gray-900 transition">Bệnh nhân</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">{{ $patient->full_name }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.patients.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
                <a href="{{ route('admin.patients.edit', $patient->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition flex items-center gap-2">
                    <i class="fa-solid fa-pen"></i> Chỉnh sửa
                </a>
            </div>
        </div>

        <!-- Session Alerts -->
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mb-4 flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
            <button @click="show=false" class="ml-auto"><i class="fa-solid fa-xmark"></i></button>
        </div>
        @endif

        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mb-4 flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
            <button @click="show=false" class="ml-auto"><i class="fa-solid fa-xmark"></i></button>
        </div>
        @endif

        <!-- Header Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex items-start gap-6">
            <div class="w-20 h-20 shrink-0 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-3xl font-bold shadow-sm">
                {{ $patient->avatar_initials ?? mb_substr($patient->full_name, 0, 1) }}
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $patient->full_name }}</h2>
                    <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                        Bệnh nhân
                    </span>
                    @if ($patient->is_active)
                        <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">
                            Đang hoạt động
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                            Đã khoá
                        </span>
                    @endif
                </div>
                
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600 mt-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-phone text-gray-400"></i>
                        <span>{{ $patient->phone }}</span>
                    </div>
                    @if($patient->email)
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-envelope text-gray-400"></i>
                        <span>{{ $patient->email }}</span>
                    </div>
                    @endif
                    @if($patient->id_card)
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-id-card text-gray-400"></i>
                        <span>{{ $patient->id_card }}</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-2 border-l border-gray-200 pl-6">
                        <i class="fa-solid fa-calendar-plus text-gray-400"></i>
                        <span>Tạo lúc: {{ $patient->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointment Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
                <p class="text-sm text-gray-500 font-medium mb-1">Tổng lịch hẹn</p>
                <p class="text-2xl font-bold text-gray-900">{{ $appointmentStats['total'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500 p-4">
                <p class="text-sm text-gray-500 font-medium mb-1">Đang chờ</p>
                <p class="text-2xl font-bold text-gray-900">{{ $appointmentStats['pending'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
                <p class="text-sm text-gray-500 font-medium mb-1">Hoàn thành</p>
                <p class="text-2xl font-bold text-gray-900">{{ $appointmentStats['completed'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-red-500 p-4">
                <p class="text-sm text-gray-500 font-medium mb-1">Đã huỷ</p>
                <p class="text-2xl font-bold text-gray-900">{{ $appointmentStats['cancelled'] }}</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Cột trái (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Thông tin tài khoản -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800"><i class="fa-solid fa-user-lock mr-2 text-green-600"></i>Thông tin tài khoản</h3>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">SĐT đăng nhập</span>
                                <span class="font-medium text-gray-900">{{ $patient->phone }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Username</span>
                                <span class="font-medium text-gray-900">{{ $patient->username ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">CCCD/CMND</span>
                                <span class="font-medium text-gray-900">{{ $patient->id_card ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Email</span>
                                <span class="font-medium text-gray-900">{{ $patient->email ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Ngày tạo</span>
                                <span class="font-medium text-gray-900">{{ $patient->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Đăng nhập cuối</span>
                                <span class="font-medium text-gray-900">
                                    {{ $patient->last_login_at ? \Carbon\Carbon::parse($patient->last_login_at)->format('d/m/Y H:i') : '—' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danh sách hồ sơ bệnh nhân -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800"><i class="fa-solid fa-notes-medical mr-2 text-green-600"></i>Danh sách hồ sơ bệnh nhân ({{ $patient->patientProfiles->count() }})</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        @forelse($patient->patientProfiles as $profile)
                            <div x-data="{ open: {{ $profile->is_self ? 'true' : 'false' }} }" class="border border-gray-200 rounded-lg overflow-hidden">
                                <div @click="open = !open" class="px-4 py-3 bg-gray-50 cursor-pointer flex items-center justify-between hover:bg-gray-100 transition">
                                    <div class="flex items-center gap-3">
                                        <h4 class="font-bold text-gray-800">{{ $profile->full_name }}</h4>
                                        @if($profile->is_self)
                                            <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded border border-green-200">Bản thân</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded border border-purple-200">Người thân</span>
                                        @endif
                                        <span class="text-sm text-gray-500">
                                            @if($profile->gender == 'male') Nam @elseif($profile->gender == 'female') Nữ @else Khác @endif
                                            @if($profile->date_of_birth)
                                                • {{ \Carbon\Carbon::parse($profile->date_of_birth)->age }} tuổi
                                            @endif
                                        </span>
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                </div>
                                <div x-show="open" x-collapse>
                                    <div class="p-4 bg-white border-t border-gray-200">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                                <span class="text-gray-500">Ngày sinh</span>
                                                <span class="font-medium">{{ $profile->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth)->format('d/m/Y') : '—' }}</span>
                                            </div>
                                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                                <span class="text-gray-500">Giới tính</span>
                                                <span class="font-medium">
                                                    @if($profile->gender == 'male') Nam @elseif($profile->gender == 'female') Nữ @else Khác @endif
                                                </span>
                                            </div>
                                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                                <span class="text-gray-500">CCCD hồ sơ</span>
                                                <span class="font-medium">{{ $profile->id_card ?? '—' }}</span>
                                            </div>
                                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                                <span class="text-gray-500">SĐT liên hệ riêng</span>
                                                <span class="font-medium">{{ $profile->phone ?? '—' }}</span>
                                            </div>
                                            <div class="flex justify-between border-b border-gray-50 pb-2 md:col-span-2">
                                                <span class="text-gray-500 min-w-24">Địa chỉ</span>
                                                <span class="font-medium text-right">{{ $profile->address ?? '—' }}</span>
                                            </div>
                                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                                <span class="text-gray-500">Nghề nghiệp</span>
                                                <span class="font-medium">{{ $profile->occupation ?? '—' }}</span>
                                            </div>
                                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                                <span class="text-gray-500">Dân tộc</span>
                                                <span class="font-medium">{{ $profile->ethnicity ?? '—' }}</span>
                                            </div>
                                            
                                            <!-- BHYT -->
                                            <div class="md:col-span-2 bg-gray-50 rounded-lg p-3 mt-2">
                                                <div class="font-medium text-gray-700 mb-2 border-b border-gray-200 pb-1">Bảo hiểm y tế</div>
                                                @if($profile->insurance_code)
                                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                        <div>
                                                            <div class="text-xs text-gray-500">Mã thẻ</div>
                                                            <div class="font-mono">{{ $profile->insurance_code }}</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-500">Nơi ĐK KCB</div>
                                                            <div>{{ $profile->insurance_place ?? '—' }}</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-500">Hạn thẻ</div>
                                                            <div>
                                                                @if($profile->insurance_expiry)
                                                                    @php $expiry = \Carbon\Carbon::parse($profile->insurance_expiry); @endphp
                                                                    @if($expiry->isPast())
                                                                        <span class="text-red-600 font-medium">{{ $expiry->format('d/m/Y') }} (Đã hết hạn)</span>
                                                                    @else
                                                                        <span class="text-green-600 font-medium">{{ $expiry->format('d/m/Y') }}</span>
                                                                    @endif
                                                                @else
                                                                    —
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="text-gray-500 text-sm">Không có thông tin thẻ BHYT</div>
                                                @endif
                                            </div>

                                            <!-- Y tế -->
                                            <div class="md:col-span-2 mt-2">
                                                <div class="text-xs text-gray-500 mb-1">Tiền sử bệnh lý</div>
                                                @if($profile->medical_history && is_array($profile->medical_history) && count($profile->medical_history) > 0)
                                                    <div class="flex flex-wrap gap-1.5">
                                                        @foreach($profile->medical_history as $hist)
                                                            <span class="px-2 py-0.5 bg-gray-100 border border-gray-200 rounded text-xs">{{ $hist }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="font-medium">Không có</span>
                                                @endif
                                            </div>
                                            <div class="md:col-span-2 mt-1">
                                                <div class="text-xs text-gray-500 mb-1">Ghi chú triệu chứng thường gặp</div>
                                                <div class="font-medium bg-yellow-50 p-2 rounded text-yellow-800 text-sm border border-yellow-100">
                                                    {!! nl2br(e($profile->symptom_notes ?? 'Không có ghi chú')) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-gray-500">Chưa có hồ sơ nào.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Lịch hẹn gần nhất -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800"><i class="fa-solid fa-calendar-check mr-2 text-green-600"></i>Lịch hẹn gần nhất</h3>
                    </div>
                    <div>
                        @if($recentAppointments->isEmpty())
                            <div class="py-8 text-center text-gray-500">
                                <i class="fa-regular fa-calendar-xmark text-4xl text-gray-300 mb-3"></i>
                                <p>Chưa có lịch hẹn nào.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b">
                                        <tr>
                                            <th class="px-4 py-2">Mã LH</th>
                                            <th class="px-4 py-2">Bác sĩ & Chuyên khoa</th>
                                            <th class="px-4 py-2">Ngày giờ</th>
                                            <th class="px-4 py-2 text-center">Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($recentAppointments as $apt)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 font-mono font-medium text-gray-800">
                                                    <a href="{{ route('admin.appointments.show', $apt->id) }}" class="hover:text-blue-600">#{{ $apt->appointment_code }}</a>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="font-medium">{{ $apt->doctor?->user?->full_name ?? '—' }}</div>
                                                    <div class="text-xs text-gray-500">{{ $apt->specialty?->name ?? '—' }}</div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="font-medium text-blue-600">{{ \Carbon\Carbon::parse($apt->appointment_date)->format('d/m/Y') }}</div>
                                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    @if($apt->status == 'pending')
                                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded border border-yellow-200">Đang chờ</span>
                                                    @elseif($apt->status == 'completed')
                                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded border border-green-200">Hoàn thành</span>
                                                    @elseif($apt->status == 'cancelled')
                                                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded border border-red-200">Đã huỷ</span>
                                                    @else
                                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded border border-gray-200">{{ $apt->status }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Cột phải (1/3) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Card Thao tác -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b">Thao tác</h3>
                    
                    <div class="mb-5 flex flex-col items-center justify-center p-4 rounded-lg bg-gray-50 border border-gray-100">
                        <p class="text-sm text-gray-500 mb-2">Trạng thái tài khoản</p>
                        @if ($patient->is_active)
                            <span class="px-4 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                <i class="fa-solid fa-circle-check mr-1"></i> Đang hoạt động
                            </span>
                        @else
                            <span class="px-4 py-1.5 rounded-full text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                                <i class="fa-solid fa-circle-xmark mr-1"></i> Đã khoá
                            </span>
                        @endif
                    </div>

                    <div class="space-y-3">
                        <a href="{{ route('admin.patients.edit', $patient->id) }}" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            <i class="fa-solid fa-pen"></i> Chỉnh sửa thông tin
                        </a>
                        
                        <form action="{{ route('admin.patients.toggle-active', $patient->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn {{ $patient->is_active ? 'khoá' : 'mở khoá' }} tài khoản bệnh nhân này?');">
                            @csrf
                            @method('PATCH')
                            @if($patient->is_active)
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-red-200 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 hover:border-red-300 transition font-medium">
                                    <i class="fa-solid fa-lock"></i> Khoá tài khoản
                                </button>
                                <p class="text-xs text-center text-gray-500 mt-2">Khoá tài khoản sẽ ngăn bệnh nhân đăng nhập vào hệ thống và ứng dụng.</p>
                            @else
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-blue-200 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 hover:border-blue-300 transition font-medium">
                                    <i class="fa-solid fa-lock-open"></i> Mở khoá tài khoản
                                </button>
                            @endif
                        </form>
                    </div>
                </div>

                <!-- Hoạt động gần đây -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800"><i class="fa-solid fa-clock-rotate-left mr-2 text-green-600"></i>Hoạt động hệ thống</h3>
                    </div>
                    <div class="p-5">
                        @if($logs->isEmpty())
                            <div class="py-4 text-center text-gray-500">
                                <i class="fa-regular fa-clock text-3xl text-gray-300 mb-2"></i>
                                <p class="text-sm">Chưa có hoạt động nào.</p>
                            </div>
                        @else
                            <div class="relative border-l-2 border-gray-100 ml-3 space-y-5">
                                @foreach($logs as $log)
                                    <div class="relative pl-5">
                                        <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-white border-2 border-green-500 flex items-center justify-center">
                                            <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                        </div>
                                        <p class="text-sm font-medium text-gray-900 leading-snug">{{ $log->description ?? $log->action }}</p>
                                        <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                                            <span>{{ $log->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
