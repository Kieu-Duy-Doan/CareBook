<x-layouts.admin title="Thêm người dùng mới">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <a href="{{ route('admin.users.index') }}" class="hover:text-blue-600 transition-colors">Người dùng</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <span class="text-gray-800 font-medium">Thêm mới</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Thêm người dùng mới</h2>
            <p class="text-gray-500 mt-1">Hệ thống sẽ tự động khởi tạo hồ sơ (Profile) tương ứng dựa trên vai trò bạn chọn.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 flex items-start gap-3 border border-red-200">
            <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
            <div>
                <p class="font-bold mb-1">Vui lòng kiểm tra lại thông tin:</p>
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" x-data="{ role: '{{ old('role', 'doctor') }}' }">
        @csrf
        
        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Thông tin tài khoản cơ bản</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="VD: Nguyễn Văn A" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="09xxxxxxxxx" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="example@gmail.com" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vai trò <span class="text-red-500">*</span></label>
                <select name="role" x-model="role" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                    <option value="doctor">Bác sĩ</option>
                    <option value="receptionist">Lễ tân</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên đăng nhập <span class="text-red-500">*</span></label>
                <input type="text" name="username" value="{{ old('username') }}" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none font-mono">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu <span class="text-red-500">*</span></label>
                <input type="password" name="password" required minlength="6" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                <p class="text-[10px] text-gray-500 mt-1">Ít nhất 6 ký tự.</p>
            </div>
        </div>

        <!-- DOCTOR PROFILE FIELDS -->
        <div x-show="role === 'doctor'" class="mb-8 p-4 bg-indigo-50 border border-indigo-100 rounded-lg" style="display: none;">
            <h3 class="text-base font-bold text-indigo-900 mb-4 flex items-center gap-2"><i class="fa-solid fa-user-doctor"></i> Thông tin chi tiết Bác sĩ</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-indigo-900 mb-1">Học hàm/Học vị</label>
                    <input type="text" name="academic_title" value="{{ old('academic_title') }}" placeholder="VD: ThS. BS." class="block w-full py-2 px-3 border border-indigo-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-indigo-900 mb-1">Trình độ <span class="text-red-500">*</span></label>
                    <select name="level" class="block w-full py-2 px-3 border border-indigo-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none bg-white">
                        <option value="BS" {{ old('level') === 'BS' ? 'selected' : '' }}>Bác sĩ (BS)</option>
                        <option value="BSCK1" {{ old('level') === 'BSCK1' ? 'selected' : '' }}>Bác sĩ CK1 (BSCK1)</option>
                        <option value="BSCK2" {{ old('level') === 'BSCK2' ? 'selected' : '' }}>Bác sĩ CK2 (BSCK2)</option>
                        <option value="ThS" {{ old('level') === 'ThS' ? 'selected' : '' }}>Thạc sĩ (ThS)</option>
                        <option value="TS" {{ old('level') === 'TS' ? 'selected' : '' }}>Tiến sĩ (TS)</option>
                        <option value="PGS" {{ old('level') === 'PGS' ? 'selected' : '' }}>Phó Giáo sư (PGS)</option>
                        <option value="GS" {{ old('level') === 'GS' ? 'selected' : '' }}>Giáo sư (GS)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-indigo-900 mb-1">Số chứng chỉ hành nghề</label>
                    <input type="text" name="license_number" value="{{ old('license_number') }}" class="block w-full py-2 px-3 border border-indigo-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-indigo-900 mb-1">Số năm kinh nghiệm</label>
                    <input type="number" name="experience_years" value="{{ old('experience_years') }}" min="0" class="block w-full py-2 px-3 border border-indigo-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none bg-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-indigo-900 mb-1">Chuyên môn (Expertise)</label>
                    <input type="text" name="expertise" value="{{ old('expertise') }}" placeholder="VD: Khám và điều trị các bệnh lý tim mạch..." class="block w-full py-2 px-3 border border-indigo-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none bg-white">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-indigo-900 mb-1">Giới thiệu ngắn (Bio)</label>
                    <textarea name="bio" rows="3" class="block w-full py-2 px-3 border border-indigo-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm outline-none bg-white">{{ old('bio') }}</textarea>
                </div>
            </div>
        </div>

        <!-- STAFF PROFILE FIELDS -->
        <div x-show="role === 'receptionist'" class="mb-8 p-4 bg-blue-50 border border-blue-100 rounded-lg" style="display: none;">
            <h3 class="text-base font-bold text-blue-900 mb-4 flex items-center gap-2"><i class="fa-solid fa-user-tie"></i> Thông tin chi tiết Lễ tân</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-blue-900 mb-1">Chức danh <span class="text-red-500">*</span></label>
                    <input type="text" name="position" value="{{ old('position', 'Lễ tân') }}" required class="block w-full py-2 px-3 border border-blue-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-900 mb-1">Phòng ban</label>
                    <input type="text" name="department" value="{{ old('department', 'Phòng Khám Bệnh') }}" class="block w-full py-2 px-3 border border-blue-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-900 mb-1">SĐT Nội bộ</label>
                    <input type="text" name="internal_phone" value="{{ old('internal_phone') }}" placeholder="VD: 101" class="block w-full py-2 px-3 border border-blue-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-900 mb-1">Ngày bắt đầu làm việc</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="block w-full py-2 px-3 border border-blue-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                </div>
            </div>
        </div>

        <div class="mb-6 pt-4 border-t border-gray-100 flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium text-gray-900">Trạng thái kích hoạt</h4>
                <p class="text-xs text-gray-500 mt-0.5">Cho phép người dùng này đăng nhập ngay.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
            </label>
        </div>

        <div class="flex gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.users.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                Huỷ
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Lưu người dùng
            </button>
        </div>
    </form>
</x-layouts.admin>
