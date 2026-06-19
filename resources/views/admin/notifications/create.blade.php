<x-layouts.admin title="Tạo Thông báo mới">
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 md:mb-8">
        <div class="flex items-center text-sm text-gray-500">
            <a href="{{ route('admin.notifications.index') }}" class="hover:text-blue-600 transition-colors">Thông báo</a>
            <span class="mx-2 text-gray-300">/</span>
            <span class="font-bold text-gray-900">Thêm mới</span>
        </div>

        <a href="{{ route('admin.notifications.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 mb-6 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200 shadow-sm animate-pulse-once">
        <div class="font-bold mb-2 flex items-center"><i class="fa-solid fa-triangle-exclamation mr-2 text-red-500"></i> Vui lòng kiểm tra lại dữ liệu:</div>
        <ul class="list-disc list-inside space-y-1 ml-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.notifications.store') }}" method="POST">
        @csrf

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Cột trái: Nội dung chính -->
            <div class="flex-1 space-y-6">
                <!-- Card Soạn thảo -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                        <i class="fa-regular fa-pen-to-square text-blue-500"></i>
                        <h3 class="text-base font-bold text-gray-900">Nội dung Thông báo</h3>
                    </div>

                    <div class="p-6 md:p-8 space-y-6">
                        <!-- Người nhận -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Người nhận <span class="text-red-500">*</span></label>
                            <select name="user_ids[]" id="choices-users" multiple="multiple" class="w-full" required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ is_array(old('user_ids')) && in_array($user->id, old('user_ids')) ? 'selected' : '' }}>
                                        {{ $user->full_name }} ({{ $user->email ?? 'Không có email' }}) - {{ ucfirst($user->role) }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-2 flex items-center"><i class="fa-solid fa-circle-info mr-1 text-blue-400"></i> Hỗ trợ tìm kiếm theo tên hoặc email. Có thể chọn cùng lúc nhiều người.</p>
                        </div>

                        <!-- Tiêu đề -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tiêu đề <span class="text-red-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" required placeholder="Ví dụ: Lịch hẹn của bạn đã được xác nhận..." class="w-full border-gray-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors bg-gray-50 focus:bg-white text-base py-3">
                        </div>

                        <!-- Nội dung -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Thông điệp chi tiết <span class="text-red-500">*</span></label>
                            <textarea name="content" rows="6" required placeholder="Nhập nội dung chi tiết bạn muốn gửi đến người nhận..." class="w-full border-gray-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors bg-gray-50 focus:bg-white resize-y p-4">{{ old('content') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Cấu hình gửi -->
            <div class="w-full lg:w-1/3 space-y-6">
                <!-- Card Tùy chọn -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-blue-500"></i>
                        <h3 class="text-base font-bold text-gray-900">Cấu hình gửi</h3>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Loại thông báo -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Phân loại <span class="text-red-500">*</span></label>
                            <select name="type" class="w-full border-gray-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="system" {{ old('type') == 'system' ? 'selected' : '' }}>Hệ thống (System)</option>
                                <option value="reminder" {{ old('type') == 'reminder' ? 'selected' : '' }}>Nhắc nhở (Reminder)</option>
                                <option value="appointment" {{ old('type') == 'appointment' ? 'selected' : '' }}>Lịch hẹn (Appointment)</option>
                                <option value="result" {{ old('type') == 'result' ? 'selected' : '' }}>Kết quả (Result)</option>
                            </select>
                        </div>

                        <!-- Kênh gửi -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Kênh gửi <span class="text-red-500">*</span></label>
                            <div class="space-y-3">
                                <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors group">
                                    <input type="checkbox" name="channels[]" value="in_web" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                                    <div class="ml-3">
                                        <div class="text-sm font-bold text-gray-900 group-hover:text-blue-700 transition-colors"><i class="fa-regular fa-bell text-gray-400 mr-1 group-hover:text-blue-500"></i> Trong hệ thống</div>
                                        <div class="text-xs text-gray-500">Nhận trực tiếp trên Website</div>
                                    </div>
                                </label>

                                <label class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors group">
                                    <input type="checkbox" name="channels[]" value="email" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" {{ is_array(old('channels')) && in_array('email', old('channels')) ? 'checked' : '' }}>
                                    <div class="ml-3">
                                        <div class="text-sm font-bold text-gray-900 group-hover:text-blue-700 transition-colors"><i class="fa-regular fa-envelope text-gray-400 mr-1 group-hover:text-blue-500"></i> Gửi qua Email</div>
                                        <div class="text-xs text-gray-500">Email tự động từ hệ thống</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Hẹn giờ -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Lên lịch (Tùy chọn)</label>
                            <div class="relative">
                                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="w-full border-gray-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5">
                            </div>
                            <p class="text-xs text-gray-500 mt-2 leading-relaxed">Nếu để trống, hệ thống sẽ thực hiện gửi ngay lập tức. Nếu có chọn giờ, thông báo sẽ được lưu vào Queue.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-4 bg-blue-600 text-white rounded-2xl hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 transition-all font-bold text-base flex items-center justify-center gap-2 shadow-lg shadow-blue-600/30">
                    <i class="fa-solid fa-paper-plane"></i> Phát hành Thông báo
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Thêm TomSelect CSS & JS -->
<x-slot name="styles">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
    /* Tuỳ biến giao diện TomSelect cho mượt với Tailwind */
    .ts-control {
        border-radius: 0.75rem !important;
        border-color: #e5e7eb !important;
        background-color: #f9fafb !important;
        padding: 0.5rem 0.75rem !important;
        min-height: 48px;
    }
    .ts-control.focus {
        background-color: #ffffff !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 1px #3b82f6 !important;
    }
    .ts-dropdown {
        border-radius: 0.75rem !important;
        border-color: #e5e7eb !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        margin-top: 4px !important;
    }
    .ts-control .item {
        background-color: #eff6ff !important;
        border: 1px solid #bfdbfe !important;
        color: #1e40af !important;
        border-radius: 0.5rem !important;
        padding: 2px 8px !important;
    }
</style>
</x-slot>

<x-slot name="scripts">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectEl = document.getElementById('choices-users');
        if (selectEl) {
            new TomSelect(selectEl, {
                plugins: ['remove_button'],
                placeholder: 'Nhập tên hoặc email...',
                searchField: ['text', 'value'],
                maxOptions: 50,
                render: {
                    no_results: function(data, escape) {
                        return '<div class="no-results p-2 text-gray-500">Không tìm thấy người dùng phù hợp</div>';
                    }
                }
            });
        }
    });
</script>
</x-slot>
</x-layouts.admin>
