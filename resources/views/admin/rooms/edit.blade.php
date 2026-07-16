
<x-layouts.admin title="Chỉnh sửa Phòng khám">
    <div class="mb-6">
        <a href="{{ route('admin.rooms.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition-colors mb-2 inline-block">
            <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại Danh sách
        </a>
        <h2 class="text-2xl font-bold text-gray-900 mt-2">Chỉnh sửa Phòng khám</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.rooms.update', $room->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="px-6 py-6 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" x-data="{ roomType: '{{ old('room_type', $room->room_type) }}' }">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên phòng <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $room->name) }}" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: Phòng khám Nội 1">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Số phòng</label>
                        <input type="text" name="room_number" value="{{ old('room_number', $room->room_number) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: P101">
                        @error('room_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Loại phòng <span class="text-red-500">*</span></label>
                        <select name="room_type" x-model="roomType" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                            <option value="">-- Chọn loại phòng --</option>
                            <option value="examination">Phòng khám</option>
                            <option value="diagnostic">Cận lâm sàng</option>
                            <option value="surgery">Phẫu thuật</option>
                            <option value="other">Khác</option>
                        </select>
                        @error('room_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Ô nhập giá tiền (chỉ hiện khi room_type là diagnostic) -->
                    <div x-show="roomType === 'diagnostic'" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Giá tiền dịch vụ (VNĐ) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price', $room->price) }}" min="0" :required="roomType === 'diagnostic'"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: 200000">
                        @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Toà nhà</label>
                        <select name="building" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                            <option value="">Không xác định</option>
                            <option value="Nhà K1" {{ old('building', $room->building) == 'Nhà K1' ? 'selected' : '' }}>Nhà K1</option>
                            <option value="Nhà K2" {{ old('building', $room->building) == 'Nhà K2' ? 'selected' : '' }}>Nhà K2</option>
                            <option value="Nhà K3" {{ old('building', $room->building) == 'Nhà K3' ? 'selected' : '' }}>Nhà K3</option>
                            <option value="Khác" {{ old('building', $room->building) == 'Khác' ? 'selected' : '' }}>Khác</option>
                        </select>
                        @error('building') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tầng</label>
                        <input type="text" name="floor" value="{{ old('floor', $room->floor) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: 1">
                        @error('floor') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sức chứa (người)</label>
                        <input type="number" name="capacity" value="{{ old('capacity', $room->capacity) }}" min="1" max="200"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: 5">
                        @error('capacity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <div class="mt-2">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $room->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-700">Đang hoạt động</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Chuyên khoa phục vụ <span class="text-xs text-gray-400 font-normal">(Bỏ trống nếu phục vụ chung)</span></label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @php
                            $selectedIds = old('specialty_ids', $room->specialties->pluck('id')->toArray());
                        @endphp
                        @foreach($specialties as $sp)
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="specialty_ids[]" value="{{ $sp->id }}" 
                                    {{ in_array($sp->id, $selectedIds) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">{{ $sp->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.rooms.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Huỷ
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                    <i class="fa-solid fa-save mr-2"></i> Lưu cập nhật
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
