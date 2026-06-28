<x-layouts.admin title="Quản lý Chuyên khoa">
    
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Quản lý Chuyên khoa</h2>
            <p class="text-gray-500 mt-1">Danh sách chuyên khoa bệnh viện</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.specialties.export', request()->query()) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                <i class="fa-solid fa-file-export"></i> Export Excel
            </a>
            <button type="button" onclick="document.getElementById('importSpecialtyModal').showModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <i class="fa-solid fa-file-excel"></i> Import Excel
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 flex items-center gap-3 border border-green-200">
            <i class="fa-solid fa-circle-check text-green-500"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 flex items-center gap-3 border border-red-200">
            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-6">
        <div class="w-full lg:w-2/3">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Thứ tự</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên chuyên khoa</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Số bác sĩ</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Số phòng</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($specialties as $specialty)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <!-- Thứ tự -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <input type="number" value="{{ $specialty->display_order }}" min="0"
                                        onblur="updateOrder({{ $specialty->id }}, this.value)"
                                        class="w-16 px-2 py-1 text-center text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                                </td>

                                <!-- Tên -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        @if($specialty->image_url)
                                            <div class="flex-shrink-0 h-10 w-10 mr-4 rounded-lg overflow-hidden border border-gray-100 bg-gray-50 flex items-center justify-center">
                                                <img src="{{ asset('storage/' . $specialty->image_url) }}" alt="{{ $specialty->name }}" class="h-full w-full object-cover">
                                            </div>
                                        @else
                                            <div class="flex-shrink-0 h-10 w-10 mr-4 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center border border-blue-100">
                                                <i class="fa-solid fa-stethoscope text-xl"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('admin.specialties.show', $specialty->id) }}" class="text-sm font-bold text-blue-600 hover:text-blue-800 transition-colors">{{ $specialty->name }}</a>
                                            @if($specialty->description)
                                                <div class="text-xs text-gray-500 mt-1 line-clamp-1" title="{{ $specialty->description }}">{{ $specialty->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Số bác sĩ -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                        <i class="fa-solid fa-user-doctor mr-1.5"></i> {{ $specialty->doctors_count }}
                                    </span>
                                </td>

                                <!-- Số phòng -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
                                        <i class="fa-solid fa-door-open mr-1.5"></i> {{ $specialty->rooms_count }}
                                    </span>
                                </td>

                                <!-- Trạng thái -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($specialty->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            Hiển thị
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                            Đã ẩn
                                        </span>
                                    @endif
                                </td>

                                <!-- Thao tác -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-3">
                                        <button @click="$dispatch('edit-specialty', { 
                                            id: {{ $specialty->id }}, 
                                            name: '{{ addslashes($specialty->name) }}', 
                                            description: '{{ addslashes($specialty->description) }}', 
                                            display_order: {{ $specialty->display_order }}, 
                                            is_active: {{ $specialty->is_active ? 'true' : 'false' }} 
                                        })" class="text-blue-600 hover:text-blue-900 transition-colors" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        
                                        <form action="{{ route('admin.specialties.toggle-active', $specialty->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-gray-500 hover:text-gray-800 transition-colors" title="{{ $specialty->is_active ? 'Ẩn chuyên khoa' : 'Hiện chuyên khoa' }}">
                                                <i class="fa-solid {{ $specialty->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.specialties.destroy', $specialty->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                onclick="return confirm('Bạn có chắc muốn xoá chuyên khoa này? Các dữ liệu liên quan có thể bị ảnh hưởng.')"
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
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fa-solid fa-stethoscope text-2xl text-gray-400"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900">Chưa có chuyên khoa nào</h3>
                                        <p class="text-sm mt-1 text-gray-500">Hãy thêm mới ở khung bên phải.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($specialties->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-white">
                    {{ $specialties->links() }}
                </div>
                @endif
            </div>
        </div>

        <div class="w-full lg:w-1/3">
            <div x-data="{
                mode: 'create',
                form: { id: null, name: '', description: '', display_order: 0, is_active: true },
                fillForm(specialty) {
                    this.mode = 'edit';
                    this.form = { 
                        id: specialty.id, 
                        name: specialty.name, 
                        description: specialty.description, 
                        display_order: specialty.display_order, 
                        is_active: specialty.is_active 
                    };
                    if (this.$refs.imageInput) {
                        this.$refs.imageInput.value = '';
                    }
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                resetForm() {
                    this.mode = 'create';
                    this.form = { id: null, name: '', description: '', display_order: 0, is_active: true };
                    if (this.$refs.imageInput) {
                        this.$refs.imageInput.value = '';
                    }
                }
            }" @edit-specialty.window="fillForm($event.detail)">
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden sticky top-24">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900" x-text="mode === 'create' ? 'Thêm chuyên khoa' : 'Chỉnh sửa chuyên khoa'"></h3>
                        <button x-show="mode === 'edit'" @click="resetForm()" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Thêm mới</button>
                    </div>
                    <div class="p-6">
                        <form x-bind:action="mode === 'create' ? '{{ route('admin.specialties.store') }}' : '{{ url('admin/specialties') }}/' + form.id" method="POST" enctype="multipart/form-data">
                            @csrf
                            <template x-if="mode === 'edit'">
                                <input type="hidden" name="_method" value="PUT">
                            </template>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên chuyên khoa <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" x-model="form.name" required
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: Nội tim mạch">
                                    @error('name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ảnh đại diện <span class="text-xs text-gray-400 font-normal">(Tùy chọn)</span></label>
                                    <input type="file" name="image" x-ref="imageInput" accept="image/*"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    <div class="text-[10px] text-gray-500 mt-1">Định dạng: JPG, PNG, SVG (Max 2MB). Ảnh mới sẽ ghi đè ảnh cũ nếu cập nhật.</div>
                                    @error('image')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả ngắn</label>
                                    <textarea name="description" x-model="form.description" rows="3"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="Chức năng, đối tượng phục vụ..."></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Thứ tự hiển thị</label>
                                    <input type="number" name="display_order" x-model="form.display_order" min="0"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="0">
                                </div>

                                <div>
                                    <label class="flex items-center cursor-pointer">
                                        <div class="relative">
                                            <input type="checkbox" name="is_active" x-model="form.is_active" class="sr-only" value="1">
                                            <div class="block bg-gray-200 w-10 h-6 rounded-full transition-colors" :class="form.is_active ? 'bg-blue-600' : 'bg-gray-200'"></div>
                                            <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform" :class="form.is_active ? 'transform translate-x-4' : ''"></div>
                                        </div>
                                        <div class="ml-3 text-sm font-medium text-gray-700">Trạng thái hiển thị</div>
                                    </label>
                                </div>
                            </div>

                            <div class="mt-6 flex items-center justify-end gap-3">
                                <button type="button" @click="resetForm()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    Đặt lại
                                </button>
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fa-solid fa-save mr-1"></i> Lưu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateOrder(id, order) {
            fetch(`{{ url('admin/specialties') }}/${id}/order`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ display_order: order })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    // Optional: show a small toast notification here
                }
            })
            .catch(err => console.error(err));
        }
    </script>

    <!-- Import Modal (Native Dialog) -->
    <dialog id="importSpecialtyModal" class="p-0 rounded-lg shadow-xl bg-white backdrop:bg-gray-500 backdrop:bg-opacity-75 w-full max-w-lg mx-auto my-auto">
        <form action="{{ route('admin.specialties.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fa-solid fa-file-excel text-green-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Import Chuyên khoa từ Excel
                        </h3>
                        <div class="mt-2 space-y-4">
                            <p class="text-sm text-gray-500">
                                Tải lên file Excel (.xlsx, .xls) hoặc CSV để thêm hàng loạt chuyên khoa vào hệ thống. (Tối đa 10MB)
                            </p>
                            
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.specialties.download-template') }}" class="text-sm text-purple-600 hover:text-purple-700 font-medium">
                                    <i class="fa-solid fa-download mr-1"></i> Tải xuống file mẫu
                                </a>
                            </div>

                            <div class="text-left">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Chọn file</label>
                                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 border border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Thực hiện Import
                </button>
                <button type="button" onclick="document.getElementById('importSpecialtyModal').close()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Huỷ
                </button>
            </div>
        </form>
    </dialog>
</x-layouts.admin>
