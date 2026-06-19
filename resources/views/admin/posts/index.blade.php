<x-layouts.admin title="Quản lý Bài viết">
    <div>
        <!-- Header & Alert -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Quản lý Bài viết</h2>
                <p class="text-gray-500 mt-1">Danh sách tin tức, dịch vụ, thông báo y tế</p>
            </div>
            <div>
                <a href="{{ route('admin.posts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Viết bài mới
                </a>
            </div>
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

        @if(session('error'))
            <div class="mb-6 bg-red-50 text-red-800 rounded-lg p-4 flex items-center border border-red-200" x-data="{ show: true }" x-show="show">
                <i class="fa-solid fa-circle-exclamation text-red-500 mr-3 text-lg"></i>
                <span class="flex-1 text-sm font-medium">{{ session('error') }}</span>
                <button @click="show = false" class="text-red-600 hover:text-red-900">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- Filter & Search -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
            <form method="GET" action="{{ route('admin.posts.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tìm kiếm</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nhập tiêu đề..."
                            class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Loại bài viết</label>
                    <select name="post_type" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        <option value="">Tất cả loại bài</option>
                        <option value="news" {{ request('post_type') == 'news' ? 'selected' : '' }}>Tin tức y tế</option>
                        <option value="service" {{ request('post_type') == 'service' ? 'selected' : '' }}>Dịch vụ</option>
                        <option value="guide" {{ request('post_type') == 'guide' ? 'selected' : '' }}>Hướng dẫn khách hàng</option>
                        <option value="announcement" {{ request('post_type') == 'announcement' ? 'selected' : '' }}>Thông báo</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Trạng thái</label>
                    <select name="is_published" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>Đã xuất bản</option>
                        <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>Bản nháp</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Lọc dữ liệu
                    </button>
                    @if(request()->anyFilled(['search', 'post_type', 'is_published', 'specialty_id']))
                        <a href="{{ route('admin.posts.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-sm font-medium transition-colors" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                            <th class="px-6 py-4">Bài viết</th>
                            <th class="px-6 py-4">Phân loại</th>
                            <th class="px-6 py-4 text-center">Lượt xem</th>
                            <th class="px-6 py-4 text-center">Trạng thái</th>
                            <th class="px-6 py-4 text-right">Ngày đăng</th>
                            <th class="px-6 py-4 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($posts as $post)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="h-14 w-14 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden border border-gray-200 flex items-center justify-center">
                                        @if($post->thumbnail_url)
                                            <img src="{{ asset($post->thumbnail_url) }}" alt="{{ $post->title }}" class="h-full w-full object-cover">
                                        @else
                                            <i class="fa-regular fa-image text-gray-400 text-xl"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 line-clamp-1" title="{{ $post->title }}">{{ $post->title }}</div>
                                        <div class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                            <span>Bởi: <span class="font-medium text-gray-700">{{ $post->author->name ?? 'Admin' }}</span></span>
                                            @if($post->specialty)
                                                <span class="text-gray-300">|</span>
                                                <span class="text-blue-600 truncate max-w-[150px]"><i class="fa-solid fa-stethoscope mr-1 text-[10px]"></i>{{ $post->specialty->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($post->post_type === 'news')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">Tin tức y tế</span>
                                @elseif($post->post_type === 'service')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">Dịch vụ</span>
                                @elseif($post->post_type === 'guide')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-50 text-teal-700 border border-teal-200">Hướng dẫn</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">Thông báo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                {{ number_format($post->view_count) }} <i class="fa-regular fa-eye ml-1 text-gray-400"></i>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($post->is_published)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>
                                        Đã xuất bản
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></span>
                                        Bản nháp
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                @if($post->published_at)
                                    {{ $post->published_at->format('d/m/Y') }}<br>
                                    <span class="text-xs">{{ $post->published_at->format('H:i') }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.posts.edit', $post->id) }}" class="text-blue-600 hover:text-blue-900 transition-colors" title="Sửa bài viết">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    <form action="{{ route('admin.posts.toggle-publish', $post->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="{{ $post->is_published ? 'text-orange-500 hover:text-orange-700' : 'text-green-600 hover:text-green-800' }} transition-colors" title="{{ $post->is_published ? 'Gỡ xuống thành Bản nháp' : 'Xuất bản bài viết' }}">
                                            <i class="fa-solid {{ $post->is_published ? 'fa-file-arrow-down' : 'fa-paper-plane' }}"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Bạn có chắc muốn xoá bài viết này không? Hành động này không thể hoàn tác.')"
                                            class="text-red-600 hover:text-red-900 transition-colors"
                                            title="Xoá bài viết">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-regular fa-newspaper text-2xl text-gray-400"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900">Chưa có bài viết nào</h3>
                                    <p class="mt-1 text-sm text-gray-500">Bắt đầu tạo nội dung để tương tác với bệnh nhân.</p>
                                    <a href="{{ route('admin.posts.create') }}" class="mt-4 bg-blue-50 text-blue-600 hover:bg-blue-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Viết bài mới ngay
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($posts->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-white">
                {{ $posts->links() }}
            </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
