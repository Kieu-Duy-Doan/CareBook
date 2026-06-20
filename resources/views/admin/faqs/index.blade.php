<x-layouts.admin title="Quản lý FAQ">
    <div>
        <!-- Header & Alert -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Quản lý FAQ (Câu hỏi thường gặp)</h2>
                <p class="text-gray-500 mt-1">Quản lý dữ liệu trả lời nhanh và kho kiến thức cho Chatbot</p>
            </div>
            <div>
                <a href="{{ route('admin.faqs.create') }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Thêm FAQ mới
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 bg-green-50 text-green-800 rounded-lg p-4 flex items-center border border-green-200"
                x-data="{ show: true }" x-show="show">
                <i class="fa-solid fa-circle-check text-green-500 mr-3 text-lg"></i>
                <span class="flex-1 text-sm font-medium">{{ session('success') }}</span>
                <button @click="show = false" class="text-green-600 hover:text-green-900">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- Filter & Search -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
            <form method="GET" action="{{ route('admin.faqs.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tìm kiếm theo câu hỏi</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nhập câu hỏi cần tìm..."
                            class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Chuyên khoa</label>
                    <select name="specialty_id"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        <option value="">Tất cả chuyên khoa</option>
                        @foreach ($specialties as $sp)
                            <option value="{{ $sp->id }}"
                                {{ request('specialty_id') == $sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="w-full bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Lọc dữ liệu
                    </button>
                    @if (request()->anyFilled(['search', 'specialty_id']))
                        <a href="{{ route('admin.faqs.index') }}"
                            class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-sm font-medium transition-colors"
                            title="Xóa bộ lọc">
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
                        <tr
                            class="bg-gray-50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-semibold">
                            <th class="px-6 py-4">Nội dung Câu hỏi / Trả lời</th>
                            <th class="px-6 py-4">Chuyên khoa</th>
                            <th class="px-6 py-4 text-center">Trạng thái hiển thị</th>
                            <th class="px-6 py-4 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($faqs as $faq)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 mb-1">{{ $faq->question }}</div>
                                    <div class="text-sm text-gray-500 line-clamp-2 max-w-2xl"
                                        title="{{ $faq->answer }}">{{ $faq->answer }}</div>
                                    @if ($faq->keywords)
                                        <div class="mt-2 text-xs">
                                            <span class="text-gray-400"><i class="fa-solid fa-tags mr-1"></i>Từ
                                                khóa:</span>
                                            <span class="text-blue-600">{{ $faq->keywords }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($faq->specialty)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $faq->specialty->name }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                            Dùng chung
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <form action="{{ route('admin.faqs.toggle-active', $faq->id) }}" method="POST"
                                        class="inline-block" x-data="{ submitting: false }" @submit="submitting = true">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" :disabled="submitting"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 {{ $faq->is_active ? 'bg-blue-600' : 'bg-gray-200' }}"
                                            role="switch" aria-checked="{{ $faq->is_active ? 'true' : 'false' }}">
                                            <span class="sr-only">Toggle active status</span>
                                            <span aria-hidden="true"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $faq->is_active ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.faqs.edit', $faq->id) }}"
                                            class="text-blue-600 hover:text-blue-900 transition-colors" title="Sửa FAQ">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST"
                                            class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                onclick="return confirm('Bạn có chắc muốn xoá câu hỏi này không?')"
                                                class="text-red-600 hover:text-red-900 transition-colors"
                                                title="Xoá FAQ">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fa-regular fa-comments text-2xl text-gray-400"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900">Chưa có câu hỏi nào</h3>
                                        <p class="mt-1 text-sm text-gray-500">Tạo kho kiến thức để tư vấn bệnh nhân tốt
                                            hơn.</p>
                                        <a href="{{ route('admin.faqs.create') }}"
                                            class="mt-4 bg-blue-50 text-blue-600 hover:bg-blue-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                            Thêm câu hỏi ngay
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($faqs->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-white">
                    {{ $faqs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
