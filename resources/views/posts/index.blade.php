<x-layouts.app>
    <div class="bg-blue-600 pb-24 pt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-white mb-2">Tin tức & Sự kiện</h1>
            <p class="text-blue-100">Cập nhật những thông tin y tế, sức khỏe và các hoạt động mới nhất từ CareBook.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 pb-20 relative z-10">
        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Sidebar Filter --}}
            <div class="lg:w-1/4">
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-slate-100 sticky top-24">
                    <h3 class="text-lg font-bold text-blue-900 mb-4 pb-2 border-b border-slate-100">
                        <i class="fa-solid fa-filter mr-2 text-amber-500"></i> Lọc Tin Tức
                    </h3>

                    <form method="GET" action="{{ route('posts.index') }}" class="space-y-6">
                        {{-- Search --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Tìm kiếm</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-search text-slate-400"></i>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    class="pl-10 block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors"
                                    placeholder="Tên bài viết...">
                            </div>
                        </div>

                        {{-- Type --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Chuyên mục</label>
                            <select name="type"
                                class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors">
                                <option value="">Tất cả</option>
                                <option value="news" {{ request('type') == 'news' ? 'selected' : '' }}>Tin tức y tế</option>
                                <option value="service" {{ request('type') == 'service' ? 'selected' : '' }}>Dịch vụ</option>
                                <option value="promotion" {{ request('type') == 'promotion' ? 'selected' : '' }}>Khuyến mãi</option>
                            </select>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex gap-3">
                            @if(request()->anyFilled(['search', 'type']))
                                <a href="{{ route('posts.index') }}"
                                   class="flex-1 bg-slate-100 text-slate-600 px-4 py-2 rounded-xl text-center text-sm font-bold hover:bg-slate-200 transition-colors">
                                    Xóa lọc
                                </a>
                            @endif
                            <button type="submit"
                                class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-blue-700 transition-colors shadow-md shadow-blue-200">
                                Áp dụng
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Main Content --}}
            <div class="lg:w-3/4">
                @if($posts->isEmpty())
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-12 text-center">
                        <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-500">
                            <i class="fa-regular fa-newspaper text-4xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">Không tìm thấy bài viết nào</h3>
                        <p class="text-slate-500">Vui lòng thử lại với các tiêu chí tìm kiếm khác.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($posts as $post)
                            <a href="{{ route('posts.show', $post->slug) }}" class="group bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col">
                                <div class="aspect-[4/3] bg-slate-100 overflow-hidden relative">
                                    <img src="{{ $post->thumbnail_url ? asset('storage/' . $post->thumbnail_url) : asset('assets/images/placeholder.jpg') }}" 
                                         alt="{{ $post->title }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                         onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.jpg') }}'">
                                    
                                    <div class="absolute top-3 left-3 flex gap-2">
                                        @if($post->post_type == 'news')
                                            <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">Tin tức</span>
                                        @elseif($post->post_type == 'service')
                                            <span class="bg-emerald-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">Dịch vụ</span>
                                        @elseif($post->post_type == 'promotion')
                                            <span class="bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">Khuyến mãi</span>
                                        @else
                                            <span class="bg-slate-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">Khác</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="p-5 flex-1 flex flex-col">
                                    <div class="flex items-center text-xs text-slate-500 mb-3 gap-4">
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-regular fa-calendar"></i>
                                            {{ $post->published_at ? $post->published_at->format('d/m/Y') : $post->created_at->format('d/m/Y') }}
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <i class="fa-regular fa-eye"></i>
                                            {{ number_format($post->view_count) }}
                                        </div>
                                    </div>
                                    
                                    <h3 class="text-lg font-bold text-slate-800 mb-2 group-hover:text-blue-600 transition-colors line-clamp-2">
                                        {{ $post->title }}
                                    </h3>
                                    
                                    <p class="text-slate-600 text-sm line-clamp-3 mb-4 flex-1">
                                        {{ $post->summary }}
                                    </p>
                                    
                                    <div class="mt-auto flex items-center justify-between">
                                        <span class="text-blue-600 font-semibold text-sm group-hover:underline">Xem chi tiết</span>
                                        <i class="fa-solid fa-arrow-right text-blue-600 group-hover:translate-x-1 transition-transform"></i>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $posts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
