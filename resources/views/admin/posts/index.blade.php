<x-layouts.admin title="Quản lý Bài viết">
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


</x-layouts.admin>
