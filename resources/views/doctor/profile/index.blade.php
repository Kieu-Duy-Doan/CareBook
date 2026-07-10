<x-layouts.doctor title="Hồ sơ cá nhân">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Hồ sơ cá nhân</h2>
        <p class="text-gray-500 mt-1">Cập nhật thông tin chuyên môn và thông tin liên hệ của bạn</p>
    </div>

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" style="display: none;" class="bg-green-50 text-green-800 p-4 rounded-lg mb-6 flex items-center justify-between border border-green-200">
            <div class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-green-500"></i>{{ session('success') }}</div>
            <button @click="show=false" class="text-green-500 hover:text-green-700"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 text-red-800 p-4 rounded-lg mb-6 border border-red-200">
            <div class="flex items-center gap-2 mb-2 font-bold"><i class="fa-solid fa-triangle-exclamation"></i> Có lỗi xảy ra</div>
            <ul class="list-disc ml-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('doctor.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">Thông tin tài khoản</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2 flex items-center gap-6 mb-4">
                        <div class="shrink-0">
                            @if($user->avatar_url)
                                <img class="h-24 w-24 object-cover rounded-full border border-gray-200" src="{{ $user->avatar_url }}" alt="Avatar">
                            @else
                                <div class="h-24 w-24 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center text-3xl font-bold border border-blue-200">
                                    {{ substr($user->name ?? 'D', 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh đại diện (Avatar)</label>
                            <input type="file" name="avatar" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="mt-1 text-xs text-gray-500">JPG, PNG, GIF. Tối đa 2MB.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên</label>
                        <input type="text" value="{{ $user->name ?? $user->full_name }}" class="block w-full py-2 px-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed text-sm outline-none" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" value="{{ $user->email }}" class="block w-full py-2 px-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed text-sm outline-none" readonly>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu mới</label>
                        <input type="password" name="password" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="Để trống nếu không muốn đổi">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Xác nhận mật khẩu</label>
                        <input type="password" name="password_confirmation" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="Xác nhận mật khẩu mới">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b">Thông tin chuyên môn</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chuyên môn (Expertise)</label>
                        <input type="text" name="expertise" value="{{ old('expertise', $doctorProfile->expertise ?? '') }}" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: Khám nội, siêu âm">
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kinh nghiệm (số năm)</label>
                        <input type="number" name="experience_years" value="{{ old('experience_years', $doctorProfile->experience_years ?? '') }}" min="0" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tiểu sử chuyên môn (Bio)</label>
                        <textarea name="bio" rows="4" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="Giới thiệu về quá trình công tác, thành tựu...">{{ old('bio', $doctorProfile->bio ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</x-layouts.doctor>
