<x-layouts.receptionist title="Chi tiết Hồ sơ — {{ $profile->full_name }}">
    <div class="space-y-6">
        <!-- Breadcrumbs & Actions -->
        <div class="flex items-center justify-between">
            <nav class="flex text-sm text-gray-500 font-medium">
                <a href="{{ route('receptionist.dashboard') }}" class="hover:text-gray-900 transition">Dashboard</a>
                <span class="mx-2 text-gray-400">/</span>
                <a href="{{ route('receptionist.patients.index') }}" class="hover:text-gray-900 transition">Hồ sơ bệnh nhân</a>
                <span class="mx-2 text-gray-400">/</span>
                <span class="text-gray-900">{{ $profile->full_name }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <a href="{{ route('receptionist.patients.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
                <a href="{{ route('receptionist.patients.edit', $profile->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition flex items-center gap-2">
                    <i class="fa-solid fa-pen"></i> Chỉnh sửa
                </a>
            </div>
        </div>

        <!-- Session Alerts -->
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mb-4 flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
            <button @click="show=false" class="ml-auto"><i class="fa-solid fa-xmark"></i></button>
        </div>
        @endif

        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mb-4 flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
            <button @click="show=false" class="ml-auto"><i class="fa-solid fa-xmark"></i></button>
        </div>
        @endif

        <!-- Header Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row items-start gap-6">
            <div class="w-20 h-20 shrink-0 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-3xl font-bold shadow-sm">
                {{ mb_substr($profile->full_name, 0, 1) }}
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $profile->full_name }}</h2>
                    @if($profile->is_self)
                        <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">Hồ sơ bản thân</span>
                    @else
                        <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">Hồ sơ người thân</span>
                    @endif
                </div>
                
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600 mt-3">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-hashtag text-gray-400"></i>
                        <span class="font-mono font-medium">{{ $profile->patient_code ?? '—' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-phone text-gray-400"></i>
                        <span>{{ $profile->phone ?? '—' }}</span>
                    </div>
                    @if($profile->id_card)
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-id-card text-gray-400"></i>
                        <span>{{ $profile->id_card }}</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-2 border-l border-gray-200 pl-6">
                        <i class="fa-solid fa-calendar-plus text-gray-400"></i>
                        <span>Tạo lúc: {{ $profile->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="mt-4 md:mt-0 p-4 bg-gray-50 rounded-lg border border-gray-100 min-w-64">
                <div class="text-sm text-gray-500 mb-1">Tài khoản quản lý:</div>
                <div class="font-medium text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-shield text-gray-400"></i> {{ $profile->user->full_name }}
                </div>
                <div class="text-xs text-gray-500 mt-1">SĐT: {{ $profile->user->phone }}</div>
                <div class="mt-2">
                    @if ($profile->user->is_active)
                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700">TK Đang hoạt động</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700">TK Đã khóa</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Appointment Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
                <p class="text-sm text-gray-500 font-medium mb-1">Tổng lịch hẹn</p>
                <p class="text-2xl font-bold text-gray-900">{{ $appointmentStats['total'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500 p-4">
                <p class="text-sm text-gray-500 font-medium mb-1">Đang chờ</p>
                <p class="text-2xl font-bold text-gray-900">{{ $appointmentStats['pending'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
                <p class="text-sm text-gray-500 font-medium mb-1">Hoàn thành</p>
                <p class="text-2xl font-bold text-gray-900">{{ $appointmentStats['completed'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-red-500 p-4">
                <p class="text-sm text-gray-500 font-medium mb-1">Đã huỷ</p>
                <p class="text-2xl font-bold text-gray-900">{{ $appointmentStats['cancelled'] }}</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Cột trái (1/3) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Chi tiết Hồ sơ -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-semibold text-gray-800"><i class="fa-regular fa-address-card mr-2 text-green-600"></i>Thông tin hồ sơ</h3>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 gap-y-4 text-sm">
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Ngày sinh</span>
                                <span class="font-medium text-gray-900">{{ $profile->date_of_birth ? \Carbon\Carbon::parse($profile->date_of_birth)->format('d/m/Y') : '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Giới tính</span>
                                <span class="font-medium text-gray-900">
                                    @if($profile->gender == 'male') Nam @elseif($profile->gender == 'female') Nữ @else Khác @endif
                                </span>
                            </div>
                            <div class="flex flex-col border-b border-gray-50 pb-2">
                                <span class="text-gray-500 mb-1">Địa chỉ</span>
                                <span class="font-medium text-gray-900">{{ $profile->address ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Nghề nghiệp</span>
                                <span class="font-medium text-gray-900">{{ $profile->occupation ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-50 pb-2">
                                <span class="text-gray-500">Dân tộc</span>
                                <span class="font-medium text-gray-900">{{ $profile->ethnicity ?? '—' }}</span>
                            </div>
                        </div>

                        <!-- BHYT -->
                        <div class="mt-4 bg-blue-50 rounded-lg p-4 border border-blue-100">
                            <div class="font-semibold text-blue-900 mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-notes-medical"></i> Bảo hiểm y tế
                            </div>
                            @if($profile->insurance_code)
                                <div class="space-y-2 text-sm text-blue-800">
                                    <div class="flex justify-between">
                                        <span class="opacity-80">Mã thẻ:</span>
                                        <span class="font-mono font-bold">{{ $profile->insurance_code }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="opacity-80">Nơi ĐK:</span>
                                        <span class="font-medium text-right">{{ $profile->insurance_place ?? '—' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="opacity-80">Hạn thẻ:</span>
                                        <span class="font-medium">
                                            @if($profile->insurance_expiry)
                                                @php $expiry = \Carbon\Carbon::parse($profile->insurance_expiry); @endphp
                                                @if($expiry->isPast())
                                                    <span class="text-red-600">{{ $expiry->format('d/m/Y') }} (Hết hạn)</span>
                                                @else
                                                    {{ $expiry->format('d/m/Y') }}
                                                @endif
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="text-sm text-blue-600 italic">Không có thông tin thẻ BHYT</div>
                            @endif
                        </div>

                        <!-- Y tế -->
                        <div class="mt-4">
                            <div class="text-sm text-gray-500 mb-1">Tiền sử bệnh lý</div>
                            @if($profile->medical_history && is_array($profile->medical_history) && count($profile->medical_history) > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($profile->medical_history as $hist)
                                        <a href="{{ Storage::url($hist) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-green-200 rounded text-xs font-medium text-green-700 hover:bg-green-50 transition" title="Xem trước / Tải về">
                                            @if(Str::endsWith($hist, ['.pdf']))
                                                <i class="fa-solid fa-file-pdf text-red-500"></i>
                                            @elseif(Str::endsWith($hist, ['.doc', '.docx']))
                                                <i class="fa-solid fa-file-word text-blue-600"></i>
                                            @elseif(Str::endsWith($hist, ['.png', '.jpg', '.jpeg']))
                                                <i class="fa-regular fa-image text-green-500"></i>
                                            @else
                                                <i class="fa-solid fa-file text-gray-400"></i>
                                            @endif
                                            Tệp đính kèm {{ $loop->iteration }}
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-sm font-medium text-gray-900">Không có</span>
                            @endif
                        </div>
                        <div class="mt-3">
                            <div class="text-sm text-gray-500 mb-1">Triệu chứng / Ghi chú y tế</div>
                            <div class="font-medium bg-yellow-50 p-3 rounded text-yellow-800 text-sm border border-yellow-100">
                                {!! nl2br(e($profile->symptom_notes ?? 'Không có ghi chú')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thao tác Tài khoản -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4 pb-2 border-b">Thao tác tài khoản</h3>
                    <form action="{{ route('receptionist.patients.toggle-active', $profile->id) }}" method="POST" onsubmit="return confirm('Hành động này sẽ thay đổi trạng thái của Tài khoản quản lý ({{ $profile->user->full_name }}). Bạn có chắc chắn không?');">
                        @csrf
                        @method('PATCH')
                        @if($profile->user->is_active)
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-red-200 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 hover:border-red-300 transition font-medium">
                                <i class="fa-solid fa-lock"></i> Khoá tài khoản quản lý
                            </button>
                            <p class="text-xs text-center text-gray-500 mt-2">Khoá tài khoản sẽ ngăn bệnh nhân đăng nhập.</p>
                        @else
                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-blue-200 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 hover:border-blue-300 transition font-medium">
                                <i class="fa-solid fa-lock-open"></i> Mở khoá tài khoản
                            </button>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Cột phải (2/3) - Lịch sử khám -->
            <div class="lg:col-span-2 space-y-6">
                
                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 border-b border-gray-200 pb-2">
                    <i class="fa-solid fa-clock-rotate-left text-green-600"></i> Lịch sử Khám bệnh
                </h3>

                @forelse($profile->appointments as $apt)
                    <div x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-4">
                        <!-- Lịch hẹn Header -->
                        <div @click="open = !open" class="px-5 py-4 cursor-pointer flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-gray-50 transition" :class="open ? 'bg-gray-50 border-b border-gray-200' : ''">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex flex-col items-center justify-center shrink-0">
                                    <span class="text-xs font-semibold">{{ \Carbon\Carbon::parse($apt->appointment_date)->format('M') }}</span>
                                    <span class="text-lg font-bold leading-none">{{ \Carbon\Carbon::parse($apt->appointment_date)->format('d') }}</span>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <a href="{{ route('receptionist.appointments.show', $apt->id) }}" class="font-bold text-lg text-blue-600 hover:underline">#{{ $apt->appointment_code }}</a>
                                        @if($apt->status == 'completed')
                                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Hoàn thành</span>
                                        @elseif($apt->status == 'pending')
                                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">Đang chờ</span>
                                        @elseif($apt->status == 'cancelled')
                                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Đã hủy</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">{{ $apt->status }}</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <i class="fa-regular fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($apt->appointment_time)->format('H:i') }}
                                        <span class="mx-2">•</span>
                                        <i class="fa-solid fa-user-doctor mr-1"></i> {{ $apt->doctor?->user?->full_name ?? '—' }} ({{ $apt->specialty?->name ?? '—' }})
                                    </div>
                                </div>
                            </div>
                            <div class="text-gray-400">
                                <i class="fa-solid fa-chevron-down transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </div>
                        </div>

                        <!-- Content chi tiết (Hiển thị Record & Prescription) -->
                        <div x-show="open" x-collapse>
                            <div class="p-5 space-y-6">
                                
                                <!-- Lý do khám & Sinh hiệu -->
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-2 text-sm uppercase tracking-wider">Thông tin tiếp nhận</h4>
                                    <div class="bg-gray-50 rounded p-4 text-sm border border-gray-100">
                                        <div class="mb-2"><span class="text-gray-500">Lý do khám:</span> <span class="font-medium">{{ $apt->reason ?? '—' }}</span></div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3 pt-3 border-t border-gray-200">
                                            <div>
                                                <div class="text-xs text-gray-500">Mạch</div>
                                                <div class="font-medium">{{ $apt->vital_pulse ? $apt->vital_pulse . ' bpm' : '—' }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-500">Huyết áp</div>
                                                <div class="font-medium">{{ $apt->vital_systolic_bp && $apt->vital_diastolic_bp ? $apt->vital_systolic_bp . '/' . $apt->vital_diastolic_bp . ' mmHg' : '—' }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-500">Nhiệt độ</div>
                                                <div class="font-medium">{{ $apt->vital_temperature ? $apt->vital_temperature . ' °C' : '—' }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-500">SpO2</div>
                                                <div class="font-medium">{{ $apt->vital_spo2 ? $apt->vital_spo2 . ' %' : '—' }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-500">Cân nặng</div>
                                                <div class="font-medium">{{ $apt->vital_weight_kg ? $apt->vital_weight_kg . ' kg' : '—' }}</div>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-500">Chiều cao</div>
                                                <div class="font-medium">{{ $apt->vital_height_cm ? $apt->vital_height_cm . ' cm' : '—' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Medical Record -->
                                @if($apt->medicalRecord)
                                    <div>
                                        <h4 class="font-semibold text-green-700 mb-2 text-sm uppercase tracking-wider flex items-center gap-2">
                                            <i class="fa-solid fa-stethoscope"></i> Kết quả khám bệnh
                                        </h4>
                                        <div class="bg-green-50/50 rounded p-4 text-sm border border-green-100">
                                            <div class="mb-3">
                                                <div class="text-xs text-gray-500 mb-1">Chẩn đoán (ICD-10)</div>
                                                <div class="font-medium text-gray-900">
                                                    @if($apt->medicalRecord->icd10_code)
                                                        <span class="px-2 py-0.5 bg-gray-200 rounded text-xs mr-1">{{ $apt->medicalRecord->icd10_code }}</span>
                                                    @endif
                                                    {{ $apt->medicalRecord->diagnosis ?? '—' }}
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="text-xs text-gray-500 mb-1">Kết luận</div>
                                                <div class="text-gray-800">{{ $apt->medicalRecord->conclusion ?? '—' }}</div>
                                            </div>
                                            <div class="mb-3">
                                                <div class="text-xs text-gray-500 mb-1">Lời khuyên / Dặn dò</div>
                                                <div class="text-gray-800 italic">{{ $apt->medicalRecord->advice ?? '—' }}</div>
                                            </div>
                                            @if($apt->medicalRecord->followup_date)
                                                <div class="mt-2 text-sm text-blue-700 font-medium">
                                                    <i class="fa-regular fa-calendar-check mr-1"></i> Hẹn tái khám: {{ \Carbon\Carbon::parse($apt->medicalRecord->followup_date)->format('d/m/Y') }}
                                                </div>
                                            @endif

                                            @if($apt->medicalRecord->result_files && is_array($apt->medicalRecord->result_files) && count($apt->medicalRecord->result_files) > 0)
                                                <div class="mt-3 pt-3 border-t border-green-200">
                                                    <div class="text-xs text-gray-500 mb-2"><i class="fa-solid fa-paperclip mr-1"></i> File đính kèm kết quả</div>
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach($apt->medicalRecord->result_files as $file)
                                                            <a href="{{ Storage::url($file['url']) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-green-200 rounded text-xs font-medium text-green-700 hover:bg-green-50 transition">
                                                                @if(Str::endsWith($file['url'], ['.pdf']))
                                                                    <i class="fa-solid fa-file-pdf text-red-500"></i>
                                                                @elseif(Str::endsWith($file['url'], ['.doc', '.docx']))
                                                                    <i class="fa-solid fa-file-word text-blue-600"></i>
                                                                @elseif(Str::endsWith($file['url'], ['.png', '.jpg', '.jpeg']))
                                                                    <i class="fa-regular fa-image text-green-500"></i>
                                                                @else
                                                                    <i class="fa-solid fa-file text-gray-400"></i>
                                                                @endif
                                                                Tệp đính kèm {{ $loop->iteration }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Prescription -->
                                    @if($apt->medicalRecord->prescription)
                                        <div>
                                            <h4 class="font-semibold text-blue-700 mb-2 text-sm uppercase tracking-wider flex items-center gap-2">
                                                <i class="fa-solid fa-pills"></i> Đơn thuốc
                                            </h4>
                                            <div class="bg-white rounded border border-blue-100 overflow-hidden text-sm">
                                                @if($apt->medicalRecord->prescription->items && is_array($apt->medicalRecord->prescription->items))
                                                    <table class="w-full text-left">
                                                        <thead class="bg-blue-50 text-blue-800 text-xs uppercase">
                                                            <tr>
                                                                <th class="px-4 py-2">Tên thuốc</th>
                                                                <th class="px-4 py-2 text-center">Số lượng</th>
                                                                <th class="px-4 py-2">Cách dùng</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100">
                                                            @foreach($apt->medicalRecord->prescription->items as $item)
                                                                <tr>
                                                                    <td class="px-4 py-2 font-medium text-gray-800">{{ $item['name'] ?? '—' }}</td>
                                                                    <td class="px-4 py-2 text-center">{{ $item['quantity'] ?? '—' }} {{ $item['unit'] ?? '' }}</td>
                                                                    <td class="px-4 py-2 text-gray-600">{{ $item['usage'] ?? '—' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @else
                                                    <div class="p-4 text-gray-500">Chưa có danh sách thuốc.</div>
                                                @endif
                                                @if($apt->medicalRecord->prescription->general_note)
                                                    <div class="p-3 bg-blue-50/50 border-t border-blue-100 text-sm text-gray-700">
                                                        <span class="font-medium text-blue-800">Ghi chú đơn thuốc:</span> {{ $apt->medicalRecord->prescription->general_note }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-500 italic mt-2"><i class="fa-solid fa-info-circle mr-1"></i> Không có đơn thuốc cho ca khám này.</div>
                                    @endif

                                @else
                                    @if($apt->status == 'completed')
                                        <div class="p-4 bg-gray-50 rounded border border-gray-200 text-center text-gray-500 text-sm">
                                            Chưa có dữ liệu kết quả khám cho ca khám này.
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-500 bg-white rounded-lg shadow-sm border border-gray-100">
                        <i class="fa-regular fa-calendar-xmark text-4xl text-gray-300 mb-3"></i>
                        <p class="text-lg">Chưa có lịch hẹn nào.</p>
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</x-layouts.receptionist>
