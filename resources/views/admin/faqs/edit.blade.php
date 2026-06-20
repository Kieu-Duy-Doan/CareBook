<x-layouts.admin title="Chỉnh sửa câu hỏi">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 md:mb-8">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.faqs.index') }}" class="hover:text-blue-600 transition-colors">Câu hỏi thường gặp</a>
                <span class="mx-2 text-gray-300">/</span>
                <span class="font-bold text-gray-900">Chỉnh sửa</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Chỉnh sửa câu hỏi</h2>
        </div>

        <a href="{{ route('admin.faqs.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-50 text-green-800 rounded-xl p-4 flex items-center border border-green-200 shadow-sm" x-data="{ show: true }" x-show="show">
            <i class="fa-solid fa-circle-check text-green-500 mr-3 text-lg"></i>
            <span class="flex-1 text-sm font-medium">{{ session('success') }}</span>
            <button @click="show = false" class="text-green-600 hover:text-green-900 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-50 text-red-800 rounded-xl p-4 flex items-center border border-red-200 shadow-sm" x-data="{ show: true }" x-show="show">
            <i class="fa-solid fa-circle-xmark text-red-500 mr-3 text-lg"></i>
            <span class="flex-1 text-sm font-medium">{{ session('error') }}</span>
            <button @click="show = false" class="text-red-600 hover:text-red-900 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200 shadow-sm">
            <div class="font-bold mb-2 flex items-center"><i class="fa-solid fa-triangle-exclamation mr-2 text-red-500"></i> Vui lòng kiểm tra lại dữ liệu:</div>
            <ul class="list-disc list-inside space-y-1 ml-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.faqs.update', $faq->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Cột chính (Câu hỏi & Trả lời) -->
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Câu hỏi (Question) <span class="text-red-500">*</span></label>
                        <input type="text" name="question" value="{{ old('question', $faq->question) }}" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-base font-medium outline-none" placeholder="Nhập câu hỏi thường gặp...">
                        @error('question') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung Trả lời (Answer) <span class="text-red-500">*</span></label>
                        <textarea name="answer" rows="8" required
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none font-mono" placeholder="Nhập câu trả lời chi tiết...">{{ old('answer', $faq->answer) }}</textarea>
                        @error('answer') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Cột phụ (Phân loại & Keywords) -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chuyên khoa áp dụng</label>
                        <select name="specialty_id" class="block w-full py-2.5 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                            <option value="">-- Dùng chung (Tất cả chuyên khoa) --</option>
                            @foreach($specialties as $sp)
                                <option value="{{ $sp->id }}" {{ old('specialty_id', $faq->specialty_id) == $sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Gắn FAQ này vào một chuyên khoa cụ thể hoặc để trống nếu là câu hỏi chung.</p>
                        @error('specialty_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Từ khóa nhận diện (Keywords)</label>
                        <textarea name="keywords" rows="3"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="Ví dụ: giờ làm việc, lịch mở cửa, thời gian khám...">{{ old('keywords', $faq->keywords) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Cách nhau bằng dấu phẩy. Giúp Chatbot AI nhận diện được ý định của bệnh nhân tốt hơn.</p>
                        @error('keywords') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center cursor-pointer bg-gray-50 p-3 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                            <div class="relative">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $faq->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </div>
                            <span class="ms-3 text-sm font-medium text-gray-800">Hiển thị FAQ này</span>
                        </label>
                    </div>

                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                        <p class="text-sm font-medium text-blue-900 mb-1"><i class="fa-solid fa-chart-line mr-1"></i> Thống kê hiển thị</p>
                        <p class="text-xs text-blue-700">FAQ này đã được xem <strong class="font-bold text-blue-900">{{ number_format($faq->view_count) }}</strong> lần.</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('admin.faqs.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Hủy bỏ
                </a>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> Cập nhật câu hỏi
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
