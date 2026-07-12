<x-layouts.admin title="Quản lý Thông báo">
<div class="space-y-6" x-data="{ showFilters: false }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Quản lý Thông báo</h1>
            <p class="text-sm text-gray-500 mt-1">Quản lý và theo dõi các thông báo trong hệ thống</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showFilters = !showFilters" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                <i class="fa-solid fa-filter mr-2"></i>Bộ lọc
            </button>
            <a href="{{ route('admin.notifications.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors shadow-sm">
                <i class="fa-solid fa-plus mr-2"></i>Tạo thông báo mới
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div x-show="showFilters" x-collapse>
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
            <!-- Form chứa các bộ lọc để tìm kiếm chiến dịch thông báo cho nhanh -->
            <form action="{{ route('admin.notifications.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Loại thông báo</label>
                    <select name="type" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Tất cả</option>
                        <option value="appointment" {{ request('type') === 'appointment' ? 'selected' : '' }}>Lịch hẹn</option>
                        <option value="result" {{ request('type') === 'result' ? 'selected' : '' }}>Kết quả</option>
                        <option value="system" {{ request('type') === 'system' ? 'selected' : '' }}>Hệ thống</option>
                        <option value="reminder" {{ request('type') === 'reminder' ? 'selected' : '' }}>Nhắc nhở</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái gửi Email</label>
                    <select name="status" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Tất cả</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Hoàn tất</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chưa gửi / Lên lịch</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 transition-colors text-sm font-medium">
                        Lọc kết quả
                    </button>
                    @if(request()->anyFilled(['type', 'status']))
                    <a href="{{ route('admin.notifications.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 transition-colors text-sm font-medium text-center">
                        Xóa
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Alert -->
    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
        <span class="font-medium">Thành công!</span> {{ session('success') }}
    </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Bảng hiển thị thông tin chính của từng chiến dịch trên màn hình máy tính (PC) -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thông tin chiến dịch</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Loại</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Thống kê / Kênh</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($campaigns as $campaign)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900 line-clamp-1" title="{{ $campaign->title }}">{{ $campaign->title }}</div>
                            <div class="text-xs text-gray-500 line-clamp-2 mt-1" title="{{ $campaign->content }}">{{ $campaign->content }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $typeColors = [
                                    'appointment' => 'blue',
                                    'result' => 'green',
                                    'system' => 'gray',
                                    'reminder' => 'yellow'
                                ];
                                $color = $typeColors[$campaign->type] ?? 'gray';
                                $typeLabels = [
                                    'appointment' => 'Lịch hẹn',
                                    'result' => 'Kết quả',
                                    'system' => 'Hệ thống',
                                    'reminder' => 'Nhắc nhở'
                                ];
                                $label = $typeLabels[$campaign->type] ?? 'Khác';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 border border-{{ $color }}-200">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <!-- Thống kê xem đã gửi thành công bao nhiêu email, bao nhiêu người đã đọc trên web -->
                            <div class="text-sm font-medium text-gray-900"><i class="fa-solid fa-users mr-1"></i> {{ number_format($campaign->total_recipients) }} người nhận</div>
                            <div class="mt-1 flex flex-col gap-1 items-center">
                                @if($campaign->total_in_web > 0)
                                    <span class="text-xs text-gray-500">
                                        <i class="fa-regular fa-bell text-blue-500"></i> Web: {{ $campaign->read_in_web_count }}/{{ $campaign->total_in_web }} đã đọc
                                    </span>
                                @endif
                                @if($campaign->total_email > 0)
                                    <span class="text-xs text-gray-500">
                                        <i class="fa-regular fa-envelope text-red-500"></i> Email: {{ $campaign->sent_email_count }}/{{ $campaign->total_email }} đã gửi
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            <div>{{ \Carbon\Carbon::parse($campaign->created_at)->format('d/m/Y H:i') }}</div>
                            @if($campaign->scheduled_at)
                                <div class="text-xs {{ \Carbon\Carbon::parse($campaign->scheduled_at)->isPast() ? 'text-green-600' : 'text-blue-600' }} mt-1">
                                    Lịch: {{ \Carbon\Carbon::parse($campaign->scheduled_at)->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Hiện nút gửi lại nếu có lỗi xảy ra hoặc email chưa được gửi hết -->
                                @if($campaign->total_email > 0 && $campaign->sent_email_count < $campaign->total_email)
                                <form action="{{ route('admin.notifications.resend') }}" method="POST" class="inline-block" onsubmit="return confirm('Bạn có chắc muốn đặt lại trạng thái để gửi lại thông báo chưa gửi thành công trong chiến dịch này?');">
                                    @csrf
                                    <input type="hidden" name="batch_id" value="{{ $campaign->batch_id }}">
                                    <button type="submit" class="text-blue-600 hover:text-blue-900" title="Thử gửi lại các email lỗi">
                                        <i class="fa-solid fa-rotate-right"></i>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.notifications.destroy') }}" method="POST" class="inline-block" onsubmit="return confirm('Xoá chiến dịch này? Hành động này không thể hoàn tác.');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="batch_id" value="{{ $campaign->batch_id }}">
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Xóa">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="mb-3 text-gray-300"><i class="fa-solid fa-bullhorn text-4xl"></i></div>
                            <p>Không có chiến dịch thông báo nào</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Desktop -->
        @if($campaigns->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $campaigns->links() }}
        </div>
        @endif
    </div>

    <!-- Mobile/Tablet List View -->
    <div class="block lg:hidden space-y-4">
        <!-- Giao diện dạng thẻ thu gọn dành cho màn hình điện thoại -->
        @forelse($campaigns as $campaign)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 transition-all hover:shadow-md">
            <div class="flex justify-between items-start mb-3 border-b border-gray-50 pb-3">
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-gray-900 mb-1 leading-snug">{{ $campaign->title }}</h3>
                    <p class="text-xs text-gray-600 line-clamp-2">{{ $campaign->content }}</p>
                </div>
                <div class="flex items-center gap-1 ml-3">
                    @if($campaign->total_email > 0 && $campaign->sent_email_count < $campaign->total_email)
                    <form action="{{ route('admin.notifications.resend') }}" method="POST" class="inline-block" onsubmit="return confirm('Gửi lại các email bị lỗi?');">
                        @csrf
                        <input type="hidden" name="batch_id" value="{{ $campaign->batch_id }}">
                        <button type="submit" class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors" title="Gửi lại">
                            <i class="fa-solid fa-rotate-right"></i>
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('admin.notifications.destroy') }}" method="POST" class="inline-block" onsubmit="return confirm('Xoá chiến dịch này?');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="batch_id" value="{{ $campaign->batch_id }}">
                        <button type="submit" class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors" title="Xóa">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm bg-gray-50 p-3 rounded-lg mb-3">
                <div>
                    <span class="text-gray-500 block text-xs mb-1 font-medium">Loại</span>
                    @php
                        $color = $typeColors[$campaign->type] ?? 'gray';
                        $label = $typeLabels[$campaign->type] ?? 'Khác';
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 border border-{{ $color }}-200">
                        {{ $label }}
                    </span>
                    <div class="mt-2 text-xs font-medium text-gray-900">
                        <i class="fa-solid fa-users mr-1 text-gray-400"></i> {{ number_format($campaign->total_recipients) }} người
                    </div>
                </div>
                <div>
                    <span class="text-gray-500 block text-xs mb-1 font-medium">Tiến độ gửi</span>
                    <div class="space-y-1.5">
                        @if($campaign->total_in_web > 0)
                            <div class="text-xs text-gray-600 font-medium">
                                <i class="fa-regular fa-bell text-blue-500 mr-1"></i> Web: {{ $campaign->read_in_web_count }}/{{ $campaign->total_in_web }}
                            </div>
                        @endif
                        @if($campaign->total_email > 0)
                            <div class="text-xs text-gray-600 font-medium">
                                <i class="fa-regular fa-envelope text-red-500 mr-1"></i> Email: {{ $campaign->sent_email_count }}/{{ $campaign->total_email }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center text-xs font-medium text-gray-500">
                <span class="flex items-center"><i class="fa-regular fa-clock mr-1.5"></i> {{ \Carbon\Carbon::parse($campaign->created_at)->format('d/m/Y H:i') }}</span>
                @if($campaign->scheduled_at)
                    <span class="{{ \Carbon\Carbon::parse($campaign->scheduled_at)->isPast() ? 'text-green-600 bg-green-50' : 'text-blue-600 bg-blue-50' }} flex items-center px-2 py-1 rounded">
                        <i class="fa-regular fa-calendar-check mr-1.5"></i> Lịch: {{ \Carbon\Carbon::parse($campaign->scheduled_at)->format('d/m/Y H:i') }}
                    </span>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-500">
            <div class="mb-3 text-gray-300"><i class="fa-solid fa-bullhorn text-4xl"></i></div>
            <p class="font-medium">Không có chiến dịch thông báo nào</p>
        </div>
        @endforelse

        <!-- Pagination Mobile -->
        @if($campaigns->hasPages())
        <div class="mt-4">
            {{ $campaigns->links() }}
        </div>
        @endif
    </div>

</div>
</x-layouts.admin>
