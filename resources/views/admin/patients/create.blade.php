<x-layouts.admin title="Thêm Hồ sơ Bệnh nhân">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <nav class="flex text-sm text-gray-500 font-medium">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition">Dashboard</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('admin.patients.index') }}" class="hover:text-gray-900 transition">Hồ sơ bệnh nhân</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">Thêm mới</span>
            </nav>
            <a href="{{ route('admin.patients.index') }}"
                class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                <div class="flex items-center gap-2 mb-2 font-bold">
                    <i class="fa-solid fa-triangle-exclamation"></i> Vui lòng kiểm tra lại thông tin:
                </div>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center gap-2 font-bold">
                <i class="fa-solid fa-triangle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.patients.store') }}" method="POST" enctype="multipart/form-data" x-data="{ loading: false }"
            @submit="loading = true">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Cột trái: Form (2/3) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Thông tin Tài khoản liên kết -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                <i class="fa-solid fa-link text-blue-600"></i> Liên kết Tài khoản Khách hàng
                                <span class="text-red-500">*</span>
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Khách hàng quản lý hồ sơ này
                                        <span class="text-red-500">*</span></label>
                                    <select id="select-customer" name="owner_id" required class="w-full">
                                        <option value=""></option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('owner_id') == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->full_name }} ({{ $customer->phone ?? 'Không có SĐT' }}{{ $customer->id_card ? ' | CCCD: ' . $customer->id_card : '' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Gõ tên, số điện thoại hoặc số CCCD để tìm kiếm nhanh trong hàng chục ngàn khách hàng.</p>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Loại hồ sơ <span class="text-red-500">*</span></label>
                                    <div class="flex items-center gap-6 mt-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="is_self" value="1" {{ old('is_self', '0') == '1' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                            <span class="text-sm text-gray-700">Hồ sơ bản thân</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="is_self" value="0" {{ old('is_self', '0') == '0' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                            <span class="text-sm text-gray-700">Hồ sơ người thân</span>
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">Lưu ý: Mỗi khách hàng chỉ được có 1 "Hồ sơ bản thân".</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin cơ bản -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                <i class="fa-solid fa-address-card text-blue-600"></i> Thông tin Cơ bản
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên bệnh nhân <span class="text-red-500">*</span></label>
                                    <input type="text" name="full_name" value="{{ old('full_name') }}"
                                        required
                                        class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 @error('full_name') border-red-500 @enderror">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ngày sinh <span
                                            class="text-red-500">*</span></label>
                                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                        max="{{ date('Y-m-d') }}" required
                                        class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 @error('date_of_birth') border-red-500 @enderror">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Giới tính <span
                                            class="text-red-500">*</span></label>
                                    <select name="gender" required
                                        class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 @error('gender') border-red-500 @enderror">
                                        <option value="">-- Chọn giới tính --</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Nam
                                        </option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Nữ
                                        </option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Khác
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại liên hệ</label>
                                    <input type="text" name="phone" value="{{ old('phone') }}"
                                        class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 @error('phone') border-red-500 @enderror">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Số CCCD / CMND</label>
                                    <input type="text" name="id_card" value="{{ old('id_card') }}"
                                        class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 @error('id_card') border-red-500 @enderror">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Dân tộc</label>
                                    <input type="text" name="ethnicity" value="{{ old('ethnicity') }}"
                                        class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nghề nghiệp</label>
                                    <input type="text" name="occupation" value="{{ old('occupation') }}"
                                        class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Địa chỉ</label>
                                    <textarea name="address" rows="2"
                                        class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2">{{ old('address') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin bảo hiểm y tế -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                <i class="fa-solid fa-id-card-clip text-blue-600"></i> Thông tin bảo hiểm y tế
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mã thẻ BHYT</label>
                                    <input type="text" name="insurance_code" value="{{ old('insurance_code') }}"
                                        class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 font-mono">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ngày hết hạn
                                        thẻ</label>
                                    <input type="date" name="insurance_expiry" value="{{ old('insurance_expiry') }}"
                                        class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nơi đăng ký KCB ban
                                        đầu</label>
                                    <input type="text" name="insurance_place" value="{{ old('insurance_place') }}"
                                        class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ghi chú y tế -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                <i class="fa-solid fa-file-medical text-blue-600"></i> Ghi chú y tế
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tiền sử bệnh lý (Tải lên file PDF)</label>
                                    <input type="file" name="medical_history[]" multiple accept=".pdf"
                                        class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú triệu chứng, dị
                                        ứng, thông tin khác...</label>
                                    <textarea name="symptom_notes" rows="4"
                                        class="w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2">{{ old('symptom_notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Cột phải: Hướng dẫn -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-5 sticky top-24">
                        <h4 class="font-bold text-blue-800 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info"></i> Hướng dẫn tạo hồ sơ
                        </h4>
                        <ul class="space-y-3 text-sm text-blue-800">
                            <li class="flex gap-2">
                                <i class="fa-solid fa-check mt-1"></i>
                                <span><b>Hồ sơ bệnh nhân</b> là nơi lưu trữ toàn bộ dữ liệu khám chữa bệnh.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="fa-solid fa-check mt-1"></i>
                                <span>Mỗi hồ sơ bắt buộc phải thuộc về <b>1 Tài khoản khách hàng</b>.</span>
                            </li>
                            <li class="flex gap-2">
                                <i class="fa-solid fa-check mt-1"></i>
                                <span>Mã bệnh nhân (BN...) sẽ được hệ thống <b>tự động tạo ra</b> dựa vào số CMND/CCCD. Nếu không có CMND/CCCD, hệ thống sẽ tự phát sinh mã ngẫu nhiên.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Nút Lưu -->
            <div
                class="bg-white border-t border-gray-200 mt-6 pt-6 sticky bottom-0 z-10 pb-6 -mx-4 px-4 sm:mx-0 sm:px-0">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('admin.patients.index') }}"
                        class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                        Huỷ bỏ
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                        :disabled="loading">
                        <i class="fa-solid fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                        <i class="fa-solid fa-save" x-show="!loading"></i>
                        <span>Thêm Hồ Sơ Bệnh Nhân</span>
                    </button>
                </div>
            </div>
        </form>

    </div>

    <x-slot name="styles">
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        <style>
            .ts-wrapper {
                border: none !important;
                padding: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
            }
            .ts-control {
                border: 1px solid #d1d5db !important;
                border-radius: 0.5rem !important;
                padding: 0.55rem 0.75rem !important;
                min-height: 42px;
                font-size: 0.875rem !important;
                background-color: #ffffff !important;
                box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            }
            .ts-control.focus {
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
            }
            .ts-dropdown {
                border-radius: 0.5rem !important;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
                border-color: #e5e7eb !important;
                z-index: 50 !important;
            }
            .ts-dropdown .active {
                background-color: #eff6ff !important;
                color: #1d4ed8 !important;
            }
        </style>
    </x-slot>

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const selectEl = document.getElementById('select-customer');
                if (selectEl) {
                    new TomSelect(selectEl, {
                        valueField: 'id',
                        labelField: 'text',
                        searchField: ['text', 'phone', 'id_card', 'full_name'],
                        placeholder: 'Gõ tên, SĐT hoặc số CCCD để tìm nhanh...',
                        plugins: ['clear_button'],
                        maxOptions: 50,
                        loadThrottle: 300,
                        load: function(query, callback) {
                            if (!query.length) return callback();
                            fetch(`{{ route('users.ajax-search') }}?role=patient&q=${encodeURIComponent(query)}`)
                                .then(response => response.json())
                                .then(json => {
                                    callback(json.items);
                                })
                                .catch(() => {
                                    callback();
                                });
                        },
                        render: {
                            no_results: function(data, escape) {
                                return '<div class="no-results p-3 text-sm text-gray-500 text-center"><i class="fa-solid fa-magnifying-glass mr-1"></i> Không tìm thấy khách hàng phù hợp</div>';
                            },
                            loading: function(data, escape) {
                                return '<div class="spinner p-3 text-sm text-gray-500 text-center"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang tìm kiếm...</div>';
                            }
                        }
                    });
                }
            });
        </script>
    </x-slot>
</x-layouts.admin>
