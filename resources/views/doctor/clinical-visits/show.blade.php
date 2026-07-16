<x-layouts.doctor title="Giám sát lâm sàng & Thanh toán">
    <!-- Breadcrumb & Actions -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('doctor.clinical-visits.index') }}" class="hover:text-blue-600 transition-colors">Giám sát lâm sàng</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <span class="text-gray-800 font-medium">Chi tiết LH: {{ $appointment->appointment_code }}</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Chi tiết khám & Thanh toán</h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('doctor.appointments.show', $appointment->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" style="display: none;" class="bg-green-50 text-green-800 p-4 rounded-lg mb-6 flex items-center justify-between border border-green-200">
            <div class="flex items-center gap-3"><i class="fa-solid fa-circle-check text-green-500"></i>{{ session('success') }}</div>
            <button @click="show=false" class="text-green-500 hover:text-green-700"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif
    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" style="display: none;" class="bg-red-50 text-red-800 p-4 rounded-lg mb-6 flex items-center justify-between border border-red-200">
            <div class="flex items-center gap-3"><i class="fa-solid fa-circle-exclamation text-red-500"></i>{{ session('error') }}</div>
            <button @click="show=false" class="text-red-500 hover:text-red-700"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Danh sách quá trình khám -->
        <div class="lg:col-span-2 space-y-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Danh sách các phòng khám theo lịch</h3>
            @foreach ($appointment->clinicalVisits as $visit)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="text-lg font-bold text-gray-900">{{ $visit->room->name ?? 'Phòng khám' }}</h4>
                            <p class="text-sm text-gray-500">Trạng thái: 
                                @if($visit->status == 'waiting') <span class="text-yellow-600 font-medium">Chờ khám</span>
                                @elseif($visit->status == 'in_progress') <span class="text-blue-600 font-medium">Đang khám</span>
                                @elseif($visit->status == 'completed') <span class="text-green-600 font-medium">Hoàn thành</span>
                                @else <span class="text-gray-600 font-medium">{{ $visit->status }}</span> @endif
                            </p>
                        </div>
                        <div class="text-right pr-10">
                            <span class="text-sm text-gray-500 block mb-1">Chi phí (VNĐ)</span>
                            <span class="font-mono font-bold text-lg text-red-600">{{ number_format($visit->payment_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <form action="{{ route('doctor.clinical-visits.update', $visit->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm text-gray-500 mb-1">Cập nhật trạng thái</label>
                                <select name="status" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                    <option value="waiting" {{ $visit->status == 'waiting' ? 'selected' : '' }}>Chờ khám</option>
                                    <option value="in_progress" {{ $visit->status == 'in_progress' ? 'selected' : '' }}>Đang khám</option>
                                    <option value="completed" {{ $visit->status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-500 mb-1">Chi phí khám (VNĐ)</label>
                                <input type="number" name="payment_amount" value="{{ $visit->payment_amount }}" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm text-gray-500 mb-1">Ghi chú lâm sàng</label>
                            <textarea name="findings" rows="3" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">{{ $visit->findings }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm text-gray-500 mb-1">Tải lên file kết quả (Có thể chọn nhiều file)</label>
                            <input type="file" name="result_files[]" multiple class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                            
                            @if($visit->result_files && is_array($visit->result_files) && count($visit->result_files) > 0)
                                <div class="mt-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                    <h5 class="text-xs font-semibold text-gray-700 mb-2">Các file kết quả đã tải lên:</h5>
                                    <ul class="space-y-2">
                                        @foreach($visit->result_files as $file)
                                            <li class="flex items-center gap-2">
                                                <i class="fa-solid fa-file-medical text-blue-500"></i>
                                                <a href="{{ is_array($file) ? asset('storage/' . ($file['path'] ?? '')) : asset('storage/' . $file) }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 hover:underline truncate max-w-full">
                                                    {{ is_array($file) ? ($file['name'] ?? 'File đính kèm') : basename($file) }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">Cập nhật lượt khám này</button>
                        </div>
                    </form>
                    @if(!$visit->is_origin)
                    <form action="{{ route('doctor.clinical-visits.destroy-visit', $visit->id) }}" method="POST" class="absolute top-6 right-6" onsubmit="return confirm('Bạn có chắc muốn xoá lượt khám này?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 w-8 h-8 flex items-center justify-center rounded-full transition"><i class="fa-solid fa-trash-can text-sm"></i></button>
                    </form>
                    @endif
                </div>
            @endforeach
            
            <!-- Form Thêm lượt khám -->
            <div class="bg-blue-50 rounded-xl border border-blue-100 p-6 mt-6">
                <h4 class="text-md font-bold text-blue-900 mb-4 flex items-center gap-2"><i class="fa-solid fa-plus-circle"></i> Chỉ định thêm phòng khám / dịch vụ</h4>
                <form action="{{ route('doctor.clinical-visits.store-visit', $appointment->id) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Chọn phòng / dịch vụ <span class="text-red-500">*</span></label>
                            <select name="room_id" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                <option value="">-- Chọn phòng --</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Chi phí dự kiến (VNĐ)</label>
                            <input type="number" name="payment_amount" value="0" min="0" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-gray-700 mb-1">Ghi chú yêu cầu</label>
                        <input type="text" name="notes" placeholder="VD: Siêu âm ổ bụng, xét nghiệm máu..." class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Thêm vào danh sách
                    </button>
                </form>
            </div>
        </div>

        <!-- Thanh toán tổng & Thông tin bệnh nhân -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-4">Thông tin bệnh nhân</h3>
                <h4 class="text-lg font-bold text-gray-900">{{ $appointment->patientProfile->full_name }}</h4>
                <div class="text-sm text-gray-600 mt-2 space-y-1">
                    <p>SĐT: <span class="font-medium text-gray-900">{{ $appointment->patientProfile->phone ?? '—' }}</span></p>
                    <p>BHYT: <span class="font-medium text-gray-900">{{ $appointment->patientProfile->health_insurance_number ?? '—' }}</span></p>
                    <p>Lý do: <span class="font-medium text-gray-900">{{ $appointment->reason }}</span></p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-4 mb-4">Thanh toán dịch vụ</h3>
                
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600 text-sm">Tổng chi phí:</span>
                    <span class="font-bold text-gray-900">{{ number_format($totalAmount, 0, ',', '.') }} đ</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600 text-sm">Đã thanh toán:</span>
                    <span class="font-bold text-green-600">{{ number_format($paidAmount, 0, ',', '.') }} đ</span>
                </div>
                <div class="flex justify-between items-center mb-4 pt-2 border-t border-gray-100">
                    <span class="text-gray-900 font-bold text-sm">Còn nợ:</span>
                    <span class="font-bold text-red-600 text-xl">{{ number_format($unpaidAmount, 0, ',', '.') }} đ</span>
                </div>

                @if ($unpaidAmount > 0)
                    <div class="bg-amber-50 p-3 rounded-lg mb-4 text-amber-800 text-sm border border-amber-200">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i> Bệnh nhân cần thanh toán khoản phí này để tiếp tục khám các phòng ban khác.
                    </div>
                    
                    <a href="{{ route('doctor.payments.checkout', $appointment->id) }}" class="w-full inline-flex justify-center items-center bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg text-sm font-medium transition-colors shadow-sm mb-3">
                        <i class="fa-solid fa-qrcode mr-2 text-lg"></i> Tạo mã QR Thanh toán
                    </a>
                    
                    <div class="text-center text-sm text-gray-500">
                        hoặc <span class="font-semibold text-gray-700">hướng dẫn bệnh nhân ra quầy lễ tân</span> để thanh toán bằng tiền mặt.
                    </div>
                @else
                    <div class="bg-green-50 text-green-700 p-3 rounded-lg text-center font-medium border border-green-200">
                        <i class="fa-solid fa-check-circle mr-1"></i> Đã thanh toán đầy đủ
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.doctor>
