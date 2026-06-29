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

    /* Computed */
    get filteredSpecialties() {
        if (!this.specialtySearch) return this.specialties;
        return this.specialties.filter(s =>
            s.name.toLowerCase().includes(this.specialtySearch.toLowerCase()) ||
            (s.description && s.description.toLowerCase().includes(this.specialtySearch.toLowerCase()))
        );
    },

    get filteredDoctors() {
        if (!this.doctorSearch) return this.allDoctors;
        return this.allDoctors.filter(d =>
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

    openDoctorModal() { this.doctorSearch = ''; this.showDoctorModal = true; },

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

    selectSlot(slot) {
        if (!slot.available) return;
        this.selectedSlot = slot;
        this.showSlotModal = false;
    },

    goStep4() { if (!this.canGoStep4) return; this.step = 4; }
}" class="min-h-screen" style="background-color:#f8fafc; font-family:'Be Vietnam Pro',sans-serif;">

    {{-- ===== PROGRESS STEPPER ===== --}}
    <div class="sticky z-30 bg-white/90 backdrop-blur-sm border-b shadow-sm transition-all top-[110px] md:top-[124px]" style="border-color:#e2e8f0;">
        <div class="max-w-2xl mx-auto px-4 py-3">
            <div class="flex items-start justify-between relative">
                {{-- Line nền xám --}}
                <div class="absolute h-0.5 bg-gray-200 z-0" style="top:16px; left:10%; right:10%;"></div>
                {{-- Line xanh progress --}}
                <div class="absolute h-0.5 z-0 transition-all duration-500" style="top:16px; left:10%; background-color:var(--primary);"
                     :style="'width:' + ((step-1)/3 * 80) + '%'"></div>

                {{-- Step 1 --}}
                <div class="flex flex-col items-center z-10 w-1/4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all"
                         :class="step >= 1 ? 'text-white' : 'bg-white border-gray-300 text-gray-400'"
                         :style="step >= 1 ? 'background-color:var(--primary);border-color:var(--primary);' : ''">
                        <i x-show="step > 1" class="fa-solid fa-check text-xs"></i>
                        <span x-show="step <= 1">1</span>
                    </div>
                    <span class="text-xs mt-1 text-center leading-tight font-semibold"
                          :style="step >= 1 ? 'color:var(--primary);' : 'color:#9ca3af;'">Chọn thành viên</span>
                </div>

                {{-- Step 2 --}}
                <div class="flex flex-col items-center z-10 w-1/4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all"
                         :class="step >= 2 ? 'text-white' : 'bg-white border-gray-300 text-gray-400'"
                         :style="step >= 2 ? 'background-color:var(--primary);border-color:var(--primary);' : ''">
                        <i x-show="step > 2" class="fa-solid fa-check text-xs"></i>
                        <span x-show="step <= 2">2</span>
                    </div>
                    <span class="text-xs mt-1 text-center leading-tight font-semibold"
                          :style="step >= 2 ? 'color:var(--primary);' : 'color:#9ca3af;'">Phương thức</span>
                </div>

                {{-- Step 3 --}}
                <div class="flex flex-col items-center z-10 w-1/4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all"
                         :class="step >= 3 ? 'text-white' : 'bg-white border-gray-300 text-gray-400'"
                         :style="step >= 3 ? 'background-color:var(--primary);border-color:var(--primary);' : ''">
                        <i x-show="step > 3" class="fa-solid fa-check text-xs"></i>
                        <span x-show="step <= 3">3</span>
                    </div>
                    <span class="text-xs mt-1 text-center leading-tight font-semibold"
                          :style="step >= 3 ? 'color:var(--primary);' : 'color:#9ca3af;'">Chọn lịch khám</span>
                </div>

                {{-- Step 4 --}}
                <div class="flex flex-col items-center z-10 w-1/4">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all"
                         :class="step === 4 ? 'text-white' : 'bg-white border-gray-300 text-gray-400'"
                         :style="step === 4 ? 'background-color:var(--primary);border-color:var(--primary);' : ''">
                        <span>4</span>
                    </div>
                    <span class="text-xs mt-1 text-center leading-tight font-semibold"
                          :style="step === 4 ? 'color:var(--primary);' : 'color:#9ca3af;'">Xác nhận</span>
                </div>
            </div>

        </div>
    {{-- Alerts Container --}}
    <div class="max-w-2xl mx-auto px-4 mt-6">
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

    {{-- ===== BƯỚC 1: CHỌN THÀNH VIÊN ===== --}}
    <div x-show="step === 1" x-cloak class="max-w-2xl mx-auto px-4 py-6">
        <div class="flex items-center gap-3 mb-6">
            <i class="fa-solid fa-users text-2xl" style="color:var(--primary);"></i>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Chọn người cần khám</h1>
                <p class="text-sm text-gray-500">Vui lòng chọn thành viên gia đình cần đặt lịch khám</p>
            </div>
        </div>

        {{-- Danh sách hồ sơ --}}
        <div class="space-y-4 mb-6">
            <template x-for="profile in profiles" :key="profile.id">
                <div @click="selectProfile(profile)"
                     class="group relative flex items-center gap-4 p-5 bg-white border rounded-3xl cursor-pointer transition-all duration-300 overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-0.5"
                     :class="selectedProfile?.id === profile.id ? 'border-primary ring-1 ring-primary/20' : 'border-slate-100'">
                     
                    {{-- Active Decor --}}
                    <div class="absolute inset-y-0 left-0 w-1.5 transition-colors duration-300"
                         :class="selectedProfile?.id === profile.id ? 'bg-primary' : 'bg-transparent group-hover:bg-primary/20'"></div>

                    {{-- Radio dot --}}
                    <div class="flex-shrink-0 relative z-10">
                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-300"
                             :class="selectedProfile?.id === profile.id ? 'bg-primary border-primary shadow-sm shadow-primary/30' : 'border-slate-300 group-hover:border-primary/50'">
                            <i x-show="selectedProfile?.id === profile.id"
                               class="fa-solid fa-check text-white text-[10px]"></i>
                        </div>
                    </div>

                    {{-- Avatar --}}
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 relative z-10 transition-colors"
                         :class="selectedProfile?.id === profile.id ? 'bg-primary/10' : 'bg-slate-50 group-hover:bg-primary/5'">
                        <span class="font-extrabold text-lg text-primary"
                              x-text="profile.full_name.split(' ').slice(-2).map(w=>w[0]).join('').slice(0,2).toUpperCase()"></span>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0 relative z-10">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <p class="font-bold text-slate-800 text-lg transition-colors group-hover:text-primary" x-text="profile.full_name"></p>
                            <span x-show="profile.is_self"
                                  class="text-[10px] px-2.5 py-1 rounded-full font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20">
                                Chủ tài khoản
                            </span>
                        </div>
                        <div class="flex items-center gap-4 text-sm font-medium text-slate-500 flex-wrap">
                            <span x-show="profile.phone" class="flex items-center gap-1.5">
                                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center"><i class="fa-solid fa-phone text-[10px] text-slate-400"></i></div>
                                <span x-text="profile.phone"></span>
                            </span>
                            <span x-show="profile.date_of_birth" class="flex items-center gap-1.5">
                                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center"><i class="fa-solid fa-cake-candles text-[10px] text-slate-400"></i></div>
                                <span x-text="profile.date_of_birth ? new Date(profile.date_of_birth).toLocaleDateString('vi-VN') : ''"></span>
                            </span>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Thêm thành viên --}}
            <a href="{{ route('patient.profiles.create') }}?redirect=booking"
               class="flex items-center gap-4 p-5 border-2 border-dashed border-slate-200 rounded-3xl transition-all duration-300 group hover:border-primary hover:bg-primary/5">
                <div class="w-6 h-6"></div>
                <div class="w-14 h-14 rounded-2xl bg-slate-50 group-hover:bg-white flex items-center justify-center flex-shrink-0 transition-colors group-hover:shadow-sm">
                    <i class="fa-solid fa-plus text-slate-400 group-hover:text-primary transition-colors text-xl"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-600 group-hover:text-primary transition-colors text-lg">Thêm thành viên mới</p>
                    <p class="text-sm font-medium text-slate-400">Thêm hồ sơ cho người thân trong gia đình</p>
                </div>
            </a>
        </div>

        {{-- Cảnh báo không có hồ sơ --}}
        <div x-show="profiles.length === 0"
             class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-sm text-yellow-700 mb-4">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>
            Bạn chưa có hồ sơ bệnh nhân.
            <a href="{{ route('patient.profiles.create') }}" class="underline font-medium ml-1">Tạo hồ sơ ngay</a>
        </div>

        {{-- Navigation --}}
        <div class="flex gap-4 sticky bottom-0 bg-white pt-6 pb-4 border-t border-slate-100 z-20">
            <a href="{{ route('home') }}"
               class="flex-1 py-4 border-2 border-slate-200 text-slate-500 rounded-2xl text-center font-bold hover:bg-slate-50 hover:text-slate-700 transition-colors active:scale-95">
                <i class="fa-solid fa-xmark mr-1.5"></i> Thoát
            </a>
            <button @click="goStep2()"
                    :disabled="!selectedProfile"
                    class="py-4 rounded-2xl font-extrabold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 shadow-lg shadow-primary/30 hover:shadow-primary/40 active:scale-95 bg-primary hover:bg-primary-dark"
                    style="flex:2;">
                Tiếp tục <i class="fa-solid fa-arrow-right ml-1.5"></i>
            </button>
        </div>
    </div>
    {{-- END BƯỚC 1 --}}

    {{-- ===== BƯỚC 2: CHỌN PHƯƠNG THỨC ===== --}}
    <div x-show="step === 2" class="max-w-2xl mx-auto px-4 py-6">
        <div class="flex items-center gap-3 mb-6">
            <i class="fa-solid fa-stethoscope text-2xl" style="color:var(--primary);"></i>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Chọn phương thức đặt lịch</h2>
                <p class="text-sm text-gray-500">
                    Đặt lịch cho: <strong x-text="selectedProfile?.full_name"></strong>
                </p>
            </div>
        </div>

        {{-- 2 lựa chọn --}}
        <div class="space-y-4 mb-6">
            {{-- Theo chuyên khoa --}}
            <div @click="selectMethod('specialty')"
                 class="group relative flex items-center gap-4 p-5 bg-white border rounded-3xl cursor-pointer transition-all duration-300 overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-0.5"
                 :class="bookingMethod === 'specialty' ? 'border-primary ring-1 ring-primary/20' : 'border-slate-100'">
                 
                {{-- Active Decor --}}
                <div class="absolute inset-y-0 left-0 w-1.5 transition-colors duration-300"
                     :class="bookingMethod === 'specialty' ? 'bg-primary' : 'bg-transparent group-hover:bg-primary/20'"></div>

                <div class="flex-shrink-0 relative z-10">
                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-300"
                         :class="bookingMethod === 'specialty' ? 'bg-primary border-primary shadow-sm shadow-primary/30' : 'border-slate-300 group-hover:border-primary/50'">
                        <i x-show="bookingMethod === 'specialty'" class="fa-solid fa-check text-white text-[10px]"></i>
                    </div>
                </div>
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors relative z-10"
                     :class="bookingMethod === 'specialty' ? 'bg-primary/10' : 'bg-slate-50 group-hover:bg-primary/5'">
                    <i class="fa-solid fa-briefcase-medical text-2xl text-primary"></i>
                </div>
                <div class="relative z-10">
                    <p class="font-bold text-slate-800 text-lg transition-colors group-hover:text-primary">Theo chuyên khoa</p>
                    <p class="text-sm font-medium text-slate-500 mt-0.5">Hệ thống gợi ý bác sĩ phù hợp nhất</p>
                </div>
            </div>

            {{-- Theo bác sĩ --}}
            <div @click="selectMethod('doctor')"
                 class="group relative flex items-center gap-4 p-5 bg-white border rounded-3xl cursor-pointer transition-all duration-300 overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-0.5"
                 :class="bookingMethod === 'doctor' ? 'border-primary ring-1 ring-primary/20' : 'border-slate-100'">
                 
                {{-- Active Decor --}}
                <div class="absolute inset-y-0 left-0 w-1.5 transition-colors duration-300"
                     :class="bookingMethod === 'doctor' ? 'bg-primary' : 'bg-transparent group-hover:bg-primary/20'"></div>

                <div class="flex-shrink-0 relative z-10">
                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-300"
                         :class="bookingMethod === 'doctor' ? 'bg-primary border-primary shadow-sm shadow-primary/30' : 'border-slate-300 group-hover:border-primary/50'">
                        <i x-show="bookingMethod === 'doctor'" class="fa-solid fa-check text-white text-[10px]"></i>
                    </div>
                </div>
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors relative z-10"
                     :class="bookingMethod === 'doctor' ? 'bg-primary/10' : 'bg-slate-50 group-hover:bg-primary/5'">
                    <i class="fa-solid fa-user-doctor text-2xl text-primary"></i>
                </div>
                <div class="relative z-10">
                    <p class="font-bold text-slate-800 text-lg transition-colors group-hover:text-primary">Theo bác sĩ</p>
                    <p class="text-sm font-medium text-slate-500 mt-0.5">Chọn trực tiếp bác sĩ mong muốn khám</p>
                </div>
            </div>
        </div>

        {{-- Chọn chuyên khoa --}}
        <div x-show="bookingMethod === 'specialty'" class="mb-5">
            <button @click="openSpecialtyModal()"
                    class="w-full flex items-center gap-3 p-4 bg-white border-2 rounded-2xl text-left transition-all"
                    :style="selectedSpecialty ? 'border-color:var(--primary);' : 'border-color:#e2e8f0;'">
                <i class="fa-solid fa-briefcase-medical" style="color:var(--primary);"></i>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-400" x-show="!selectedSpecialty">Chưa chọn chuyên khoa — Nhấn để chọn</p>
                    <p class="font-semibold text-gray-800" x-show="selectedSpecialty" x-text="selectedSpecialty?.name"></p>
                    <p class="text-xs text-gray-400 truncate" x-show="selectedSpecialty"
                       x-text="selectedSpecialty.description ? (selectedSpecialty.description.length > 60 ? selectedSpecialty.description.substring(0,60) + '...' : selectedSpecialty.description) : 'Hệ thống sẽ tự động xếp bác sĩ phù hợp'"></p>
                </div>
                <i class="fa-solid fa-chevron-right text-gray-400 flex-shrink-0"></i>
            </button>
        </div>

        {{-- Chọn bác sĩ --}}
        <div x-show="bookingMethod === 'doctor'" class="mb-5">
            <button @click="openDoctorModal()"
                    class="w-full flex items-center gap-3 p-4 bg-white border-2 rounded-2xl text-left transition-all"
                    :style="selectedDoctor ? 'border-color:var(--primary);' : 'border-color:#e2e8f0;'">
                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0"
                     :style="selectedDoctor ? 'background-color:rgba(29,111,164,0.10);' : ''">
                    <i x-show="!selectedDoctor" class="fa-solid fa-user text-gray-400"></i>
                    <span x-show="selectedDoctor"
                          class="font-bold text-sm" style="color:var(--primary);"
                          x-text="selectedDoctor?.full_title?.split(' ').slice(-2).map(w=>w[0]).join('').slice(0,2).toUpperCase()">
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-gray-400 text-sm" x-show="!selectedDoctor">Chưa chọn bác sĩ — Nhấn để chọn</p>
                    <p class="font-bold text-gray-800 truncate" x-show="selectedDoctor" x-text="selectedDoctor?.full_title"></p>
                    <p class="text-sm font-medium truncate" x-show="selectedDoctor"
                       style="color:var(--primary);" x-text="selectedDoctor?.primary_specialty"></p>
                </div>
                <i class="fa-solid fa-chevron-right text-gray-400 flex-shrink-0"></i>
            </button>
        </div>

        {{-- Navigation --}}
        <div class="flex gap-4 sticky bottom-0 bg-white pt-6 pb-4 border-t border-slate-100 z-20">
            <button @click="step = 1"
                    class="flex-1 py-4 border-2 border-primary/20 text-primary rounded-2xl font-bold hover:bg-primary/5 transition-colors active:scale-95">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Quay lại
            </button>
            <button @click="goStep3()"
                    :disabled="!canGoStep3"
                    class="py-4 rounded-2xl font-extrabold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 shadow-lg shadow-primary/30 hover:shadow-primary/40 active:scale-95 bg-primary hover:bg-primary-dark"
                    style="flex:2;">
                Tiếp tục <i class="fa-solid fa-arrow-right ml-1.5"></i>
            </button>
        </div>
    </div>
    {{-- END BƯỚC 2 --}}

    {{-- ===== MODAL CHỌN CHUYÊN KHOA ===== --}}
    <div x-show="showSpecialtyModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/50"
         @click.self="showSpecialtyModal = false">
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl max-h-[85vh] flex flex-col"
             @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:#e2e8f0;">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-briefcase-medical" style="color:var(--primary);"></i>
                    <span class="font-bold text-lg text-gray-800">Chọn chuyên khoa</span>
                </div>
                <button @click="showSpecialtyModal = false"
                        class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors">
                    <i class="fa-solid fa-xmark text-gray-500"></i>
                </button>
            </div>

            <div class="px-4 py-3 border-b" style="border-color:#e2e8f0;">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2" style="color:var(--primary);"></i>
                    <input x-model="specialtySearch" type="search"
                           placeholder='Tìm kiếm... (VD: "Tim mạch")'
                           class="w-full pl-10 pr-4 py-3 border-2 rounded-xl text-sm focus:outline-none"
                           style="border-color:var(--primary);">
                </div>
            </div>

            <div class="overflow-y-auto flex-1">
                <template x-for="specialty in filteredSpecialties" :key="specialty.id">
                    <div @click="selectSpecialty(specialty)"
                         class="flex items-center gap-4 px-5 py-4 border-b hover:bg-blue-50 cursor-pointer transition-colors"
                         style="border-color:#f8fafc;">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800" x-text="specialty.name"></p>
                            <p class="text-sm text-gray-400 mt-0.5 line-clamp-2" x-text="specialty.description"></p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-300 flex-shrink-0"></i>
                    </div>
                </template>

                <div x-show="filteredSpecialties.length === 0" class="text-center py-12 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-3xl mb-3 block"></i>
                    <p>Không tìm thấy chuyên khoa nào</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL CHỌN BÁC SĨ ===== --}}
    <div x-show="showDoctorModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/50"
         @click.self="showDoctorModal = false">
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl max-h-[85vh] flex flex-col"
             @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color:#e2e8f0;">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-user-doctor" style="color:var(--primary);"></i>
                    <span class="font-bold text-lg text-gray-800">Chọn bác sĩ</span>
                </div>
                <button @click="showDoctorModal = false"
                        class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition-colors">
                    <i class="fa-solid fa-xmark text-gray-500"></i>
                </button>
            </div>

            <div class="px-4 py-3 border-b" style="border-color:#e2e8f0;">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2" style="color:var(--primary);"></i>
                    <input x-model="doctorSearch" type="search"
                           placeholder='Tìm kiếm... (VD: "Nguyễn")'
                           class="w-full pl-10 pr-4 py-3 border-2 rounded-xl text-sm focus:outline-none"
                           style="border-color:var(--primary);">
                </div>
            </div>

            <div class="overflow-y-auto flex-1">
                <template x-for="doctor in filteredDoctors" :key="doctor.id">
                    <div @click="selectDoctor(doctor)"
                         class="flex items-center gap-4 px-5 py-4 border-b hover:bg-blue-50 cursor-pointer transition-colors"
                         style="border-color:#f8fafc;">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                             style="background-color:rgba(29,111,164,0.10);">
                            <span class="font-bold" style="color:var(--primary);"
                                  x-text="doctor.full_title.split(' ').slice(-2).map(w=>w[0]).join('').slice(0,2).toUpperCase()"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-800 uppercase truncate" x-text="doctor.full_title"></p>
                            <p class="text-sm text-gray-400" x-text="doctor.level_label"></p>
                            <p class="text-sm font-medium" style="color:var(--primary);" x-text="doctor.primary_specialty"></p>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-300 flex-shrink-0"></i>
                    </div>
                </template>

                <div x-show="filteredDoctors.length === 0" class="text-center py-12 text-gray-400">
                    <i class="fa-solid fa-user-doctor text-3xl mb-3 block"></i>
                    <p>Không tìm thấy bác sĩ nào</p>
                </div>
            </div>
        </div>
    </div>
    {{-- END MODALS BƯỚC 2 --}}

    {{-- ===== BƯỚC 3: CHỌN LỊCH KHÁM ===== --}}
    <div x-show="step === 3" class="max-w-2xl mx-auto px-4 py-6">
        <div class="flex items-center gap-3 mb-4">
            <i class="fa-solid fa-calendar-days text-2xl" style="color:var(--primary);"></i>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Chọn ngày và giờ khám</h2>
                <p class="text-sm text-gray-500">
                    Bệnh nhân: <strong x-text="selectedProfile?.full_name"></strong>
                </p>
                <p class="text-sm text-gray-500" x-show="bookingMethod === 'doctor'">
                    Bác sĩ: <strong x-text="selectedDoctor?.full_title?.toUpperCase()"></strong>
                </p>
                <p class="text-sm text-gray-500" x-show="bookingMethod === 'specialty'">
                    Chuyên khoa: <strong x-text="selectedSpecialty?.name"></strong>
                </p>
            </div>
        </div>

        {{-- Chọn ngày --}}
        <div class="mb-4">
            <p class="font-semibold mb-3" style="color:var(--primary);">Chọn ngày khám:</p>

            {{-- Horizontal scroll dates --}}
            <div class="flex gap-3 overflow-x-auto pb-2" style="-ms-overflow-style:none;scrollbar-width:none;">
                <template x-for="dateObj in availableDates" :key="dateObj.date">
                    <button @click="selectDate(dateObj)"
                            type="button"
                            class="flex-shrink-0 flex flex-col items-center justify-center w-16 h-20 rounded-2xl border-2 transition-all"
                            :class="selectedDate?.date === dateObj.date
                                ? 'border-primary bg-primary text-white shadow-md'
                                : 'border-gray-200 text-gray-600 hover:border-primary/50 hover:bg-primary/5'">
                        <span class="text-xs" x-text="dateObj.day_name"></span>
                        <span class="text-lg font-bold" x-text="dateObj.display"></span>
                    </button>
                </template>

                {{-- Loading skeleton --}}
                <template x-if="loadingDates">
                    <div class="flex gap-3">
                        <template x-for="i in [1,2,3,4,5]" :key="i">
                            <div class="flex-shrink-0 w-16 h-20 rounded-2xl bg-gray-100 animate-pulse"></div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- Giờ khám (hiện sau khi chọn ngày) --}}
        <div x-show="selectedDate" x-transition class="bg-white border rounded-2xl p-4 mb-4" style="border-color:#e2e8f0;">
            {{-- Header phòng --}}
            <div class="rounded-xl p-3 mb-4" style="background-color:rgba(29,111,164,0.08);">
                <div class="flex items-center gap-2 font-semibold" style="color:var(--primary);">
                    <i class="fa-solid fa-location-dot"></i>
                    <span x-show="bookingMethod === 'doctor'" x-text="selectedDoctor?.room_name ?? 'Phòng khám'"></span>
                    <span x-show="bookingMethod === 'specialty'" x-text="selectedSpecialty?.name"></span>
                </div>
            </div>

            {{-- Đã chọn slot --}}
            <div x-show="selectedSlot"
                 class="flex items-center gap-3 p-3 border rounded-xl mb-4"
                 style="background-color:var(--primary-light);border-color:var(--primary);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color:var(--primary);">
                    <i class="fa-solid fa-clock text-white"></i>
                </div>
                <div>
                    <p class="text-lg font-bold" style="color:var(--primary);" x-text="selectedSlot?.time"></p>
                    <p class="text-sm text-gray-500"
                       x-text="selectedDate ? 'Ngày: ' + new Date(selectedDate.date).toLocaleDateString('vi-VN') : ''"></p>
                </div>
                <i class="fa-solid fa-circle-check text-xl ml-auto" style="color:#27AE60;"></i>
            </div>

            {{-- Nút đổi/chọn giờ --}}
            <button @click="showSlotModal = true"
                    class="w-full flex items-center justify-center gap-2 py-3 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                <i x-show="!selectedSlot" class="fa-regular fa-clock"></i>
                <i x-show="selectedSlot" class="fa-solid fa-pen text-xs"></i>
                <span x-text="selectedSlot ? 'Đổi giờ khám' : 'Chọn giờ khám'"></span>
            </button>
        </div>

        {{-- Empty state --}}
        <div x-show="!selectedDate"
             class="bg-gray-50 rounded-2xl p-8 text-center text-gray-400 mb-4">
            <i class="fa-solid fa-calendar text-4xl mb-3 block text-gray-300"></i>
            <p>Vui lòng chọn ngày khám</p>
        </div>

        {{-- Navigation --}}
        <div class="flex gap-4 sticky bottom-0 bg-white pt-6 pb-4 border-t border-slate-100 z-20">
            <button @click="step = 2"
                    class="flex-1 py-4 border-2 border-primary/20 text-primary rounded-2xl font-bold hover:bg-primary/5 transition-colors active:scale-95">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Quay lại
            </button>
            <button @click="goStep4()"
                    :disabled="!canGoStep4"
                    class="py-4 rounded-2xl font-extrabold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100 shadow-lg shadow-primary/30 hover:shadow-primary/40 active:scale-95 bg-primary hover:bg-primary-dark"
                    style="flex:2;">
                Tiếp tục <i class="fa-solid fa-arrow-right ml-1.5"></i>
            </button>
        </div>
    </div>
    {{-- END BƯỚC 3 --}}

    {{-- ===== MODAL CHỌN GIỜ KHÁM ===== --}}
    <div x-show="showSlotModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/50"
         @click.self="showSlotModal = false">
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl max-h-[80vh] flex flex-col"
             @click.stop>
            {{-- Header modal --}}
            <div class="flex items-center justify-between px-5 py-4 rounded-t-3xl" style="background-color:var(--primary);">
                <div class="flex items-center gap-2 text-white">
                    <i class="fa-solid fa-clock"></i>
                    <span class="font-bold">Chọn giờ khám</span>
                </div>
                <div class="text-white text-sm opacity-80"
                     x-text="selectedDate ? 'Ngày ' + new Date(selectedDate.date).toLocaleDateString('vi-VN') : ''"></div>
                <button @click="showSlotModal = false">
                    <i class="fa-solid fa-xmark text-white text-xl"></i>
                </button>
            </div>

            {{-- Loading --}}
            <div x-show="loadingSlots" class="flex items-center justify-center py-12">
                <i class="fa-solid fa-spinner fa-spin text-3xl" style="color:var(--primary);"></i>
            </div>

            {{-- Slots --}}
            <div x-show="!loadingSlots" class="overflow-y-auto flex-1 px-5 py-4">

                {{-- Phòng khám --}}
                <div x-show="selectedDoctor?.room_name"
                     class="flex items-center gap-2 font-semibold mb-4 p-3 rounded-xl"
                     style="color:var(--primary);background-color:var(--primary-light);">
                    <i class="fa-solid fa-location-dot"></i>
                    <span x-text="selectedDoctor?.room_name"></span>
                </div>

                {{-- Buổi sáng --}}
                <div x-show="slots.filter(s => parseInt(s.time.split(':')[0]) < 12).length > 0" class="mb-6">
                    <div class="flex items-center gap-2 text-orange-500 font-semibold mb-3">
                        <i class="fa-solid fa-sun"></i>
                        <span>Buổi sáng</span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <template x-for="slot in slots.filter(s => parseInt(s.time.split(':')[0]) < 12)" :key="slot.time">
                            <button @click="selectSlot(slot)"
                                    :disabled="!slot.available"
                                    class="flex items-center gap-2 px-4 py-3 rounded-xl border-2 text-sm font-medium transition-all"
                                    :class="{
                                        'border-gray-100 text-gray-300 cursor-not-allowed line-through bg-gray-50': !slot.available
                                    }"
                                    :style="slot.available
                                        ? (selectedSlot?.time === slot.time
                                            ? 'background-color:var(--primary);border-color:var(--primary);color:#ffffff;'
                                            : 'border-color:#e2e8f0;color:#374151;')
                                        : ''">
                                <i class="fa-regular fa-clock text-xs"></i>
                                <span x-text="slot.time"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Buổi chiều --}}
                <div x-show="slots.filter(s => parseInt(s.time.split(':')[0]) >= 12).length > 0">
                    <div class="flex items-center gap-2 text-blue-500 font-semibold mb-3">
                        <i class="fa-solid fa-cloud-sun"></i>
                        <span>Buổi chiều</span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <template x-for="slot in slots.filter(s => parseInt(s.time.split(':')[0]) >= 12)" :key="slot.time">
                            <button @click="selectSlot(slot)"
                                    :disabled="!slot.available"
                                    class="flex items-center gap-2 px-4 py-3 rounded-xl border-2 text-sm font-medium transition-all"
                                    :class="{
                                        'border-gray-100 text-gray-300 cursor-not-allowed line-through bg-gray-50': !slot.available
                                    }"
                                    :style="slot.available
                                        ? (selectedSlot?.time === slot.time
                                            ? 'background-color:var(--primary);border-color:var(--primary);color:#ffffff;'
                                            : 'border-color:#e2e8f0;color:#374151;')
                                        : ''">
                                <i class="fa-regular fa-clock text-xs"></i>
                                <span x-text="slot.time"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Không có slot --}}
                <div x-show="slots.length === 0 && !loadingSlots"
                     class="text-center py-8 text-gray-400">
                    <i class="fa-solid fa-calendar-xmark text-4xl mb-3 block"></i>
                    <p>Không có lịch khám vào ngày này</p>
                    <p class="text-sm mt-1">Vui lòng chọn ngày khác</p>
                </div>
            </div>
        </div>
    </div>
    {{-- END MODAL SLOT --}}

    {{-- ===== BƯỚC 4: XÁC NHẬN ===== --}}
    <div x-show="step === 4" class="max-w-2xl mx-auto px-4 py-6">
        <div class="flex items-center gap-3 mb-5">
            <i class="fa-solid fa-square-check text-2xl" style="color:var(--primary);"></i>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Xác nhận thông tin đặt lịch</h2>
                <p class="text-sm text-gray-500">Vui lòng kiểm tra thông tin và nhập triệu chứng</p>
            </div>
        </div>

        {{-- Nhập triệu chứng --}}
        <div class="bg-white border rounded-2xl p-5 mb-4" style="border-color:#e2e8f0;">
            <p class="font-bold mb-3" style="color:var(--primary);">
                <i class="fa-solid fa-pencil mr-2"></i>
                Triệu chứng / Lý do khám:
            </p>
            <textarea x-model="reason"
                      rows="4"
                      maxlength="500"
                      placeholder="Mô tả triệu chứng, tình trạng sức khoẻ hiện tại..."
                      class="w-full border border-gray-200 rounded-xl px-4 py-3 resize-none text-gray-800 focus:outline-none focus:ring-2 focus:border-transparent placeholder-gray-300 text-sm"
                      style="--tw-ring-color:var(--primary);"></textarea>
            <div class="flex justify-between mt-2">
                <p class="text-xs text-gray-400">
                    <i class="fa-solid fa-lightbulb text-yellow-400 mr-1"></i>
                    Thông tin chi tiết giúp bác sĩ chuẩn bị tốt hơn
                </p>
                <p class="text-xs text-gray-400" x-text="reason.length + '/500'"></p>
            </div>
        </div>

        {{-- Tóm tắt thông tin --}}
        <div class="bg-white border rounded-2xl overflow-hidden mb-4" style="border-color:#e2e8f0;">
            {{-- Header --}}
            <div class="flex items-center gap-2 px-5 py-3 border-b" style="background-color:var(--primary-light);border-color:rgba(29,111,164,0.10);">
                <i class="fa-solid fa-briefcase-medical" style="color:var(--primary);"></i>
                <span class="font-bold uppercase text-sm" style="color:var(--primary);">Thông tin đặt lịch</span>
            </div>

            <div class="divide-y divide-gray-50">
                <div class="flex items-start px-5 py-3">
                    <span class="text-gray-400 text-sm w-28 flex-shrink-0 pt-0.5">Bệnh nhân:</span>
                    <span class="font-semibold text-gray-800" x-text="selectedProfile?.full_name"></span>
                </div>
                <div class="flex items-start px-5 py-3">
                    <span class="text-gray-400 text-sm w-28 flex-shrink-0 pt-0.5">Phương thức:</span>
                    <span class="font-semibold text-gray-800"
                          x-text="bookingMethod === 'doctor' ? 'Theo bác sĩ' : 'Theo chuyên khoa'"></span>
                </div>
                <div class="flex items-start px-5 py-3" x-show="bookingMethod === 'doctor'">
                    <span class="text-gray-400 text-sm w-28 flex-shrink-0 pt-0.5">Bác sĩ:</span>
                    <span class="font-semibold text-gray-800 uppercase" x-text="selectedDoctor?.full_title"></span>
                </div>
                <div class="flex items-start px-5 py-3" x-show="bookingMethod === 'specialty'">
                    <span class="text-gray-400 text-sm w-28 flex-shrink-0 pt-0.5">Chuyên khoa:</span>
                    <span class="font-semibold text-gray-800" x-text="selectedSpecialty?.name"></span>
                </div>
                <div class="flex items-start px-5 py-3">
                    <span class="text-gray-400 text-sm w-28 flex-shrink-0 pt-0.5">Ngày khám:</span>
                    <span class="font-semibold text-gray-800"
                          x-text="selectedDate ? new Date(selectedDate.date).toLocaleDateString('vi-VN') : ''"></span>
                </div>
                <div class="flex items-start px-5 py-3">
                    <span class="text-gray-400 text-sm w-28 flex-shrink-0 pt-0.5">Giờ khám:</span>
                    <span class="font-bold text-lg" style="color:var(--primary);" x-text="selectedSlot?.time"></span>
                </div>
                <div class="flex items-start px-5 py-3">
                    <span class="text-gray-400 text-sm w-28 flex-shrink-0 pt-0.5">Phòng khám:</span>
                    <span class="font-semibold text-gray-800"
                          x-text="selectedSlot?.room_name ?? selectedDoctor?.room_name ?? '—'"></span>
                </div>
                <div class="flex items-start px-5 py-3">
                    <span class="text-gray-400 text-sm w-28 flex-shrink-0 pt-0.5">Triệu chứng:</span>
                    <span class="font-semibold text-gray-800 italic"
                          x-text="reason.trim() || 'Không có'"></span>
                </div>
                <div class="flex items-center px-5 py-4 bg-gray-50">
                    <div class="flex items-center gap-2 text-gray-500 text-sm">
                        <i class="fa-solid fa-credit-card"></i>
                        Phí khám dự kiến:
                    </div>
                    <span class="ml-auto font-bold" style="color:var(--primary);">Liên hệ tại quầy</span>
                </div>
            </div>
        </div>

        {{-- Lưu ý quan trọng --}}
        <div class="bg-orange-50 border border-orange-200 rounded-2xl p-4 mb-5">
            <p class="font-bold text-orange-700 mb-2">
                <i class="fa-solid fa-circle-info mr-2"></i>
                LƯU Ý QUAN TRỌNG
            </p>
            <ul class="text-sm text-orange-600 space-y-1.5">
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-circle text-[5px] mt-1.5 flex-shrink-0"></i>
                    Vui lòng có mặt trước 15 phút so với giờ hẹn
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-circle text-[5px] mt-1.5 flex-shrink-0"></i>
                    Mang theo CCCD và thẻ BHYT (nếu có)
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-circle text-[5px] mt-1.5 flex-shrink-0"></i>
                    Có thể huỷ lịch trước 2 tiếng qua ứng dụng
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-circle text-[5px] mt-1.5 flex-shrink-0"></i>
                    Tuân thủ quy định của bệnh viện
                </li>
            </ul>
        </div>

        {{-- Form submit --}}
        <form method="POST" action="{{ route('booking.store') }}" id="booking-form"
              x-data="{ 
                  submitting: false, 
                  errorMessage: '' 
              }" 
              @submit.prevent="
                  submitting = true; 
                  errorMessage = '';
                  fetch($event.target.action, {
                      method: 'POST',
                      body: new FormData($event.target),
                      headers: { 'Accept': 'application/json' }
                  }).then(res => {
                      if (res.redirected) {
                          window.location.href = res.url;
                          return null;
                      }
                      return res.json().then(data => ({status: res.status, body: data}));
                  }).then(res => {
                      if (!res) return; // Was redirected
                      if (res.status === 422) {
                          errorMessage = Object.values(res.body.errors)[0][0];
                      } else {
                          errorMessage = res.body.message || 'Có lỗi xảy ra khi đặt lịch. Vui lòng thử lại.';
                      }
                      submitting = false;
                  }).catch(err => {
                      errorMessage = 'Không thể kết nối đến máy chủ.';
                      submitting = false;
                  })
              ">
            @csrf
            <input type="hidden" name="specialty_id"
                   :value="bookingMethod === 'specialty' ? selectedSpecialty?.id : selectedDoctor?.primary_specialty_id">
            <input type="hidden" name="doctor_profile_id" :value="selectedSlot?.doctor_id || selectedDoctor?.id">
            <input type="hidden" name="patient_profile_id" :value="selectedProfile?.id">
            <input type="hidden" name="appointment_date" :value="selectedDate?.date">
            <input type="hidden" name="appointment_time" :value="selectedSlot?.time">
            <input type="hidden" name="reason" :value="reason.trim() || 'Không có'">
            <input type="hidden" name="booking_method" :value="bookingMethod">

            <!-- Error message display -->
            <div x-show="errorMessage" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-start gap-2 text-sm font-medium">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span x-text="errorMessage"></span>
            </div>

            <div class="flex gap-4 sticky bottom-0 bg-white pt-6 pb-4 border-t border-slate-100 z-20">
                <button type="button" @click="step = 3"
                        class="flex-1 py-4 border-2 border-primary/20 text-primary rounded-2xl font-bold hover:bg-primary/5 transition-colors active:scale-95">
                    <i class="fa-solid fa-arrow-left mr-1.5"></i> Quay lại
                </button>
                <button type="submit"
                        :disabled="submitting"
                        class="py-4 rounded-2xl font-extrabold text-white uppercase tracking-wider transition-all disabled:opacity-70 disabled:cursor-not-allowed disabled:active:scale-100 shadow-[0_8px_20px_-8px_rgba(37,99,235,0.5)] hover:shadow-[0_12px_25px_-8px_rgba(37,99,235,0.6)] active:scale-95 bg-primary hover:bg-primary-dark"
                        style="flex:2;">
                    <i x-show="submitting" class="fa-solid fa-spinner fa-spin mr-2"></i>
                    <span x-text="submitting ? 'Đang xử lý...' : 'XÁC NHẬN ĐẶT LỊCH'"></span>
                </button>
            </div>
        </form>
    </div>
    {{-- END BƯỚC 4 --}}

    </div>{{-- END x-data wrapper --}}

</x-layouts.patient>

