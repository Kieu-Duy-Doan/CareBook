<x-layouts.patient title="Đặt lịch khám">
<meta name="description" content="Đặt lịch khám bệnh trực tuyến tại CareBook - Nhanh chóng, tiện lợi, chính xác.">

<div x-data="{
    step: 1,

    /* BƯỚC 1 — Hồ sơ */
    profiles: {{ auth()->user()->patientProfiles->toJson() }},
    selectedProfile: null,

    /* BƯỚC 2 — Phương thức */
    bookingMethod: null,

    /* Nhánh Chuyên khoa */
    specialties: {{ $specialties->toJson() }},
    selectedSpecialty: null,
    specialtySearch: '',
    showSpecialtyModal: false,

    /* Nhánh Bác sĩ */
    allDoctors: {{ $doctors->toJson() }},
    selectedDoctor: null,
    doctorSearch: '',
    showDoctorModal: false,

    /* BƯỚC 3 — Ngày giờ */
    availableDates: [],
    loadingDates: false,
    selectedDate: null,
    slots: [],
    selectedSlot: null,
    loadingSlots: false,
    showSlotModal: false,

    /* BƯỚC 4 */
    reason: '',
    cancelledDoctorId: null, // Lưu ID bác sĩ đã huỷ để lọc khỏi danh sách gợi ý

    /* Computed */
    get filteredSpecialties() {
        if (!this.specialtySearch) return this.specialties;
        return this.specialties.filter(s =>
            s.name.toLowerCase().includes(this.specialtySearch.toLowerCase()) ||
            (s.description && s.description.toLowerCase().includes(this.specialtySearch.toLowerCase()))
        );
    },

    get filteredDoctors() {
        let docs = this.allDoctors;
        // Loại bỏ bác sĩ đã huỷ lịch khỏi danh sách gợi ý
        if (this.cancelledDoctorId) {
            docs = docs.filter(d => d.id !== this.cancelledDoctorId);
        }
        
        if (!this.doctorSearch) return docs;
        return docs.filter(d =>
            d.full_title.toLowerCase().includes(this.doctorSearch.toLowerCase()) ||
            (d.primary_specialty && d.primary_specialty.toLowerCase().includes(this.doctorSearch.toLowerCase()))
        );
    },

    get canGoStep3() {
        if (this.bookingMethod === 'specialty') return this.selectedSpecialty !== null;
        if (this.bookingMethod === 'doctor') return this.selectedDoctor !== null;
        return false;
    },

    get canGoStep4() {
        return this.selectedDate !== null && this.selectedSlot !== null;
    },

    /* Methods */
    selectProfile(profile) { this.selectedProfile = profile; },

    goStep2() { if (!this.selectedProfile) return; this.step = 2; },

    selectMethod(method) {
        this.bookingMethod = method;
        this.selectedSpecialty = null;
        this.selectedDoctor = null;
    },

    openSpecialtyModal() { this.specialtySearch = ''; this.showSpecialtyModal = true; },

    selectSpecialty(specialty) {
        this.selectedSpecialty = specialty;
        this.showSpecialtyModal = false;
        this.loadAvailableDates();
    },

    openDoctorModal() { 
        if (!this.selectedSpecialty || this.doctorSearch !== this.selectedSpecialty.name) {
            this.doctorSearch = ''; 
        }
        this.showDoctorModal = true; 
    },

    selectDoctor(doctor) {
        this.selectedDoctor = doctor;
        this.showDoctorModal = false;
        this.loadAvailableDates();
    },

    async loadAvailableDates() {
        this.loadingDates = true;
        this.availableDates = [];
        const params = this.bookingMethod === 'doctor'
            ? '?doctor_id=' + this.selectedDoctor.id
            : '?specialty_id=' + this.selectedSpecialty.id;
        try {
            const res = await fetch('/dat-lich/ngay-kha-dung' + params, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.dates) {
                this.availableDates = data.dates;
            } else {
                console.error('API Error:', data);
                this.availableDates = [];
            }
        } catch(e) {
            console.error('Fetch Error:', e);
        }
        this.loadingDates = false;
        this.selectedDate = null;
        this.slots = [];
        this.selectedSlot = null;
    },

    selectDate(date) {
        this.selectedDate = date;
        this.slots = [];
        this.selectedSlot = null;
        this.showSlotModal = true;
        this.loadSlots();
    },

    goStep3() { if (!this.canGoStep3) return; this.step = 3; },

    async loadSlots() {
        if (!this.selectedDate) return;
        this.loadingSlots = true;
        const params = this.bookingMethod === 'doctor'
            ? '?doctor_id=' + this.selectedDoctor.id + '&date=' + this.selectedDate.date
            : '?specialty_id=' + this.selectedSpecialty.id + '&date=' + this.selectedDate.date;
        try {
            const res = await fetch('/dat-lich/slots' + params, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.slots) {
                this.slots = data.slots;
            } else {
                console.error('API Error:', data);
                this.slots = [];
            }
        } catch(e) {
            console.error('Fetch Error:', e);
        }
        this.loadingSlots = false;
    },

    init() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('fast_track')) {
            const pId = parseInt(urlParams.get('patient_profile_id'));
            const sId = parseInt(urlParams.get('specialty_id'));
            const dId = parseInt(urlParams.get('doctor_id')); // Bác sĩ được chọn thay thế
            const cId = parseInt(urlParams.get('cancelled_doctor_id')); // Bác sĩ cũ bị huỷ
            const bMethod = urlParams.get('booking_method');
            const r = urlParams.get('reason');
            
            if (pId) {
                this.selectedProfile = this.profiles.find(p => p.id === pId) || null;
            }
            if (sId) {
                this.selectedSpecialty = this.specialties.find(s => s.id === sId) || null;
            }
            if (cId) {
                this.cancelledDoctorId = cId;
            } else if (!dId && bMethod === 'doctor') {
                // Tương thích ngược: nếu không có cId nhưng có bMethod=doctor và gọi từ popup huỷ cũ
                // Nhưng do url thay thế giờ đã truyền dId, nên ta có thể bỏ qua hoặc gán dự phòng.
            }

            if (bMethod === 'specialty' && sId && !dId) {
                this.bookingMethod = 'specialty';
                this.step = 3;
                this.loadAvailableDates();
            } else if (dId) {
                this.bookingMethod = 'doctor';
                this.selectedDoctor = this.allDoctors.find(d => d.id === dId) || null;
                
                if (this.selectedDoctor) {
                    this.step = 3;
                    this.loadAvailableDates();
                } else {
                    this.step = 2;
                    if (this.selectedSpecialty) {
                        this.doctorSearch = this.selectedSpecialty.name;
                    }
                    setTimeout(() => { this.showDoctorModal = true; }, 500);
                }
            } else if (sId) {
                this.bookingMethod = 'specialty';
                this.step = 3;
                this.loadAvailableDates();
            }
            
            if (r) {
                this.reason = decodeURIComponent(r);
            }
            
            if (this.step !== 2 && this.step !== 3 && this.selectedProfile && (this.selectedSpecialty || this.selectedDoctor)) {
                this.step = 3;
                this.loadAvailableDates();
            }
        }
    },

    selectSlot(slot) {
        if (!slot.available) return;
        this.selectedSlot = slot;
        this.showSlotModal = false;
    },

    goStep4() { if (!this.canGoStep4) return; this.step = 4; }
}" class="min-h-screen" style="background-color:#f8fafc; font-family:'Be Vietnam Pro',sans-serif;">

    {{-- ===== PROGRESS STEPPER ===== --}}
    <div class="sticky z-30 bg-white/90 backdrop-blur-sm border-b shadow-sm transition-all top-[110px] md:top-[124px]" style="border-color:#e2e8f0;">
        <div class="max-w-5xl mx-auto px-4 py-3">
            <div class="flex items-start justify-between relative">
                {{-- Line nền xám --}}
                <div class="absolute h-0.5 bg-gray-200 z-0" style="top:16px; left:10%; right:10%;"></div>
                {{-- Line xanh progress --}}
                <div class="absolute h-0.5 z-0 transition-all duration-500" style="top:16px; left:10%; background-color:var(--primary);"
                     :style="'width:' + ((step-1)/3 * 80) + '%'"></div>

                {{-- Step 1 --}}
                <div class="flex flex-col items-center z-10 w-1/4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-base font-bold border-2 transition-all"
                         :class="step >= 1 ? 'text-white shadow-md' : 'bg-white border-gray-300 text-gray-400'"
                         :style="step >= 1 ? 'background-color:var(--primary);border-color:var(--primary);' : ''">
                        <i x-show="step > 1" class="fa-solid fa-check text-sm"></i>
                        <span x-show="step <= 1">1</span>
                    </div>
                    <span class="text-sm mt-2 text-center leading-tight font-bold"
                          :style="step >= 1 ? 'color:var(--primary);' : 'color:#9ca3af;'">Hồ sơ</span>
                </div>

                {{-- Step 2 --}}
                <div class="flex flex-col items-center z-10 w-1/4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-base font-bold border-2 transition-all"
                         :class="step >= 2 ? 'text-white shadow-md' : 'bg-white border-gray-300 text-gray-400'"
                         :style="step >= 2 ? 'background-color:var(--primary);border-color:var(--primary);' : ''">
                        <i x-show="step > 2" class="fa-solid fa-check text-sm"></i>
                        <span x-show="step <= 2">2</span>
                    </div>
                    <span class="text-sm mt-2 text-center leading-tight font-bold"
                          :style="step >= 2 ? 'color:var(--primary);' : 'color:#9ca3af;'">Dịch vụ</span>
                </div>

                {{-- Step 3 --}}
                <div class="flex flex-col items-center z-10 w-1/4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-base font-bold border-2 transition-all"
                         :class="step >= 3 ? 'text-white shadow-md' : 'bg-white border-gray-300 text-gray-400'"
                         :style="step >= 3 ? 'background-color:var(--primary);border-color:var(--primary);' : ''">
                        <i x-show="step > 3" class="fa-solid fa-check text-sm"></i>
                        <span x-show="step <= 3">3</span>
                    </div>
                    <span class="text-sm mt-2 text-center leading-tight font-bold"
                          :style="step >= 3 ? 'color:var(--primary);' : 'color:#9ca3af;'">Thời gian</span>
                </div>

                {{-- Step 4 --}}
                <div class="flex flex-col items-center z-10 w-1/4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-base font-bold border-2 transition-all"
                         :class="step === 4 ? 'text-white shadow-md' : 'bg-white border-gray-300 text-gray-400'"
                         :style="step === 4 ? 'background-color:var(--primary);border-color:var(--primary);' : ''">
                        <span>4</span>
                    </div>
                    <span class="text-sm mt-2 text-center leading-tight font-bold"
                          :style="step === 4 ? 'color:var(--primary);' : 'color:#9ca3af;'">Xác nhận</span>
                </div>
            </div>

        </div>
    {{-- Alerts Container --}}
    <div class="max-w-5xl mx-auto px-4 mt-6">
        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r-lg shadow-sm animate-fade-in-down">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-bold text-red-800">Lỗi khi đặt lịch</p>
                        <p class="text-sm text-red-700 mt-1">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r-lg shadow-sm animate-fade-in-down">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mt-0.5">
                        <i class="fa-solid fa-circle-xmark text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-bold text-red-800">Vui lòng kiểm tra lại thông tin:</p>
                        <ul class="list-disc list-inside text-sm text-red-700 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded-r-lg shadow-sm animate-fade-in-down" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fa-solid fa-circle-check text-green-500 flex-shrink-0"></i>
                        <p class="text-sm font-bold text-green-800 ml-3">{{ session('success') }}</p>
                    </div>
                    <button @click="show = false" class="text-green-600 hover:text-green-800"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
        @endif
    </div>

    @include('patient.booking.steps.step1')

    @include('patient.booking.steps.step2')

    @include('patient.booking.modals.specialty')
    @include('patient.booking.modals.doctor')



    @include('patient.booking.steps.step3')

    @include('patient.booking.modals.slot')


    @include('patient.booking.steps.step4')

    </div>{{-- END x-data wrapper --}}

</x-layouts.patient>

