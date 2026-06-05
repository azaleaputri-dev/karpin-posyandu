@extends('layouts.app')

@section('content')
    <section class="card overflow-hidden">
        <div class="grid gap-6 bg-[linear-gradient(135deg,_rgba(15,118,110,0.96),_rgba(15,23,42,0.98))] p-6 text-white md:grid-cols-2">
            <div class="flex flex-col justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.35em] text-teal-100">Indeks Perkembangan</p>
                    <h3 class="mt-3 max-w-xl text-4xl font-black leading-tight">Ringkasan pertumbuhan dan pemantauan posyandu</h3>
                    <p class="mt-3 max-w-2xl text-base leading-7 text-cyan-50">
                        {{ auth()->user()->isAdmin() ? 'Pantauan lintas seluruh posyandu dengan fokus pada cakupan pengukuran, intensitas pencatatan, dan tren perkembangan anak selama 6 bulan terakhir.' : $scopeLabel . ' dengan fokus pada cakupan pengukuran, intensitas pencatatan, dan tren perkembangan anak selama 6 bulan terakhir.' }}
                    </p>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <span class="rounded-2xl bg-white/10 px-4 py-2 text-sm font-semibold text-cyan-50">Periode {{ $developmentSummary['month_label'] }}</span>
                    <span class="rounded-2xl bg-white/10 px-4 py-2 text-sm font-semibold text-cyan-50">{{ $developmentSummary['children_measured_this_month'] }} anak terpantau</span>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div class="rounded-3xl border border-white/15 bg-white/10 p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-teal-100">Indeks Bulan Ini</p>
                    <p class="mt-3 text-4xl font-black">{{ number_format($developmentSummary['monitoring_index'], 1) }}%</p>
                    <p class="mt-2 text-sm text-cyan-50">Persentase anak yang sudah terpantau pada bulan berjalan.</p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-teal-100">Pengukuran Aktif</p>
                    <p class="mt-3 text-4xl font-black">{{ $stats['measurements_this_month'] }}</p>
                    <p class="mt-2 text-sm text-cyan-50">Total catatan pengukuran yang masuk bulan ini.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5">
            <p class="text-sm text-slate-500">Posyandu</p>
            <p class="mt-2 text-4xl font-black text-slate-900">{{ $stats['posyandus'] }}</p>
            <p class="mt-2 text-xs text-slate-500">Wilayah yang sedang dipantau.</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-slate-500">Data Anak</p>
            <p class="mt-2 text-4xl font-black text-slate-900">{{ $stats['children'] }}</p>
            <p class="mt-2 text-xs text-slate-500">Anak terdaftar pada ruang lingkup akun ini.</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-slate-500">Pengukuran Bulan Ini</p>
            <p class="mt-2 text-4xl font-black text-slate-900">{{ $stats['measurements_this_month'] }}</p>
            <p class="mt-2 text-xs text-slate-500">Aktivitas timbang dan ukur terbaru.</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-slate-500">Indeks Pemantauan</p>
            <p class="mt-2 text-4xl font-black text-slate-900">{{ number_format($stats['monitoring_index'], 1) }}%</p>
            <p class="mt-2 text-xs text-slate-500">Cakupan anak yang sudah terpantau.</p>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <div class="card p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm text-slate-500">Tren 6 bulan</p>
                    <h3 class="text-xl font-bold text-slate-900">Grafik indeks perkembangan</h3>
                    <p class="mt-1 text-sm text-slate-500">Pergerakan indeks pemantauan, jumlah pengukuran, dan anak yang tercatat setiap bulan.</p>
                </div>
                <span class="inline-flex rounded-2xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600" style="align-self:flex-start; white-space:nowrap;">
                    {{ auth()->user()->isAdmin() ? 'Mode Admin - Lintas Posyandu' : $scopeLabel }}
                </span>
            </div>

            <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-4" style="height: 360px;">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>

        <div class="card p-6">
            <p class="text-sm text-slate-500">Ringkasan bulan ini</p>
            <h3 class="text-xl font-bold text-slate-900">Performa pemantauan</h3>
            <div class="mt-5 space-y-3">
                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-sm text-slate-500">Anak terpantau</p>
                    <p class="mt-2 text-4xl font-black text-slate-900">{{ $developmentSummary['children_measured_this_month'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">dari {{ $stats['children'] }} anak yang terdaftar</p>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="rounded-3xl bg-emerald-50 p-5">
                        <p class="text-sm text-emerald-700">Rata-rata berat terakhir</p>
                        <p class="mt-2 text-2xl font-black text-emerald-900">{{ $developmentSummary['avg_latest_weight'] !== null ? number_format($developmentSummary['avg_latest_weight'], 2) . ' kg' : '-' }}</p>
                    </div>
                    <div class="rounded-3xl bg-cyan-100 p-5">
                        <p class="text-sm text-cyan-700">Rata-rata tinggi terakhir</p>
                        <p class="mt-2 text-2xl font-black text-slate-900">{{ $developmentSummary['avg_latest_height'] !== null ? number_format($developmentSummary['avg_latest_height'], 2) . ' cm' : '-' }}</p>
                    </div>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5">
                    <p class="text-sm text-slate-500">Total riwayat pengukuran</p>
                    <p class="mt-2 text-3xl font-black text-slate-900">{{ $stats['measurements'] }}</p>
                </div>
            </div>
        </div>
    </section>

    @if (auth()->user()->isAdmin())
        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="card p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Perbandingan wilayah</p>
                        <h3 class="text-xl font-bold text-slate-900">Indeks perkembangan antar posyandu</h3>
                        <p class="mt-1 text-sm text-slate-500">Bandingkan cakupan pemantauan dan intensitas pencatatan tiap posyandu.</p>
                    </div>
                    <span class="rounded-2xl bg-cyan-100 px-3 py-2 text-xs font-semibold uppercase text-cyan-700">Admin Puskesmas</span>
                </div>

                @if ($posyanduAggregate->isNotEmpty())
                    <div class="mt-6 grid gap-3 md:grid-cols-3">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs uppercase text-slate-500">Posyandu Terpantau</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">{{ $posyanduAggregate->count() }}</p>
                        </div>
                        <div class="rounded-2xl bg-emerald-50 p-4">
                            <p class="text-xs uppercase text-emerald-700">Indeks Tertinggi</p>
                            <p class="mt-2 text-2xl font-black text-emerald-900">{{ number_format($posyanduAggregate->max('monitoring_index'), 1) }}%</p>
                        </div>
                        <div class="rounded-2xl bg-cyan-50 p-4">
                            <p class="text-xs uppercase text-cyan-700">Pengukuran Terbanyak</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">{{ $posyanduAggregate->max('month_measurements_count') }}</p>
                        </div>
                    </div>

                    <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-4" style="height: 400px;">
                        <canvas id="posyanduAggregateChart"></canvas>
                    </div>
                @else
                    <div class="mt-6 rounded-3xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
                        Belum ada data posyandu untuk ditampilkan.
                    </div>
                @endif
            </div>

            <div class="card p-6">
                <p class="text-sm text-slate-500">Snapshot per posyandu</p>
                <h3 class="text-xl font-bold text-slate-900">Cakupan bulan berjalan</h3>
                <div class="mt-5 space-y-3">
                    @forelse ($posyanduAggregate as $item)
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $item->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $item->children_count }} anak | {{ $item->month_measurements_count }} pengukuran bulan ini</p>
                                </div>
                                <span class="rounded-2xl bg-emerald-100 px-3 py-2 text-xs font-semibold uppercase text-emerald-700">
                                    {{ number_format($item->monitoring_index, 1) }}% terpantau
                                </span>
                            </div>
                            <div class="mt-4 grid gap-3 md:grid-cols-2 text-sm">
                                <div class="rounded-2xl bg-white p-3">
                                    <p class="text-slate-500">Rata-rata berat terakhir</p>
                                    <p class="mt-1 font-bold text-slate-900">{{ $item->avg_latest_weight !== null ? number_format($item->avg_latest_weight, 2) . ' kg' : '-' }}</p>
                                </div>
                                <div class="rounded-2xl bg-white p-3">
                                    <p class="text-slate-500">Rata-rata tinggi terakhir</p>
                                    <p class="mt-1 font-bold text-slate-900">{{ $item->avg_latest_height !== null ? number_format($item->avg_latest_height, 2) . ' cm' : '-' }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-3xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
                            Belum ada data agregat.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    <section class="card p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-slate-500">Aktivitas terbaru</p>
                <h3 class="text-xl font-bold text-slate-900">Pengukuran terakhir</h3>
            </div>
            <a href="{{ route('measurements.index') }}" class="btn-secondary">Lihat semua</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="text-slate-400">
                    <tr>
                        <th class="pb-3 font-medium">Anak</th>
                        <th class="pb-3 font-medium">Berat</th>
                        <th class="pb-3 font-medium">Tinggi</th>
                        <th class="pb-3 font-medium">Sumber</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($latestMeasurements as $measurement)
                        <tr>
                            <td class="py-4">
                                <p class="font-semibold text-slate-800">{{ $measurement->child->child_name }}</p>
                                <p class="text-xs text-slate-500">{{ $measurement->measured_at->format('d M Y H:i') }}</p>
                            </td>
                            <td class="py-4">{{ $measurement->weight_kg }} kg</td>
                            <td class="py-4">{{ $measurement->height_cm }} cm</td>
                            <td class="py-4">
                                <span class="rounded-2xl bg-slate-100 px-3 py-2 text-xs font-semibold uppercase text-slate-600">{{ $measurement->source }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-500">Belum ada data pengukuran.</td>
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
        new Chart(document.getElementById('monthlyTrendChart'), {
            type: 'line',
            data: {
                labels: @json($monthlyTrend['labels']),
                datasets: [
                    {
                        label: 'Indeks Pemantauan (%)',
                        data: @json($monthlyTrend['monitoringIndexes']),
                        yAxisID: 'y',
                        borderColor: 'rgba(13, 148, 136, 1)',
                        backgroundColor: 'rgba(13, 148, 136, 0.14)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'Jumlah Pengukuran',
                        data: @json($monthlyTrend['measurementCounts']),
                        yAxisID: 'y1',
                        borderColor: 'rgba(8, 145, 178, 1)',
                        backgroundColor: 'rgba(8, 145, 178, 0.18)',
                        tension: 0.35,
                        pointRadius: 3,
                    },
                    {
                        label: 'Anak Terpantau',
                        data: @json($monthlyTrend['measuredChildrenCounts']),
                        yAxisID: 'y1',
                        borderColor: 'rgba(245, 158, 11, 1)',
                        backgroundColor: 'rgba(245, 158, 11, 0.18)',
                        tension: 0.35,
                        pointRadius: 3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 14,
                            color: '#475569',
                        }
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#64748b',
                        },
                        grid: {
                            display: false,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            color: '#64748b',
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.12)',
                        },
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        ticks: {
                            color: '#64748b',
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    </script>

    @if (auth()->user()->isAdmin() && $posyanduAggregate->isNotEmpty())
        <script>
            new Chart(document.getElementById('posyanduAggregateChart'), {
                data: {
                    labels: @json($posyanduChart['labels']),
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Pengukuran Bulan Ini',
                            data: @json($posyanduChart['measurements']),
                            backgroundColor: 'rgba(8, 145, 178, 0.72)',
                            borderRadius: 10,
                            barThickness: 24,
                            maxBarThickness: 28,
                            yAxisID: 'y1',
                        },
                        {
                            type: 'line',
                            label: 'Indeks Pemantauan (%)',
                            data: @json($posyanduChart['monitoringIndexes']),
                            borderColor: 'rgba(13, 148, 136, 1)',
                            backgroundColor: 'rgba(13, 148, 136, 0.16)',
                            tension: 0.35,
                            fill: false,
                            pointRadius: 4,
                            pointHoverRadius: 5,
                            borderWidth: 3,
                            yAxisID: 'y',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 14,
                                color: '#475569',
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            bodyColor: '#e2e8f0',
                            padding: 12,
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#64748b',
                                maxRotation: 0,
                                minRotation: 0,
                                callback: function(value) {
                                    const label = this.getLabelForValue(value);
                                    if (! label) {
                                        return '';
                                    }

                                    if (label.length <= 14) {
                                        return label;
                                    }

                                    const words = label.split(' ');
                                    const lines = [];
                                    let current = '';

                                    words.forEach(function(word) {
                                        const next = current ? current + ' ' + word : word;

                                        if (next.length > 14) {
                                            if (current) {
                                                lines.push(current);
                                            }
                                            current = word;
                                        } else {
                                            current = next;
                                        }
                                    });

                                    if (current) {
                                        lines.push(current);
                                    }

                                    return lines;
                                }
                            },
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                color: '#64748b',
                                callback: function(value) {
                                    return value + '%';
                                }
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.12)',
                            },
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            ticks: {
                                color: '#64748b',
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        </script>
    @endif
@endpush
