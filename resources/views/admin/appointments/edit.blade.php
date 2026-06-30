<x-layouts.admin title="Chỉnh sửa lịch hẹn">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <a href="{{ route('admin.appointments.index') }}" class="hover:text-blue-600 transition-colors">Lịch
                    hẹn</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <span class="text-gray-800 font-medium">Chỉnh sửa</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Chỉnh sửa lịch hẹn: {{ $appointment->appointment_code }}</h2>
            <p class="text-gray-500 mt-1">Cập nhật thông tin chi tiết lịch hẹn hoặc các chỉ số sinh hiệu (vitals) đo
                được.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 flex items-start gap-3 border border-red-200">
            <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
            <div>
                <p class="font-bold mb-1">Vui lòng kiểm tra lại thông tin:</p>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST" x-data="{
        weight: '{{ old('vital_weight_kg', $appointment->vital_weight_kg) }}',
        height: '{{ old('vital_height_cm', $appointment->vital_height_cm) }}',
        get bmi() {
            if (!this.weight || !this.height) return '';
            const hm = this.height / 100;
            return (this.weight / (hm * hm)).toFixed(2);
        }
    }">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Cột trái: Thông tin lịch hẹn chính (chiếm 2/3) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3
                        class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-blue-600"></i>
                        Thông tin lịch hẹn
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bệnh nhân <span
                                    class="text-red-500">*</span></label>
                            <select name="patient_profile_id" required
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                <option value="">-- Chọn bệnh nhân --</option>
                                @foreach ($patients as $pat)
                                    <option value="{{ $pat->id }}"
                                        {{ old('patient_profile_id', $appointment->patient_profile_id) == $pat->id ? 'selected' : '' }}>
                                        {{ $pat->full_name }} (SĐT: {{ $pat->phone ?? 'N/A' }} - Ngày sinh:
                                        {{ $pat->date_of_birth ? $pat->date_of_birth->format('d/m/Y') : 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chuyên khoa khám <span
                                    class="text-red-500">*</span></label>
                            <select name="specialty_id" onchange="handleSpecialtyChange(event)" required
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                <option value="">-- Chọn chuyên khoa --</option>
                                @foreach ($specialties as $sp)
                                    <option value="{{ $sp->id }}"
                                        {{ old('specialty_id', $appointment->specialty_id) == $sp->id ? 'selected' : '' }}>
                                        {{ $sp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bác sĩ khám <span
                                    class="text-red-500">*</span></label>
                            <select name="doctor_profile_id" required onchange="handleDoctorOrAppointmentChange(event)"
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                <option value="">-- Chọn bác sĩ --</option>
                                @foreach ($doctors as $doc)
                                    <option value="{{ $doc->id }}"
                                        {{ old('doctor_profile_id', $appointment->doctor_profile_id) == $doc->id ? 'selected' : '' }}>
                                        {{ $doc->full_title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày hẹn khám <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="appointment_date"
                                value="{{ old('appointment_date', $appointment->appointment_date ? $appointment->appointment_date->toDateString() : '') }}"
                                required onchange="handleDoctorOrAppointmentChange(event)"
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Giờ khám <span
                                    class="text-red-500">*</span></label>
                            <div id='time_infomation'
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-700">
                                Đang tải lịch hẹn...
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phòng khám <span
                                    class="text-red-500">*</span></label>
                            <input type="hidden" name="room_id" value="{{ old('room_id', $appointment->room_id) }}">
                            <div id='room_infomation'
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-700 font-medium">
                                Đang tải thông tin phòng...
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái <span
                                    class="text-red-500">*</span></label>
                            <select name="status" required
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                <option value="pending"
                                    {{ old('status', $appointment->status) === 'pending' ? 'selected' : '' }}>Đã tiếp nhận
                                </option>
                                <option value="checked_in"
                                    {{ old('status', $appointment->status) === 'checked_in' ? 'selected' : '' }}>Đã checkin</option>
                                <option value="examining"
                                    {{ old('status', $appointment->status) === 'examining' ? 'selected' : '' }}>Đang
                                    khám</option>
                                <option value="completed"
                                    {{ old('status', $appointment->status) === 'completed' ? 'selected' : '' }}>Hoàn
                                    thành</option>
                                <option value="cancelled"
                                    {{ old('status', $appointment->status) === 'cancelled' ? 'selected' : '' }}>Đã huỷ
                                </option>
                                <option value="absent"
                                    {{ old('status', $appointment->status) === 'absent' ? 'selected' : '' }}>Vắng mặt
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nguồn đặt lịch <span
                                    class="text-red-500">*</span></label>
                            <select name="source" required
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                <option value="counter"
                                    {{ old('source', $appointment->source) === 'counter' ? 'selected' : '' }}>Quầy lễ
                                    tân</option>
                                <option value="web"
                                    {{ old('source', $appointment->source) === 'web' ? 'selected' : '' }}>Website đặt
                                    trực tuyến</option>
                                <option value="chatbot"
                                    {{ old('source', $appointment->source) === 'chatbot' ? 'selected' : '' }}>Hỗ trợ
                                    Chatbot</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lý do khám <span
                                class="text-red-500">*</span></label>
                        <textarea name="reason" rows="3" required placeholder="Nhập triệu chứng sơ bộ hoặc lý do khám..."
                            class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">{{ old('reason', $appointment->reason) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú của lễ tân</label>
                        <textarea name="receptionist_note" rows="2" placeholder="Ghi chú thêm về yêu cầu đặc biệt của bệnh nhân..."
                            class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">{{ old('receptionist_note', $appointment->receptionist_note) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Chỉ số sinh tồn (Vitals - chiếm 1/3) -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3
                        class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2 flex items-center gap-2">
                        <i class="fa-solid fa-heart-pulse text-red-500"></i>
                        Chỉ số sinh tồn (Vitals)
                    </h3>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nhịp tim (Pulse)</label>
                                <div class="relative">
                                    <input type="number" name="vital_pulse"
                                        value="{{ old('vital_pulse', $appointment->vital_pulse) }}" min="0"
                                        placeholder="VD: 80"
                                        class="block w-full py-2 pl-3 pr-12 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                    <span
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-gray-400 pointer-events-none">bpm</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nhịp thở (Resp)</label>
                                <div class="relative">
                                    <input type="number" name="vital_respiratory"
                                        value="{{ old('vital_respiratory', $appointment->vital_respiratory) }}"
                                        min="0" placeholder="VD: 18"
                                        class="block w-full py-2 pl-3 pr-12 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                    <span
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-gray-400 pointer-events-none">l/ph</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Huyết áp Tâm thu</label>
                                <div class="relative">
                                    <input type="number" name="vital_systolic_bp"
                                        value="{{ old('vital_systolic_bp', $appointment->vital_systolic_bp) }}"
                                        min="0" placeholder="VD: 120"
                                        class="block w-full py-2 pl-3 pr-12 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                    <span
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-gray-400 pointer-events-none">mmHg</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Huyết áp Tâm trương</label>
                                <div class="relative">
                                    <input type="number" name="vital_diastolic_bp"
                                        value="{{ old('vital_diastolic_bp', $appointment->vital_diastolic_bp) }}"
                                        min="0" placeholder="VD: 80"
                                        class="block w-full py-2 pl-3 pr-12 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                    <span
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-gray-400 pointer-events-none">mmHg</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nhiệt độ (Temp)</label>
                                <div class="relative">
                                    <input type="number" step="0.1" name="vital_temperature"
                                        value="{{ old('vital_temperature', $appointment->vital_temperature) }}"
                                        min="0" placeholder="VD: 36.5"
                                        class="block w-full py-2 pl-3 pr-8 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                    <span
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-gray-400 pointer-events-none">°C</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nồng độ SpO2</label>
                                <div class="relative">
                                    <input type="number" step="0.1" name="vital_spo2"
                                        value="{{ old('vital_spo2', $appointment->vital_spo2) }}" min="0"
                                        max="100" placeholder="VD: 98"
                                        class="block w-full py-2 pl-3 pr-8 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                    <span
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-gray-400 pointer-events-none">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Cân nặng (Weight)</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="vital_weight_kg" x-model="weight"
                                        placeholder="VD: 65"
                                        class="block w-full py-2 pl-3 pr-10 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                    <span
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-gray-400 pointer-events-none">kg</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Chiều cao (Height)</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="vital_height_cm" x-model="height"
                                        placeholder="VD: 170"
                                        class="block w-full py-2 pl-3 pr-10 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                    <span
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-gray-400 pointer-events-none">cm</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Chỉ số BMI (Tự động
                                tính)</label>
                            <input type="number" step="0.01" name="vital_bmi" :value="bmi" readonly
                                placeholder="Nhập cân nặng & chiều cao..."
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 text-sm outline-none font-medium text-gray-700">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Ghi chú sinh hiệu</label>
                            <textarea name="vital_note" rows="2" placeholder="Ghi chú thêm về mạch, huyết áp hoặc trạng thái đo..."
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">{{ old('vital_note', $appointment->vital_note) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Người đo sinh hiệu</label>
                            <select name="measured_by"
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                <option value="">-- Chọn nhân viên --</option>
                                @foreach ($users as $usr)
                                    <option value="{{ $usr->id }}"
                                        {{ old('measured_by', $appointment->measured_by) == $usr->id ? 'selected' : '' }}>
                                        {{ $usr->full_name }}
                                        ({{ $usr->role === 'receptionist' ? 'Lễ tân' : ($usr->role === 'doctor' ? 'Bác sĩ' : 'Admin') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.appointments.index') }}"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                Huỷ
            </a>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
            </button>
        </div>
    </form>

    @php
        $initDate = $appointment->appointment_date ? $appointment->appointment_date->toDateString() : '';
        $initTime = $appointment->appointment_time ? substr($appointment->appointment_time, 0, 5) : '';
        $initRoomName = $appointment->room ? $appointment->room->name . ' - ' . $appointment->room->room_number : '';
    @endphp
    <script>
        const initialSpecialty = @json(old('specialty_id', $appointment->specialty_id));
        const initialDoctor = @json(old('doctor_profile_id', $appointment->doctor_profile_id));
        const initialDate = @json(old('appointment_date', $initDate));
        const initialTime = @json(old('appointment_time', $initTime));
        const initialRoomId = @json(old('room_id', $appointment->room_id));
        const initialRoomName = @json($initRoomName);
    </script>

    <script>
        const specialtySelect = document.querySelector('select[name="specialty_id"]');
        const doctorSelect = document.querySelector('select[name="doctor_profile_id"]');
        const appointmentDateSelect = document.querySelector('input[name="appointment_date"]');

        const timeAppointment = document.querySelector('#time_infomation');
        const roomInput = document.querySelector('input[name="room_id"]');
        const roomInfo = document.querySelector('#room_infomation');

        function renderDoctors(doctors) {
            doctorSelect.innerHTML = '<option value="">-- Chọn bác sĩ --</option>';

            if (doctors.length === 0) {
                const option = document.createElement('option');
                option.textContent = 'Không có bác sĩ nào thuộc chuyên khoa';
                option.disabled = true;
                doctorSelect.appendChild(option);
                return;
            }
            doctors.forEach(doctor => {
                const option = document.createElement('option');
                option.value = doctor.id;
                option.textContent = doctor.user.full_name;
                if (doctor.id == initialDoctor) {
                    option.selected = true;
                }
                doctorSelect.appendChild(option);
            });
        }

        function renderTimeAppointment(times) {
            if (!times || times.length === 0) {
                timeAppointment.innerHTML = 'Không có lịch khám trong ngày này.';
                roomInput.value = '';
                roomInfo.textContent = 'Vui lòng chọn ngày có lịch khám';
                return;
            }

            const div = document.createElement('div');
            div.classList.add('flex', 'flex-wrap', 'gap-3');

            let hasChecked = false;

            const htmls = times.map((slot, index) => {
                const isChecked = slot.time === initialTime;
                if (isChecked) hasChecked = true;
                
                // Nếu ca này đang được chọn (lịch cũ), không chặn dù nó báo is_full = true
                const disabled = (slot.is_full && !isChecked) ? 'disabled' : '';
                const labelClass = (slot.is_full && !isChecked) 
                    ? 'block px-4 py-2 border border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed rounded-lg'
                    : 'block px-4 py-2 border border-green-500 rounded-lg cursor-pointer peer-checked:bg-green-500 peer-checked:text-white';
                    
                const roomDisplay = slot.room ? `${slot.room.name} - ${slot.room.room_number}` : '';
                const roomId = slot.room ? slot.room.id : '';

                return `
                    <div>
                        <input type="radio" name="appointment_time" value="${slot.time}" id="time_${index}"
                            class="hidden peer" data-room-id="${roomId}" data-room-name="${roomDisplay}" ${disabled} ${isChecked ? 'checked' : ''}>

                        <label for="time_${index}" class="${labelClass}">
                            ${slot.time}
                        </label>
                    </div>
                `;
            }).join('');

            div.innerHTML = htmls;
            timeAppointment.innerHTML = '';
            timeAppointment.appendChild(div);

            const appointmentTimes = document.querySelectorAll('input[name="appointment_time"]');

            appointmentTimes.forEach(radio => {
                radio.addEventListener('change', (event) => {
                    const selected = event.target;
                    roomInput.value = selected.dataset.roomId;
                    roomInfo.textContent = selected.dataset.roomName;
                });
            });
            
            // Tự động set thông tin phòng nếu có radio checked
            const checkedRadio = document.querySelector('input[name="appointment_time"]:checked');
            if(checkedRadio) {
                roomInput.value = checkedRadio.dataset.roomId;
                roomInfo.textContent = checkedRadio.dataset.roomName;
            } else if (!hasChecked) {
                roomInput.value = '';
                roomInfo.textContent = 'Vui lòng chọn ca khám';
            }
        }
        
        function handleSpecialtyChange(event) {
            doctorSelect.innerHTML = '<option value="">Đang tải bác sĩ...</option>';
            let specialtyId = event.target.value;

            if (specialtyId) {
                axios
                    .get('/api/doctors/by-specialty/' + specialtyId)
                    .then((response) => {
                        renderDoctors(response.data.data);
                    })
                    .catch((error) => {
                        const option = document.createElement('option');
                        option.textContent = 'Có lỗi xảy ra vui lòng thử lại!';
                        option.disabled = true;
                        doctorSelect.appendChild(option);
                    });
            } else {
                doctorSelect.innerHTML = '<option value="">-- Chọn bác sĩ --</option>';
            }
            timeAppointment.innerHTML = 'Vui lòng chọn bác sĩ và ngày khám';
            roomInfo.textContent = 'Vui lòng chọn bác sĩ và ngày khám';
            roomInput.value = '';
        }

        function handleDoctorOrAppointmentChange(event) {
            let doctorId = doctorSelect.value;
            let appointmentDate = appointmentDateSelect.value;

            if (doctorId && appointmentDate) {
                timeAppointment.innerHTML = 'Đang tải lịch hẹn...';
                axios
                    .get('/api/work-schedule/by-doctor-date/' + doctorId + '/' + appointmentDate)
                    .then((response) => {
                        renderTimeAppointment(response.data.data.today_schedule);
                    })
                    .catch((error) => {
                        timeAppointment.innerHTML = 'Có lỗi xảy ra vui lòng thử lại!';
                        console.log(error);
                    });
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            if (initialDoctor && initialDate) {
                if (initialRoomId) {
                    roomInput.value = initialRoomId;
                    roomInfo.textContent = initialRoomName || 'Đang tải thông tin phòng...';
                }
                handleDoctorOrAppointmentChange();
            }
        });
    </script>
</x-layouts.admin>
