<x-layouts.patient title="Đặt lịch khám">
    <meta name="description" content="Đặt lịch khám bệnh trực tuyến tại CareBook - Nhanh chóng, tiện lợi, chính xác.">

    <div x-data="{
    step: 1,

    /* BƯỚC 1 — Hồ sơ */
    profiles: {{ auth()->user()->patientProfiles->toJson() }},
    selectedProfile: null,

    /* BƯỚC 2 — Phương thức */
    bookingMethod: null,
    fees: {{ isset($fees) ? $fees->toJson() : '[]' }},

    /* Nhánh Bác sĩ Gợi ý (Từ thông báo huỷ lịch) */
    suggestedDoctors: {{ isset($suggestedDoctors) ? json_encode($suggestedDoctors) : '[]' }},
    oldAppointment: {{ isset($oldAppointment) ? json_encode($oldAppointment) : 'null' }},

    /* Nhánh Chuyên khoa */
    specialties: {{ $specialties->toJson() }},
    selectedSpecialty: null,
    selectedLevel: null,
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

    init() {
        if (this.oldAppointment && this.suggestedDoctors.length > 0) {
            this.selectedProfile = this.profiles.find(p => p.id === this.oldAppointment.patient_profile_id) || this.profiles[0];
            this.bookingMethod = 'suggested';
            this.reason = this.oldAppointment.reason || '';
            this.step = 2; // Jump to step 2 to show alternatives
        }
    },

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
        
        // Bắt buộc theo chuyên khoa
        if (this.bookingMethod === 'doctor') {
            if (!this.selectedSpecialty) return [];
            docs = docs.filter(d => d.specialty_ids && d.specialty_ids.includes(this.selectedSpecialty.id));
        }
        
        if (!this.doctorSearch) return docs;
        return docs.filter(d =>
            d.full_title.toLowerCase().includes(this.doctorSearch.toLowerCase()) ||
            (d.primary_specialty && d.primary_specialty.toLowerCase().includes(this.doctorSearch.toLowerCase()))
        );
    },

    get canGoStep3() {
        if (this.bookingMethod === 'specialty') return this.selectedSpecialty !== null && this.selectedLevel !== null;
        if (this.bookingMethod === 'doctor' || this.bookingMethod === 'suggested') return this.selectedDoctor !== null;
        return false;
    },

    get availableLevels() {
        if (!this.selectedSpecialty) return [];
        // Lấy danh sách bác sĩ thuộc chuyên khoa đã chọn
        const docs = this.allDoctors.filter(d => d.specialty_ids && d.specialty_ids.includes(this.selectedSpecialty.id));
        // Lấy danh sách học vị (level) duy nhất của các bác sĩ này
        const levels = [...new Set(docs.map(d => d.level))];
        // Lọc mảng fees chỉ giữ lại các học vị có bác sĩ
        return this.fees.filter(f => levels.includes(f.level));
    },

    get totalFee() {
        const isInheritedSpecialty = this.bookingMethod === 'suggested' && this.oldAppointment?.booking_method === 'specialty';
        if (this.bookingMethod === 'specialty' || isInheritedSpecialty) {
            const level = this.bookingMethod === 'suggested' ? this.selectedDoctor?.level : this.selectedLevel;
            const f = this.fees.find(x => x.level === level);
            return f ? parseFloat(f.base_price) : 0;
        } else if (this.selectedDoctor) {
            const doc = this.allDoctors.find(d => d.id === this.selectedDoctor.id) || this.suggestedDoctors.find(d => d.id === this.selectedDoctor.id);
            const level = doc ? doc.level : null;
            const f = this.fees.find(x => x.level === level);
            return f ? parseFloat(f.specific_price) : 0;
        }
        return 0;
    },

    get canGoStep4() {
        return this.selectedDate !== null && this.selectedSlot !== null;
    },

    /* Methods */
    selectProfile(profile) { this.selectedProfile = profile; },

    goStep2() { if (!this.selectedProfile) return; this.step = 2; },

    selectSuggestedDoctor(doc) {
        this.selectedDoctor = {id: doc.id, full_title: doc.full_title, level: doc.level};
        if (doc.has_same_slot && this.oldAppointment) {
            this.selectedDate = doc.alternative_date;
            this.selectedSlot = this.oldAppointment.appointment_time.substring(0, 5);
            this.step = 4; // Nhảy thẳng sang bước xác nhận
        } else {
            this.step = 3;
            this.loadAvailableDates();
        }
    },

    selectMethod(method) {
        this.bookingMethod = method;
        this.selectedSpecialty = null;
        this.selectedDoctor = null;
    },

    openSpecialtyModal() { this.specialtySearch = ''; this.showSpecialtyModal = true; },

    selectSpecialty(specialty) {
        this.selectedSpecialty = specialty;
        this.selectedLevel = null;
        this.showSpecialtyModal = false;
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
        const params = (this.bookingMethod === 'doctor' || this.bookingMethod === 'suggested')
            ? '?doctor_id=' + this.selectedDoctor.id
            : '?specialty_id=' + this.selectedSpecialty.id + '&level=' + this.selectedLevel;
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

    goStep3() { 
        if (!this.canGoStep3) return; 
        this.step = 3; 
        if (this.bookingMethod === 'specialty') {
            this.loadAvailableDates();
        }
    },

    async loadSlots() {
        if (!this.selectedDate) return;
        this.loadingSlots = true;
        const params = (this.bookingMethod === 'doctor' || this.bookingMethod === 'suggested')
            ? '?doctor_id=' + this.selectedDoctor.id + '&date=' + this.selectedDate.date
            : '?specialty_id=' + this.selectedSpecialty.id + '&date=' + this.selectedDate.date + '&level=' + this.selectedLevel;
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
        
        const initialMethod = urlParams.get('booking_method');
        if (initialMethod === 'suggested') {
            if (this.suggestedDoctors.length > 0) {
                this.bookingMethod = 'suggested';
            } else {
                this.bookingMethod = 'doctor';
                this.selectedDoctor = this.allDoctors.find(d => d.id === parseInt(urlParams.get('doctor_id'))) || null;
            }
        }
        
        const cId = parseInt(urlParams.get('cancelled_doctor_id'));
        if (cId) {
            this.cancelledDoctorId = cId;
        }

        if (urlParams.get('fast_track')) {
            const pId = parseInt(urlParams.get('patient_profile_id'));
            const sId = parseInt(urlParams.get('specialty_id'));
            const dId = parseInt(urlParams.get('doctor_id')); // Bác sĩ được chọn thay thế
            const bMethod = urlParams.get('booking_method');
            const r = urlParams.get('reason');
            
            if (pId) {
                this.selectedProfile = this.profiles.find(p => p.id === pId) || null;
            }
            if (sId) {
                this.selectedSpecialty = this.specialties.find(s => s.id === sId) || null;
            }
            if (!dId && bMethod === 'doctor') {
                // Tương thích ngược: nếu không có dId nhưng có bMethod=doctor và gọi từ popup huỷ cũ
            }

            if (bMethod === 'specialty' && sId && !dId) {
                this.bookingMethod = 'specialty';
                this.step = 3;
                this.loadAvailableDates();
            } else if (dId) {
                // Tự select specialty trước để bypass validation luồng Chỉ định bác sĩ
                if (sId) {
                    this.selectedSpecialty = this.specialties.find(s => s.id === sId) || null;
                }
                
                this.bookingMethod = 'doctor';
                this.selectedDoctor = this.allDoctors.find(d => d.id === dId) || null;
                
                if (this.selectedDoctor) {
                    this.step = 3;
                    this.loadAvailableDates();
                } else {
                    this.step = 2;
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
        <div class="sticky z-40 bg-white/80 backdrop-blur-md shadow-sm transition-all top-[110px] md:top-[124px] border-b border-slate-200">
            <div class="max-w-5xl mx-auto px-4 py-3 md:py-4">
                <div class="flex items-center justify-between relative">
                    {{-- Line nền xám --}}
                    <div class="absolute h-1 bg-slate-100 rounded-full z-0" style="top:50%; transform:translateY(-50%); left:10%; right:10%;"></div>
                    {{-- Line xanh progress --}}
                    <div class="absolute h-1 rounded-full z-0 transition-all duration-500 ease-out" style="top:50%; transform:translateY(-50%); left:10%; background-color:var(--primary);"
                        :style="'width:' + ((step-1)/3 * 80) + '%'"></div>

                    {{-- Step 1 --}}
                    <div class="flex flex-col items-center z-10 w-1/4 group cursor-pointer" @click="if(step > 1) step = 1">
                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold transition-all duration-300"
                            :class="step >= 1 ? 'text-white shadow-md shadow-primary/30 ring-4 ring-white' : 'bg-white border-2 border-slate-200 text-slate-400'"
                            :style="step >= 1 ? 'background-color:var(--primary);' : ''">
                            <i x-show="step > 1" class="fa-solid fa-check text-xs md:text-sm"></i>
                            <span x-show="step <= 1">1</span>
                        </div>
                        <span class="text-[10px] md:text-xs mt-1.5 md:mt-2 text-center font-bold tracking-wide uppercase transition-colors"
                            :class="step >= 1 ? 'text-primary' : 'text-slate-400'">Hồ sơ</span>
                    </div>

                    {{-- Step 2 --}}
                    <div class="flex flex-col items-center z-10 w-1/4 group cursor-pointer" @click="if(step > 2) goStep2()">
                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold transition-all duration-300"
                            :class="step >= 2 ? 'text-white shadow-md shadow-primary/30 ring-4 ring-white' : 'bg-white border-2 border-slate-200 text-slate-400'"
                            :style="step >= 2 ? 'background-color:var(--primary);' : ''">
                            <i x-show="step > 2" class="fa-solid fa-check text-xs md:text-sm"></i>
                            <span x-show="step <= 2">2</span>
                        </div>
                        <span class="text-[10px] md:text-xs mt-1.5 md:mt-2 text-center font-bold tracking-wide uppercase transition-colors"
                            :class="step >= 2 ? 'text-primary' : 'text-slate-400'">Dịch vụ</span>
                    </div>

                    {{-- Step 3 --}}
                    <div class="flex flex-col items-center z-10 w-1/4 group cursor-pointer" @click="if(step > 3) goStep3()">
                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold transition-all duration-300"
                            :class="step >= 3 ? 'text-white shadow-md shadow-primary/30 ring-4 ring-white' : 'bg-white border-2 border-slate-200 text-slate-400'"
                            :style="step >= 3 ? 'background-color:var(--primary);' : ''">
                            <i x-show="step > 3" class="fa-solid fa-check text-xs md:text-sm"></i>
                            <span x-show="step <= 3">3</span>
                        </div>
                        <span class="text-[10px] md:text-xs mt-1.5 md:mt-2 text-center font-bold tracking-wide uppercase transition-colors"
                            :class="step >= 3 ? 'text-primary' : 'text-slate-400'">Thời gian</span>
                    </div>

                    {{-- Step 4 --}}
                    <div class="flex flex-col items-center z-10 w-1/4 group">
                        <div class="w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center text-sm md:text-base font-bold transition-all duration-300"
                            :class="step === 4 ? 'text-white shadow-md shadow-primary/30 ring-4 ring-white' : 'bg-white border-2 border-slate-200 text-slate-400'"
                            :style="step === 4 ? 'background-color:var(--primary);' : ''">
                            <span>4</span>
                        </div>
                        <span class="text-[10px] md:text-xs mt-1.5 md:mt-2 text-center font-bold tracking-wide uppercase transition-colors"
                            :class="step === 4 ? 'text-primary' : 'text-slate-400'">Xác nhận</span>
                    </div>
                </div>
            </div>
        </div>
        {{-- Alerts Container (Toast Style) --}}
        <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full px-4 md:px-0">
            @if (session('error'))
            <div class="bg-white border border-red-100 rounded-xl shadow-lg p-4 animate-fade-in-up relative overflow-hidden" x-data="{ show: true }" x-show="show">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
                <div class="flex items-start">
                    <div class="flex-shrink-0 mt-0.5">
                        <i class="fa-solid fa-circle-exclamation text-red-500 text-lg"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-bold text-slate-800">Lỗi hệ thống</p>
                        <p class="text-sm text-slate-500 mt-1">{{ session('error') }}</p>
                    </div>
                    <button @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors ml-2"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            @endif

            @if ($errors->any())
            <div class="bg-white border border-red-100 rounded-xl shadow-lg p-4 animate-fade-in-up relative overflow-hidden" x-data="{ show: true }" x-show="show">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
                <div class="flex items-start">
                    <div class="flex-shrink-0 mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation text-red-500 text-lg"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-bold text-slate-800">Vui lòng kiểm tra lại:</p>
                        <ul class="list-disc list-inside text-sm text-slate-500 mt-1">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors ml-2"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            @endif

            @if (session('success'))
            <div class="bg-white border border-green-100 rounded-xl shadow-lg p-4 animate-fade-in-up relative overflow-hidden" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>
                <div class="flex items-start">
                    <div class="flex-shrink-0 mt-0.5">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-bold text-slate-800">Thành công</p>
                        <p class="text-sm text-slate-500 mt-1">{{ session('success') }}</p>
                    </div>
                    <button @click="show = false" class="text-slate-400 hover:text-slate-600 transition-colors ml-2"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            @endif
        </div>

        @include('patient.booking.steps.step1')
        @include('patient.booking.steps.step2')
        @include('patient.booking.steps.step3')


        @include('patient.booking.steps.step4')

    </div>{{-- END x-data wrapper --}}

</x-layouts.patient>