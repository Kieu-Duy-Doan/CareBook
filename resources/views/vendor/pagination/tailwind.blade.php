@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">

        <div class="flex gap-2 items-center justify-between sm:hidden">

            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-sm font-bold text-slate-400 bg-slate-50 border border-slate-200 cursor-not-allowed rounded-xl shadow-sm">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-bold text-blue-600 bg-white border border-slate-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 transition-all shadow-sm">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-bold text-blue-600 bg-white border border-slate-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 transition-all shadow-sm">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-sm font-bold text-slate-400 bg-slate-50 border border-slate-200 cursor-not-allowed rounded-xl shadow-sm">
                    {!! __('pagination.next') !!}
                </span>
            @endif

        </div>

        <div class="hidden sm:flex-1 sm:flex sm:gap-2 sm:items-center sm:justify-between">

            <div>
                <p class="text-sm text-gray-700 leading-5 dark:text-gray-600">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-medium">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <span class="inline-flex items-center gap-2 rtl:flex-row-reverse">

                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="inline-flex items-center justify-center w-10 h-10 text-sm font-bold text-slate-400 bg-white border border-slate-200 cursor-not-allowed rounded-xl" aria-hidden="true">
                                <i class="fa-solid fa-chevron-left"></i>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-10 h-10 text-sm font-bold text-blue-600 bg-white border border-slate-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 transition-all shadow-sm" aria-label="{{ __('pagination.previous') }}">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center justify-center w-10 h-10 text-sm font-bold text-slate-500 bg-transparent cursor-default">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="inline-flex items-center justify-center w-10 h-10 text-sm font-bold text-white bg-blue-600 shadow-md shadow-blue-600/30 cursor-default rounded-xl transition-all">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center justify-center w-10 h-10 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 transition-all shadow-sm" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-10 h-10 text-sm font-bold text-blue-600 bg-white border border-slate-200 rounded-xl hover:bg-blue-50 hover:border-blue-200 transition-all shadow-sm" aria-label="{{ __('pagination.next') }}">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="inline-flex items-center justify-center w-10 h-10 text-sm font-bold text-slate-400 bg-white border border-slate-200 cursor-not-allowed rounded-xl" aria-hidden="true">
                                <i class="fa-solid fa-chevron-right"></i>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
