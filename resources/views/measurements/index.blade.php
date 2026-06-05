@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Riwayat data',
            'title' => 'Pengukuran Balita',
            'description' => 'Simpan hasil timbang dan ukur dari input manual atau perangkat IoT.',
            'action' => new \Illuminate\Support\HtmlString('<a href="' . route('measurements.create') . '" class="btn-primary">Tambah Pengukuran</a>'),
        ])

        <form method="GET" action="{{ route('measurements.index') }}" class="mt-6 grid gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-2 xl:grid-cols-[1.2fr_0.8fr_1fr_0.9fr_0.9fr_auto]">
            <div>
                <label for="search" class="label">Cari anak</label>
                <input id="search" name="search" value="{{ $filters['search'] }}" class="input" placeholder="Nama anak atau NIK">
            </div>
            <div>
                <label for="source" class="label">Sumber</label>
                <select id="source" name="source" class="input">
                    <option value="">Semua sumber</option>
                    <option value="manual" {{ $filters['source'] === 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="iot" {{ $filters['source'] === 'iot' ? 'selected' : '' }}>IoT</option>
                </select>
            </div>
            <div>
                <label for="posyandu_id" class="label">Posyandu</label>
                @if (auth()->user()->isAdmin())
                    <select id="posyandu_id" name="posyandu_id" class="input">
                        <option value="">Semua posyandu</option>
                        @foreach ($posyandus as $posyandu)
                            <option value="{{ $posyandu->id }}" {{ (string) $filters['posyandu_id'] === (string) $posyandu->id ? 'selected' : '' }}>{{ $posyandu->name }}</option>
                        @endforeach
                    </select>
                @else
                    <input class="input bg-slate-100" value="{{ optional($posyandus->first())->name }}" disabled>
                @endif
            </div>
            <div>
                <label for="date_from" class="label">Dari tanggal</label>
                <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}" class="input">
            </div>
            <div>
                <label for="date_to" class="label">Sampai tanggal</label>
                <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}" class="input">
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('measurements.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="text-slate-400">
                    <tr>
                        <th class="pb-3 font-medium">Anak</th>
                        <th class="pb-3 font-medium">Waktu</th>
                        <th class="pb-3 font-medium">Berat / Tinggi</th>
                        <th class="pb-3 font-medium">Sumber</th>
                        <th class="pb-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($measurements as $measurement)
                        <tr>
                            <td class="py-4">
                                <p class="font-semibold text-slate-800">{{ $measurement->child->child_name }}</p>
                                <p class="text-xs text-slate-500">{{ optional($measurement->device)->device_name ?? 'Manual input' }}</p>
                            </td>
                            <td class="py-4">{{ $measurement->measured_at->format('d M Y H:i') }}</td>
                            <td class="py-4">{{ $measurement->weight_kg }} kg / {{ $measurement->height_cm }} cm</td>
                            <td class="py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase text-slate-600">{{ $measurement->source }}</span>
                            </td>
                            <td class="py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('measurements.edit', $measurement) }}" class="btn-secondary">Edit</a>
                                    <form action="{{ route('measurements.destroy', $measurement) }}" method="POST" onsubmit="return confirm('Hapus data pengukuran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Hapus</button>
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

        <div class="mt-6">
            @include('partials.pagination', ['paginator' => $measurements])
        </div>
    </section>
@endsection
