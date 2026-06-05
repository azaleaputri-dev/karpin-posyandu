<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <p class="text-sm text-slate-500">{{ $eyebrow ?? 'Manajemen data' }}</p>
        <h3 class="text-2xl font-bold text-slate-900">{{ $title }}</h3>
        @isset($description)
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        @endisset
    </div>
    @isset($action)
        {{ $action }}
    @endisset
</div>
