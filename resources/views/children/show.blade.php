@extends('layouts.app')

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="space-y-6">
            <div class="card border-none p-8">
                @include('partials.page-header', [
                    'eyebrow' => 'Detail Profil Anak',
                    'title' => $child->child_name,
                    'description' => 'Pantau pertumbuhan berat dan tinggi badan anak secara real-time.',
                    'action' => new \Illuminate\Support\HtmlString('<div class="flex gap-3"><a href="' . route('children.export-pdf', $child) . '" class="btn-secondary px-6">Export PDF</a><a href="' . route('children.index') . '" class="btn-secondary px-6">Kembali</a></div>'),
                ])

                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-3xl bg-slate-50 p-5 ring-1 ring-black/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Posyandu</p>
                        <p class="mt-2 font-black text-slate-800">{{ $child->posyandu->name }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-5 ring-1 ring-black/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Usia Saat Ini</p>
                        <p class="mt-2 font-black text-slate-800">{{ $child->birth_date->age }} Tahun</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-5 ring-1 ring-black/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Nama Ibu</p>
                        <p class="mt-2 font-black text-slate-800">{{ $child->mother_name }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-5 ring-1 ring-black/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Nomor NIK</p>
                        <p class="mt-2 font-black text-slate-800">{{ $child->nik ?: '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="card border-none p-8">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-black tracking-tight text-slate-800">Grafik Pertumbuhan</h3>
                        <p class="text-sm font-medium text-slate-400">Visualisasi tren berat dan tinggi badan.</p>
                    </div>
                    @unless (auth()->user()->isAdmin())
                        <a href="{{ route('measurements.create') }}" class="btn-primary px-6">Tambah Pengukuran</a>
                    @endunless
                </div>

                @if ($summary['total_measurements'] > 0)
                    <div class="mt-8 space-y-8">
                        <div class="rounded-[2rem] bg-slate-50 p-6 ring-1 ring-black/5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-brand-600 mb-4 ml-2">Tren Berat Badan (kg)</p>
                            <div class="h-[240px] w-full">
                                <canvas id="weightChart"></canvas>
                            </div>
                        </div>
                        <div class="rounded-[2rem] bg-slate-50 p-6 ring-1 ring-black/5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-4 ml-2">Tren Tinggi Badan (cm)</p>
                            <div class="h-[240px] w-full">
                                <canvas id="heightChart"></canvas>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-8 flex h-60 items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-100 p-8 text-center text-sm font-bold text-slate-300">
                        Belum ada riwayat pengukuran untuk anak ini.
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="card border-none p-8">
                <h3 class="text-xl font-black tracking-tight text-slate-800">Analisis Gizi</h3>
                <div class="mt-6 rounded-[2rem] bg-gradient-to-br from-brand-600 to-indigo-900 p-8 text-white shadow-xl shadow-brand-900/10">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-brand-200">Status Keseluruhan</p>
                            <p class="mt-2 text-3xl font-black tracking-tight">{{ $nutritionStatus['overall'] }}</p>
                        </div>
                        <div class="rounded-full bg-white/20 p-4 backdrop-blur-md">
                            📊
                        </div>
                    </div>
                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl bg-white/10 p-4 backdrop-blur-md">
                            <p class="text-[10px] font-black uppercase tracking-widest text-brand-100">Indikator Berat</p>
                            <p class="mt-1 font-black">{{ $nutritionStatus['weight'] ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 p-4 backdrop-blur-md">
                            <p class="text-[10px] font-black uppercase tracking-widest text-brand-100">Indikator Tinggi</p>
                            <p class="mt-1 font-black">{{ $nutritionStatus['height'] ?? '-' }}</p>
                        </div>
                    </div>
                    <p class="mt-6 text-xs font-medium leading-relaxed text-brand-100/70">{{ $nutritionStatus['note'] }}</p>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-black/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Berat Terakhir</p>
                        <p class="mt-2 text-3xl font-black text-slate-800">{{ $summary['latest_weight'] !== null ? $summary['latest_weight'] . ' kg' : '-' }}</p>
                        <p class="mt-1 text-[10px] font-bold {{ ($summary['weight_gain'] ?? 0) >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                            {{ ($summary['weight_gain'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($summary['weight_gain'] ?? 0, 2) }} kg dari awal
                        </p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-black/5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tinggi Terakhir</p>
                        <p class="mt-2 text-3xl font-black text-slate-800">{{ $summary['latest_height'] !== null ? $summary['latest_height'] . ' cm' : '-' }}</p>
                        <p class="mt-1 text-[10px] font-bold {{ ($summary['height_gain'] ?? 0) >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                            {{ ($summary['height_gain'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($summary['height_gain'] ?? 0, 2) }} cm dari awal
                        </p>
                    </div>
                </div>
            </div>

            <div class="card border-none p-8">
                <h3 class="text-xl font-black tracking-tight text-slate-800">Timeline Riwayat</h3>
                <div class="mt-6 space-y-4">
                    @forelse ($measurements as $measurement)
                        <div class="group relative rounded-3xl border border-slate-50 bg-slate-50/50 p-6 transition-all hover:bg-white hover:shadow-xl hover:shadow-slate-200/50">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <p class="font-black text-slate-800">{{ $measurement->measured_at->format('d M Y') }}</p>
                                    <p class="text-[10px] font-bold uppercase tracking-tight text-slate-400">
                                        {{ optional($measurement->device)->device_name ?? 'Input Manual' }} • {{ $measurement->measured_at->format('H:i') }}
                                    </p>
                                </div>
                                <span class="rounded-lg bg-white px-3 py-1.5 text-[10px] font-black uppercase tracking-tighter text-slate-500 shadow-sm">
                                    {{ $measurement->source }}
                                </span>
                            </div>
                            <div class="mt-6 grid grid-cols-3 gap-3">
                                <div class="rounded-2xl bg-white p-3 text-center ring-1 ring-black/5">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Berat</p>
                                    <p class="mt-1 font-black text-brand-600">{{ $measurement->weight_kg }}kg</p>
                                </div>
                                <div class="rounded-2xl bg-white p-3 text-center ring-1 ring-black/5">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Tinggi</p>
                                    <p class="mt-1 font-black text-indigo-600">{{ $measurement->height_cm }}cm</p>
                                </div>
                                <div class="rounded-2xl bg-white p-3 text-center ring-1 ring-black/5">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Suhu</p>
                                    <p class="mt-1 font-black text-slate-800">{{ $measurement->temperature_c !== null ? $measurement->temperature_c . '°' : '-' }}</p>
                                </div>
                            </div>
                            @if ($measurement->notes)
                                <div class="mt-4 rounded-xl bg-slate-100/50 p-3 text-xs font-medium italic text-slate-500">
                                    "{{ $measurement->notes }}"
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-12 text-center text-sm font-bold text-slate-300">
                            Riwayat pengukuran masih kosong.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    @if ($summary['total_measurements'] > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const labels = @json($chartData['labels']);
            const weightData = @json($chartData['weights']);
            const heightData = @json($chartData['heights']);

            const sharedOptions = {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 12,
                        cornerRadius: 12,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 12 },
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: '600' }, color: '#94a3b8' }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 10, weight: '600' }, color: '#94a3b8' }
                    },
                },
            };

            new Chart(document.getElementById('weightChart'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Berat Badan',
                        data: weightData,
                        borderColor: '#0e8ce9',
                        backgroundColor: 'rgba(14, 140, 233, 0.08)',
                        borderWidth: 4,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#0e8ce9',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 3,
                        tension: 0.4,
                        fill: true,
                    }],
                },
                options: sharedOptions,
            });

            new Chart(document.getElementById('heightChart'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Tinggi Badan',
                        data: heightData,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.08)',
                        borderWidth: 4,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#6366f1',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 3,
                        tension: 0.4,
                        fill: true,
                    }],
                },
                options: sharedOptions,
            });
        </script>
    @endif
@endpush
