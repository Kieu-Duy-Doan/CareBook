<x-layouts.receptionist>
    <x-slot:title>Thông tin cá nhân</x-slot:title>

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Thông tin cá nhân</h2>
        <p class="text-gray-500 text-sm mt-1">Quản lý và cập nhật hồ sơ cá nhân, mật khẩu.</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden w-full">
        <div class="p-6">
            <form action="{{ route('receptionist.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
                        <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        @error('full_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        @error('phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <hr class="border-gray-200">

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Đổi mật khẩu</h3>
                        <p class="text-sm text-gray-500 mb-4">Để trống nếu bạn không muốn đổi mật khẩu.</p>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu hiện tại</label>
                                <input type="password" name="current_password"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                @error('current_password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
                                <input type="password" name="password"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                                @error('password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu mới</label>
                                <input type="password" name="password_confirmation"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="bg-emerald-600 text-white font-medium py-2 px-6 rounded-lg hover:bg-emerald-700 transition-colors">
                            Cập nhật thông tin
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.receptionist>