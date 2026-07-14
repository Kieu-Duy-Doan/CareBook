<x-layouts.receptionist title="Chi tiết Khách hàng — {{ $customer->full_name }}">
    <div class="space-y-6">
        <!-- Breadcrumbs & Actions -->
        <div class="flex items-center justify-between">
            <nav class="flex text-sm text-gray-500 font-medium">
                <a href="{{ route('receptionist.dashboard') }}" class="hover:text-gray-900 transition">Dashboard</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('receptionist.customers.index') }}" class="hover:text-gray-900 transition">Khách hàng</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">{{ $customer->full_name }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <a href="{{ route('receptionist.customers.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
                <a href="{{ route('receptionist.customers.edit', $customer->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition flex items-center gap-2">
                    <i class="fa-solid fa-pen"></i> Chỉnh sửa
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Thông tin khách hàng -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl font-bold shadow-sm">
                            {{ mb_substr($customer->full_name, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $customer->full_name }}</h2>
                            @if ($customer->is_active)
                                <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                                    <i class="fa-solid fa-circle-check"></i> Đang hoạt động
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700 border border-red-200">
                                    <i class="fa-solid fa-circle-xmark"></i> Đã khoá
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 shrink-0">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Số điện thoại</p>
                                <p class="font-medium text-gray-900">{{ $customer->phone }}</p>
                            </div>
                        </div>
                        
                        @if($customer->id_card)
                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 shrink-0">
                                <i class="fa-regular fa-id-card"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Số CCCD / CMND</p>
                                <p class="font-medium text-gray-900">{{ $customer->id_card }}</p>
                            </div>
                        </div>
                        @endif

                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 shrink-0">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Email</p>
                                <p class="font-medium text-gray-900">{{ $customer->email ?? 'Chưa cập nhật' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-sm">
                            <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 shrink-0">
                                <i class="fa-solid fa-calendar-plus"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Ngày tham gia</p>
                                <p class="font-medium text-gray-900">{{ $customer->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách hồ sơ -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                        <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                            <i class="fa-solid fa-folder-open text-blue-500"></i> Danh sách Hồ sơ Bệnh nhân
                        </h3>
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-md">
                            {{ $customer->patientProfiles->count() }} Hồ sơ
                        </span>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 gap-4">
                            @forelse($customer->patientProfiles as $profile)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition hover:shadow-sm">
                                    <div class="flex items-start justify-between">
                                        <div class="flex gap-4">
                                            <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center font-bold text-xl shrink-0">
                                                {{ mb_substr($profile->full_name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h4 class="font-bold text-lg text-gray-900">{{ $profile->full_name }}</h4>
                                                    @if($profile->is_self)
                                                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-[10px] font-medium rounded border border-purple-200">Bản thân</span>
                                                    @else
                                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] font-medium rounded border border-gray-200">Người thân</span>
                                                    @endif
                                                </div>
                                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600 mb-2">
                                                    <span><i class="fa-solid fa-hashtag text-gray-400 mr-1"></i>{{ $profile->patient_code ?? '—' }}</span>
                                                    <span><i class="fa-regular fa-calendar mr-1 text-gray-400"></i>{{ $profile->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth)->format('d/m/Y') : '—' }}</span>
                                                    <span><i class="fa-solid fa-venus-mars mr-1 text-gray-400"></i>@if($profile->gender=='male') Nam @elseif($profile->gender=='female') Nữ @else Khác @endif</span>
                                                </div>
                                                <div class="text-xs text-gray-500 flex items-center gap-2">
                                                    <span class="bg-gray-100 px-2 py-1 rounded">
                                                        <strong>{{ $profile->appointments->count() }}</strong> lịch khám
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="{{ route('receptionist.patients.show', $profile->id) }}" class="px-3 py-1.5 bg-white border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 transition flex items-center gap-1.5 whitespace-nowrap">
                                                Chi tiết <i class="fa-solid fa-arrow-right text-xs"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500">
                                    Không có hồ sơ nào.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Nhật ký hoạt động -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <i class="fa-solid fa-list-ul text-gray-500"></i> Nhật ký hoạt động gần đây
                        </h3>
                    </div>
                    <div class="p-0">
                        <ul class="divide-y divide-gray-100">
                            @forelse($logs as $log)
                            <li class="px-5 py-3 hover:bg-gray-50 transition flex items-start gap-3">
                                <div class="mt-1 w-2 h-2 rounded-full bg-blue-400 shrink-0"></div>
                                <div>
                                    <p class="text-sm text-gray-800">{{ $log->description }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
                                </div>
                            </li>
                            @empty
                            <li class="px-5 py-6 text-center text-sm text-gray-500">
                                Chưa có nhật ký hoạt động nào.
                            </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.receptionist>
