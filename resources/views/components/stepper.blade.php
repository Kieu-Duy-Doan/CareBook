@props(['step' => 1])

<div class="mb-8 hidden md:block">
    <div class="flex items-center justify-between relative">
        <!-- Connecting Line -->
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-100 rounded-full z-0"></div>
        <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-primary rounded-full z-0 transition-all duration-500" 
             style="width: {{ ($step - 1) * 33.33 }}%;"></div>

        <!-- Step 1: Chọn Hồ Sơ -->
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-300 shadow-sm
                {{ $step >= 1 ? 'bg-primary text-white border-4 border-primary/20' : 'bg-slate-100 text-slate-400 border-4 border-white' }}">
                @if($step > 1) <i class="fa-solid fa-check"></i> @else 1 @endif
            </div>
            <span class="mt-2 text-xs font-bold {{ $step >= 1 ? 'text-primary' : 'text-slate-400' }}">Hồ sơ</span>
        </div>

        <!-- Step 2: Chọn Phương thức -->
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-300 shadow-sm
                {{ $step >= 2 ? 'bg-primary text-white border-4 border-primary/20' : 'bg-slate-100 text-slate-400 border-4 border-white' }}">
                @if($step > 2) <i class="fa-solid fa-check"></i> @else 2 @endif
            </div>
            <span class="mt-2 text-xs font-bold {{ $step >= 2 ? 'text-primary' : 'text-slate-400' }}">Dịch vụ</span>
        </div>

        <!-- Step 3: Chọn Thời Gian -->
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-300 shadow-sm
                {{ $step >= 3 ? 'bg-primary text-white border-4 border-primary/20' : 'bg-slate-100 text-slate-400 border-4 border-white' }}">
                @if($step > 3) <i class="fa-solid fa-check"></i> @else 3 @endif
            </div>
            <span class="mt-2 text-xs font-bold {{ $step >= 3 ? 'text-primary' : 'text-slate-400' }}">Thời gian</span>
        </div>

        <!-- Step 4: Xác Nhận -->
        <div class="relative z-10 flex flex-col items-center">
            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-300 shadow-sm
                {{ $step >= 4 ? 'bg-primary text-white border-4 border-primary/20' : 'bg-slate-100 text-slate-400 border-4 border-white' }}">
                4
            </div>
            <span class="mt-2 text-xs font-bold {{ $step >= 4 ? 'text-primary' : 'text-slate-400' }}">Xác nhận</span>
        </div>
    </div>
</div>
<!-- Mobile Progress Bar -->
<div class="mb-6 block md:hidden">
    <div class="flex items-center justify-between text-xs font-bold mb-2">
        <span class="text-primary">Bước {{ $step }}/4</span>
        <span class="text-slate-500">
            @if($step == 1) Hồ sơ 
            @elseif($step == 2) Dịch vụ
            @elseif($step == 3) Thời gian
            @else Xác nhận @endif
        </span>
    </div>
    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
        <div class="h-full bg-primary transition-all duration-500" style="width: {{ ($step / 4) * 100 }}%"></div>
    </div>
</div>
