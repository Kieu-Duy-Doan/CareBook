<x-layouts.admin title="Chi tiết Chuyên khoa">
    @csrf
    <div class="mb-6">
        <a href="{{ route('admin.specialties.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors mb-2 inline-block">
            <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại Danh sách
        </a>
        <h2 class="text-2xl font-bold text-gray-900 mt-2">Chi tiết Chuyên khoa</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8 flex flex-col md:flex-row gap-6 items-start">
        <div class="flex-shrink-0">
            @if($specialty->image_url)
                <div class="h-32 w-32 rounded-xl overflow-hidden border border-gray-100 bg-gray-50 flex items-center justify-center">
                    <img src="{{ asset('storage/' . $specialty->image_url) }}" alt="{{ $specialty->name }}" class="h-full w-full object-cover">
                </div>
            @else
                <div class="h-32 w-32 rounded-xl bg-blue-50 text-blue-500 flex flex-col items-center justify-center border border-blue-100">
                    <i class="fa-solid fa-stethoscope text-4xl mb-2"></i>
                    <span class="text-xs font-medium">Chưa có ảnh</span>
                </div>
            @endif
        </div>

        <div class="flex-1">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-2xl font-bold text-gray-900">{{ $specialty->name }}</h3>
                @if($specialty->is_active)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200">
                        <i class="fa-solid fa-check-circle mr-1"></i> Đang hoạt động
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                        <i class="fa-solid fa-times-circle mr-1"></i> Đã ẩn
                    </span>
                @endif
            </div>
            
            <p class="text-gray-600 mb-6">{{ $specialty->description ?? 'Chưa có mô tả cho chuyên khoa này.' }}</p>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-1">Thứ tự hiển thị</div>
                    <div class="text-xl font-bold text-gray-900">{{ $specialty->display_order }}</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <div class="text-xs text-blue-600 font-medium uppercase tracking-wider mb-1">Số Bác sĩ</div>
                    <div class="text-xl font-bold text-blue-900">{{ $specialty->doctors->count() }}</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-100">
                    <div class="text-xs text-purple-600 font-medium uppercase tracking-wider mb-1">Số Phòng khám</div>
                    <div class="text-xl font-bold text-purple-900">{{ $specialty->rooms->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8" x-data="specialtyManager()" @notify.window="successMessage = $event.detail.message; setTimeout(() => successMessage = '', 3000)">
        <!-- Success Notification -->
        <div x-show="successMessage" x-transition class="col-span-full mb-6 bg-green-50 text-green-800 rounded-lg p-4 flex items-center border border-green-200" style="display: none;">
            <i class="fa-solid fa-check-circle mr-3 text-lg"></i>
            <span x-text="successMessage" class="text-sm font-medium flex-1"></span>
            <button @click="successMessage = ''" class="text-green-600 hover:text-green-800">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        
    </div>

</x-layouts.admin>