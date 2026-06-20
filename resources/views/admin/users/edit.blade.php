<x-layouts.admin title="Chỉnh sửa quản trị viên">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">
                Chỉnh sửa quản trị viên: {{ $user->full_name }}
            </h2>
            <a href="{{ route('admin.users.show', $user->id) }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>

        @if($errors->any())
            <div class="p-4 bg-red-50 text-red-600 rounded-lg border border-red-200">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Họ và tên -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
                        <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" required
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition shadow-sm text-sm" placeholder="Nhập họ và tên">
                    </div>

                    <!-- Số điện thoại -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition shadow-sm text-sm" placeholder="Nhập số điện thoại">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition shadow-sm text-sm" placeholder="Nhập địa chỉ email (không bắt buộc)">
                    </div>

                    <!-- CCCD/CMND -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CCCD/CMND</label>
                        <input type="text" name="id_card" value="{{ old('id_card', $user->id_card) }}"
                            class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition shadow-sm text-sm" placeholder="Số CCCD hoặc CMND">
                    </div>

                    <!-- Mật khẩu mới -->
                    <div class="md:col-span-2 border-t pt-4 mt-2">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Đổi mật khẩu</h3>
                        <p class="text-sm text-gray-500 mb-4">Nếu không muốn đổi mật khẩu, vui lòng bỏ trống trường này.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
                                <input type="password" name="password"
                                    class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition shadow-sm text-sm" placeholder="Nhập mật khẩu mới">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
