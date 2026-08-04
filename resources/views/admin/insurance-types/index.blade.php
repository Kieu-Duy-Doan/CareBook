<x-layouts.admin :title="'Cấu hình BHYT'">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Cấu hình Bảo hiểm Y tế</h1>
                <p class="text-sm text-gray-500 mt-1">Quản lý mã đầu thẻ BHYT và tỷ lệ chi trả tương ứng</p>
            </div>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fa-solid fa-plus"></i>
                Thêm loại BHYT
            </button>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition
            class="bg-green-50 text-green-800 p-4 rounded-lg flex items-center justify-between border border-green-200">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-green-500"></i>
                {{ session('success') }}
            </div>
            <button @click="show=false" class="text-green-500 hover:text-green-700"><i class="fa-solid fa-xmark"></i></button>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 text-red-800 p-4 rounded-lg border border-red-200">
            <div class="flex items-center gap-3 mb-2">
                <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                <span class="font-medium">Có lỗi xảy ra:</span>
            </div>
            <ul class="list-disc list-inside text-sm space-y-1 ml-6">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Info Banner --}}
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex gap-3">
                <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1">Hướng dẫn sử dụng</p>
                    <ul class="list-disc list-inside space-y-1 text-blue-700">
                        <li><strong>Mã đầu thẻ:</strong> 2 ký tự đầu tiên trên thẻ BHYT của bệnh nhân (vd: TE, HT, DN...)</li>
                        <li><strong>Tỷ lệ chi trả:</strong> Phần trăm BHYT chi trả cho tổng chi phí khám chữa bệnh</li>
                        <li>Khi thông tư/luật BHYT thay đổi tỷ lệ, chỉ cần cập nhật tại đây mà không cần sửa mã nguồn</li>
                        <li>Các hóa đơn đã thanh toán trước đó <strong>không bị ảnh hưởng</strong> bởi thay đổi tỷ lệ mới</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mã đầu thẻ</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên loại BHYT</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Tỷ lệ chi trả</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Cập nhật lần cuối</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($insuranceTypes as $type)
                        <tr class="hover:bg-gray-50 transition-colors {{ !$type->is_active ? 'opacity-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                    {{ $type->prefix }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $type->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-semibold
                                    {{ $type->coverage_percent == 100 ? 'bg-green-100 text-green-800' : ($type->coverage_percent >= 95 ? 'bg-teal-100 text-teal-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $type->coverage_percent }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $type->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $type->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                    {{ $type->is_active ? 'Đang hoạt động' : 'Đã vô hiệu' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs text-gray-500">
                                {{ $type->updated_at->format('H:i d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="openEditModal({{ $type->id }}, '{{ $type->prefix }}', '{{ addslashes($type->name) }}', {{ $type->coverage_percent }})"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Sửa">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <form action="{{ route('admin.insurance-types.toggle-active', $type->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-2 {{ $type->is_active ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition-colors"
                                            title="{{ $type->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}">
                                            <i class="fa-solid {{ $type->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.insurance-types.destroy', $type->id) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa loại BHYT {{ $type->prefix }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Xóa">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fa-solid fa-shield-halved text-4xl text-gray-300 mb-3 block"></i>
                                <p class="text-sm">Chưa có loại BHYT nào được cấu hình.</p>
                                <p class="text-xs text-gray-400 mt-1">Bấm nút "Thêm loại BHYT" để bắt đầu.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Thêm mới --}}
    <div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50" onclick="this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4" onclick="event.stopPropagation()">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Thêm loại BHYT mới</h3>
                <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form action="{{ route('admin.insurance-types.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mã đầu thẻ <span class="text-red-500">*</span></label>
                    <input type="text" name="prefix" maxlength="5" placeholder="VD: TE, HT, DN..."
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none uppercase"
                        style="text-transform: uppercase;" value="{{ old('prefix') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên loại BHYT <span class="text-red-500">*</span></label>
                    <input type="text" name="name" placeholder="VD: Trẻ em dưới 6 tuổi"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none"
                        value="{{ old('name') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tỷ lệ chi trả (%) <span class="text-red-500">*</span></label>
                    <input type="number" name="coverage_percent" min="0" max="100" placeholder="VD: 80, 95, 100"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none"
                        value="{{ old('coverage_percent') }}">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="flex-1 py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                        Hủy
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Thêm mới
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Sửa --}}
    <div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50" onclick="this.classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4" onclick="event.stopPropagation()">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Chỉnh sửa loại BHYT</h3>
                <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="editForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mã đầu thẻ <span class="text-red-500">*</span></label>
                    <input type="text" name="prefix" id="editPrefix" maxlength="5"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none uppercase"
                        style="text-transform: uppercase;">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên loại BHYT <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="editName"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tỷ lệ chi trả (%) <span class="text-red-500">*</span></label>
                    <input type="number" name="coverage_percent" id="editPercent" min="0" max="100"
                        class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                        class="flex-1 py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                        Hủy
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, prefix, name, percent) {
            document.getElementById('editForm').action = '/admin/insurance-types/' + id;
            document.getElementById('editPrefix').value = prefix;
            document.getElementById('editName').value = name;
            document.getElementById('editPercent').value = percent;
            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>
</x-layouts.admin>
