@extends('layouts.app')

@section('content')
    <section class="card border-none p-8">
        @include('partials.page-header', [
            'eyebrow' => 'Riwayat Pengukuran',
            'title' => 'Monitoring Balita',
            'description' => 'Simpan hasil timbang dan ukur dari input manual atau identitas kartu RFID.',
            'action' => new \Illuminate\Support\HtmlString('<a href="' . route('measurements.create') . '" class="btn-primary px-8">Tambah Pengukuran</a>'),
        ])

        <form method="GET" action="{{ route('measurements.index') }}" class="mt-8 grid gap-6 rounded-[2rem] bg-slate-50/50 p-6 ring-1 ring-black/5 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-[1.2fr_0.8fr_1fr_0.9fr_0.9fr_auto]">
            <div class="space-y-1.5">
                <label for="search" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Cari Anak</label>
                <input id="search" name="search" value="{{ $filters['search'] }}" class="input" placeholder="Nama atau NIK">
            </div>
            <div class="space-y-1.5">
                <label for="source" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Sumber Data</label>
                <select id="source" name="source" class="input">
                    <option value="">Semua</option>
                    <option value="manual" {{ $filters['source'] === 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="iot" {{ $filters['source'] === 'iot' ? 'selected' : '' }}>Karpin</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label for="posyandu_id" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Posyandu</label>
                @if (auth()->user()->isAdmin())
                    <select id="posyandu_id" name="posyandu_id" class="input">
                        <option value="">Semua posyandu</option>
                        @foreach ($posyandus as $posyandu)
                            <option value="{{ $posyandu->id }}" {{ (string) $filters['posyandu_id'] === (string) $posyandu->id ? 'selected' : '' }}>{{ $posyandu->name }}</option>
                        @endforeach
                    </select>
                @else
                    <input class="input bg-slate-100 cursor-not-allowed" value="{{ optional($posyandus->first())->name }}" disabled>
                @endif
            </div>
            <div class="space-y-1.5">
                <label for="date_from" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Dari Tanggal</label>
                <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}" class="input">
            </div>
            <div class="space-y-1.5">
                <label for="date_to" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Sampai Tanggal</label>
                <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}" class="input">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1 px-6">Filter</button>
                <a href="{{ route('measurements.index') }}" class="btn-secondary px-4" title="Reset">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
        </form>

        <div class="mt-8 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="pb-4 pl-4 font-black">Nama Anak</th>
                        <th class="pb-4 font-black">Waktu Ukur</th>
                        <th class="pb-4 font-black">Parameter</th>
                        <th class="pb-4 font-black">Sumber</th>
                        <th class="pb-4 pr-4 text-right font-black">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($measurements as $measurement)
                        <tr class="group transition-colors hover:bg-slate-50/50">
                            <td class="py-5 pl-4">
                                <p class="font-black text-slate-800">{{ $measurement->child->child_name }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-tight text-slate-400">{{ optional($measurement->device)->device_name ?? 'Input Manual' }}</p>
                            </td>
                            <td class="py-5">
                                <p class="font-bold text-slate-600">{{ $measurement->measured_at->format('d/m/Y') }}</p>
                                <p class="text-[10px] font-bold text-slate-400">{{ $measurement->measured_at->format('H:i') }} WIB</p>
                            </td>
                            <td class="py-5">
                                <div class="flex gap-4">
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>
                                        <span class="font-bold text-slate-700">{{ $measurement->weight_kg }}kg</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                                        <span class="font-bold text-slate-700">{{ $measurement->height_cm }}cm</span>
                                    </span>
                                </div>
                            </td>
                            <td class="py-5">
                                <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-[10px] font-black uppercase tracking-tighter text-slate-500">
                                    {{ $measurement->source }}
                                </span>
                            </td>
                            <td class="py-5 pr-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('measurements.edit', $measurement) }}" class="btn-secondary px-4 py-2 text-xs">Edit</a>
                                    <form action="{{ route('measurements.destroy', $measurement) }}" method="POST" onsubmit="return confirm('Hapus data pengukuran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-secondary px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 hover:border-rose-200">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @component('partials.empty-state', ['colspan' => 5])Belum ada data pengukuran.@endcomponent
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            @include('partials.pagination', ['paginator' => $measurements])
        </div>
    </section>
@endsection
