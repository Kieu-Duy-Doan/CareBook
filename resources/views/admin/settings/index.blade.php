<x-layouts.admin title="Cài đặt hệ thống">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Cài đặt hệ thống</h2>
        <p class="text-gray-500 mt-1">Cấu hình các thông số cốt lõi và quy định hoạt động của Phòng khám.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 text-green-800 rounded-lg p-4 flex items-center border border-green-200">
            <i class="fa-solid fa-circle-check text-green-500 mr-3 text-lg"></i>
            <span class="flex-1 text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-50 text-red-800 rounded-lg p-4 flex items-center border border-red-200">
            <i class="fa-solid fa-circle-xmark text-red-500 mr-3 text-lg"></i>
            <span class="flex-1 text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 text-red-800 rounded-lg p-4 border border-red-200">
            <div class="flex items-center mb-2">
                <i class="fa-solid fa-triangle-exclamation text-red-500 mr-3 text-lg"></i>
                <span class="text-sm font-bold">Vui lòng kiểm tra lại thông tin:</span>
            </div>
            <ul class="list-disc list-inside text-sm ml-7">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="{ activeTab: '{{ request()->hasAny(['module', 'action_search', 'user_id', 'date_from', 'date_to', 'page', 'active_tab']) ? 'logs' : 'general' }}' }" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Thanh menu chọn qua lại giữa các tab cài đặt -->
        <div class="flex border-b border-gray-100 overflow-x-auto hide-scrollbar">
            <button @click="activeTab = 'general'"
                class="px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-medium transition-colors border-b-2 whitespace-nowrap"
                :class="activeTab === 'general' ? 'border-blue-600 text-blue-600' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'">
                <i class="fa-solid fa-house-chimney-medical mr-1 sm:mr-2"></i> Thông tin phòng khám
            </button>


            <button @click="activeTab = 'system'"
                class="px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-medium transition-colors border-b-2 whitespace-nowrap"
                :class="activeTab === 'system' ? 'border-blue-600 text-blue-600' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'">
                <i class="fa-solid fa-server mr-1 sm:mr-2"></i> Hệ thống chung
            </button>
            <button @click="activeTab = 'logs'"
                class="px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-medium transition-colors border-b-2 whitespace-nowrap"
                :class="activeTab === 'logs' ? 'border-blue-600 text-blue-600' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50'">
                <i class="fa-solid fa-shield-halved mr-1 sm:mr-2"></i> Nhật ký hệ thống
            </button>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data"
            :class="activeTab === 'logs' ? '' : 'p-4 sm:p-6'">
            @csrf
            @method('PUT')


            <!-- Tab 1: Cài đặt thông tin chung của phòng khám (Tên, địa chỉ, logo...) -->
            <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                style="display: none;">

                <!-- Logo -->
                <div class="mb-6 sm:mb-8 pb-6 border-b border-gray-100">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Logo Phòng khám</label>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6" x-data="{ logoRemoved: false }">
                        @if (isset($settings['logo']) && $settings['logo'] !== '')
                            <div x-show="!logoRemoved"
                                class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-xl border border-gray-200 overflow-hidden bg-gray-50 flex-shrink-0 flex items-center justify-center p-2 shadow-sm group">
                                <img src="{{ asset('storage/' . $settings['logo']) }}" alt="Logo"
                                    class="max-w-full max-h-full object-contain">
                                <!-- Nút xóa ảnh hiện ra khi hover -->
                                <button type="button" @click="logoRemoved = true" class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" title="Xóa ảnh này">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                            </div>
                            <div x-show="logoRemoved" style="display: none;"
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 flex-shrink-0 flex items-center justify-center text-gray-400">
                                <i class="fa-solid fa-image text-2xl sm:text-3xl"></i>
                            </div>
                        @else
                            <div
                                class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 flex-shrink-0 flex items-center justify-center text-gray-400">
                                <i class="fa-solid fa-image text-2xl sm:text-3xl"></i>
                            </div>
                        @endif
                        <div class="flex-1 w-full">
                            <input type="hidden" name="remove_logo" :value="logoRemoved ? '1' : '0'">
                            <input type="file" name="logo" accept="image/*" @change="logoRemoved = false"
                                class="block w-full text-xs sm:text-sm text-gray-500 file:mr-4 file:py-2 file:sm:py-2.5 file:px-4 file:sm:px-5 file:rounded-lg file:border-0 file:text-xs file:sm:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer transition-colors @error('logo') ring-2 ring-red-500 @enderror">
                            @error('logo')
                                <p class="text-xs text-red-600 mt-2 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-2.5 leading-relaxed">Định dạng khuyên dùng: <strong
                                    class="text-gray-700">PNG nền trong suốt</strong>.<br>Kích thước tỷ lệ 1:1 hoặc 3:1.
                                Dung lượng tối đa 2MB.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                    <!-- Cột 1 -->
                    <div class="space-y-4 sm:space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tên Phòng khám <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="settings[clinic_name]"
                                value="{{ $settings['clinic_name'] ?? 'Carebook Clinic' }}" required
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <input type="hidden" name="settings_types[clinic_name]" value="string">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Liên hệ</label>
                            <input type="email" name="settings[contact_email]"
                                value="{{ $settings['contact_email'] ?? '' }}"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                                placeholder="contact@carebook.com">
                            <input type="hidden" name="settings_types[contact_email]" value="string">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại Hotline</label>
                            <input type="text" name="settings[contact_phone]"
                                value="{{ $settings['contact_phone'] ?? '' }}"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                                placeholder="1900 1234">
                            <input type="hidden" name="settings_types[contact_phone]" value="string">
                        </div>
                    </div>

                    <!-- cột 2 -->
                    <div class="space-y-4 sm:space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ Cơ sở</label>
                            <textarea name="settings[address]" rows="3"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                                placeholder="Ví dụ: Số 123 Đường ABC, Quận XYZ, TP.HCM">{{ $settings['address'] ?? '' }}</textarea>
                            <input type="hidden" name="settings_types[address]" value="string">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Khung giờ hoạt động hiển
                                thị</label>
                            <input type="text" name="settings[working_hours]"
                                value="{{ $settings['working_hours'] ?? 'T2 - T6: 08:00 - 17:00' }}"
                                class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                                placeholder="T2 - T6: 08:00 - 17:00">
                            <input type="hidden" name="settings_types[working_hours]" value="string">
                        </div>
                    </div>
                </div>

                <!-- social section merged -->
                <div class="mt-6 sm:mt-8 pt-6 border-t border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Mạng xã hội & Tích hợp</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Đường dẫn Fanpage
                                    Facebook</label>
                                <input type="url" name="settings[facebook_url]"
                                    value="{{ $settings['facebook_url'] ?? '' }}"
                                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="https://facebook.com/carebook">
                                <input type="hidden" name="settings_types[facebook_url]" value="string">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Zalo ZOA / Nhóm
                                    Zalo</label>
                                <input type="url" name="settings[zalo_url]"
                                    value="{{ $settings['zalo_url'] ?? '' }}"
                                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="https://zalo.me/...">
                                <input type="hidden" name="settings_types[zalo_url]" value="string">
                            </div>
                        </div>
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Google Maps Embed
                                    Link</label>
                                <textarea name="settings[google_maps_iframe]" rows="3"
                                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder='<iframe src="..."></iframe>'>{{ $settings['google_maps_iframe'] ?? '' }}</textarea>
                                <input type="hidden" name="settings_types[google_maps_iframe]" value="string">
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Tab 3: Cài đặt hệ thống (Bật/Tắt chế độ bảo trì web) -->
            <div x-show="activeTab === 'system'" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                style="display: none;">
                <div class="space-y-4 sm:space-y-6">
                    <div class="bg-red-50 p-4 sm:p-5 rounded-xl border border-red-200">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-red-900">Chế độ Bảo trì (Maintenance Mode)</h3>
                                    <p class="text-xs text-red-700">Bật chế độ bảo trì, chặn truy cập web (trừ admin).
                                    </p>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="settings[maintenance_mode]" value="false">
                                    <input type="checkbox" name="settings[maintenance_mode]" value="true"
                                        {{ isset($settings['maintenance_mode']) && filter_var($settings['maintenance_mode'], FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-red-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600">
                                    </div>
                                </label>
                                <input type="hidden" name="settings_types[maintenance_mode]" value="boolean">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-bold text-red-900 mb-1">Nội dung hiển thị khi bảo
                                trì</label>
                            <textarea name="settings[maintenance_message]" rows="3"
                                class="block w-full px-4 py-2 border border-red-300 rounded-lg focus:ring-red-500 focus:border-red-500 text-sm bg-white text-red-800 placeholder-red-300"
                                placeholder="Hệ thống đang bảo trì...">{{ $settings['maintenance_message'] ?? 'Hệ thống đang bảo trì nâng cấp, vui lòng quay lại sau.' }}</textarea>
                            <input type="hidden" name="settings_types[maintenance_message]" value="string">
                        </div>
                    </div>
                </div>
            </div>
            <!-- end tabs -->

            <!-- Nút bấm để lưu tất cả thông tin cài đặt ở trên vào Database -->
            <div x-show="activeTab !== 'logs'" class="mt-8 pt-5 border-t border-gray-100">
                <button type="submit"
                    class="w-full sm:w-auto justify-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 sm:py-2.5 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-save"></i> Lưu toàn bộ cấu hình
                </button>
            </div>
        </form>

        <!-- Tab 4: Xem lịch sử hoạt động (Nhật ký hệ thống) -->
        <div x-show="activeTab === 'logs'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            class="p-4 sm:p-6" style="display: none;">

            <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h3 class="text-base font-semibold text-gray-900 mb-1">Theo dõi hoạt động của người dùng trong hệ thống
                </h3>
                <button type="submit" form="logFilterForm" name="export" value="json"
                    class="w-full sm:w-auto justify-center bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 sm:py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-file-export"></i> Xuất JSON
                </button>
            </div>

            <!-- Form tìm kiếm và lọc lịch sử hoạt động theo nhiều điều kiện -->
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-6">
                <form id="logFilterForm" action="{{ route('admin.settings.index') }}" method="GET"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
                    <input type="hidden" name="active_tab" value="logs">

                    <div class="lg:col-span-2">
                        <select name="module"
                            class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                            <option value="">Tất cả Module</option>
                            @foreach ($modules as $mod)
                                <option value="{{ $mod }}"
                                    {{ request('module') === $mod ? 'selected' : '' }}>{{ ucfirst($mod) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <input type="text" name="action_search" value="{{ request('action_search') }}"
                            placeholder="Tìm hành động..."
                            class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none font-mono">
                    </div>

                    <div class="lg:col-span-3">
                        <select name="user_id" id="filter-user-id"
                            class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                            <option value="">Tất cả người thực hiện</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" selected>{{ $user->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3 flex items-center gap-2">
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="block w-full py-2 px-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-xs outline-none"
                            title="Từ ngày">
                        <span class="text-gray-400">-</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="block w-full py-2 px-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-xs outline-none"
                            title="Đến ngày">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-1 flex items-center gap-2">
                        <button type="submit"
                            class="flex-1 lg:w-full bg-gray-900 hover:bg-gray-800 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors text-center">Lọc</button>
                        <a href="{{ route('admin.settings.index') }}?active_tab=logs"
                            class="flex-1 lg:w-full text-center bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Bảng hiển thị danh sách lịch sử hoạt động -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Thời gian</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Người thực hiện</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hành động</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Module</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Đối tượng</th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Chi tiết</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    IP / Thiết bị</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if ($log->user)
                                            <div class="font-bold text-gray-900">{{ $log->user->full_name }}</div>
                                        @else
                                            <div class="font-bold text-gray-500 italic">Hệ thống</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $color = $log->action_color;
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold font-mono bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->module }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($log->ref_type && $log->ref_id)
                                            {{ $log->ref_type }} #{{ $log->ref_id }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($log->old_data || $log->new_data)
                                            <button type="button" x-data
                                                x-on:click="$dispatch('open-log-modal', {
                                            old: {{ json_encode($log->old_data ?? new stdClass()) }},
                                            new: {{ json_encode($log->new_data ?? new stdClass()) }}
                                        })"
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                <i class="fa-solid fa-code"></i> Xem
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-sm">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-left text-sm text-gray-500 max-w-[200px]">
                                        <div class="font-mono text-xs text-gray-700">{{ $log->ip_address ?? '—' }}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1 truncate"
                                            title="{{ $log->user_agent }}">{{ $log->user_agent ?? 'Không xác định' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <div
                                                class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                                <i class="fa-solid fa-clipboard-list text-2xl text-gray-400"></i>
                                            </div>
                                            <h3 class="text-lg font-medium text-gray-900">Chưa có nhật ký nào</h3>
                                            <p class="text-sm mt-1 text-gray-500">Hệ thống chưa ghi nhận hoạt động nào
                                                khớp với bộ lọc.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-white">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Hộp thoại popup hiện ra khi bấm nút "Xem chi tiết" để so sánh dữ liệu trước và sau khi đổi -->
    <div x-data="{ open: false, oldData: {}, newData: {} }"
        @open-log-modal.window="open = true; oldData = $event.detail.old; newData = $event.detail.new;" x-show="open"
        x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto p-4 sm:p-6"
        style="display: none;">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="open = false"
            aria-hidden="true"></div>

        <!-- Modal Panel -->
        <div
            class="relative bg-white rounded-xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header -->
            <div class="flex justify-between items-center p-4 sm:p-6 border-b border-gray-100">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Chi tiết thay đổi dữ liệu</h3>
                <button @click="open = false"
                    class="text-gray-400 hover:text-gray-500 transition-colors rounded-lg p-1 hover:bg-gray-100">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-4 sm:p-6 overflow-y-auto flex-1">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <h4 class="text-sm font-bold text-red-600 mb-2 flex items-center gap-2"><i
                                class="fa-solid fa-minus-circle"></i> Dữ liệu cũ (Old Data)</h4>
                        <div class="bg-red-50 border border-red-100 p-3 sm:p-4 rounded-lg overflow-x-auto">
                            <pre class="text-xs font-mono text-left text-red-900" x-text="JSON.stringify(oldData, null, 2)"></pre>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-green-600 mb-2 flex items-center gap-2"><i
                                class="fa-solid fa-plus-circle"></i> Dữ liệu mới (New Data)</h4>
                        <div class="bg-green-50 border border-green-100 p-3 sm:p-4 rounded-lg overflow-x-auto">
                            <pre class="text-xs font-mono text-left text-green-900" x-text="JSON.stringify(newData, null, 2)"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 sm:p-6 border-t border-gray-100 bg-gray-50 flex justify-end">
                <button @click="open = false" type="button"
                    class="w-full sm:w-auto inline-flex justify-center px-6 py-2.5 sm:py-2 text-sm font-medium text-white bg-gray-900 border border-transparent rounded-lg shadow-sm hover:bg-gray-800 transition-colors">
                    Đóng
                </button>
            </div>
        </div>
    </div>
    
    <x-slot name="styles">
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        <style>
            .ts-control {
                border-radius: 0.5rem !important;
                border-color: #d1d5db !important;
                padding: 0.5rem 0.75rem !important;
                min-height: 38px;
            }
            .ts-dropdown {
                border-radius: 0.5rem !important;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
            }
        </style>
    </x-slot>

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selectUser = document.getElementById('filter-user-id');
                if (selectUser) {
                    new TomSelect(selectUser, {
                        valueField: 'id',
                        labelField: 'text',
                        searchField: 'text',
                        placeholder: 'Tất cả người thực hiện',
                        allowEmptyOption: true,
                        load: function(query, callback) {
                            if (!query.length) return callback();
                            fetch(`{{ route('admin.users.ajax-search') }}?q=${encodeURIComponent(query)}`)
                                .then(response => response.json())
                                .then(json => {
                                    callback(json.items);
                                }).catch(()=>{
                                    callback();
                                });
                        },
                        render: {
                            no_results: function(data, escape) {
                                return '<div class="no-results p-2 text-gray-500">Không tìm thấy</div>';
                            }
                        }
                    });
                }
            });
        </script>
    </x-slot>
</x-layouts.admin>
