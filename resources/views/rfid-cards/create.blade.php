@extends('layouts.app')

@section('content')
    <section class="card border-none p-8">
        @include('partials.page-header', [
            'eyebrow' => 'Registrasi Kartu Anak',
            'title' => 'Daftarkan Kartu RFID',
            'description' => 'Pilih anak, lalu tap kartu pada reader untuk mengambil UID secara otomatis.',
        ])

        <div id="rfid-registration-reader" data-endpoint="{{ route('iot.latest-scan') }}" data-initial-scan-id="{{ $initialScanId }}" class="mt-8 bg-slate-900 p-6 text-white">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pembaca Kartu</p>
                    <p id="reader-status" class="mt-2 font-black">Menghubungkan ke reader...</p>
                </div>
                <span id="reader-indicator" class="h-3 w-3 rounded-full bg-amber-400"></span>
            </div>
            <p class="mt-4 text-xs font-bold text-slate-400">Data lama diabaikan. UID hanya diisi dari kartu yang ditap setelah halaman ini dibuka.</p>
        </div>

        <form action="{{ route('rfid-cards.store') }}" method="POST" class="mt-8 space-y-6">
            @csrf
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-1.5">
                    <label for="child_id" class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Data Anak</label>
                    <select id="child_id" name="child_id" class="input" required>
                        <option value="">Pilih anak</option>
                        @foreach ($children as $child)
                            <option value="{{ $child->id }}" {{ old('child_id') == $child->id ? 'selected' : '' }}>
                                {{ $child->child_name }} - {{ $child->posyandu->name }}{{ $child->rfid_uid ? ' (sudah memiliki kartu)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @include('partials.field-error', ['name' => 'child_id'])
                </div>
                <div class="space-y-1.5">
                    <label for="rfid_uid" class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">UID Kartu RFID</label>
                    <input id="rfid_uid" name="rfid_uid" value="{{ old('rfid_uid') }}" class="input uppercase" placeholder="Tap kartu atau masukkan UID" required>
                    @include('partials.field-error', ['name' => 'rfid_uid'])
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Simpan Kartu</button>
                <a href="{{ route('rfid-cards.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        const reader = document.getElementById('rfid-registration-reader');
        const uidInput = document.getElementById('rfid_uid');
        const statusLabel = document.getElementById('reader-status');
        const indicator = document.getElementById('reader-indicator');
        let latestScanId = Number(reader.dataset.initialScanId || 0);

        fetch('{{ route("iot.start-listener") }}', { headers: { 'Accept': 'application/json' } }).catch(() => {});

        async function readNewCard() {
            try {
                const response = await fetch(reader.dataset.endpoint, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });
                if (!response.ok) throw new Error('HTTP ' + response.status);

                const payload = await response.json();
                const scan = payload.data;
                indicator.className = 'h-3 w-3 rounded-full bg-emerald-500';
                statusLabel.textContent = 'Reader aktif, silakan tap kartu';

                if (!scan || scan.id <= latestScanId) return;
                latestScanId = scan.id;
                uidInput.value = scan.rfid_uid;
                statusLabel.textContent = `Kartu terbaca: ${scan.rfid_uid}`;
                uidInput.focus();
            } catch (error) {
                indicator.className = 'h-3 w-3 rounded-full bg-rose-500';
                statusLabel.textContent = 'Reader tidak terhubung';
            }
        }

        readNewCard();
        window.setInterval(readNewCard, 1500);
    </script>
@endpush
