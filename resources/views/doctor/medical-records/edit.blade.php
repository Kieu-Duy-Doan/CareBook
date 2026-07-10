<x-layouts.doctor title="Sửa Hồ sơ bệnh án">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center text-sm text-gray-500 mb-2">
                <a href="{{ route('doctor.medical-records.show', $medical_record->id) }}" class="hover:text-blue-600 transition-colors">Hồ sơ bệnh án</a>
                <i class="fa-solid fa-chevron-right text-[10px] mx-2"></i>
                <span class="text-gray-800 font-medium">Sửa Hồ sơ bệnh án</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Sửa Hồ sơ bệnh án #{{ $medical_record->id }}</h2>
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

    <form action="{{ route('doctor.medical-records.update', $medical_record->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Chẩn đoán (Diagnosis) <span class="text-red-500">*</span></label>
                <textarea name="diagnosis" rows="3" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">{{ old('diagnosis', $medical_record->diagnosis) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mã ICD-10 (Tuỳ chọn)</label>
                <input type="text" name="icd10_code" value="{{ old('icd10_code', $medical_record->icd10_code) }}" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none" placeholder="VD: J01.9">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ngày tái khám (Tuỳ chọn)</label>
                <input type="date" name="followup_date" value="{{ old('followup_date', optional($medical_record->followup_date)->format('Y-m-d')) }}" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kết luận (Conclusion) (Tuỳ chọn)</label>
                <textarea name="conclusion" rows="2" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">{{ old('conclusion', $medical_record->conclusion) }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Lời khuyên (Advice) (Tuỳ chọn)</label>
                <textarea name="advice" rows="2" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">{{ old('advice', $medical_record->advice) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kết quả điều trị (Treatment Result) <span class="text-red-500">*</span></label>
                <select name="treatment_result" required class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
                    <option value="outpatient" {{ old('treatment_result', $medical_record->treatment_result) === 'outpatient' ? 'selected' : '' }}>Điều trị ngoại trú (Outpatient)</option>
                    <option value="admitted" {{ old('treatment_result', $medical_record->treatment_result) === 'admitted' ? 'selected' : '' }}>Nhập viện (Admitted)</option>
                    <option value="monitoring" {{ old('treatment_result', $medical_record->treatment_result) === 'monitoring' ? 'selected' : '' }}>Theo dõi thêm (Monitoring)</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kết quả cận lâm sàng / Tệp đính kèm (Tuỳ chọn)</label>
                
                @if($medical_record->result_files)
                <div class="mb-4">
                    <span class="text-sm font-medium text-gray-600 block mb-2">Tệp hiện tại:</span>
                    <div class="space-y-2">
                        @foreach($medical_record->result_files as $file)
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg bg-gray-50">
                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                    <i class="fa-solid fa-file-pdf text-red-500"></i>
                                    <a href="{{ Storage::url($file['path']) }}" target="_blank" class="hover:text-blue-600 hover:underline">{{ $file['name'] }}</a>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="remove_files[]" value="{{ $file['path'] }}" id="remove_{{ $loop->index }}" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                    <label for="remove_{{ $loop->index }}" class="text-sm text-red-600 cursor-pointer">Xóa</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                    <div class="space-y-1 text-center">
                        <i class="fa-solid fa-file-pdf text-gray-400 text-3xl mb-3"></i>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="result_files" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Tải lên các tệp PDF mới</span>
                                <input id="result_files" name="result_files[]" type="file" multiple accept=".pdf,application/pdf" class="sr-only">
                            </label>
                            <p class="pl-1">hoặc kéo thả vào đây</p>
                        </div>
                        <p class="text-xs text-gray-500">Chỉ chấp nhận file PDF, tối đa 10MB mỗi file.</p>
                    </div>
                </div>
                <div id="file-list-preview" class="mt-3 space-y-2 text-sm text-gray-600"></div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-100">
            <a href="{{ route('doctor.medical-records.show', $medical_record->id) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">Hủy</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                <i class="fa-solid fa-floppy-disk mr-2"></i> Lưu thay đổi
            </button>
        </div>
    </form>

    <script>
        document.getElementById('result_files').addEventListener('change', function(e) {
            const fileList = document.getElementById('file-list-preview');
            fileList.innerHTML = '';
            Array.from(this.files).forEach(file => {
                fileList.innerHTML += `<div class="flex items-center gap-2"><i class="fa-solid fa-file-pdf text-red-500"></i> <span>${file.name}</span> <span class="text-xs text-gray-400">(Mới)</span></div>`;
            });
        });
    </script>
</x-layouts.doctor>
