<x-layouts.app>
    <div class="bg-blue-600 pb-24 pt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-white mb-2">Đội ngũ chuyên gia, Bác sĩ</h1>
            <p class="text-blue-100">Tìm kiếm và đặt lịch khám với đội ngũ y bác sĩ hàng đầu tại CareBook.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 pb-20 relative z-10">
        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Sidebar Filter --}}
            <div class="lg:w-1/4">
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-slate-100 sticky top-24">
                    <h3 class="text-lg font-bold text-blue-900 mb-4 pb-2 border-b border-slate-100">
                        <i class="fa-solid fa-filter mr-2 text-amber-500"></i> Lọc Bác Sĩ
                    </h3>
                    <form action="{{ route('doctors.directory') }}" method="GET" id="filter-form">
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Tìm theo tên</label>
                            <div class="relative">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nhập tên bác sĩ..." class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-xl focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Chuyên khoa</label>
                            <select name="specialty_id" class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:ring-blue-500 focus:border-blue-500" onchange="document.getElementById('filter-form').submit()">
                                <option value="">Tất cả chuyên khoa</option>
                                @foreach($specialties as $sp)
                                    <option value="{{ $sp->id }}" {{ request('specialty_id') == $sp->id ? 'selected' : '' }}>
                                        {{ $sp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-md hover:shadow-lg">
                            Áp dụng bộ lọc
                        </button>

                        @if(request()->has('search') || request()->has('specialty_id'))
                        <a href="{{ route('doctors.directory') }}" class="block w-full py-2.5 mt-3 bg-slate-100 text-slate-600 font-bold rounded-xl text-center hover:bg-slate-200 transition-colors">
                            Xóa bộ lọc
                        </a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Grid --}}
            <div class="lg:w-3/4">
                @if($doctors->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($doctors as $doctor)
                        <div class="bg-white rounded-2xl overflow-hidden shadow-md border border-slate-100 hover:shadow-xl transition-all group flex flex-col h-full">
                            <div class="bg-slate-100 pt-6 px-6 relative flex justify-center">
                                <img src="{{ $doctor->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($doctor->user->full_name).'&background=random' }}" alt="{{ $doctor->user->full_name }}" class="w-32 h-32 object-cover rounded-full border-4 border-white shadow-md z-10 group-hover:scale-105 transition-transform duration-500 bg-white">
                                <div class="absolute inset-x-0 bottom-0 h-1/2 bg-blue-600/5"></div>
                            </div>
                            <div class="p-5 text-center flex-1 flex flex-col justify-center">
                                <h3 class="font-bold text-lg text-blue-900 mb-1 leading-tight">{{ $doctor->user->full_name }}</h3>
                                <p class="text-sm font-medium text-amber-600 mb-2">{{ $doctor->full_title }}</p>
                                <p class="text-sm text-slate-500 bg-slate-50 py-1 px-3 rounded-full inline-block mx-auto border border-slate-100">
                                    {{ $doctor->primary_specialty?->name ?? 'Đa khoa' }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $doctors->links() }}
                    </div>
                @else
                    <div class="bg-white rounded-2xl shadow-sm p-10 text-center border border-slate-100">
                        <div class="text-5xl text-slate-300 mb-4">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-700 mb-2">Không tìm thấy bác sĩ</h3>
                        <p class="text-slate-500">Vui lòng thử lại với từ khóa hoặc chuyên khoa khác.</p>
                        <a href="{{ route('doctors.directory') }}" class="inline-block mt-4 px-6 py-2 bg-blue-50 text-blue-600 font-bold rounded-xl hover:bg-blue-100 transition-colors">
                            Xóa bộ lọc
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
