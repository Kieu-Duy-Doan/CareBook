<x-layouts.receptionist title="Thêm lịch hẹn mới">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('receptionist.dashboard') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <a href="{{ route('receptionist.appointments.index') }}" class="hover:text-blue-600 transition-colors">Lịch
                    hẹn</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <span class="text-gray-800 font-medium">Thêm mới</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Thêm lịch hẹn mới</h2>
            <p class="text-gray-500 mt-1">Tạo ca hẹn mới tại quầy hoặc lên lịch đặt chỗ cho bệnh nhân.</p>
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

    <form action="{{ route('receptionist.appointments.store') }}" method="POST">
        @csrf

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
                            <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ old('patient_profile_id') }}',
                                selectedName: '',
                                patients: [
                                    @foreach($patients as $pat)
                                        {
                                            id: '{{ $pat->id }}',
                                            name: '{{ addslashes($pat->full_name) }}',
                                            phone: '{{ $pat->phone ?? '' }}',
                                            dob: '{{ $pat->date_of_birth ? $pat->date_of_birth->format('d/m/Y') : '' }}'
                                        },
                                    @endforeach
                                ],
                                init() {
                                    let selected = this.patients.find(p => p.id == this.selectedId);
                                    if (selected) {
                                        this.selectedName = selected.name + (selected.phone ? ' (SĐT: ' + selected.phone + ')' : '');
                                    }
                                },
                                get filteredPatients() {
                                    if (!this.search) return this.patients;
                                    let searchLower = this.search.toLowerCase();
                                    return this.patients.filter(p => 
                                        p.name.toLowerCase().includes(searchLower) || 
                                        p.phone.toLowerCase().includes(searchLower)
                                    );
                                },
                                selectPatient(patient) {
                                    this.selectedId = patient.id;
                                    this.selectedName = patient.name + (patient.phone ? ' (SĐT: ' + patient.phone + ')' : '');
                                    this.open = false;
                                    this.search = '';
                                }
                            }" class="relative">
                                
                                <input type="hidden" name="patient_profile_id" :value="selectedId" required>

                                <button type="button" @click="open = !open" 
                                    class="w-full bg-white border border-gray-300 rounded-lg py-2 px-3 text-left focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm flex items-center justify-between bg-white">
                                    <span x-text="selectedName || '-- Chọn bệnh nhân --'" class="truncate"></span>
                                    <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>
                                </button>

                                <div x-show="open" @click.outside="open = false" 
                                    class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto p-2"
                                    style="display: none;">
                                    
                                    <div class="relative mb-2">
                                        <input type="text" x-model="search" placeholder="Tìm theo tên hoặc số điện thoại..."
                                            class="w-full pl-8 pr-3 py-1.5 border border-gray-300 rounded-md text-xs outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                                    </div>

                                    <ul class="space-y-1">
                                        <template x-for="p in filteredPatients" :key="p.id">
                                            <li>
                                                <button type="button" @click="selectPatient(p)"
                                                    class="w-full text-left px-3 py-2 text-xs rounded hover:bg-blue-50 hover:text-blue-700 transition-colors flex flex-col">
                                                    <span class="font-semibold" x-text="p.name"></span>
                                                    <span class="text-gray-500 text-[10px]" x-text="'SĐT: ' + (p.phone || 'N/A') + ' - Ngày sinh: ' + (p.dob || 'N/A')"></span>
                                                </button>
                                            </li>
                                        </template>
                                        <li x-show="filteredPatients.length === 0" class="text-center py-4 text-xs text-gray-400">
                                            Không tìm thấy bệnh nhân nào
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chuyên khoa khám <span
                                    class="text-red-500">*</span></label>
                            <select name="specialty_id" onchange = "handleSpecialtyChange(event)" required
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                <option value="">-- Chọn chuyên khoa --</option>
                                @foreach ($specialties as $sp)
                                    <option value="{{ $sp->id }}">{{ $sp->name }}</option>
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
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngày hẹn khám <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="appointment_date" value="{{ old('appointment_date') }}"
                                required min="{{ today()->toDateString() }}"
                                onchange="handleDoctorOrAppointmentChange(event)"
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Giờ khám <span
                                    class="text-red-500">*</span></label>
                            {{-- <select name="appointment_time" x-model="selectedSlot" required
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                <option value="">-- Chọn giờ khám --</option>

                            </select> --}}
                            <div id='time_infomation'
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-700">
                                Vui lòng chọn chuyên khoa - bác sĩ - ngày khám - ca khám trước
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phòng khám <span
                                    class="text-red-500">*</span></label>
                            <input type="hidden" name="room_id">
                            <div id='room_infomation'
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-700">
                                Vui lòng chọn chuyên khoa - bác sĩ - ngày khám - ca khám trước
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái <span
                                    class="text-red-500">*</span></label>
                            <select name="status" required
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Đã tiếp nhận
                                </option>
                                <option value="checked_in" {{ old('status') === 'checked_in' ? 'selected' : '' }}>Đã checkin</option>
                                <option value="examining" {{ old('status') === 'examining' ? 'selected' : '' }}>Đang
                                    khám</option>
                                <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Hoàn
                                    thành</option>
                                <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Đã huỷ
                                </option>
                                <option value="absent" {{ old('status') === 'absent' ? 'selected' : '' }}>Vắng mặt
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nguồn đặt lịch <span
                                    class="text-red-500">*</span></label>
                            <select name="source" required
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                <option value="counter" {{ old('source', 'counter') === 'counter' ? 'selected' : '' }}>
                                    Quầy lễ tân</option>
                                <option value="web" {{ old('source') === 'web' ? 'selected' : '' }}>Website đặt
                                    trực
                                    tuyến</option>
                                <option value="chatbot" {{ old('source') === 'chatbot' ? 'selected' : '' }}>Hỗ trợ
                                    Chatbot</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lý do khám <span
                                class="text-red-500">*</span></label>
                        <textarea name="reason" rows="3" required placeholder="Nhập triệu chứng sơ bộ hoặc lý do khám..."
                            class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">{{ old('reason') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú của lễ tân</label>
                        <textarea name="receptionist_note" rows="2" placeholder="Ghi chú thêm về yêu cầu đặc biệt của bệnh nhân..."
                            class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">{{ old('receptionist_note') }}</textarea>
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
                                    <input type="number" name="vital_pulse" value="{{ old('vital_pulse') }}"
                                        min="0" placeholder="VD: 80"
                                        class="block w-full py-2 pl-3 pr-12 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                    <span
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-gray-400 pointer-events-none">bpm</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nhịp thở (Resp)</label>
                                <div class="relative">
                                    <input type="number" name="vital_respiratory"
                                        value="{{ old('vital_respiratory') }}" min="0" placeholder="VD: 18"
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
                                        value="{{ old('vital_systolic_bp') }}" min="0" placeholder="VD: 120"
                                        class="block w-full py-2 pl-3 pr-12 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                    <span
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-gray-400 pointer-events-none">mmHg</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Huyết áp Tâm trương</label>
                                <div class="relative">
                                    <input type="number" name="vital_diastolic_bp"
                                        value="{{ old('vital_diastolic_bp') }}" min="0" placeholder="VD: 80"
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
                                        value="{{ old('vital_temperature') }}" min="0" placeholder="VD: 36.5"
                                        class="block w-full py-2 pl-3 pr-8 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                                    <span
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs text-gray-400 pointer-events-none">°C</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nồng độ SpO2</label>
                                <div class="relative">
                                    <input type="number" step="0.1" name="vital_spo2"
                                        value="{{ old('vital_spo2') }}" min="0" max="100"
                                        placeholder="VD: 98"
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
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">{{ old('vital_note') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Người đo sinh hiệu</label>
                            <select name="measured_by"
                                class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none bg-white">
                                <option value="">-- Chọn nhân viên --</option>
                                @foreach ($users as $usr)
                                    <option value="{{ $usr->id }}"
                                        {{ old('measured_by', Auth::id()) == $usr->id ? 'selected' : '' }}>
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
            <a href="{{ route('receptionist.appointments.index') }}"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                Huỷ
            </a>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Lưu lịch hẹn
            </button>
        </div>
    </form>

    <script>
        const initialSpecialty = @json(old('specialty_id'));
        const initialDoctor = @json(old('doctor_profile_id'));
        const initialDate = @json(old('appointment_date', today()->toDateString()));
    </script>

    <script>
        const specialtySelect = document.querySelector('select[name="specialty_id"]');
        const doctorSelect = document.querySelector('select[name="doctor_profile_id"]');
        const appointmentDateSelect = document.querySelector('input[name="appointment_date"]');

        const timeAppointment = document.querySelector('#time_infomation');

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
            if (times.length === 0) {
                timeAppointment.innerHTML = 'Không có lịch hiện tại'
                return;
            }

            const div = document.createElement('div');

            div.classList.add(
                'flex',
                'flex-wrap',
                'gap-3',
            );

            htmls = times.map((slot, index) => {
                const disabled = slot.is_full ? 'disabled' : '';
                const labelClass = slot.is_full 
                    ? 'block px-4 py-2 border border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed rounded-lg'
                    : 'block px-4 py-2 border border-green-500 rounded-lg cursor-pointer peer-checked:bg-green-500 peer-checked:text-white';
                    
                return `
                    <div>
                        <input type="radio" name="appointment_time" value="${slot.time}" id="time_${index}"
                            class="hidden peer" data-room-id="${slot.room.id}" data-room-name="${slot.room.name} - ${slot.room.room_number} - ${slot.room.building}" ${disabled}>

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
            const roomInput = document.querySelector('input[name="room_id"]');
            const roomInfo = document.querySelector('#room_infomation');

            appointmentTimes.forEach(radio => {
                radio.addEventListener('change', (event) => {
                    const selected = event.target;
                    roomInput.value = selected.dataset.roomId;
                    roomInfo.textContent = selected.dataset.roomName;
                });
            });
        }
        // Thay đổi chuyên khoa

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
                    })
                    .finally(() => {
                        console.log("Request completed");
                    });
            }
        }


        // Thay đổi bác sĩ hoặc ngày đặt để render ra lịch làm việc bác sĩ

        function handleDoctorOrAppointmentChange(event) {
            appointmentDateSelect.innerHTML = '<option value="">Đang tải lịch hẹn...</option>';
            let doctorId = doctorSelect.value;
            let appointmentDate = appointmentDateSelect.value;

            if (doctorId && appointmentDate) {
                axios
                    .get('/api/work-schedule/by-doctor-date/' + doctorId + '/' + appointmentDate)
                    .then((response) => {
                        renderTimeAppointment(response.data.data.today_schedule);
                        console.log(response.data.data.today_schedule);
                    })
                    .catch((error) => {
                        // timeAppointment.innerHTML = 'Có lỗi xảy ra vui lòng thử lại!';
                        console.log(error);

                    })
                    .finally(() => {
                        console.log("Request completed");
                    });
            }
        }
    </script>
</x-layouts.receptionist>