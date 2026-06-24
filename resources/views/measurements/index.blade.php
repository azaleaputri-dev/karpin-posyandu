@extends('layouts.app')

@section('content')
    <section class="card border-none p-8" id="rfid-monitor" data-endpoint="{{ route('iot.latest-scan') }}" data-measurement-url="{{ route('measurements.create') }}" data-initial-scan-id="{{ $initialScanId }}">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-xl font-black tracking-tight text-slate-800">Monitoring RFID Langsung</h3>
                <p class="mt-1 text-sm font-medium text-slate-400">Tap kartu, lalu data anak langsung muncul di layar tanpa langkah tambahan.</p>
            </div>
            <div class="flex items-center gap-2 text-xs font-black text-slate-500">
                <span id="rfid-indicator" class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                <span id="rfid-connection-label">Menunggu perangkat</span>
            </div>
        </div>

        <div id="rfid-empty" class="mt-6 border-2 border-dashed border-slate-200 p-8 text-center text-sm font-bold text-slate-400">
            Belum ada kartu yang ditap.
        </div>

        <div id="rfid-result" class="mt-6 hidden grid gap-6 lg:grid-cols-[1fr_0.7fr]">
            <div class="bg-slate-50 p-6 ring-1 ring-slate-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Anak Teridentifikasi</p>
                        <h4 id="rfid-child-name" class="mt-2 text-2xl font-black text-slate-800"></h4>
                        <p id="rfid-child-meta" class="mt-1 text-sm font-bold text-slate-500"></p>
                    </div>
                    <span id="rfid-status" class="bg-emerald-100 px-3 py-1.5 text-[10px] font-black uppercase text-emerald-700"></span>
                </div>
                <dl class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div><dt class="text-[10px] font-black uppercase text-slate-400">UID</dt><dd id="rfid-uid" class="mt-1 font-black text-slate-700"></dd></div>
                    <div><dt class="text-[10px] font-black uppercase text-slate-400">Ibu</dt><dd id="rfid-mother" class="mt-1 font-black text-slate-700"></dd></div>
                    <div><dt class="text-[10px] font-black uppercase text-slate-400">Posyandu</dt><dd id="rfid-posyandu" class="mt-1 font-black text-slate-700"></dd></div>
                </dl>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a id="rfid-measurement-link" href="#" class="btn-primary inline-flex hidden">Lanjut Pengukuran</a>
                    <a id="rfid-edit-link" href="#" class="btn-secondary inline-flex">Edit Data</a>
                    <a id="rfid-child-link" href="#" class="btn-secondary inline-flex">Buka data anak</a>
                    <form id="rfid-delete-form" action="#" method="POST" class="inline-flex" onsubmit="return confirm('Hapus data anak ini beserta riwayat pengukurannya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary text-rose-600 hover:border-rose-200 hover:bg-rose-50">Hapus</button>
                    </form>
                </div>
            </div>
            <div class="bg-slate-900 p-6 text-white">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pengukuran Terakhir</p>
                <div class="mt-5 grid grid-cols-2 gap-4">
                    <div><p class="text-xs font-bold text-slate-400">Berat</p><p id="rfid-weight" class="mt-1 text-3xl font-black">-</p></div>
                    <div><p class="text-xs font-bold text-slate-400">Tinggi</p><p id="rfid-height" class="mt-1 text-3xl font-black">-</p></div>
                </div>
                <p id="rfid-measured-at" class="mt-5 text-xs font-bold text-slate-400">Belum ada pengukuran.</p>
            </div>
        </div>
    </section>

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

