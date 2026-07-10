<x-layouts.doctor title="Lịch sử khám bệnh nhân">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Bệnh án & Lịch sử khám</h2>
            <p class="text-gray-500 mt-1">Tra cứu thông tin và lịch sử khám của bệnh nhân</p>
        </div>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('doctor.patient-history.index') }}" method="GET" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tìm kiếm bệnh nhân</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Tên bệnh nhân, số điện thoại..."
                    class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm outline-none">
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                    Tìm kiếm
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($patients as $patient)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-start gap-4 mb-4">
                    <div class="h-12 w-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-lg flex-shrink-0">
                        {{ substr($patient->full_name, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-gray-900">{{ $patient->full_name }}</h4>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $patient->gender === 'male' ? 'Nam' : ($patient->gender === 'female' ? 'Nữ' : 'Khác') }} 
                            @if($patient->date_of_birth)
                                • {{ \Carbon\Carbon::parse($patient->date_of_birth)->age }} tuổi
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-sm text-gray-600 space-y-2 mb-4">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-phone w-4 text-gray-400"></i> {{ $patient->phone ?? '—' }}
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-id-card w-4 text-gray-400"></i> {{ $patient->identity_card ?? '—' }}
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-heart w-4 text-gray-400"></i> BHYT: {{ $patient->health_insurance_number ?? '—' }}
                    </div>
                </div>
                <a href="{{ route('doctor.patient-history.show', $patient->id) }}" class="block text-center w-full bg-gray-50 hover:bg-gray-100 text-blue-600 border border-gray-200 py-2 rounded-lg text-sm font-medium transition-colors">
                    Xem chi tiết bệnh án
                </a>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-12 text-gray-500 bg-white rounded-xl border border-gray-100">
                <i class="fa-solid fa-users-slash text-4xl text-gray-300 mb-4 block"></i>
                <h3 class="text-lg font-medium text-gray-900">Không tìm thấy bệnh nhân nào</h3>
            </div>
        @endforelse
    </div>

    @if ($patients->hasPages())
        <div class="mt-6">
            {{ $patients->links() }}
        </div>
    @endif
</x-layouts.doctor>
