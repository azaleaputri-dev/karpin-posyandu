@extends('layouts.app')

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-6">
            <div class="card p-6">
                @include('partials.page-header', [
                    'eyebrow' => 'Detail anak',
                    'title' => $child->child_name,
                    'description' => 'Pantau pertumbuhan berat dan tinggi badan anak dari riwayat pengukuran.',
                    'action' => new \Illuminate\Support\HtmlString('<div class="flex gap-3"><a href="' . route('children.export-pdf', $child) . '" class="btn-primary">Export PDF</a><a href="' . route('children.index') . '" class="btn-secondary">Kembali</a></div>'),
                ])

                <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Posyandu</p>
                        <p class="mt-2 font-bold text-slate-900">{{ $child->posyandu->name }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Usia</p>
                        <p class="mt-2 font-bold text-slate-900">{{ $child->birth_date->age }} tahun</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Ibu</p>
                        <p class="mt-2 font-bold text-slate-900">{{ $child->mother_name }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">NIK</p>
                        <p class="mt-2 font-bold text-slate-900">{{ $child->nik ?: '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Grafik pertumbuhan</p>
                        <h3 class="text-xl font-bold text-slate-900">Berat dan tinggi dari waktu ke waktu</h3>
                    </div>
                    <a href="{{ route('measurements.create') }}" class="btn-primary">Tambah Pengukuran</a>
                </div>

                @if ($summary['total_measurements'] > 0)
                    <div class="mt-6 grid gap-6">
                        <div class="rounded-3xl border border-slate-200 bg-white p-4">
                            <canvas id="weightChart" height="120"></canvas>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-white p-4">
                            <canvas id="heightChart" height="120"></canvas>
                        </div>
                    </div>
                @else
                    <div class="mt-6 rounded-3xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">
                        Belum ada riwayat pengukuran untuk anak ini.
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <p class="text-sm text-slate-500">Ringkasan pertumbuhan</p>
                <h3 class="text-xl font-bold text-slate-900">Snapshot terkini</h3>
                <div class="mt-4 rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">Status gizi sederhana</p>
                            <p class="mt-1 text-xl font-bold text-slate-900">{{ $nutritionStatus['overall'] }}</p>
                        </div>
                        <span class="rounded-full px-4 py-2 text-sm font-semibold {{ $nutritionStatus['badge'] }}">
                            {{ $nutritionStatus['overall'] }}
                        </span>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl bg-white p-3">
                            <p class="text-sm text-slate-500">Indikator berat</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $nutritionStatus['weight'] ?? '-' }}</p>
                        </div>
                        <div class="rounded-2xl bg-white p-3">
                            <p class="text-sm text-slate-500">Indikator tinggi</p>
                            <p class="mt-1 font-semibold text-slate-900">{{ $nutritionStatus['height'] ?? '-' }}</p>
                        </div>
                    </div>
                    <p class="mt-4 text-xs leading-6 text-slate-500">{{ $nutritionStatus['note'] }}</p>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl bg-emerald-50 p-4">
                        <p class="text-sm text-emerald-700">Berat terakhir</p>
                        <p class="mt-2 text-3xl font-black text-emerald-900">{{ $summary['latest_weight'] !== null ? $summary['latest_weight'] . ' kg' : '-' }}</p>
                    </div>
                    <div class="rounded-3xl bg-cyan-50 p-4">
                        <p class="text-sm text-cyan-700">Tinggi terakhir</p>
                        <p class="mt-2 text-3xl font-black text-cyan-900">{{ $summary['latest_height'] !== null ? $summary['latest_height'] . ' cm' : '-' }}</p>
                    </div>
                    <div class="rounded-3xl bg-amber-50 p-4">
                        <p class="text-sm text-amber-700">Kenaikan berat</p>
                        <p class="mt-2 text-3xl font-black text-amber-900">{{ $summary['weight_gain'] !== null ? number_format($summary['weight_gain'], 2) . ' kg' : '-' }}</p>
                    </div>
                    <div class="rounded-3xl bg-violet-50 p-4">
                        <p class="text-sm text-violet-700">Kenaikan tinggi</p>
                        <p class="mt-2 text-3xl font-black text-violet-900">{{ $summary['height_gain'] !== null ? number_format($summary['height_gain'], 2) . ' cm' : '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <p class="text-sm text-slate-500">Timeline pengukuran</p>
                <h3 class="text-xl font-bold text-slate-900">Riwayat lengkap</h3>
                <div class="mt-5 space-y-3">
                    @forelse ($measurements as $measurement)
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $measurement->measured_at->format('d M Y H:i') }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ optional($measurement->device)->device_name ?? 'Manual input' }} | {{ $measurement->source }}</p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase text-slate-600">{{ $measurement->source }}</span>
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-3 text-sm">
                                <div class="rounded-2xl bg-white p-3">
                                    <p class="text-slate-500">Berat</p>
                                    <p class="mt-1 font-bold text-slate-900">{{ $measurement->weight_kg }} kg</p>
                                </div>
                                <div class="rounded-2xl bg-white p-3">
                                    <p class="text-slate-500">Tinggi</p>
                                    <p class="mt-1 font-bold text-slate-900">{{ $measurement->height_cm }} cm</p>
                                </div>
                                <div class="rounded-2xl bg-white p-3">
                                    <p class="text-slate-500">Suhu</p>
                                    <p class="mt-1 font-bold text-slate-900">{{ $measurement->temperature_c !== null ? $measurement->temperature_c . ' C' : '-' }}</p>
                                </div>
                            </div>
                            @if ($measurement->notes)
                                <p class="mt-3 text-sm text-slate-600">{{ $measurement->notes }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-3xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
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
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#64748b',
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.12)',
                        },
                    },
                    y: {
                        ticks: {
                            color: '#64748b',
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.12)',
                        },
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
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.15)',
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: '#059669',
                        tension: 0.35,
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
                        borderColor: '#0891b2',
                        backgroundColor: 'rgba(8, 145, 178, 0.15)',
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: '#0891b2',
                        tension: 0.35,
                        fill: true,
                    }],
                },
                options: sharedOptions,
            });
        </script>
    @endif
@endpush
