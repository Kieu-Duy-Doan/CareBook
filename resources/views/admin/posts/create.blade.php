<x-layouts.admin title="Viết bài mới">

    <div class="mb-6">
        <a href="{{ route('admin.posts.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors mb-2 inline-block">
            <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại Danh sách Bài viết
        </a>
        <h2 class="text-2xl font-bold text-gray-900 mt-2">Viết bài mới</h2>
    </div>

    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col lg:flex-row gap-6">
        @csrf

        <!-- Cột chính (Content) -->
        <div class="flex-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề bài viết <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-base font-medium outline-none" placeholder="Nhập tiêu đề...">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Đường dẫn tĩnh (Slug)</label>
                        <div class="flex rounded-lg shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                {{ url('/') }}/
                            </span>
                            <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                                class="flex-1 block w-full min-w-0 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="De-trong-de-tu-dong-tao">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Chuỗi định danh trên URL. Để trống hệ thống sẽ tự tạo từ Tiêu đề.</p>
                        @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tóm tắt nội dung</label>
                        <textarea name="summary" rows="3"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="Nhập tóm tắt ngắn cho bài viết (Sẽ hiển thị ở trang chủ hoặc thẻ mô tả mạng xã hội)...">{{ old('summary') }}</textarea>
                        @error('summary') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung chi tiết <span class="text-red-500">*</span></label>
                        <textarea name="content" rows="15" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none font-mono" placeholder="Nhập nội dung bài viết. Bạn có thể sử dụng các thẻ HTML cơ bản ở đây...">{{ old('content') }}</textarea>
                        @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phụ (Sidebar) -->
        <div class="lg:w-80 space-y-6">
            <!-- Xuất bản -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Xuất bản</h3>

                <div class="mb-4">
                    <label class="flex items-center cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="is_published" value="1" class="sr-only peer" {{ old('is_published', '1') == '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                        </div>
                        <span class="ms-3 text-sm font-medium text-gray-700">Đăng bài ngay lập tức</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-2">Tắt công tắc này để lưu dưới dạng Bản nháp (Draft).</p>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-save"></i> Lưu bài viết
                </button>
            </div>

            <!-- Phân loại & Chuyên khoa -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Phân loại</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Loại bài viết <span class="text-red-500">*</span></label>
                        <select name="post_type" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                            <option value="news" {{ old('post_type') == 'news' ? 'selected' : '' }}>Tin tức y tế</option>
                            <option value="service" {{ old('post_type') == 'service' ? 'selected' : '' }}>Giới thiệu Dịch vụ</option>
                            <option value="guide" {{ old('post_type') == 'guide' ? 'selected' : '' }}>Hướng dẫn khách hàng</option>
                            <option value="announcement" {{ old('post_type') == 'announcement' ? 'selected' : '' }}>Thông báo hệ thống</option>
                        </select>
                        @error('post_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chuyên khoa liên quan</label>
                        <select name="specialty_id" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                            <option value="">-- Không phân loại chuyên khoa --</option>
                            @foreach($specialties as $sp)
                                <option value="{{ $sp->id }}" {{ old('specialty_id') == $sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>
                            @endforeach
                        </select>
                        @error('specialty_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Ảnh đại diện (Thumbnail) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5" x-data="imageUploader()">
                <h3 class="font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Ảnh đại diện</h3>

                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg relative overflow-hidden group hover:border-blue-400 transition-colors cursor-pointer" @click="$refs.fileInput.click()">

                    <!-- Box hiển thị khi chưa có ảnh -->
                    <div class="space-y-1 text-center" x-show="!imageUrl">
                        <i class="fa-regular fa-image text-4xl text-gray-400 mb-2"></i>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <span class="relative bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                <span>Tải ảnh lên</span>
                            </span>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, WEBP tới 2MB</p>
                    </div>

                    <!-- Box hiển thị Preview Ảnh -->
                    <div x-show="imageUrl" class="absolute inset-0 w-full h-full" style="display: none;">
                        <img :src="imageUrl" class="w-full h-full object-cover">
                        <!-- Nút đổi ảnh (hiển thị khi hover) -->
                        <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="text-white font-medium flex items-center gap-2">
                                <i class="fa-solid fa-pen"></i> Đổi ảnh khác
                            </span>
                        </div>
                    </div>

                    <input type="file" x-ref="fileInput" name="thumbnail" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="fileChosen">
                </div>
                @error('thumbnail') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>
        </div>
    </form>

</x-layouts.admin>