@push('scripts')
    <script>
        const rfidMonitor = document.getElementById('rfid-monitor');
        let lastRfidScanId = Number(rfidMonitor.dataset.initialScanId || 0);

        fetch('{{ route("iot.start-listener") }}', { headers: { 'Accept': 'application/json' } }).catch(() => {});

        async function refreshRfidMonitor() {
            const indicator = document.getElementById('rfid-indicator');
            const connectionLabel = document.getElementById('rfid-connection-label');

            try {
                const response = await fetch(rfidMonitor.dataset.endpoint, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });
                if (!response.ok) throw new Error('HTTP ' + response.status);

                const payload = await response.json();
                const monitor = payload.data || {};
                const scan = monitor.id ? monitor : null;
                const device = monitor.device;

                if (device) {
                    indicator.className = 'h-2.5 w-2.5 rounded-full bg-emerald-500';
                    connectionLabel.textContent = device.last_seen_at_human
                        ? `${device.device_name} terkoneksi · ${device.last_seen_at_human}`
                        : `${device.device_name} terkoneksi`;
                } else {
                    indicator.className = 'h-2.5 w-2.5 rounded-full bg-amber-400';
                    connectionLabel.textContent = 'Menunggu perangkat';
                }

                if (!scan || scan.id <= lastRfidScanId) return;
                lastRfidScanId = scan.id;
                const scanDevice = scan.device || device;
                connectionLabel.textContent = scanDevice
                    ? `${scanDevice.device_name} membaca kartu`
                    : 'Kartu terbaca';
                document.getElementById('rfid-empty').classList.add('hidden');
                document.getElementById('rfid-result').classList.remove('hidden');
                rfidMonitor.scrollIntoView({ behavior: 'smooth', block: 'start' });
                document.getElementById('rfid-uid').textContent = scan.rfid_uid;

                if (!scan.child) {
                    document.getElementById('rfid-child-name').textContent = 'Kartu belum terdaftar';
                    document.getElementById('rfid-child-meta').textContent = 'Daftarkan UID ini pada data anak terlebih dahulu.';
                    document.getElementById('rfid-status').textContent = 'Tidak dikenal';
                    document.getElementById('rfid-status').className = 'bg-rose-100 px-3 py-1.5 text-[10px] font-black uppercase text-rose-700';
                    document.getElementById('rfid-mother').textContent = '-';
                    document.getElementById('rfid-posyandu').textContent = '-';
                    document.getElementById('rfid-child-link').classList.add('hidden');
                    document.getElementById('rfid-measurement-link').classList.add('hidden');
                    document.getElementById('rfid-edit-link').classList.add('hidden');
                    document.getElementById('rfid-delete-form').classList.add('hidden');
                    document.getElementById('rfid-weight').textContent = '-';
                    document.getElementById('rfid-height').textContent = '-';
                    document.getElementById('rfid-measured-at').textContent = 'Belum ada pengukuran.';
                    return;
                }

                document.getElementById('rfid-child-name').textContent = scan.child.child_name;
                document.getElementById('rfid-child-meta').textContent = `${scan.child.gender === 'L' ? 'Laki-laki' : 'Perempuan'} | ${scan.child.age}`;
                document.getElementById('rfid-status').textContent = 'Terdaftar';
                document.getElementById('rfid-status').className = 'bg-emerald-100 px-3 py-1.5 text-[10px] font-black uppercase text-emerald-700';
                document.getElementById('rfid-mother').textContent = scan.child.mother_name;
                document.getElementById('rfid-posyandu').textContent = scan.child.posyandu || '-';

                const childLink = document.getElementById('rfid-child-link');
                childLink.href = scan.child.detail_url;
                childLink.classList.remove('hidden');
                const measurementLink = document.getElementById('rfid-measurement-link');
                const scanDeviceId = scan.device && scan.device.id ? scan.device.id : (scanDevice && scanDevice.id ? scanDevice.id : '');
                measurementLink.href = `${rfidMonitor.dataset.measurementUrl}?child_id=${scan.child.id}&device_id=${scanDeviceId}&source=iot`;
                measurementLink.classList.remove('hidden');
                const editLink = document.getElementById('rfid-edit-link');
                editLink.href = scan.child.edit_url;
                editLink.classList.remove('hidden');
                const deleteForm = document.getElementById('rfid-delete-form');
                deleteForm.action = scan.child.delete_url;
                deleteForm.classList.remove('hidden');
                const measurement = scan.latest_measurement;
                document.getElementById('rfid-weight').textContent = measurement ? `${measurement.weight_kg} kg` : '-';
                document.getElementById('rfid-height').textContent = measurement ? `${measurement.height_cm} cm` : '-';
                document.getElementById('rfid-measured-at').textContent = measurement ? `Dicatat ${measurement.measured_at}` : 'Belum ada pengukuran.';
            } catch (error) {
                indicator.className = 'h-2.5 w-2.5 rounded-full bg-rose-500';
                connectionLabel.textContent = 'Koneksi monitoring terputus';
            }
        }

        refreshRfidMonitor();
        window.setInterval(refreshRfidMonitor, 750);
    </script>
@endpush
