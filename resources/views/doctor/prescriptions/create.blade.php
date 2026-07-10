<x-layouts.doctor title="Kê đơn thuốc">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('doctor.medical-records.show', $medical_record->id) }}" class="hover:text-blue-600 transition-colors">Hồ sơ bệnh án #{{ $medical_record->id }}</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <span class="text-gray-800 font-medium">Kê đơn thuốc</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Kê đơn thuốc</h2>
        </div>
        <div>
            <a href="{{ route('doctor.medical-records.show', $medical_record->id) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-800 p-4 rounded-lg mb-6 border border-red-200">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('doctor.prescriptions.store', $medical_record->id) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @csrf
        
        <div class="p-6 border-b border-gray-100">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú chẩn đoán (Tuỳ chọn)</label>
                <textarea name="diagnosis_note" rows="2" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: Viêm họng cấp, cần uống thuốc đúng giờ">{{ old('diagnosis_note', $medical_record->diagnosis) }}</textarea>
            </div>
        </div>

        <div class="p-6 bg-gray-50 border-b border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Danh sách thuốc</h3>
                <button type="button" id="add-medicine-btn" class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors flex items-center">
                    <i class="fa-solid fa-plus mr-1"></i> Thêm thuốc
                </button>
            </div>

            <div id="medicine-list" class="space-y-4">
                <!-- Template cho 1 hàng thuốc -->
                <div class="medicine-item relative bg-white border border-gray-200 rounded-lg p-4 shadow-sm" data-index="0">
                    <button type="button" class="remove-medicine absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-5">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Tên thuốc <span class="text-red-500">*</span></label>
                            <input type="text" name="items[0][medicine_name]" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: Paracetamol 500mg">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Số lượng <span class="text-red-500">*</span></label>
                            <input type="text" name="items[0][quantity]" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: 10 viên">
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Liều dùng <span class="text-red-500">*</span></label>
                            <input type="text" name="items[0][dosage]" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: 1 viên/lần x 2 lần/ngày">
                        </div>
                        <div class="md:col-span-12">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Hướng dẫn sử dụng (Tuỳ chọn)</label>
                            <input type="text" name="items[0][instructions]" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: Uống sau khi ăn no">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú chung / Lời dặn (Tuỳ chọn)</label>
                <textarea name="general_note" rows="2" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: Kiêng ăn đồ cay nóng, tái khám sau 3 ngày nếu không đỡ">{{ old('general_note', $medical_record->advice) }}</textarea>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
                <a href="{{ route('doctor.medical-records.show', $medical_record->id) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">Hủy</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fa-solid fa-check mr-2"></i> Lưu đơn thuốc
                </button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const list = document.getElementById('medicine-list');
            const addBtn = document.getElementById('add-medicine-btn');
            let indexCounter = 1;

            addBtn.addEventListener('click', function() {
                const itemHtml = `
                <div class="medicine-item relative bg-white border border-gray-200 rounded-lg p-4 shadow-sm mt-4" data-index="${indexCounter}">
                    <button type="button" class="remove-medicine absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-5">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Tên thuốc <span class="text-red-500">*</span></label>
                            <input type="text" name="items[${indexCounter}][medicine_name]" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: Paracetamol 500mg">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Số lượng <span class="text-red-500">*</span></label>
                            <input type="text" name="items[${indexCounter}][quantity]" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: 10 viên">
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Liều dùng <span class="text-red-500">*</span></label>
                            <input type="text" name="items[${indexCounter}][dosage]" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: 1 viên/lần x 2 lần/ngày">
                        </div>
                        <div class="md:col-span-12">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Hướng dẫn sử dụng (Tuỳ chọn)</label>
                            <input type="text" name="items[${indexCounter}][instructions]" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: Uống sau khi ăn no">
                        </div>
                    </div>
                </div>
                `;
                
                list.insertAdjacentHTML('beforeend', itemHtml);
                indexCounter++;
            });

            list.addEventListener('click', function(e) {
                if (e.target.closest('.remove-medicine')) {
                    const items = list.querySelectorAll('.medicine-item');
                    if (items.length > 1) {
                        e.target.closest('.medicine-item').remove();
                    } else {
                        alert('Đơn thuốc phải có ít nhất 1 loại thuốc.');
                    }
                }
            });
        });
    </script>
</x-layouts.doctor>
