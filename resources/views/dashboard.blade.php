@extends('layouts.app')

@section('content')
    <section class="card overflow-hidden border-none shadow-xl">
        <div class="grid gap-8 bg-gradient-to-br from-brand-600 via-brand-700 to-indigo-900 p-8 text-white md:grid-cols-2">
            <div class="flex flex-col justify-between space-y-8">
                <div>
                    <span class="inline-block rounded-full bg-white/20 px-4 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-white">Indeks Perkembangan</span>
                    <h3 class="mt-4 text-4xl font-black leading-tight tracking-tight md:text-5xl">Ringkasan pertumbuhan posyandu</h3>
                    <p class="mt-4 max-w-xl text-sm font-medium leading-relaxed text-brand-100/80">
                        {{ auth()->user()->isAdmin() ? 'Pantauan lintas seluruh posyandu dengan fokus pada cakupan pengukuran, intensitas pencatatan, dan tren perkembangan anak selama 6 bulan terakhir.' : $scopeLabel . ' dengan fokus pada cakupan pengukuran, intensitas pencatatan, dan tren perkembangan anak selama 6 bulan terakhir.' }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center gap-3 rounded-2xl bg-white/10 px-5 py-3 backdrop-blur-md">
                        <div class="h-2 w-2 rounded-full bg-brand-300 shadow-[0_0_12px_rgba(124,199,251,0.8)]"></div>
                        <span class="text-xs font-bold text-white">Periode {{ $developmentSummary['month_label'] }}</span>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl bg-white/10 px-5 py-3 backdrop-blur-md">
                        <div class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_12px_rgba(52,211,153,0.8)]"></div>
                        <span class="text-xs font-bold text-white">{{ $developmentSummary['children_measured_this_month'] }} Anak Terpantau</span>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="group relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 p-6 transition-all hover:bg-white/10">
                    <p class="text-[10px] font-black uppercase tracking-widest text-brand-200">Indeks Bulan Ini</p>
                    <p class="mt-4 text-5xl font-black tracking-tighter">{{ number_format($developmentSummary['monitoring_index'], 1) }}<span class="text-2xl">%</span></p>
                    <p class="mt-4 text-[11px] font-medium leading-relaxed text-brand-100/60">Persentase anak yang sudah terpantau pada bulan berjalan.</p>
                </div>
                <div class="group relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 p-6 transition-all hover:bg-white/10">
                    <p class="text-[10px] font-black uppercase tracking-widest text-brand-200">Pengukuran Aktif</p>
                    <p class="mt-4 text-5xl font-black tracking-tighter">{{ $stats['measurements_this_month'] }}</p>
                    <p class="mt-4 text-[11px] font-medium leading-relaxed text-brand-100/60">Total catatan pengukuran yang masuk bulan ini.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        @php
            $statCards = [
                ['label' => 'Posyandu', 'value' => $stats['posyandus'], 'desc' => 'Wilayah pemantauan', 'icon' => '🏢'],
                ['label' => 'Data Anak', 'value' => $stats['children'], 'desc' => 'Total anak terdaftar', 'icon' => '👶'],
                ['label' => 'Pengukuran', 'value' => $stats['measurements_this_month'], 'desc' => 'Aktivitas bulan ini', 'icon' => '📊'],
                ['label' => 'Cakupan', 'value' => number_format($stats['monitoring_index'], 1) . '%', 'desc' => 'Indeks pemantauan', 'icon' => '🎯'],
            ];
        @endphp

        @foreach ($statCards as $card)
            <div class="card group flex items-start justify-between border-none p-6 transition-all hover:scale-[1.02]">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">{{ $card['label'] }}</p>
                    <p class="mt-2 text-3xl font-black text-slate-800 tracking-tight">{{ $card['value'] }}</p>
                    <p class="mt-1 text-[11px] font-bold text-slate-400">{{ $card['desc'] }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-xl transition-colors group-hover:bg-brand-100">
                    {{ $card['icon'] }}
                </div>
            </div>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
        <div class="card border-none p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-xl font-black tracking-tight text-slate-800">Tren Perkembangan</h3>
                    <p class="mt-1 text-sm font-medium text-slate-400">Analisis aktivitas 6 bulan terakhir.</p>
                </div>
                <span class="inline-flex rounded-xl bg-slate-100 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-slate-500">
                    {{ auth()->user()->isAdmin() ? 'Mode Administrator' : $scopeLabel }}
                </span>
            </div>

            <div class="mt-8 h-[360px] w-full">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>

        <div class="card border-none p-8">
            <h3 class="text-xl font-black tracking-tight text-slate-800">Performa</h3>
            <p class="mt-1 text-sm font-medium text-slate-400">Ringkasan capaian pemantauan dan kualitas pencatatan bulan ini.</p>
            <div class="mt-6 space-y-4">
                <div class="rounded-[2rem] bg-slate-50 p-6 ring-1 ring-black/5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-widest text-slate-400">Anak Terpantau</p>
                            <p class="mt-2 text-5xl font-black tracking-tighter text-slate-800">{{ $developmentSummary['children_measured_this_month'] }}</p>
                            <p class="mt-2 text-[11px] font-bold text-slate-400">dari <span class="text-slate-600">{{ $stats['children'] }}</span> anak terdaftar</p>
                        </div>
                        <div class="rounded-2xl bg-white px-4 py-3 text-right ring-1 ring-slate-200">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Cakupan</p>
                            <p class="mt-1 text-2xl font-black text-slate-800">{{ number_format($developmentSummary['monitoring_index'], 1) }}%</p>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-white px-4 py-3 ring-1 ring-slate-200">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pengukuran Bulan Ini</p>
                            <p class="mt-1 text-lg font-black text-slate-800">{{ number_format($developmentSummary['measurements_this_month']) }}</p>
                        </div>
                        <div class="rounded-xl bg-white px-4 py-3 ring-1 ring-slate-200">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Periode</p>
                            <p class="mt-1 text-lg font-black text-slate-800">{{ $developmentSummary['month_label'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-[2rem] bg-emerald-50 p-6 ring-1 ring-emerald-500/10">
                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Avg Weight</p>
                        <p class="mt-2 text-2xl font-black text-emerald-900">{{ $developmentSummary['avg_latest_weight'] !== null ? number_format($developmentSummary['avg_latest_weight'], 1) . ' kg' : '-' }}</p>
                        <p class="mt-2 text-[11px] font-bold text-emerald-800/70">Rerata berat dari catatan terakhir tiap anak yang sudah dipantau.</p>
                    </div>
                    <div class="rounded-[2rem] bg-brand-50 p-6 ring-1 ring-brand-500/10">
                        <p class="text-[10px] font-black uppercase tracking-widest text-brand-600">Avg Height</p>
                        <p class="mt-2 text-2xl font-black text-brand-900">{{ $developmentSummary['avg_latest_height'] !== null ? number_format($developmentSummary['avg_latest_height'], 1) . ' cm' : '-' }}</p>
                        <p class="mt-2 text-[11px] font-bold text-brand-900/55">Rerata tinggi dari catatan terakhir tiap anak yang sudah dipantau.</p>
                    </div>
                </div>
                <div class="flex items-center justify-between rounded-[2rem] bg-slate-900 p-6 text-white">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Riwayat</p>
                        <p class="mt-1 text-2xl font-black">{{ number_format($stats['measurements']) }}</p>
                        <p class="mt-1 text-[11px] font-bold text-slate-400">Akumulasi seluruh catatan pengukuran yang tersimpan.</p>
                    </div>
                    <div class="rounded-xl bg-white/10 px-4 py-2 text-[10px] font-black uppercase">Pencatatan</div>
                </div>
            </div>
        </div>
    </section>

    @if (auth()->user()->isAdmin())
        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="card border-none p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-xl font-black tracking-tight text-slate-800">Perbandingan Wilayah</h3>
                        <p class="mt-1 text-sm font-medium text-slate-400">Cakupan monitoring antar posyandu.</p>
                    </div>
                    <span class="rounded-xl bg-brand-50 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-brand-600">Puskesmas Admin</span>
                </div>

                @if ($posyanduAggregate->isNotEmpty())
                    <div class="mt-8 h-[400px] w-full">
                        <canvas id="posyanduAggregateChart"></canvas>
                    </div>
                @else
                    <div class="mt-8 flex h-[400px] items-center justify-center rounded-[2rem] border-2 border-dashed border-slate-100 p-8 text-center text-sm font-bold text-slate-300">
                        Belum ada data posyandu untuk ditampilkan.
                    </div>
                @endif
            </div>

            <div class="card border-none p-8">
                <h3 class="text-xl font-black tracking-tight text-slate-800">Snapshot Posyandu</h3>
                <div class="mt-6 space-y-3">
                    @forelse ($posyanduAggregate as $item)
                        <div class="group flex items-center gap-4 rounded-3xl border border-slate-50 bg-slate-50/50 p-4 transition-all hover:bg-white hover:shadow-lg hover:shadow-slate-200/50">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white font-black text-brand-600 shadow-sm transition-colors group-hover:bg-brand-600 group-hover:text-white">
                                {{ number_format($item->monitoring_index, 0) }}%
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-black text-slate-800">{{ $item->name }}</p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ $item->children_count }} Anak • {{ $item->month_measurements_count }} Pengukuran</p>
                            </div>
                            <div class="hidden sm:block text-right">
                                <p class="text-[11px] font-black text-slate-800">{{ $item->avg_latest_weight !== null ? number_format($item->avg_latest_weight, 1) : '-' }}kg</p>
                                <p class="text-[11px] font-bold text-slate-400">{{ $item->avg_latest_height !== null ? number_format($item->avg_latest_height, 1) : '-' }}cm</p>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-sm font-bold text-slate-300">
                            Belum ada data agregat.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    <section class="card border-none p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-xl font-black tracking-tight text-slate-800">Aktivitas Terkini</h3>
                <p class="mt-1 text-sm font-medium text-slate-400">Data pengukuran terbaru yang masuk ke sistem.</p>
            </div>
            <a href="{{ route('measurements.index') }}" class="btn-secondary px-6">Selengkapnya</a>
        </div>

        <div class="mt-8 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="pb-4 font-black">Anak</th>
                        <th class="pb-4 font-black">Parameter</th>
                        <th class="pb-4 font-black">Sumber</th>
                        <th class="pb-4 font-black">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($latestMeasurements as $measurement)
                        <tr class="group transition-colors hover:bg-slate-50/50">
                            <td class="py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 font-black text-brand-600 transition-colors group-hover:bg-brand-600 group-hover:text-white">
                                        {{ substr($measurement->child->child_name, 0, 1) }}
                                    </div>
                                    <p class="font-black text-slate-800">{{ $measurement->child->child_name }}</p>
                                </div>
                            </td>
                            <td class="py-5">
                                <div class="flex gap-4">
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase">Berat</p>
                                        <p class="font-bold text-slate-700">{{ $measurement->weight_kg }} kg</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase">Tinggi</p>
                                        <p class="font-bold text-slate-700">{{ $measurement->height_cm }} cm</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5">
                                <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-[10px] font-black uppercase tracking-tighter text-slate-600">
                                    {{ $measurement->source }}
                                </span>
                            </td>
                            <td class="py-5 text-xs font-bold text-slate-400">
                                {{ $measurement->measured_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center font-bold text-slate-300">Belum ada data pengukuran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctxTrend = document.getElementById('monthlyTrendChart');
        if (ctxTrend) {
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: @json($monthlyTrend['labels']),
                    datasets: [
                        {
                            label: 'Indeks (%)',
                            data: @json($monthlyTrend['monitoringIndexes']),
                            yAxisID: 'y',
                            borderColor: '#0e8ce9',
                            backgroundColor: 'rgba(14, 140, 233, 0.08)',
                            borderWidth: 4,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#0e8ce9',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 3,
                        },
                        {
                            label: 'Pengukuran',
                            data: @json($monthlyTrend['measurementCounts']),
                            yAxisID: 'y1',
                            borderColor: '#6366f1',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            tension: 0.4,
                            pointRadius: 0,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 12 },
                            cornerRadius: 12,
                            displayColors: false
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, weight: '600' }, color: '#94a3b8' }
                        },
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: { color: '#f1f5f9' },
                            ticks: { font: { size: 11, weight: '600' }, color: '#94a3b8', callback: v => v + '%' }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            display: false
                        }
                    }
                }
            });
        }
    </script>

    @if (auth()->user()->isAdmin() && $posyanduAggregate->isNotEmpty())
        <script>
            const ctxPosyandu = document.getElementById('posyanduAggregateChart');
            if (ctxPosyandu) {
                new Chart(ctxPosyandu, {
                    type: 'bar',
                    data: {
                        labels: @json($posyanduChart['labels']),
                        datasets: [
                            {
                                label: 'Pengukuran',
                                data: @json($posyanduChart['measurements']),
                                backgroundColor: '#0e8ce9',
                                borderRadius: 12,
                                barThickness: 20,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 12,
                                cornerRadius: 12,
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10, weight: '600' }, color: '#94a3b8' }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: { font: { size: 10, weight: '600' }, color: '#94a3b8' }
                            }
                        }
                    }
                });
            }
        </script>
    @endif
@endpush
