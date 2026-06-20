<x-layouts.admin title="Tạo Thông báo mới">
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 md:mb-8">
            <div class="flex items-center text-sm text-gray-500">
                <a href="{{ route('admin.notifications.index') }}" class="hover:text-blue-600 transition-colors">Thông
                    báo</a>
                <span class="mx-2 text-gray-300">/</span>
                <span class="font-bold text-gray-900">Thêm mới</span>
            </div>

            <a href="{{ route('admin.notifications.index') }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors shadow-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
            </a>
        </div>

        @if ($errors->any())
            <div
                class="p-4 mb-6 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200 shadow-sm animate-pulse-once">
                <div class="font-bold mb-2 flex items-center"><i
                        class="fa-solid fa-triangle-exclamation mr-2 text-red-500"></i> Vui lòng kiểm tra lại dữ liệu:
                </div>
                <ul class="list-disc list-inside space-y-1 ml-1">
                    @foreach ($errors->all() as $error)
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
                                <!-- Ô chọn người nhận, đã chuyển sang dùng TomSelect gọi API để không load toàn bộ user -->
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Người nhận <span
                                        class="text-red-500">*</span></label>
                                <select name="user_ids[]" id="choices-users" multiple="multiple" class="w-full"
                                    required>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" selected>
                                            {{ $user->full_name }} ({{ $user->email ?? 'Không có email' }}) -
                                            {{ ucfirst($user->role) }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-2 flex items-center"><i
                                        class="fa-solid fa-circle-info mr-1 text-blue-400"></i> Hỗ trợ tìm kiếm theo tên
                                    hoặc email. Có thể chọn cùng lúc nhiều người.</p>
                            </div>

                            <!-- Tiêu đề -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tiêu đề <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title') }}" required
                                    placeholder="Ví dụ: Lịch hẹn của bạn đã được xác nhận..."
                                    class="w-full border-gray-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors bg-gray-50 focus:bg-white text-base py-3">
                            </div>

                            <!-- Nội dung -->
                            <div>
                                <!-- Ô nhập nội dung chi tiết của thông báo -->
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Thông điệp chi tiết <span
                                        class="text-red-500">*</span></label>
                                <textarea name="content" rows="6" required placeholder="Nhập nội dung chi tiết bạn muốn gửi đến người nhận..."
                                    class="w-full border-gray-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors bg-gray-50 focus:bg-white resize-y p-4">{{ old('content') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.notifications.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors shadow-sm">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
                </a>
            </div>

            @if ($errors->any())
                <div
                    class="p-4 mb-6 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200 shadow-sm animate-pulse-once">
                    <div class="font-bold mb-2 flex items-center"><i
                            class="fa-solid fa-triangle-exclamation mr-2 text-red-500"></i> Vui lòng kiểm tra lại dữ
                        liệu:
                    </div>
                    <ul class="list-disc list-inside space-y-1 ml-1">
                        @foreach ($errors->all() as $error)
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

                            <!-- Kênh gửi -->
                            <div>
                                <!-- Cho phép người dùng chọn gửi thông báo ngay trên web hay bắn thẳng email -->
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Kênh gửi <span
                                        class="text-red-500">*</span></label>
                                <div class="space-y-3">
                                    <label
                                        class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors group">
                                        <input type="checkbox" name="channels[]" value="in_web"
                                            class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            checked>
                                        <div class="ml-3">
                                            <div
                                                class="text-sm font-bold text-gray-900 group-hover:text-blue-700 transition-colors">
                                                <i
                                                    class="fa-regular fa-bell text-gray-400 mr-1 group-hover:text-blue-500"></i>
                                                Trong hệ thống
                                            </div>
                                            <div class="text-xs text-gray-500">Nhận trực tiếp trên Website</div>
                                        </div>
                                    </label>

                                    <label
                                        class="flex items-center p-3 border border-gray-200 rounded-xl hover:bg-gray-50 cursor-pointer transition-colors group">
                                        <input type="checkbox" name="channels[]" value="email"
                                            class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            {{ is_array(old('channels')) && in_array('email', old('channels')) ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <div
                                                class="text-sm font-bold text-gray-900 group-hover:text-blue-700 transition-colors">
                                                <i
                                                    class="fa-regular fa-envelope text-gray-400 mr-1 group-hover:text-blue-500"></i>
                                                Gửi qua Email
                                            </div>
                                            <div class="text-xs text-gray-500">Email tự động từ hệ thống</div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Tiêu đề -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tiêu đề <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="title" value="{{ old('title') }}" required
                                        placeholder="Ví dụ: Lịch hẹn của bạn đã được xác nhận..."
                                        class="w-full px-4 py-3 border-gray-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors bg-gray-50 focus:bg-white text-base">
                                </div>

                                <!-- Nội dung -->
                                <div>
                                    <!-- Ô nhập nội dung chi tiết của thông báo -->
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Thông điệp chi tiết
                                        <span class="text-red-500">*</span></label>
                                    <textarea name="content" rows="6" required placeholder="Nhập nội dung chi tiết bạn muốn gửi đến người nhận..."
                                        class="w-full border-gray-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors bg-gray-50 focus:bg-white resize-y p-4">{{ old('content') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="btn-submit"
                        class="w-full py-4 bg-blue-600 text-white rounded-2xl hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 transition-all font-bold text-base flex items-center justify-center gap-2 shadow-lg shadow-blue-600/30">
                        <i class="fa-solid fa-paper-plane" id="btn-icon"></i> <span id="btn-text">Phát hành Thông
                            báo</span>
                    </button>
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

            <x-slot name="scripts"><script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script><script>
                document.addEventListener('DOMContentLoaded', function() {
                    const selectEl = document.getElementById('choices-users');
                    if (selectEl) {
                        new TomSelect(selectEl, {
                            plugins: ['remove_button'],
                            placeholder: 'Nhập tên hoặc email...',
                            valueField: 'id',
                            labelField: 'text',
                            searchField: 'text',

                            // Tự động gọi lên API tìm người dùng mỗi khi gõ phím, thay vì load sẵn hàng chục ngàn người
                            load: function(query, callback) {
                                if (!query.length) return callback();
                                fetch(`{{ route('admin.users.ajax-search') }}?q=${encodeURIComponent(query)}`)
                                    .then(response => response.json())
                                    .then(json => {
                                        callback(json.items);
                                    }).catch(() => {
                                        callback();
                                    });
                            },
                            render: {
                                no_results: function(data, escape) {
                                    return '<div class="no-results p-2 text-gray-500">Không tìm thấy người dùng phù hợp</div>';
                                },
                                option: function(item, escape) {
                                    return `<div class="p-2"><span class="font-medium text-gray-900">${escape(item.text)}</span></div>`;
                                },
                                item: function(item, escape) {
                                    return `<div class="item">${escape(item.text)}</div>`;
                                }
                            }
                        });
                    }

                    // Chống bấm đúp chuột (double-click) gây ra 2 thông báo trùng nhau
                    const formEl = document.querySelector('form');
                    const btnSubmit = document.getElementById('btn-submit');
                    if (formEl && btnSubmit) {
                        formEl.addEventListener('submit', function() {
                            // Đổi nút sang trạng thái đang xử lý
                            btnSubmit.disabled = true;
                            btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
                            document.getElementById('btn-icon').className = 'fa-solid fa-spinner fa-spin';
                            document.getElementById('btn-text').innerText = 'Đang xử lý...';
                        });
                    }
                });
            </script></x-slot></x-layouts.admin>
