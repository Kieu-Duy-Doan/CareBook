<x-layouts.admin title="Biểu phí khám theo cấp bác sĩ">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Biểu phí khám</h2>
                <p class="text-gray-500 mt-1">Cấu hình giá khám cơ bản và giá dịch vụ theo từng cấp bậc bác sĩ</p>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-emerald-50 text-emerald-800 p-4 rounded-xl border border-emerald-200 flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.doctor-level-fees.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Biểu phí theo cấp bậc</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">Cấp bậc</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên đầy đủ</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-52">Giá cơ bản (₫)</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-52">Giá theo yêu cầu (₫)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @php
                            $levelNames = [
                                'BS'    => 'Bác sĩ',
                                'BSCK1' => 'Bác sĩ Chuyên khoa I',
                                'BSCK2' => 'Bác sĩ Chuyên khoa II',
                                'ThS'   => 'Thạc sĩ Y khoa',
                                'TS'    => 'Tiến sĩ Y khoa',
                                'PGS'   => 'Phó Giáo sư',
                                'GS'    => 'Giáo sư',
                            ];
                            $levelColors = [
                                'BS'    => 'bg-gray-100 text-gray-700',
                                'BSCK1' => 'bg-blue-100 text-blue-700',
                                'BSCK2' => 'bg-indigo-100 text-indigo-700',
                                'ThS'   => 'bg-cyan-100 text-cyan-700',
                                'TS'    => 'bg-emerald-100 text-emerald-700',
                                'PGS'   => 'bg-amber-100 text-amber-700',
                                'GS'    => 'bg-rose-100 text-rose-700',
                            ];
                            @endphp

                            @foreach ($fees as $fee)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold {{ $levelColors[$fee->level] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $fee->level }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700 font-medium">
                                    {{ $levelNames[$fee->level] ?? $fee->level }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="relative">
                                        <input type="number"
                                            name="fees[{{ $fee->id }}][base_price]"
                                            value="{{ $fee->base_price }}"
                                            min="0" step="1000"
                                            class="w-full py-2 pl-3 pr-10 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none text-right">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none">₫</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="relative">
                                        <input type="number"
                                            name="fees[{{ $fee->id }}][specific_price]"
                                            value="{{ $fee->specific_price }}"
                                            min="0" step="1000"
                                            class="w-full py-2 pl-3 pr-10 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none text-right">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none">₫</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Giá cơ bản áp dụng cho tất cả, giá theo yêu cầu áp dụng khi bệnh nhân chỉ định bác sĩ cụ thể.
                    </p>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Lưu biểu phí
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.admin>
