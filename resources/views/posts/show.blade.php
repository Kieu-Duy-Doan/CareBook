<x-layouts.app>
    <div class="bg-slate-50 py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Breadcrumb --}}
            <nav class="flex text-sm text-slate-500 mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('home') }}" class="inline-flex items-center hover:text-blue-600 transition-colors">
                            <i class="fa-solid fa-house mr-2"></i>
                            Trang chủ
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fa-solid fa-chevron-right text-xs mx-2"></i>
                            <a href="{{ route('posts.index') }}" class="hover:text-blue-600 transition-colors">Tin tức</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fa-solid fa-chevron-right text-xs mx-2"></i>
                            <span class="text-slate-800 font-medium line-clamp-1">{{ $post->title }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            {{-- Article Header --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 mb-8">
                <div class="flex items-center gap-3 mb-6">
                    @if($post->post_type == 'news')
                        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Tin tức y tế</span>
                    @elseif($post->post_type == 'service')
                        <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Dịch vụ</span>
                    @elseif($post->post_type == 'promotion')
                        <span class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Khuyến mãi</span>
                    @else
                        <span class="bg-slate-100 text-slate-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Khác</span>
                    @endif
                </div>

                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 leading-tight mb-6">
                    {{ $post->title }}
                </h1>

                <div class="flex flex-wrap items-center text-sm text-slate-500 gap-6 pb-6 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-calendar text-blue-500"></i>
                        {{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : $post->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-user text-emerald-500"></i>
                        {{ $post->author->full_name ?? 'Ban biên tập CareBook' }}
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-eye text-amber-500"></i>
                        {{ number_format($post->view_count) }} lượt xem
                    </div>
                </div>

                <div class="mt-8">
                    @if($post->thumbnail_url)
                        <img src="{{ asset('storage/' . $post->thumbnail_url) }}" alt="{{ $post->title }}" class="w-full rounded-2xl mb-8 object-cover max-h-[500px]">
                    @endif

                    <div class="prose prose-slate prose-lg max-w-none prose-headings:font-bold prose-headings:text-slate-900 prose-a:text-blue-600 hover:prose-a:text-blue-800 prose-img:rounded-2xl">
                        {!! $post->content !!}
                    </div>
                </div>
            </div>

            {{-- Related Posts --}}
            @if($relatedPosts->isNotEmpty())
                <div class="mb-12">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-slate-900">Bài viết liên quan</h2>
                        <a href="{{ route('posts.index') }}" class="text-blue-600 font-semibold hover:underline text-sm flex items-center gap-2">
                            Xem tất cả <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($relatedPosts as $related)
                            <a href="{{ route('posts.show', $related->slug) }}" class="group bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300">
                                <div class="aspect-video bg-slate-100 overflow-hidden">
                                    <img src="{{ $related->thumbnail_url ? asset('storage/' . $related->thumbnail_url) : asset('assets/images/placeholder.jpg') }}" 
                                         alt="{{ $related->title }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                         onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.jpg') }}'">
                                </div>
                                <div class="p-4">
                                    <h3 class="font-bold text-slate-800 mb-2 group-hover:text-blue-600 transition-colors line-clamp-2 text-sm">
                                        {{ $related->title }}
                                    </h3>
                                    <div class="flex items-center text-xs text-slate-500">
                                        <i class="fa-regular fa-calendar mr-1.5"></i>
                                        {{ $related->published_at ? $related->published_at->format('d/m/Y') : $related->created_at->format('d/m/Y') }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
