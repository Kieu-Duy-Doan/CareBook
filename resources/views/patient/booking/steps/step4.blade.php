{{-- ===== BƯỚC 4: XÁC NHẬN ===== --}}
    <div x-show="step === 4" class="max-w-5xl mx-auto px-4 py-6">
        <div class="flex items-center gap-3 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Xác nhận thông tin đặt lịch</h2>
                <p class="text-sm text-gray-500 mt-1">Vui lòng kiểm tra thông tin và nhập triệu chứng</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Cột bên trái: Triệu chứng & Lưu ý --}}
            <div class="space-y-8">
                {{-- Nhập triệu chứng --}}
                <div class="bg-white border rounded-3xl p-6 shadow-sm" style="border-color:#e2e8f0;">
                    <p class="font-bold text-lg mb-4" style="color:var(--primary);">
                        <i class="fa-solid fa-pencil mr-2"></i>
                        Triệu chứng / Lý do khám:
                    </p>
                    <textarea x-model="reason"
                              rows="5"
                              maxlength="500"
                              placeholder="Mô tả triệu chứng, tình trạng sức khoẻ hiện tại..."
                              class="w-full border-2 border-gray-200 rounded-2xl px-5 py-4 resize-none text-gray-800 focus:outline-none focus:ring-0 focus:border-primary placeholder-gray-400 text-base"
                              ></textarea>
                    <div class="flex justify-between mt-3">
                        <p class="text-sm font-medium text-gray-500">
                            <i class="fa-solid fa-lightbulb text-yellow-500 mr-1.5"></i>
                            Thông tin chi tiết giúp bác sĩ chuẩn bị tốt hơn
                        </p>
                        <p class="text-sm font-medium text-gray-400" x-text="reason.length + '/500'"></p>
                    </div>
                </div>

                {{-- Lưu ý quan trọng --}}
                <div class="bg-orange-50 border-2 border-orange-200 rounded-3xl p-6 shadow-sm">
                    <p class="font-bold text-orange-700 text-lg mb-4">
                        <i class="fa-solid fa-circle-info mr-2"></i>
                        LƯU Ý QUAN TRỌNG
                    </p>
                    <ul class="text-base font-medium text-orange-800 space-y-3">
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-circle text-[6px] mt-2 flex-shrink-0 text-orange-500"></i>
                            Vui lòng có mặt trước 15 phút so với giờ hẹn
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-circle text-[6px] mt-2 flex-shrink-0 text-orange-500"></i>
                            Mang theo CCCD và thẻ BHYT (nếu có)
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-circle text-[6px] mt-2 flex-shrink-0 text-orange-500"></i>
                            Có thể huỷ lịch trước 2 tiếng qua ứng dụng
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-circle text-[6px] mt-2 flex-shrink-0 text-orange-500"></i>
                            Tuân thủ quy định của bệnh viện
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Cột bên phải: Tóm tắt thông tin --}}
            <div>
                <div class="bg-white border-2 rounded-3xl overflow-hidden shadow-sm h-full flex flex-col" style="border-color:#e2e8f0;">
                    {{-- Header --}}
                    <div class="flex items-center gap-3 px-6 py-4 border-b" style="background-color:var(--primary-light);border-color:rgba(29,111,164,0.10);">
                        <i class="fa-solid fa-briefcase-medical text-xl" style="color:var(--primary);"></i>
                        <span class="font-extrabold uppercase text-base" style="color:var(--primary);">Thông tin đặt lịch</span>
                    </div>

                    <div class="divide-y divide-gray-100 flex-1">
                        <div class="flex items-start px-5 py-4 hover:bg-slate-50 transition-colors">
                            <span class="text-gray-500 text-sm md:text-base font-medium w-28 md:w-32 flex-shrink-0 pt-0.5">Bệnh nhân:</span>
                            <div class="flex-1">
                                <p class="font-bold text-gray-800 text-base md:text-lg" x-text="selectedProfile?.full_name"></p>
                                <p class="text-sm text-gray-500 mt-1">
                                    <i class="fa-solid fa-phone text-xs mr-1 text-gray-400"></i>
                                    <span x-text="selectedProfile?.phone || 'Chưa cập nhật'"></span>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-start px-5 py-3 hover:bg-slate-50 transition-colors">
                            <span class="text-gray-500 text-sm md:text-base font-medium w-28 md:w-32 flex-shrink-0 pt-0.5">CCCD:</span>
                            <span class="font-bold text-gray-800 text-base" x-text="selectedProfile?.id_card || 'Không có'"></span>
                        </div>
                        <div class="flex items-start px-5 py-3 hover:bg-slate-50 transition-colors">
                            <span class="text-gray-500 text-sm md:text-base font-medium w-28 md:w-32 flex-shrink-0 pt-0.5">Số BHYT:</span>
                            <span class="font-bold text-gray-800 text-base" x-text="selectedProfile?.insurance_code || 'Không có'"></span>
                        </div>
                        <div class="flex items-start px-5 py-3 hover:bg-slate-50 transition-colors">
                            <span class="text-gray-500 text-sm md:text-base font-medium w-28 md:w-32 flex-shrink-0 pt-0.5">Phương thức:</span>
                            <span class="font-bold text-gray-800 text-base"
                                  x-text="bookingMethod === 'doctor' ? 'Theo bác sĩ' : 'Theo chuyên khoa'"></span>
                        </div>
                        <div class="flex items-start px-5 py-3 hover:bg-slate-50 transition-colors" x-show="bookingMethod === 'doctor'">
                            <span class="text-gray-500 text-sm md:text-base font-medium w-28 md:w-32 flex-shrink-0 pt-0.5">Bác sĩ:</span>
                            <span class="font-bold text-gray-800 text-base uppercase" x-text="selectedDoctor?.full_title"></span>
                        </div>
                        <div class="flex items-start px-5 py-3 hover:bg-slate-50 transition-colors" x-show="bookingMethod === 'specialty'">
                            <span class="text-gray-500 text-sm md:text-base font-medium w-28 md:w-32 flex-shrink-0 pt-0.5">Chuyên khoa:</span>
                            <span class="font-bold text-gray-800 text-base" x-text="selectedSpecialty?.name"></span>
                        </div>
                        <div class="flex items-start px-5 py-3 hover:bg-slate-50 transition-colors">
                            <span class="text-gray-500 text-sm md:text-base font-medium w-28 md:w-32 flex-shrink-0 pt-0.5">Ngày khám:</span>
                            <span class="font-bold text-gray-800 text-base"
                                  x-text="selectedDate ? new Date(selectedDate.date).toLocaleDateString('vi-VN') : ''"></span>
                        </div>
                        <div class="flex items-start px-5 py-4 hover:bg-slate-50 transition-colors bg-primary/5">
                            <span class="text-gray-500 text-sm md:text-base font-medium w-28 md:w-32 flex-shrink-0 pt-0.5">Giờ khám:</span>
                            <span class="font-black text-xl" style="color:var(--primary);" x-text="selectedSlot?.time"></span>
                        </div>
                        <div class="flex items-start px-5 py-3 hover:bg-slate-50 transition-colors">
                            <span class="text-gray-500 text-sm md:text-base font-medium w-28 md:w-32 flex-shrink-0 pt-0.5">Phòng khám:</span>
                            <span class="font-bold text-gray-800 text-base"
                                  x-text="selectedSlot?.room_name ?? selectedDoctor?.room_name ?? '—'"></span>
                        </div>
                        <div class="flex items-start px-5 py-3 hover:bg-slate-50 transition-colors">
                            <span class="text-gray-500 text-sm md:text-base font-medium w-28 flex-shrink-0">Triệu chứng:</span>
                            <span class="font-bold text-gray-800 italic text-base"
                                  x-text="reason.trim() || 'Không có'"></span>
                        </div>
                    </div>
                    <div class="flex items-center px-5 py-4 bg-gray-50 mt-auto border-t" style="border-color:#e2e8f0;">
                        <div class="flex items-center gap-2 text-gray-600 font-medium">
                            <i class="fa-solid fa-credit-card text-lg"></i>
                            Phí khám dự kiến:
                        </div>
                        <span class="ml-auto font-black text-lg" style="color:var(--primary);">Liên hệ tại quầy</span>
                    </div>
                </div>
            </div>
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

            <div class="flex gap-4 sticky bottom-0 bg-white pt-4 pb-3 border-t border-slate-100 z-20">
                <button type="button" @click="step = 3"
                        class="w-1/3 md:w-1/4 py-3 border-2 border-primary/20 text-primary rounded-xl font-bold hover:bg-primary/5 transition-colors active:scale-95 text-base">
                    Quay lại
                </button>
                <button type="submit"
                        :disabled="submitting"
                        class="flex-1 py-3 rounded-xl font-extrabold text-white uppercase tracking-wider transition-all disabled:opacity-70 disabled:cursor-not-allowed shadow-[0_8px_20px_-8px_rgba(37,99,235,0.5)] hover:shadow-[0_12px_25px_-8px_rgba(37,99,235,0.6)] active:scale-95 bg-primary hover:bg-primary-dark text-base">
                    <i x-show="submitting" class="fa-solid fa-spinner fa-spin mr-2"></i>
                    <span x-text="submitting ? 'Đang xử lý...' : 'XÁC NHẬN ĐẶT LỊCH'"></span>
                </button>
            </div>
        </form>
    </div>
    {{-- END BƯỚC 4 --}}