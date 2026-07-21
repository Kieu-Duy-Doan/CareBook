<x-layouts.receptionist title="Chi tiết lịch hẹn #{{ $appointment->appointment_code }}">
    <div id="appointment-content" x-data="{ activeTab: 'overview' }" @beforeprint.window="activeTab = 'prescription'">
        <!-- Breadcrumb & Actions -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center text-sm text-gray-500 mb-2">
                    <a href="{{ route('receptionist.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                    <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                    <a href="{{ route('receptionist.appointments.index') }}" class="hover:text-blue-600 transition-colors">Lịch
                        hẹn</a>
                    <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                    <span class="text-gray-800 font-medium">{{ $appointment->appointment_code }}</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Chi tiết lịch hẹn <span
                        class="text-blue-600">#{{ $appointment->appointment_code }}</span></h2>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('receptionist.appointments.index') }}"
                    class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Quay lại
                </a>


                @if (in_array($appointment->status, ['pending']))
                    <form action="{{ route('receptionist.appointments.update-status', $appointment->id) }}" method="POST" class="inline-block">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="late">
                        <button type="submit" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fa-solid fa-clock mr-1"></i> Đến muộn
                        </button>
                    </form>
                @endif

                @if (in_array($appointment->status, ['pending', 'late']))
                    <form action="{{ route('receptionist.appointments.update-status', $appointment->id) }}" method="POST" class="inline-block">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="checked_in">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            <i class="fa-solid fa-check-circle mr-1"></i> Check-in
                        </button>
                    </form>
                @endif

                <button type="button" @click="activeTab = 'payments'" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fa-solid fa-money-bill mr-1"></i> Thanh toán
                    @if ($appointment->payments->where('status', 'pending')->count() > 0)
                        <span class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-green-600 bg-white rounded-full">
                            {{ $appointment->payments->where('status', 'pending')->count() }}
                        </span>
                    @endif
                </button>

                <a href="{{ route('receptionist.appointments.edit', $appointment->id) }}"
                    class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fa-solid fa-pen mr-1"></i> Chỉnh sửa
                </a>

            </div>
        </div>



        <!-- Stepper / Status Banner -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            @if (in_array($appointment->status, ['cancelled', 'absent']))
            <div class="flex items-center justify-center py-4">
                <div class="bg-red-50 border border-red-200 rounded-lg px-6 py-4 text-center max-w-lg w-full">
                    <div
                        class="h-12 w-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i
                            class="fa-solid {{ $appointment->status === 'cancelled' ? 'fa-ban' : 'fa-user-slash' }} text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-red-800 mb-1">
                        {{ $appointment->status === 'cancelled' ? 'Lịch hẹn đã huỷ' : 'Bệnh nhân vắng mặt' }}
                    </h3>
                    <p class="text-sm text-red-600">Lịch trình này đã đóng và không thể tiếp tục.</p>
                </div>
            </div>
            @else
            @php
            $steps = [
            'pending' => 1,
            'checked_in' => 2,
            'examining' => 3,
            'completed' => 4,
            ];
            $currentStep = $steps[$appointment->status] ?? 1;
            @endphp
            <div class="relative max-w-4xl mx-auto">
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-100">
                    <div style="width: {{ ($currentStep - 1) * 33.33 }}%"
                        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 transition-all duration-500">
                    </div>
                </div>
                <div class="flex justify-between relative">
                    <!-- Step 1 -->
                    <div class="text-center w-1/4">
                        <div
                            class="mx-auto flex items-center justify-center w-8 h-8 rounded-full border-2 {{ $currentStep >= 1 ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-300 bg-white text-gray-400' }} -mt-[25px] mb-2 transition-colors duration-500">
                            <i class="fa-solid {{ $currentStep > 1 ? 'fa-check text-xs' : 'fa-1 text-xs' }}"></i>
                        </div>
                        <div class="text-xs font-medium {{ $currentStep >= 1 ? 'text-blue-600' : 'text-gray-500' }}">Đã
                            đặt lịch</div>
                    </div>
                    <!-- Step 2 -->
                    <div class="text-center w-1/4">
                        <div
                            class="mx-auto flex items-center justify-center w-8 h-8 rounded-full border-2 {{ $currentStep >= 2 ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-300 bg-white text-gray-400' }} -mt-[25px] mb-2 transition-colors duration-500">
                            <i class="fa-solid {{ $currentStep > 2 ? 'fa-check text-xs' : 'fa-2 text-xs' }}"></i>
                        </div>
                        <div class="text-xs font-medium {{ $currentStep >= 2 ? 'text-blue-600' : 'text-gray-500' }}">Đã
                            tiếp nhận</div>
                    </div>
                    <!-- Step 3 -->
                    <div class="text-center w-1/4">
                        <div
                            class="mx-auto flex items-center justify-center w-8 h-8 rounded-full border-2 {{ $currentStep >= 3 ? 'border-blue-500 bg-blue-500 text-white' : 'border-gray-300 bg-white text-gray-400' }} -mt-[25px] mb-2 transition-colors duration-500">
                            <i class="fa-solid {{ $currentStep > 3 ? 'fa-check text-xs' : 'fa-3 text-xs' }}"></i>
                        </div>
                        <div class="text-xs font-medium {{ $currentStep >= 3 ? 'text-blue-600' : 'text-gray-500' }}">
                            Đang khám</div>
                    </div>
                    <!-- Step 4 -->
                    <div class="text-center w-1/4">
                        <div
                            class="mx-auto flex items-center justify-center w-8 h-8 rounded-full border-2 {{ $currentStep >= 4 ? 'border-green-500 bg-green-500 text-white' : 'border-gray-300 bg-white text-gray-400' }} -mt-[25px] mb-2 transition-colors duration-500">
                            <i class="fa-solid fa-4 text-xs"></i>
                        </div>
                        <div class="text-xs font-medium {{ $currentStep >= 4 ? 'text-green-600' : 'text-gray-500' }}">
                            Hoàn thành</div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Main Layout 3 Cột -->
        <div class="flex flex-col lg:flex-row gap-6">

            <!-- CỘT TRÁI & GIỮA (2/3) -->
            <div class="w-full lg:w-2/3">

                <!-- Tabs Header -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6 print:hidden">
                    <div class="flex border-b border-gray-100">
                        <button type="button" @click="activeTab = 'overview'"
                            :class="{ 'border-b-2 border-blue-500 text-blue-600 bg-blue-50': activeTab === 'overview', 'text-gray-500 hover:text-gray-700 hover:bg-gray-50': activeTab !== 'overview' }"
                            class="flex-1 py-4 px-2 sm:px-4 text-sm font-bold transition-colors">
                            <i class="fa-solid fa-circle-info mr-1 sm:mr-2"></i><span class="hidden sm:inline">Tổng
                                quan</span>
                        </button>
                        <button type="button" @click="activeTab = 'medical_record'"
                            :class="{ 'border-b-2 border-blue-500 text-blue-600 bg-blue-50': activeTab === 'medical_record', 'text-gray-500 hover:text-gray-700 hover:bg-gray-50': activeTab !== 'medical_record' }"
                            class="flex-1 py-4 px-2 sm:px-4 text-sm font-bold transition-colors">
                            <i class="fa-solid fa-file-medical mr-1 sm:mr-2"></i><span class="hidden sm:inline">Bệnh
                                án</span>
                        </button>
                        <button type="button" @click="activeTab = 'prescription'"
                            :class="{ 'border-b-2 border-blue-500 text-blue-600 bg-blue-50': activeTab === 'prescription', 'text-gray-500 hover:text-gray-700 hover:bg-gray-50': activeTab !== 'prescription' }"
                            class="flex-1 py-4 px-2 sm:px-4 text-sm font-bold transition-colors">
                            <i class="fa-solid fa-pills mr-1 sm:mr-2"></i><span class="hidden sm:inline">Đơn thuốc</span>
                        </button>
                        <button type="button" @click="activeTab = 'payments'"
                            :class="{ 'border-b-2 border-blue-500 text-blue-600 bg-blue-50': activeTab === 'payments', 'text-gray-500 hover:text-gray-700 hover:bg-gray-50': activeTab !== 'payments' }"
                            class="flex-1 py-4 px-2 sm:px-4 text-sm font-bold transition-colors relative">
                            <i class="fa-solid fa-receipt mr-1 sm:mr-2"></i><span class="hidden sm:inline">Thanh toán</span>
                            @if ($appointment->payments->count() > 0)
                            <span class="absolute -top-1 -right-1 bg-blue-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">{{ $appointment->payments->count() }}</span>
                            @endif
                        </button>
                    </div>
                </div>

                <!-- Tab 1: Tổng quan -->
                <div x-show="activeTab === 'overview'" class="space-y-6 print:hidden"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100">


                    <!-- Thông tin lịch hẹn -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                            <i class="fa-regular fa-calendar-check text-blue-500"></i>
                            <h3 class="text-lg font-bold text-gray-900">Thông tin lịch hẹn</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <div class="text-xs text-gray-500 font-medium mb-1">Mã lịch hẹn</div>
                                    <div class="font-mono font-medium text-gray-900">{{ $appointment->appointment_code }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 font-medium mb-1">Ngày đặt lịch</div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $appointment->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 font-medium mb-1">Nguồn đặt</div>
                                    <div class="text-sm font-medium text-gray-900">{{ $appointment->source_label }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 font-medium mb-1">Ngày khám</div>
                                    <div class="text-sm font-bold text-blue-600">
                                        {{ $appointment->appointment_date ? $appointment->appointment_date->format('d/m/Y') : '—' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 font-medium mb-1">Giờ khám</div>
                                    <div class="text-sm font-bold text-blue-600">
                                        {{ $appointment->appointment_time ? substr($appointment->appointment_time, 0, 5) : '—' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 font-medium mb-1">Trạng thái</div>
                                    @php $color = $appointment->status_color; @endphp
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 border border-{{ $color }}-200">
                                        {{ $appointment->status_label }}
                                    </span>
                                </div>
                            </div>

                            @if ($appointment->reason)
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                <div class="text-xs text-gray-500 font-medium mb-1">Lý do khám:</div>
                                <p class="text-sm text-gray-800">{{ $appointment->reason }}</p>
                            </div>
                            @endif

                            @if ($appointment->receptionist_note)
                            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-100 mt-4">
                                <div class="text-xs text-yellow-700 font-medium mb-1"><i
                                        class="fa-solid fa-note-sticky mr-1"></i> Ghi chú lễ tân:</div>
                                <p class="text-sm text-yellow-800">{{ $appointment->receptionist_note }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Thông tin bệnh nhân -->
                    @if ($appointment->patientProfile)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                            <i class="fa-regular fa-user text-blue-500"></i>
                            <h3 class="text-lg font-bold text-gray-900">Thông tin bệnh nhân</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-start gap-4 mb-6">
                                <div
                                    class="h-16 w-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-xl flex-shrink-0">
                                    {{ $appointment->patientProfile->user->avatar_initials ?? substr($appointment->patientProfile->full_name, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">
                                        {{ $appointment->patientProfile->full_name }}
                                    </h4>
                                    <div class="flex flex-wrap gap-4 mt-2 text-sm text-gray-600">
                                        @if ($appointment->patientProfile->date_of_birth)
                                        <div class="flex items-center gap-1.5"><i
                                                class="fa-regular fa-calendar"></i>
                                            {{ \Carbon\Carbon::parse($appointment->patientProfile->date_of_birth)->format('d/m/Y') }}
                                            ({{ \Carbon\Carbon::parse($appointment->patientProfile->date_of_birth)->age }}
                                            tuổi)
                                        </div>
                                        @endif
                                        <div class="flex items-center gap-1.5"><i class="fa-solid fa-venus-mars"></i>
                                            {{ $appointment->patientProfile->gender === 'male' ? 'Nam' : ($appointment->patientProfile->gender === 'female' ? 'Nữ' : 'Khác') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-6">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-id-card w-5 text-center text-gray-400"></i>
                                    <span class="text-gray-500">CCCD:</span>
                                    <span
                                        class="font-medium text-gray-900">{{ $appointment->patientProfile->identity_card ?? '—' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-phone w-5 text-center text-gray-400"></i>
                                    <span class="text-gray-500">SĐT:</span>
                                    <span
                                        class="font-medium text-gray-900">{{ $appointment->patientProfile->phone ?? '—' }}</span>
                                </div>
                                <div class="col-span-1 sm:col-span-2 flex items-start gap-2">
                                    <i class="fa-solid fa-location-dot w-5 text-center text-gray-400 mt-0.5"></i>
                                    <span class="text-gray-500 whitespace-nowrap">Địa chỉ:</span>
                                    <span
                                        class="font-medium text-gray-900">{{ $appointment->patientProfile->address ?? '—' }}</span>
                                </div>
                            </div>

                            @if ($appointment->patientProfile->insurance_code)
                            <div
                                class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-shield-heart text-blue-500 text-xl"></i>
                                    <div>
                                        <div class="text-xs text-blue-600 font-medium uppercase tracking-wider">Bảo
                                            hiểm y tế</div>
                                        <div class="font-mono font-bold text-gray-900">
                                            {{ $appointment->patientProfile->insurance_code }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500 font-medium">Hạn sử dụng</div>
                                    @if ($appointment->patientProfile->insurance_expiry)
                                    @if (now()->startOfDay()->gt(\Carbon\Carbon::parse($appointment->patientProfile->insurance_expiry)))
                                    <div
                                        class="text-sm font-bold text-red-600 border border-red-200 bg-red-50 px-2 py-0.5 rounded">
                                        {{ \Carbon\Carbon::parse($appointment->patientProfile->insurance_expiry)->format('d/m/Y') }}
                                        (Hết hạn)
                                    </div>
                                    @else
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($appointment->patientProfile->insurance_expiry)->format('d/m/Y') }}
                                    </div>
                                    @endif
                                    @else
                                    <div class="text-sm font-medium text-gray-900">—</div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if ($appointment->patientProfile->medical_history)
                            @php $medicalHistory = is_string($appointment->patientProfile->medical_history) ? json_decode($appointment->patientProfile->medical_history, true) : $appointment->patientProfile->medical_history; @endphp
                            @if (is_array($medicalHistory) && !empty($medicalHistory['allergies']))
                            <div class="bg-red-50 rounded-lg p-4 border border-red-100">
                                <div class="text-xs text-red-700 font-bold mb-1"><i
                                        class="fa-solid fa-triangle-exclamation mr-1"></i> Tiền sử dị ứng:
                                </div>
                                <p class="text-sm text-red-800">{{ $medicalHistory['allergies'] }}</p>
                            </div>
                            @endif
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Chỉ số sinh tồn -->
                    @if (
                    $appointment->vital_pulse ||
                    $appointment->vital_systolic_bp ||
                    $appointment->vital_temperature ||
                    $appointment->vital_weight_kg)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div
                            class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-heart-pulse text-blue-500"></i>
                                <h3 class="text-lg font-bold text-gray-900">Chỉ số sinh tồn</h3>
                            </div>
                            @if ($appointment->checked_in_at)
                            <div class="text-xs text-gray-500">
                                Đo lúc: {{ $appointment->checked_in_at->format('H:i d/m/Y') }}
                            </div>
                            @endif
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                                    <div class="text-gray-400 mb-1"><i class="fa-solid fa-heart-crack"></i></div>
                                    <div class="text-xs text-gray-500 uppercase font-medium">Mạch</div>
                                    <div class="text-lg font-bold text-gray-900">
                                        {{ $appointment->vital_pulse ?? '—' }} <span
                                            class="text-xs font-normal text-gray-500">l/p</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                                    <div class="text-gray-400 mb-1"><i class="fa-solid fa-droplet"></i></div>
                                    <div class="text-xs text-gray-500 uppercase font-medium">Huyết áp</div>
                                    <div class="text-lg font-bold text-gray-900">
                                        {{ $appointment->vital_systolic_bp ?? '—' }}/{{ $appointment->vital_diastolic_bp ?? '—' }}
                                        <span class="text-xs font-normal text-gray-500">mmHg</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                                    <div class="text-gray-400 mb-1"><i class="fa-solid fa-temperature-half"></i></div>
                                    <div class="text-xs text-gray-500 uppercase font-medium">Nhiệt độ</div>
                                    <div class="text-lg font-bold text-gray-900">
                                        {{ $appointment->vital_temperature ?? '—' }} <span
                                            class="text-xs font-normal text-gray-500">°C</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                                    <div class="text-gray-400 mb-1"><i class="fa-solid fa-lungs"></i></div>
                                    <div class="text-xs text-gray-500 uppercase font-medium">SpO2</div>
                                    <div class="text-lg font-bold text-gray-900">{{ $appointment->vital_spo2 ?? '—' }}
                                        <span class="text-xs font-normal text-gray-500">%</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                                    <div class="text-gray-400 mb-1"><i class="fa-solid fa-wind"></i></div>
                                    <div class="text-xs text-gray-500 uppercase font-medium">Nhịp thở</div>
                                    <div class="text-lg font-bold text-gray-900">
                                        {{ $appointment->vital_respiratory ?? '—' }} <span
                                            class="text-xs font-normal text-gray-500">l/p</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                                    <div class="text-gray-400 mb-1"><i class="fa-solid fa-weight-scale"></i></div>
                                    <div class="text-xs text-gray-500 uppercase font-medium">Cân nặng</div>
                                    <div class="text-lg font-bold text-gray-900">
                                        {{ $appointment->vital_weight_kg ?? '—' }} <span
                                            class="text-xs font-normal text-gray-500">kg</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                                    <div class="text-gray-400 mb-1"><i class="fa-solid fa-ruler-vertical"></i></div>
                                    <div class="text-xs text-gray-500 uppercase font-medium">Chiều cao</div>
                                    <div class="text-lg font-bold text-gray-900">
                                        {{ $appointment->vital_height_cm ?? '—' }} <span
                                            class="text-xs font-normal text-gray-500">cm</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                                    <div class="text-gray-400 mb-1"><i class="fa-solid fa-person"></i></div>
                                    <div class="text-xs text-gray-500 uppercase font-medium">BMI</div>
                                    <div class="text-lg font-bold text-gray-900">{{ $appointment->vital_bmi ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Quy trình khám -->
                    @if ($appointment->clinicalVisits && $appointment->clinicalVisits->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                            <i class="fa-solid fa-stethoscope text-blue-500"></i>
                            <h3 class="text-lg font-bold text-gray-900">Quy trình khám</h3>
                        </div>
                        <div class="p-6">
                            <div class="relative border-l-2 border-gray-200 ml-3 md:ml-4">
                                @foreach ($appointment->clinicalVisits as $index => $visit)
                                <div class="mb-8 ml-6 relative">
                                    <span
                                        class="absolute -left-[35px] flex items-center justify-center w-8 h-8 bg-white rounded-full border-2 border-blue-500 text-blue-600 font-bold text-xs">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="bg-gray-50 border border-gray-100 rounded-lg p-4">
                                        <div
                                            class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-3">
                                            <div>
                                                <h4 class="text-base font-bold text-gray-900">
                                                    {{ $visit->room->name ?? 'Phòng khám' }}
                                                </h4>
                                                <div class="text-sm text-gray-600 mt-0.5">Bác sĩ: <span
                                                        class="font-medium text-gray-900">{{ $visit->doctor->full_title ?? '—' }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                @php
                                                $visitColor = match ($visit->status) {
                                                'pending' => 'yellow',
                                                'examining' => 'purple',
                                                'completed' => 'green',
                                                'cancelled' => 'red',
                                                default => 'gray',
                                                };
                                                $visitLabel = match ($visit->status) {
                                                'pending' => 'Đã tiếp nhận',
                                                'examining' => 'Đang khám',
                                                'completed' => 'Hoàn thành',
                                                'cancelled' => 'Đã huỷ',
                                                default => 'Không xác định',
                                                };
                                                @endphp
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-{{ $visitColor }}-100 text-{{ $visitColor }}-800 border border-{{ $visitColor }}-200">
                                                    {{ $visitLabel }}
                                                </span>
                                            </div>
                                        </div>

                                        @if ($visit->clinical_findings)
                                        <div class="mt-3 text-sm">
                                            <div class="font-medium text-gray-700 mb-1">Ghi nhận lâm sàng:
                                            </div>
                                            <p class="text-gray-600">{{ $visit->clinical_findings }}</p>
                                        </div>
                                        @endif

                                        <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
                                            @if ($visit->started_at)
                                            <div><i class="fa-regular fa-clock"></i> Bắt đầu:
                                                {{ \Carbon\Carbon::parse($visit->started_at)->format('H:i') }}
                                            </div>
                                            @endif
                                            @if ($visit->completed_at)
                                            <div><i class="fa-regular fa-calendar-check"></i> Kết thúc:
                                                {{ \Carbon\Carbon::parse($visit->completed_at)->format('H:i') }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                </div> <!-- End Tab 1 (Tổng quan) -->

                <!-- Tab 2: Kết quả khám / Medical Record -->
                <div x-show="activeTab === 'medical_record'" style="display: none;" class="space-y-6 print:hidden"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100">

                    <!-- Kết quả khám / Medical Record -->
                    @if ($appointment->medicalRecord)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                            <i class="fa-solid fa-file-medical text-green-500"></i>
                            <h3 class="text-lg font-bold text-gray-900">Kết quả khám & Điều trị</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Chẩn đoán -->
                            <div>
                                <h4
                                    class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-2 border-b border-gray-100 pb-2">
                                    Chẩn đoán</h4>
                                <div class="flex gap-4 items-start">
                                    <div
                                        class="bg-blue-50 text-blue-700 font-mono font-bold px-3 py-1.5 rounded text-sm border border-blue-100 flex-shrink-0">
                                        {{ $appointment->medicalRecord->icd10_code ?? '—' }}
                                    </div>
                                    <div class="text-sm text-gray-800 pt-1">
                                        {{ $appointment->medicalRecord->diagnosis ?? 'Chưa có chẩn đoán' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Kết luận -->
                            @if ($appointment->medicalRecord->conclusion)
                            <div>
                                <h4
                                    class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-2 border-b border-gray-100 pb-2">
                                    Kết luận lâm sàng</h4>
                                <p class="text-sm text-gray-800">{{ $appointment->medicalRecord->conclusion }}</p>
                            </div>
                            @endif

                            <!-- Dặn dò -->
                            @if ($appointment->medicalRecord->advice)
                            <div>
                                <h4
                                    class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-2 border-b border-gray-100 pb-2">
                                    Kế hoạch điều trị / Dặn dò</h4>
                                <p class="text-sm text-gray-800">{{ $appointment->medicalRecord->advice }}
                                </p>
                            </div>
                            @endif

                            <!-- Xử lý & Tái khám -->
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                                <div>
                                    <div class="text-xs text-gray-500 font-medium mb-1">Hướng giải quyết:</div>
                                    <div class="text-sm font-medium text-gray-900">
                                        @php
                                        $resultLabel = match ($appointment->medicalRecord->treatment_result) {
                                        'outpatient' => 'Điều trị ngoại trú',
                                        'admitted' => 'Nhập viện',
                                        'monitoring' => 'Theo dõi thêm',
                                        default => 'Không xác định',
                                        };
                                        @endphp
                                        {{ $resultLabel }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 font-medium mb-1">Hẹn tái khám:</div>
                                    @if ($appointment->medicalRecord->followup_date)
                                    <div class="text-sm font-bold text-blue-600"><i
                                            class="fa-regular fa-calendar mr-1"></i>
                                        {{ \Carbon\Carbon::parse($appointment->medicalRecord->followup_date)->format('d/m/Y') }}
                                    </div>
                                    @else
                                    <div class="text-sm font-medium text-gray-900">Không hẹn tái khám</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-12 text-gray-500 bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="mb-3"><i class="fa-solid fa-file-medical text-4xl text-gray-300"></i></div>
                        <p>Chưa có kết quả khám cho lịch hẹn này.</p>
                    </div>
                    @endif

                    <!-- Tiền sử bệnh án (File PDF đính kèm từ PatientProfile) -->
                    @if ($appointment->patientProfile && $appointment->patientProfile->medical_history)
                    @php
                    $historyFiles = is_string($appointment->patientProfile->medical_history) ? json_decode($appointment->patientProfile->medical_history, true) : $appointment->patientProfile->medical_history;
                    $pdfFiles = is_array($historyFiles) ? array_filter($historyFiles, function($f) { return is_string($f) && str_starts_with($f, 'http'); }) : [];
                    @endphp
                    @if (count($pdfFiles) > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                            <i class="fa-solid fa-folder-open text-blue-500"></i>
                            <h3 class="text-lg font-bold text-gray-900">Bệnh án tiền sử (Đính kèm)</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($pdfFiles as $file)
                                <a href="{{ $file }}" target="_blank" class="flex items-center gap-3 p-4 bg-gray-50 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200 transition">
                                    <i class="fa-solid fa-file-pdf text-red-500 text-2xl"></i>
                                    <div class="flex-1 overflow-hidden">
                                        <div class="text-sm font-medium text-gray-900 truncate">File bệnh án {{ $loop->iteration }}</div>
                                        <div class="text-xs text-gray-500 truncate">{{ $file }}</div>
                                    </div>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-gray-400"></i>
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    @endif
                </div> <!-- End Tab 2 -->

                <!-- Tab 3: Đơn thuốc -->
                <div x-show="activeTab === 'prescription'" style="display: none;" class="space-y-6"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                        id="prescription-print-area">
                        <div
                            class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between gap-2 print:hidden">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-pills text-blue-500"></i>
                                <h3 class="text-lg font-bold text-gray-900">Đơn thuốc điện tử</h3>
                            </div>
                            <button onclick="window.print()"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-print"></i> In đơn thuốc
                            </button>
                        </div>

                        <!-- Print Header (Only visible when printing) -->
                        <div class="hidden print:block p-8 border-b-2 border-gray-800 mb-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h1 class="text-2xl font-bold uppercase text-gray-900">BỆNH VIỆN ĐA KHOA CAREBOOK</h1>
                                    <p class="text-sm mt-1">123 Đường Sức Khoẻ, Quận Bình Thủy, TP. Cần Thơ</p>
                                    <p class="text-sm">Hotline: 1900 1234</p>
                                </div>
                                <div class="text-right">
                                    <h2 class="text-xl font-bold uppercase mb-1">ĐƠN THUỐC</h2>
                                    <p class="text-sm italic">Mã LH: {{ $appointment->appointment_code }}</p>
                                </div>
                            </div>
                            <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
                                <div><span class="font-medium">Họ tên bệnh nhân:</span>
                                    {{ $appointment->patientProfile->full_name ?? '' }}
                                </div>
                                <div><span class="font-medium">Giới tính:</span>
                                    {{ $appointment->patientProfile->gender === 'male' ? 'Nam' : ($appointment->patientProfile->gender === 'female' ? 'Nữ' : 'Khác') }}
                                </div>
                                <div><span class="font-medium">Ngày sinh:</span>
                                    {{ $appointment->patientProfile->date_of_birth ? \Carbon\Carbon::parse($appointment->patientProfile->date_of_birth)->format('d/m/Y') : '' }}
                                </div>
                                <div><span class="font-medium">SĐT:</span> {{ $appointment->patientProfile->phone ?? '' }}
                                </div>
                                <div class="col-span-2"><span class="font-medium">Địa chỉ:</span>
                                    {{ $appointment->patientProfile->address ?? '' }}
                                </div>
                                <div class="col-span-2"><span class="font-medium">Chẩn đoán:</span>
                                    {{ optional($appointment->medicalRecord)->diagnosis ?? 'Không có thông tin' }}
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            @if (optional($appointment->medicalRecord)->prescription &&
                            !empty(optional($appointment->medicalRecord->prescription)->items))
                            <div>
                                <h4
                                    class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-3 flex items-center gap-2">
                                    <i class="fa-solid fa-pills text-blue-500"></i> Đơn thuốc
                                </h4>
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col"
                                                    class="px-4 py-2 text-left font-medium text-gray-500">Tên thuốc
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-2 text-center font-medium text-gray-500 w-20">SL
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-2 text-left font-medium text-gray-500">Liều dùng &
                                                    Cách dùng</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($appointment->medicalRecord->prescription->items as $item)
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <div class="font-medium text-gray-900">
                                                        {{ data_get($item, 'medication_name', data_get($item, 'name', '')) }}
                                                    </div>
                                                    @if (data_get($item, 'dosage_form') || data_get($item, 'unit'))
                                                    <div class="text-xs text-gray-500 mt-0.5">
                                                        {{ data_get($item, 'dosage_form', data_get($item, 'unit', '')) }}
                                                    </div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-center font-bold text-gray-900">
                                                    {{ data_get($item, 'quantity', '') }}
                                                </td>
                                                <td class="px-4 py-3 text-gray-700">
                                                    {{ data_get($item, 'instructions', data_get($item, 'usage', '')) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if ($appointment->medicalRecord->prescription->notes)
                                <div class="mt-2 text-xs text-gray-500 italic">* Lời dặn:
                                    {{ $appointment->medicalRecord->prescription->notes }}
                                </div>
                                @endif
                            </div>
                            @else
                            <div class="text-center py-12 text-gray-500">
                                <div class="mb-3"><i class="fa-solid fa-notes-medical text-4xl text-gray-300"></i>
                                </div>
                                <p>Không có đơn thuốc nào được kê cho bệnh nhân này.</p>
                            </div>
                            @endif
                        </div>

                        <!-- Print Footer (Only visible when printing) -->
                        <div class="hidden print:flex justify-between mt-12 p-8">
                            <div class="text-center">
                                <p class="font-medium mb-16">Bệnh nhân / Người nhà</p>
                                <p class="italic text-gray-500">(Ký và ghi rõ họ tên)</p>
                            </div>
                            <div class="text-center">
                                <p class="italic mb-2">Cần Thơ, ngày {{ date('d') }} tháng {{ date('m') }} năm
                                    {{ date('Y') }}
                                </p>
                                <p class="font-medium mb-16">Bác sĩ khám bệnh</p>
                                <p class="font-bold text-gray-900">{{ $appointment->doctor->full_title ?? 'BS' }}</p>
                            </div>
                        </div>
                    </div> <!-- End Print Area -->
                </div> <!-- End Tab 3 -->

                <!-- Tab 4: Thanh toán -->
                <div x-show="activeTab === 'payments'" style="display: none;" class="space-y-6"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-receipt text-green-500"></i>
                                <h3 class="text-lg font-bold text-gray-900">Lịch sử thanh toán</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($appointment->payments->where('status', 'paid')->count() > 0)
                                <a href="{{ route('receptionist.payments.printVat', $appointment->id) }}" target="_blank"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                                    <i class="fa-solid fa-print"></i> In hóa đơn
                                </a>
                                @endif
                                <a href="{{ route('receptionist.payments.create', $appointment->id) }}"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                                    <i class="fa-solid fa-plus"></i> Tạo thanh toán
                                </a>
                            </div>
                        </div>
                        <div class="p-6">
                            @if ($appointment->payments->isEmpty())
                            <div class="text-center py-12 text-gray-500">
                                <div class="mb-3"><i class="fa-solid fa-receipt text-4xl text-gray-300"></i></div>
                                <p>Chưa có giao dịch thanh toán nào.</p>
                            </div>
                            @else
                            <!-- Tổng kết thanh toán -->
                            @php
                            $totalPaid = $appointment->payments->where('status', 'paid')->sum('amount');
                            $totalPending = $appointment->payments->where('status', 'pending')->sum('amount');
                            $totalRefunded = $appointment->payments->where('status', 'refunded')->sum('amount');
                            @endphp
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="bg-green-50 rounded-lg p-4 text-center border border-green-100">
                                    <div class="text-xs text-green-600 font-medium uppercase mb-1">Đã thanh toán</div>
                                    <div class="text-lg font-bold text-green-700">{{ number_format($totalPaid, 0, ',', '.') }}đ</div>
                                </div>
                                <div class="bg-yellow-50 rounded-lg p-4 text-center border border-yellow-100">
                                    <div class="text-xs text-yellow-600 font-medium uppercase mb-1">Chờ thanh toán</div>
                                    <div class="text-lg font-bold text-yellow-700">{{ number_format($totalPending, 0, ',', '.') }}đ</div>
                                </div>
                                <div class="bg-red-50 rounded-lg p-4 text-center border border-red-100">
                                    <div class="text-xs text-red-600 font-medium uppercase mb-1">Hoàn tiền</div>
                                    <div class="text-lg font-bold text-red-700">{{ number_format($totalRefunded, 0, ',', '.') }}đ</div>
                                </div>
                            </div>

                            <!-- Bảng chi tiết -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-medium text-gray-500">Mã GD</th>
                                            <th class="px-4 py-3 text-right font-medium text-gray-500">Số tiền</th>
                                            <th class="px-4 py-3 text-center font-medium text-gray-500">Phương thức</th>
                                            <th class="px-4 py-3 text-center font-medium text-gray-500">Trạng thái</th>
                                            <th class="px-4 py-3 text-left font-medium text-gray-500">Người thu</th>
                                            <th class="px-4 py-3 text-left font-medium text-gray-500">Thời gian</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($appointment->payments->sortByDesc('created_at') as $payment)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3">
                                                <span class="font-mono text-xs text-gray-700">{{ $payment->transaction_code ?? '—' }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-right font-bold text-gray-900">
                                                {{ number_format($payment->amount, 0, ',', '.') }}đ
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @php
                                                $methodLabel = match($payment->method) {
                                                'cash' => ['Tiền mặt', 'bg-emerald-100 text-emerald-700'],
                                                'bank_transfer' => ['Chuyển khoản', 'bg-blue-100 text-blue-700'],
                                                'qr_code' => ['QR Code', 'bg-purple-100 text-purple-700'],
                                                default => [$payment->method, 'bg-gray-100 text-gray-700'],
                                                };
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $methodLabel[1] }}">{{ $methodLabel[0] }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @php
                                                $statusLabel = match($payment->status) {
                                                'paid' => ['Đã thanh toán', 'bg-green-100 text-green-700'],
                                                'pending' => ['Chờ thanh toán', 'bg-yellow-100 text-yellow-700'],
                                                'refunded' => ['Hoàn tiền', 'bg-red-100 text-red-700'],
                                                'failed' => ['Thất bại', 'bg-red-100 text-red-700'],
                                                default => [$payment->status, 'bg-gray-100 text-gray-700'],
                                                };
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusLabel[1] }}">{{ $statusLabel[0] }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-gray-700">
                                                {{ $payment->collectedBy->full_name ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-500 text-xs">
                                                {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : ($payment->created_at ? $payment->created_at->format('d/m/Y H:i') : '—') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if ($payment->note ?? false)
                            <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-100 text-sm text-gray-600">
                                <span class="font-medium">Ghi chú:</span> {{ $payment->note }}
                            </div>
                            @endif
                            @endif
                        </div>
                    </div>
                </div> <!-- End Tab 4 -->
            </div>

            <!-- CỘT PHẢI (1/3) -->
            <div class="w-full lg:w-1/3 space-y-6 print:hidden">

                <!-- Thông tin bác sĩ -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                        <i class="fa-solid fa-user-doctor text-blue-500"></i>
                        <h3 class="text-base font-bold text-gray-900">Bác sĩ phụ trách</h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-4 mb-4">
                            <div
                                class="h-12 w-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-lg flex-shrink-0">
                                {{ $appointment->doctor->user->avatar_initials ?? substr($appointment->doctor->full_title ?? 'BS', 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">
                                    {{ $appointment->doctor->full_title ?? 'Chưa chỉ định' }}
                                </div>
                                <div class="text-sm text-gray-500 mt-0.5">{{ $appointment->specialty->name ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="text-sm border-t border-gray-100 pt-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Phòng khám:</span>
                                <span class="font-medium text-gray-900">{{ $appointment->room->name ?? '—' }}
                                    {{ $appointment->room->room_number ? '(' . $appointment->room->room_number . ')' : '' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">SĐT:</span>
                                <span
                                    class="font-medium text-gray-900">{{ $appointment->doctor->user->phone ?? '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cập nhật trạng thái (Admin Action) -->
                @if (!in_array($appointment->status, ['completed', 'cancelled']))
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-yellow-500"></i>
                        <h3 class="text-base font-bold text-gray-900">Cập nhật trạng thái</h3>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('receptionist.appointments.update-status', $appointment->id) }}"
                            method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái mới <span
                                        class="text-red-500">*</span></label>
                                <select name="status" required
                                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                    <option value="pending"
                                        {{ $appointment->status === 'pending' ? 'selected' : '' }}>Đã tiếp nhận</option>
                                    <option value="late"
                                        {{ $appointment->status === 'late' ? 'selected' : '' }}>Đi trễ</option>
                                    <option value="checked_in"
                                        {{ $appointment->status === 'checked_in' ? 'selected' : '' }}>Đã checkin
                                    </option>
                                    <option value="examining"
                                        {{ $appointment->status === 'examining' ? 'selected' : '' }}>Đang khám</option>
                                    <option value="completed"
                                        {{ $appointment->status === 'completed' ? 'selected' : '' }}>Hoàn thành
                                    </option>
                                    <option value="cancelled"
                                        {{ $appointment->status === 'cancelled' ? 'selected' : '' }}>Đã huỷ</option>
                                    <option value="absent" {{ $appointment->status === 'absent' ? 'selected' : '' }}>
                                        Vắng mặt</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lý do / Ghi chú</label>
                                <textarea name="reason" rows="2"
                                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none"
                                    placeholder="VD: Khách hàng gọi huỷ, nhập sai thông tin..."></textarea>
                            </div>
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">
                                Cập nhật ngay
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Lịch sử thay đổi -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-gray-400"></i>
                        <h3 class="text-base font-bold text-gray-900">Lịch sử trạng thái</h3>
                    </div>
                    <div class="p-6">
                        @if ($appointment->logs->isEmpty())
                        <div class="text-center text-sm text-gray-500 py-4 italic">Chưa có bản ghi lịch sử nào.</div>
                        @else
                        <div class="relative border-l border-gray-200 ml-3">
                            @foreach ($appointment->logs as $log)
                            <div class="mb-6 ml-5">
                                @php
                                $logColor = match ($log->new_status) {
                                'pending' => 'bg-yellow-500',
                                'checked_in' => 'bg-blue-500',
                                'examining' => 'bg-purple-500',
                                'completed' => 'bg-green-500',
                                'cancelled' => 'bg-red-500',
                                'absent' => 'bg-gray-500',
                                'late' => 'bg-orange-500',
                                default => 'bg-gray-400',
                                };
                                @endphp
                                <span
                                    class="absolute -left-1.5 w-3 h-3 rounded-full {{ $logColor }} ring-4 ring-white mt-1.5"></span>
                                <div class="text-xs text-gray-500 mb-1">
                                    {{ $log->created_at->format('d/m/Y H:i') }}
                                </div>
                                <div class="text-sm font-medium text-gray-900">
                                    Chuyển sang:
                                    @php
                                    $labelMap = [
                                    'pending' => 'Đã tiếp nhận',
                                    'checked_in' => 'Đã checkin',
                                    'examining' => 'Đang khám',
                                    'completed' => 'Hoàn thành',
                                    'cancelled' => 'Đã huỷ',
                                    'absent' => 'Vắng mặt',
                                    'late' => 'Đến muộn',
                                    ];
                                    @endphp
                                    {{ $labelMap[$log->new_status] ?? $log->new_status }}
                                </div>
                                <div class="text-xs text-gray-600 mt-1">Bởi: <span
                                        class="font-medium">{{ $log->changedBy->full_name ?? 'Hệ thống' }}</span>
                                </div>
                                @if ($log->reason)
                                <div
                                    class="text-xs text-gray-500 mt-1 bg-gray-50 p-2 rounded border border-gray-100 italic">
                                    "{{ $log->reason }}"</div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>


    </div>
</x-layouts.receptionist>