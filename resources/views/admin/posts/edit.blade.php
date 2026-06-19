<x-layouts.admin title="Chỉnh sửa Bài viết">
    <div class="mb-6">
        <a href="{{ route('admin.posts.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors mb-2 inline-block">
            <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại Danh sách Bài viết
        </a>
        <h2 class="text-2xl font-bold text-gray-900 mt-2">Chỉnh sửa Bài viết</h2>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 text-green-800 rounded-lg p-4 flex items-center border border-green-200" x-data="{ show: true }" x-show="show">
            <i class="fa-solid fa-circle-check text-green-500 mr-3 text-lg"></i>
            <span class="flex-1 text-sm font-medium">{{ session('success') }}</span>
            <button @click="show = false" class="text-green-600 hover:text-green-900">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <form action="{{ route('admin.posts.update', $post->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col lg:flex-row gap-6">
        @csrf
        @method('PUT')

        <!-- Cột chính (Content) -->
        <div class="flex-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề bài viết <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-base font-medium outline-none" placeholder="Nhập tiêu đề...">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Đường dẫn tĩnh (Slug)</label>
                        <div class="flex rounded-lg shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                {{ url('/') }}/
                            </span>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $post->slug) }}"
                                class="flex-1 block w-full min-w-0 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="De-trong-de-tu-dong-tao">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Bạn có thể sửa lại đường dẫn tĩnh. Nếu để trống hệ thống sẽ tự tạo từ Tiêu đề hiện tại.</p>
                        @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tóm tắt nội dung</label>
                        <textarea name="summary" rows="3"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="Nhập tóm tắt ngắn cho bài viết...">{{ old('summary', $post->summary) }}</textarea>
                        @error('summary') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung chi tiết <span class="text-red-500">*</span></label>
                        <textarea name="content" rows="15" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none font-mono" placeholder="Nhập nội dung bài viết.">{{ old('content', $post->content) }}</textarea>
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
                            <input type="checkbox" name="is_published" value="1" class="sr-only peer" {{ old('is_published', $post->is_published ? '1' : '0') == '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                        </div>
                        <span class="ms-3 text-sm font-medium text-gray-700">Đăng bài ngay lập tức</span>
                    </label>
                    @if($post->published_at)
                    <p class="text-xs text-gray-500 mt-3">Đã xuất bản lúc: <br><span class="font-medium">{{ $post->published_at->format('H:i d/m/Y') }}</span></p>
                    @endif
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-save"></i> Lưu cập nhật
                </button>
            </div>

            <!-- Phân loại & Chuyên khoa -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Phân loại</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Loại bài viết <span class="text-red-500">*</span></label>
                        <select name="post_type" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                            <option value="news" {{ old('post_type', $post->post_type) == 'news' ? 'selected' : '' }}>Tin tức y tế</option>
                            <option value="service" {{ old('post_type', $post->post_type) == 'service' ? 'selected' : '' }}>Giới thiệu Dịch vụ</option>
                            <option value="guide" {{ old('post_type', $post->post_type) == 'guide' ? 'selected' : '' }}>Hướng dẫn khách hàng</option>
                            <option value="announcement" {{ old('post_type', $post->post_type) == 'announcement' ? 'selected' : '' }}>Thông báo hệ thống</option>
                        </select>
                        @error('post_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chuyên khoa liên quan</label>
                        <select name="specialty_id" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                            <option value="">-- Không phân loại chuyên khoa --</option>
                            @foreach($specialties as $sp)
                                <option value="{{ $sp->id }}" {{ old('specialty_id', $post->specialty_id) == $sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>
                            @endforeach
                        </select>
                        @error('specialty_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Ảnh đại diện (Thumbnail) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5" x-data="imageUploader('{{ $post->thumbnail_url ? asset($post->thumbnail_url) : '' }}')">
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
                <p class="text-xs text-gray-400 mt-2">Lưu ý: Bỏ trống nếu muốn giữ nguyên ảnh hiện tại.</p>
                @error('thumbnail') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
            </div>
        </div>
    </form>

    <script>
        // Alpine component for Image Upload Preview
        function imageUploader(initialUrl) {
            return {
                imageUrl: initialUrl,
                fileChosen(event) {
                    this.fileToDataUrl(event, src => this.imageUrl = src)
                },
                fileToDataUrl(event, callback) {
                    if (! event.target.files.length) return
                    let file = event.target.files[0],
                        reader = new FileReader()
                    reader.readAsDataURL(file)
                    reader.onload = e => callback(e.target.result)
                }
            }
        }

        // Auto-generate Slug from Title using pure JS
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('title');
            const slugInput = document.getElementById('slug');
            let isSlugEdited = slugInput.value.trim() !== '';

            // Chặn auto-gen nếu user cố tình tự gõ slug
            slugInput.addEventListener('input', function() {
                isSlugEdited = this.value.trim() !== '';
            });

            titleInput.addEventListener('input', function() {
                if (!isSlugEdited) {
                    let title = this.value;
                    let slug = title.toLowerCase();
                    slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
                    slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
                    slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
                    slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
                    slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
                    slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
                    slug = slug.replace(/đ/gi, 'd');
                    slug = slug.replace(/\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi, '');
                    slug = slug.replace(/ /gi, "-");
                    slug = slug.replace(/\-\-\-\-\-/gi, '-');
                    slug = slug.replace(/\-\-\-\-/gi, '-');
                    slug = slug.replace(/\-\-\-/gi, '-');
                    slug = slug.replace(/\-\-/gi, '-');
                    slug = '@' + slug + '@';
                    slug = slug.replace(/\@\-|\-\@|\@/gi, '');

                    slugInput.value = slug;
                }
            });
        });
    </script>
</x-layouts.admin>
