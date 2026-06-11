<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-slate-400">{{ $eyebrow ?? 'Manajemen Data' }}</p>
        <h3 class="mt-2 text-[2.15rem] font-black tracking-tight text-slate-900">{{ $title }}</h3>
        @isset($description)
            <p class="mt-2 max-w-2xl text-base font-medium text-slate-400">{{ $description }}</p>
        @endisset
    </div>
    @isset($action)
        <div class="flex items-center gap-3">
            {{ $action }}
        </div>
    @endisset
</div>
