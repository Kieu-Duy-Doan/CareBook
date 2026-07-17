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
                <i class="fa-solid fa-arrow-left"></i> Quay lại lịch hẹn
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
            {{-- ===== VISIT GỐC ===== --}}
            @if ($originVisit)
            <div class="bg-white rounded-xl shadow-sm border-2 {{ $originVisit->status === 'completed' ? 'border-green-300' : 'border-blue-300' }} p-6 relative mb-2">
                <div class="absolute top-4 left-4">
                    <span class="inline-flex items-center gap-1 text-xs font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                        <i class="fa-solid fa-house-medical text-[10px]"></i> Phòng khám chính
                    </span>
                </div>
                <div class="flex justify-between items-start mb-4 pt-6">
                    <div>
                        <h4 class="text-lg font-bold text-gray-900">{{ $originVisit->room->name ?? 'Phòng khám' }}</h4>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Bác sĩ: <span class="font-medium text-gray-700">{{ $originVisit->doctorProfile->user->full_name ?? '—' }}</span>
                        </p>
                        <p class="text-sm mt-1">
                            Trạng thái:
                            @if($originVisit->status === 'waiting') <span class="text-yellow-600 font-medium">Chờ khám</span>
                            @elseif($originVisit->status === 'in_progress') <span class="text-blue-600 font-medium">Đang khám</span>
                            @elseif($originVisit->status === 'completed') <span class="text-green-600 font-medium">Hoàn thành</span>
                            @else <span class="text-gray-600 font-medium">{{ $originVisit->status }}</span> @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-gray-500 block">Chi phí (VNĐ)</span>
                        <span class="font-mono font-bold text-lg text-red-600">{{ number_format($originVisit->payment_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
                @if ($originVisit->findings)
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 mb-3">
                    <p class="text-xs font-semibold text-gray-500 mb-1">Ghi chú khám:</p>
                    <p class="text-sm text-gray-800">{{ $originVisit->findings }}</p>
                </div>
                @endif

                {{-- Form cập nhật cho phòng khám chính --}}
                @if ($isOriginDoctor && !in_array($originVisit->status, ['completed', 'refused']))
                <form action="{{ route('doctor.clinical-visits.update', $originVisit->id) }}" method="POST" class="mt-3 pt-3 border-t border-gray-100">
                    @csrf
                    @method('PUT')
                    <p class="text-xs text-blue-600 font-semibold mb-3"><i class="fa-solid fa-pen-to-square mr-1"></i>Cập nhật phòng khám chính</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="block text-sm text-gray-500 mb-1">Trạng thái</label>
                            <select name="status" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none bg-white">
                                <option value="waiting" {{ $originVisit->status === 'waiting' ? 'selected' : '' }}>Chờ khám</option>
                                <option value="in_progress" {{ $originVisit->status === 'in_progress' ? 'selected' : '' }}>Đang khám</option>
                                <option value="completed" {{ $originVisit->status === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-500 mb-1">Chi phí khám (VNĐ)</label>
                            <input type="number" name="payment_amount" value="{{ $originVisit->payment_amount }}" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none bg-white">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm text-gray-500 mb-1">Ghi chú khám lâm sàng</label>
                        <textarea name="findings" rows="3" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none bg-white">{{ $originVisit->findings }}</textarea>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Cập nhật thông tin
                    </button>
                </form>
                @elseif ($originVisit->status === 'completed')
                <p class="text-xs text-green-600 mt-3 font-medium border-t border-gray-100 pt-3"><i class="fa-solid fa-circle-check mr-1"></i>Phòng khám chính đã hoàn tất.</p>
                @endif
            </div>
            @endif

            {{-- ===== PROGRESS BAR CẪN LÂM SÀNG ===== --}}
            @if ($totalVisits > 0)
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold text-gray-700">Tiến trình cận lâm sàng</span>
                    <span class="text-xs font-bold {{ $allSubCompleted ? 'text-green-600' : 'text-amber-600' }}">
                        {{ $completedVisits }}/{{ $totalVisits }} phòng hoàn thành
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-3">
                    <div class="h-2.5 rounded-full {{ $allSubCompleted ? 'bg-green-500' : 'bg-blue-500' }} transition-all"
                        style="width: {{ round(($completedVisits / $totalVisits) * 100) }}%"></div>
                </div>
                @if ($allSubCompleted)
                <div class="flex items-center gap-2 text-green-700 text-sm font-medium">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Tất cả phòng khám đã hoàn tất — Sẵn sàng ghi kết luận tổng hợp!</span>
                </div>
                @else
                <p class="text-xs text-amber-700"><i class="fa-solid fa-clock mr-1"></i>Đang chờ kết quả từ các phòng cận lâm sàng...</p>
                @endif
            </div>
            @endif

            {{-- ===== DANH SÁCH VISIT CON (CẪN LÂM SÀNG) ===== --}}
            @forelse ($subVisits as $visit)
            <div class="bg-white rounded-xl shadow-sm border {{ $visit->status === 'completed' ? 'border-green-200' : ($visit->status === 'refused' ? 'border-red-200' : 'border-gray-100') }} p-6 relative">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="text-lg font-bold text-gray-900">{{ $visit->room->name ?? 'Phòng khám' }}</h4>
                            @if($visit->status === 'completed')
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Hoàn thành</span>
                            @elseif($visit->status === 'refused')
                            <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">Từ chối</span>
                            @elseif($visit->status === 'in_progress')
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">Đang khám</span>
                            @else
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-medium">Chờ</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500">
                            Bác sĩ phụ trách: <span class="font-medium text-gray-700">{{ $visit->doctorProfile->user->full_name ?? '—' }}</span>
                        </p>
                    </div>
                    <div class="text-right pr-10">
                        <span class="text-sm text-gray-500 block mb-1">Chi phí (VNĐ)</span>
                        <span class="font-mono font-bold text-lg text-red-600">{{ number_format($visit->payment_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- File kết quả đã upload --}}
                @if($visit->result_files && is_array($visit->result_files) && count($visit->result_files) > 0)
                <div class="mb-3 bg-indigo-50 p-3 rounded-lg border border-indigo-200">
                    <h5 class="text-xs font-semibold text-indigo-700 mb-2"><i class="fa-solid fa-file-medical mr-1"></i>File kết quả:</h5>
                    <ul class="space-y-1.5">
                        @foreach($visit->result_files as $file)
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-file text-indigo-400 text-xs"></i>
                            <a href="{{ asset('storage/' . ($file['path'] ?? $file)) }}" target="_blank"
                                class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline truncate">
                                {{ $file['name'] ?? basename($file) }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if($visit->findings)
                <div class="mb-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold text-gray-500 mb-1">Nhận xét / Kết luận:</p>
                    <p class="text-sm text-gray-800">{{ $visit->findings }}</p>
                </div>
                @endif

                {{-- Form cập nhật chỉ hiện cho bác sĩ được giao visit này --}}
                @php
                $currentDoctorId = Auth::user()->doctorProfile->id ?? null;
                $canUpdateThisVisit = $visit->doctor_profile_id === $currentDoctorId;
                @endphp

                @if ($canUpdateThisVisit && !in_array($visit->status, ['completed', 'refused']))
                <form action="{{ route('doctor.clinical-visits.update', $visit->id) }}" method="POST" enctype="multipart/form-data" class="mt-3 pt-3 border-t border-gray-100">
                    @csrf
                    @method('PUT')
                    <p class="text-xs text-blue-600 font-semibold mb-3"><i class="fa-solid fa-pen-to-square mr-1"></i>Cập nhật kết quả khám</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                        <div>
                            <label class="block text-sm text-gray-500 mb-1">Trạng thái</label>
                            <select name="status" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none bg-white">
                                <option value="waiting" {{ $visit->status === 'waiting' ? 'selected' : '' }}>Chờ khám</option>
                                <option value="in_progress" {{ $visit->status === 'in_progress' ? 'selected' : '' }}>Đang khám</option>
                                <option value="completed" {{ $visit->status === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                <option value="refused" {{ $visit->status === 'refused' ? 'selected' : '' }}>Từ chối / Không thực hiện</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-500 mb-1">Chi phí (VNĐ)</label>
                            <input type="number" name="payment_amount" value="{{ $visit->payment_amount }}" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none bg-white">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm text-gray-500 mb-1">Nhận xét lâm sàng / Kết luận</label>
                        <textarea name="findings" rows="3" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none bg-white">{{ $visit->findings }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm text-gray-500 mb-1">Tải lên file kết quả (PDF, hình ảnh)</label>
                        <input type="file" name="result_files[]" multiple class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none bg-white">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Lưu kết quả
                    </button>
                </form>
                @elseif ($visit->status === 'completed')
                <p class="text-xs text-green-600 mt-3 font-medium"><i class="fa-solid fa-circle-check mr-1"></i>Lượt khám này đã hoàn tất.</p>
                @endif

                {{-- Nút xóa (chỉ cho bác sĩ gốc khi visit chưa bắt đầu) --}}
                @if ($isOriginDoctor && $visit->status === 'waiting')
                <form action="{{ route('doctor.clinical-visits.destroy-visit', $visit->id) }}" method="POST" class="absolute top-6 right-6" onsubmit="return confirm('Bạn có chắc muốn xóa chỉ định này?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 w-8 h-8 flex items-center justify-center rounded-full transition">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                </form>
                @endif
            </div>
            @empty
            <div class="text-center py-8 text-gray-400 italic">
                <i class="fa-solid fa-microscope text-3xl mb-2"></i>
                <p class="text-sm">Chưa có chỉ định cận lâm sàng nào.</p>
            </div>
            @endforelse

            {{-- Form Thêm chỉ định - chỉ cho bác sĩ gốc khi đang khám --}}
            @if ($isOriginDoctor && $appointment->status === 'examining')
            <div class="bg-blue-50 rounded-xl border border-blue-100 p-6 mt-6">
                <h4 class="text-md font-bold text-blue-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i> Chỉ định phòng khám chuyên sâu
                </h4>
                <form action="{{ route('doctor.clinical-visits.store-visit', $appointment->id) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4" x-data="{ price: 0 }">
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Chọn phòng / dịch vụ <span class="text-red-500">*</span></label>
                            <select name="room_id" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none bg-white"
                                x-on:change="price = $event.target.options[$event.target.selectedIndex].dataset.price || 0">
                                <option value="" data-price="0">-- Chọn phòng --</option>
                                @foreach($rooms as $room)
                                <option value="{{ $room->id }}" data-price="{{ $room->price ?? 0 }}">{{ $room->name }} ({{ $room->room_number ?? 'P.' . $room->id }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Hệ thống sẽ tự động gán bác sĩ đang trực tại phòng này.</p>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-700 mb-1">Chi phí dự kiến (VNĐ)</label>
                            <input type="number" name="payment_amount" x-model="price" min="0" class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none bg-white">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm text-gray-700 mb-1">Yêu cầu / Ghi chú cho bác sĩ cận lâm sàng</label>
                        <textarea name="findings" rows="2" placeholder="VD: Soi dạ dày, Xét nghiệm chức năng gan..." class="block w-full py-2 px-3 border border-gray-300 rounded-lg text-sm outline-none bg-white"></textarea>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fa-solid fa-paper-plane mr-1"></i> Chỉ định
                    </button>
                </form>
            </div>
            @endif
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