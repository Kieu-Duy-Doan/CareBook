<x-layouts.admin title="Quản lý Lịch làm việc">
    <!-- Alpine.js state for Modals -->
    <div x-data="{
        openSchedule: false,
        scheduleMode: 'create',
        scheduleForm: {
            id: null,
            doctor_profile_id: '',
            room_id: '',
            day_of_week: '',
            start_time: '',
            end_time: '',
            slot_duration_minutes: 15,
            max_slots: 2,
            is_active: true
        },
        fillSchedule(data) {
            this.scheduleMode = 'edit';
            this.scheduleForm = { ...data };
            this.openSchedule = true;
        },
        get previewSlots() {
            if (!this.scheduleForm.start_time || !this.scheduleForm.end_time || !this.scheduleForm.slot_duration_minutes) return null;
            const start = this.scheduleForm.start_time.split(':').map(Number);
            const end = this.scheduleForm.end_time.split(':').map(Number);
            const startMin = start[0] * 60 + start[1];
            const endMin = end[0] * 60 + end[1];
            const duration = parseInt(this.scheduleForm.slot_duration_minutes);
            if (endMin <= startMin || duration <= 0) return null;
            const slots = Math.floor((endMin - startMin) / duration);
            return { slots, start: this.scheduleForm.start_time, end: this.scheduleForm.end_time };
        },
    
        openOverride: false,
        overrideType: 'close'
    }">

        <!-- Session Alerts -->
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" style="display: none;"
                class="bg-green-50 text-green-800 p-4 rounded-lg mb-6 flex items-center justify-between border border-green-200">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-green-500"></i>
                    {{ session('success') }}
                </div>
                <button @click="show=false" class="text-green-500 hover:text-green-700"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 text-red-800 p-4 rounded-lg mb-6 flex items-center gap-3 border border-red-200">
                <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                {{ session('error') }}
            </div>
        @endif

        @if (session('warning'))
            <div
                class="bg-yellow-50 text-yellow-800 p-4 rounded-lg mb-6 flex items-center gap-3 border border-yellow-200">
                <i class="fa-solid fa-triangle-exclamation text-yellow-500"></i>
                {{ session('warning') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Quản lý Lịch làm việc</h2>
                <p class="text-gray-500 mt-1">Sắp xếp ca trực định kỳ và thiết lập ngoại lệ</p>
            </div>
            <div class="flex gap-3">
                <button @click="openOverride = true; overrideType = 'close'"
                    class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 border border-yellow-300 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> Thêm ngoại lệ
                </button>
                <button @click="openSchedule = true; scheduleMode = 'create'; scheduleForm.id = null"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Thêm ca trực
                </button>
            </div>
        </div>

        <!-- PHẦN 1: BỘ LỌC -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
            <form action="{{ route('admin.work-schedules.index') }}" method="GET"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <select name="doctor_id"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        <option value="">Tất cả bác sĩ</option>
                        @foreach ($doctors as $doc)
                            <option value="{{ $doc->id }}"
                                {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>{{ $doc->full_title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="room_id"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        <option value="">Tất cả phòng</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                {{ $room->name }} {{ $room->room_number ? '(' . $room->room_number . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="day_of_week"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        <option value="">Tất cả các ngày</option>
                        <option value="2" {{ request('day_of_week') == 2 ? 'selected' : '' }}>Thứ Hai (2)</option>
                        <option value="3" {{ request('day_of_week') == 3 ? 'selected' : '' }}>Thứ Ba (3)</option>
                        <option value="4" {{ request('day_of_week') == 4 ? 'selected' : '' }}>Thứ Tư (4)</option>
                        <option value="5" {{ request('day_of_week') == 5 ? 'selected' : '' }}>Thứ Năm (5)</option>
                        <option value="6" {{ request('day_of_week') == 6 ? 'selected' : '' }}>Thứ Sáu (6)</option>
                        <option value="7" {{ request('day_of_week') == 7 ? 'selected' : '' }}>Thứ Bảy (7)</option>
                        <option value="1" {{ request('day_of_week') == 1 ? 'selected' : '' }}>Chủ Nhật (1)
                        </option>
                    </select>
                </div>
                <div>
                    <select name="status"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Đang hoạt động
                        </option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Tạm ngưng</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="w-full sm:w-auto bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Lọc
                    </button>
                    <a href="{{ route('admin.work-schedules.index') }}"
                        class="w-full sm:w-auto text-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Đặt lại
                    </a>
                </div>
            </form>
        </div>

        <!-- PHẦN 2: BẢNG LỊCH TUẦN -->
        <h3 class="text-lg font-bold text-gray-900 mb-4"><i class="fa-regular fa-calendar-days text-blue-500 mr-2"></i>
            Lịch tuần định kỳ</h3>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Bác sĩ</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Phòng khám</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Thứ</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Giờ khám</th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Số slot trong 1 ca khám</th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Thời lượng</th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Trạng thái</th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($schedules as $schedule)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div
                                                class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                                {{ $schedule->doctor->user->avatar_initials }}
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $schedule->doctor->full_title }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $schedule->room->name }}</div>
                                    @if ($schedule->room->room_number)
                                        <span
                                            class="inline-flex mt-1 items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                            {{ $schedule->room->room_number }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-sm text-gray-900">
                                    {{ $schedule->day_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <i class="fa-regular fa-clock text-gray-400 mr-1"></i> {{ $schedule->time_range }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                    {{ $schedule->max_slots }} slot
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                    {{ $schedule->slot_duration_minutes }} phút
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($schedule->is_active)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            Hoạt động
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                            Tạm ngưng
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.work-schedules.show', $schedule->id) }}"
                                            class="text-blue-600 hover:text-blue-900 transition-colors"
                                            title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <button
                                            @click="fillSchedule({
                                        id: {{ $schedule->id }},
                                        doctor_profile_id: '{{ $schedule->doctor_profile_id }}',
                                        room_id: '{{ $schedule->room_id }}',
                                        day_of_week: '{{ $schedule->day_of_week }}',
                                        start_time: '{{ substr($schedule->start_time, 0, 5) }}',
                                        end_time: '{{ substr($schedule->end_time, 0, 5) }}',
                                        slot_duration_minutes: {{ $schedule->slot_duration_minutes }},
                                        max_slots: {{ $schedule->max_slots }},
                                        is_active: {{ $schedule->is_active ? 'true' : 'false' }}
                                    })"
                                            class="text-blue-600 hover:text-blue-900 transition-colors"
                                            title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>

                                        <form
                                            action="{{ route('admin.work-schedules.toggle-active', $schedule->id) }}"
                                            method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="text-gray-500 hover:text-gray-800 transition-colors"
                                                title="{{ $schedule->is_active ? 'Tạm ngưng' : 'Mở lại' }}">
                                                <i
                                                    class="fa-solid {{ $schedule->is_active ? 'fa-toggle-on text-green-500' : 'fa-toggle-off' }} text-lg"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.work-schedules.destroy', $schedule->id) }}"
                                            method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                onclick="return confirm('Bạn có chắc muốn xoá lịch này?')"
                                                class="text-red-600 hover:text-red-900 transition-colors"
                                                title="Xoá">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fa-solid fa-calendar-xmark text-2xl text-gray-400"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900">Chưa có lịch làm việc nào</h3>
                                        <p class="text-sm mt-1 text-gray-500">Hãy thêm ca trực đầu tiên.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($schedules->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-white">
                    {{ $schedules->links() }}
                </div>
            @endif
        </div>

        <!-- PHẦN 3: NGOẠI LỆ THÁNG HIỆN TẠI -->
        <h3 class="text-lg font-bold text-gray-900 mb-4"><i
                class="fa-solid fa-calendar-minus text-yellow-500 mr-2"></i> Ngoại lệ tháng {{ now()->format('m/Y') }}
        </h3>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ngày</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Bác sĩ</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Phòng</th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Loại</th>
                            <th scope="col"
                                class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Giờ</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lý do</th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Người tạo</th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($overrides as $ov)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-sm text-gray-900">
                                    {{ $ov->override_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $ov->doctor->full_title }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $ov->room ? $ov->room->name : 'Tất cả phòng' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($ov->type === 'close')
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                            Nghỉ / Đóng ca
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            Thêm ca
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                    {{ $ov->type === 'extra' ? substr($ov->start_time, 0, 5) . ' - ' . substr($ov->end_time, 0, 5) : '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $ov->reason ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $ov->createdBy->full_name ?? '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="{{ route('admin.work-schedules.overrides.destroy', $ov->id) }}"
                                        method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Bạn có chắc muốn xoá ngoại lệ này?')"
                                            class="text-red-600 hover:text-red-900 transition-colors" title="Xoá">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div
                                            class="h-12 w-12 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fa-solid fa-calendar-check text-xl text-gray-400"></i>
                                        </div>
                                        <p class="text-sm font-medium text-gray-500">Không có ngoại lệ nào trong tháng
                                            này</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL THÊM/SỬA CA TRỰC -->
        <div x-show="openSchedule" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="openSchedule" x-transition.opacity
                    class="fixed inset-0 bg-gray-900/50 transition-opacity" aria-hidden="true"
                    @click="openSchedule = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="openSchedule" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative z-10 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <form
                        x-bind:action="scheduleMode === 'create' ? '{{ route('admin.work-schedules.store') }}' :
                            '{{ url('admin/work-schedules') }}/' + scheduleForm.id"
                        method="POST" x-data="{ loading: false }" @submit="loading = true">
                        @csrf
                        <template x-if="scheduleMode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-xl font-bold text-gray-900"
                                x-text="scheduleMode === 'create' ? 'Thêm ca trực mới' : 'Chỉnh sửa ca trực'"></h3>
                            <button type="button" @click="openSchedule = false"
                                class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fa-solid fa-xmark text-xl"></i>
                            </button>
                        </div>

                        <div class="px-6 py-5 space-y-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bác sĩ <span
                                            class="text-red-500">*</span></label>
                                    <select name="doctor_profile_id" x-model="scheduleForm.doctor_profile_id" required
                                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                        <option value="">-- Chọn bác sĩ --</option>
                                        @foreach ($doctors as $doc)
                                            <option value="{{ $doc->id }}">{{ $doc->full_title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phòng khám <span
                                            class="text-red-500">*</span></label>
                                    <select name="room_id" x-model="scheduleForm.room_id" required
                                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                        <option value="">-- Chọn phòng khám --</option>
                                        @foreach ($rooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->name }}
                                                {{ $room->room_number ? '(' . $room->room_number . ')' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Thứ trong tuần <span
                                            class="text-red-500">*</span></label>
                                    <select name="day_of_week" x-model="scheduleForm.day_of_week" required
                                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                        <option value="">-- Chọn thứ --</option>
                                        <option value="2">Thứ Hai</option>
                                        <option value="3">Thứ Ba</option>
                                        <option value="4">Thứ Tư</option>
                                        <option value="5">Thứ Năm</option>
                                        <option value="6">Thứ Sáu</option>
                                        <option value="7">Thứ Bảy</option>
                                        <option value="1">Chủ Nhật</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Giờ bắt đầu <span
                                            class="text-red-500">*</span></label>
                                    <input type="time" name="start_time" x-model="scheduleForm.start_time"
                                        required
                                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Giờ kết thúc <span
                                            class="text-red-500">*</span></label>
                                    <input type="time" name="end_time" x-model="scheduleForm.end_time" required
                                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Thời lượng slot (phút)
                                        <span class="text-red-500">*</span></label>
                                    <input type="number" name="slot_duration_minutes"
                                        x-model="scheduleForm.slot_duration_minutes" required min="5"
                                        max="120"
                                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Số slot tối đa <span
                                            class="text-red-500">*</span></label>
                                    <input type="number" name="max_slots" x-model="scheduleForm.max_slots" required
                                        min="1" max="100"
                                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="flex items-center cursor-pointer mt-2">
                                        <div class="relative">
                                            <input type="checkbox" name="is_active" x-model="scheduleForm.is_active"
                                                class="sr-only" value="1">
                                            <div class="block bg-gray-200 w-10 h-6 rounded-full transition-colors"
                                                :class="scheduleForm.is_active ? 'bg-blue-600' : 'bg-gray-200'"></div>
                                            <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform"
                                                :class="scheduleForm.is_active ? 'transform translate-x-4' : ''"></div>
                                        </div>
                                        <div class="ml-3 text-sm font-medium"
                                            :class="scheduleForm.is_active ? 'text-green-600' : 'text-gray-500'"
                                            x-text="scheduleForm.is_active ? 'Đang hoạt động' : 'Tạm ngưng'"></div>
                                    </label>
                                </div>
                            </div>

                            <!-- Preview slots -->
                            <div x-show="previewSlots" style="display: none;"
                                class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start gap-3">
                                <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                                <div class="text-sm text-blue-800">
                                    Dự kiến chia được <strong><span
                                            x-text="previewSlots ? previewSlots.slots : 0"></span> slot</strong> khám
                                    trong khoảng thời gian từ <span
                                        x-text="previewSlots ? previewSlots.start : ''"></span> đến <span
                                        x-text="previewSlots ? previewSlots.end : ''"></span>.
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="openSchedule = false"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                Huỷ
                            </button>
                            <button type="submit" :disabled="loading"
                                class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors disabled:opacity-70 flex items-center gap-2">
                                <span x-show="!loading"><i class="fa-solid fa-save mr-1"></i> Lưu ca trực</span>
                                <span x-show="loading" style="display: none;"><i
                                        class="fa-solid fa-spinner fa-spin mr-1"></i> Đang xử lý...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL THÊM NGOẠI LỆ -->
        <div x-show="openOverride" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-override-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="openOverride" x-transition.opacity
                    class="fixed inset-0 bg-gray-900/50 transition-opacity" aria-hidden="true"
                    @click="openOverride = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="openOverride" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative z-10 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <form action="{{ route('admin.work-schedules.overrides.store') }}" method="POST"
                        x-data="{ loading: false }" @submit="loading = true">
                        @csrf

                        <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-xl font-bold text-gray-900" id="modal-override-title">Thêm ngoại lệ lịch
                            </h3>
                            <button type="button" @click="openOverride = false"
                                class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fa-solid fa-xmark text-xl"></i>
                            </button>
                        </div>

                        <div class="px-6 py-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bác sĩ <span
                                        class="text-red-500">*</span></label>
                                <select name="doctor_profile_id" required
                                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-sm outline-none bg-white">
                                    <option value="">-- Chọn bác sĩ --</option>
                                    @foreach ($doctors as $doc)
                                        <option value="{{ $doc->id }}">{{ $doc->full_title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phòng khám <span
                                        class="text-red-500">*</span></label>
                                <select name="room_id" required
                                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-sm outline-none bg-white">
                                    <option value="">--Vui lòng chọn phòng--</option>
                                    @foreach ($rooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }}
                                            {{ $room->room_number ? '(' . $room->room_number . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ngày áp dụng <span
                                        class="text-red-500">*</span></label>
                                <input type="date" name="override_date" required
                                    min="{{ now()->toDateString() }}"
                                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-sm outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Giờ bắt đầu <span
                                        class="text-red-500">*</span></label>
                                <input type="time" name="start_time" :required="overrideType === 'extra'"
                                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Giờ kết thúc <span
                                        class="text-red-500">*</span></label>
                                <input type="time" name="end_time" :required="overrideType === 'extra'"
                                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-sm outline-none">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Loại ngoại lệ <span
                                        class="text-red-500">*</span></label>
                                <div class="flex gap-4">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" name="type" value="close" x-model="overrideType"
                                            class="text-yellow-600 focus:ring-yellow-500 border-gray-300">
                                        <span class="ml-2 text-sm text-gray-700">Nghỉ / Đóng ca</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="radio" name="type" value="extra" x-model="overrideType"
                                            class="text-yellow-600 focus:ring-yellow-500 border-gray-300">
                                        <span class="ml-2 text-sm text-gray-700">Thêm ca ngoài lịch</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lý do ghi chú</label>
                                <textarea name="reason" rows="2"
                                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-sm outline-none"
                                    placeholder="VD: Bác sĩ bận công tác..."></textarea>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="openOverride = false"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                Huỷ
                            </button>
                            <button type="submit" :disabled="loading"
                                class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 transition-colors disabled:opacity-70 flex items-center gap-2">
                                <span x-show="!loading"><i class="fa-solid fa-save mr-1"></i> Lưu ngoại lệ</span>
                                <span x-show="loading" style="display: none;"><i
                                        class="fa-solid fa-spinner fa-spin mr-1"></i> Đang xử lý...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
