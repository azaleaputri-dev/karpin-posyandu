@extends('layouts.app')

@section('content')
    <section class="card border-none p-8">
        <div class="mb-8">
            <span class="inline-block rounded-full bg-brand-100 px-4 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-brand-600">Bridge RFID</span>
            <h3 class="mt-4 text-2xl font-black tracking-tight text-slate-800">Pembaca Kartu Lokal</h3>
            <p class="mt-2 text-sm font-medium text-slate-400">Tap kartu RFID ke reader USB — UID akan otomatis terbaca dan dikirim ke sistem.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_1.2fr]">
            <div class="space-y-6">
                <div class="space-y-1.5">
                    <label class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">Perangkat RFID</label>
                    <select id="device-select" class="input">
                        @foreach ($devices as $device)
                            <option value="{{ $device->id }}">{{ $device->device_name }} ({{ $device->device_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="uid-input" class="ml-1 text-[10px] font-black uppercase tracking-widest text-slate-400">UID Kartu</label>
                    <input id="uid-input" type="text" class="input mt-1.5 font-mono text-lg tracking-widest" placeholder="Tap kartu atau ketik UID manual" autocomplete="off" autofocus>
                </div>

                <div class="rounded-[2rem] bg-slate-900 p-8 text-center text-white ring-1 ring-white/10">
                    <div id="reader-indicator" class="mx-auto h-16 w-16 rounded-full bg-amber-500/20 ring-4 ring-amber-500/30 flex items-center justify-center">
                        <svg class="h-8 w-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 1.1-.9 2-2 2s-2-.9-2-2V9c0-1.1.9-2 2-2s2 .9 2 2v2zM8 15h8M12 19v-4"/>
                        </svg>
                    </div>
                    <p id="reader-status" class="mt-4 text-lg font-black">Menunggu kartu...</p>
                    <p id="reader-sub" class="mt-1 text-sm font-medium text-slate-400">Tap kartu pada reader USB</p>
                </div>
            </div>

            <div id="result-area" class="hidden space-y-4">
                <div class="rounded-[2rem] bg-emerald-50 p-6 ring-1 ring-emerald-500/10">
                    <div class="flex items-center gap-3">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500 text-2xl font-black text-white" id="result-initial">?</div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Kartu Teridentifikasi</p>
                            <h4 id="result-name" class="mt-1 text-xl font-black text-emerald-900"></h4>
                            <p id="result-meta" class="text-sm font-bold text-emerald-700"></p>
                        </div>
                    </div>
                    <dl class="mt-5 grid gap-3 sm:grid-cols-3">
                        <div><dt class="text-[10px] font-black uppercase text-emerald-600">UID</dt><dd id="result-uid" class="mt-1 font-black text-emerald-900"></dd></div>
                        <div><dt class="text-[10px] font-black uppercase text-emerald-600">Ibu</dt><dd id="result-mother" class="mt-1 font-black text-emerald-900"></dd></div>
                        <div><dt class="text-[10px] font-black uppercase text-emerald-600">Posyandu</dt><dd id="result-posyandu" class="mt-1 font-black text-emerald-900"></dd></div>
                    </dl>
                    <div class="mt-5 flex gap-3">
                        <a id="result-measure-link" href="#" class="btn-primary">Lanjut Pengukuran</a>
                        <a id="result-child-link" href="#" class="btn-secondary">Buka Data Anak</a>
                    </div>
                </div>
            </div>

            <div id="unrecognized-area" class="hidden space-y-4">
                <div class="rounded-[2rem] bg-amber-50 p-6 ring-1 ring-amber-500/10">
                    <div class="flex items-center gap-3">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-500 text-2xl font-black text-white">!</div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-amber-600">Kartu Tidak Dikenal</p>
                            <h4 class="mt-1 text-xl font-black text-amber-900">UID: <span id="unrecognized-uid" class="font-mono"></span></h4>
                            <p class="text-sm font-bold text-amber-700">Kartu belum terdaftar di sistem.</p>
                        </div>
                    </div>
                    <div class="mt-5 flex gap-3">
                        <a href="{{ route('rfid-cards.create') }}" class="btn-primary">Daftarkan Kartu</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const uidInput = document.getElementById('uid-input');
        const deviceSelect = document.getElementById('device-select');
        const readerStatus = document.getElementById('reader-status');
        const readerSub = document.getElementById('reader-sub');
        const readerIndicator = document.getElementById('reader-indicator');
        const resultArea = document.getElementById('result-area');
        const unrecognizedArea = document.getElementById('unrecognized-area');
        let autoSubmitTimer = null;
        let lastSubmitUid = '';

        uidInput.addEventListener('input', function() {
            if (autoSubmitTimer) {
                clearTimeout(autoSubmitTimer);
            }

            const uid = this.value.trim();
            if (uid.length === 0) return;

            autoSubmitTimer = setTimeout(() => {
                sendScan(uid);
            }, 200);
        });

        uidInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (autoSubmitTimer) {
                    clearTimeout(autoSubmitTimer);
                    autoSubmitTimer = null;
                }
                const uid = this.value.trim();
                if (uid.length > 0) {
                    sendScan(uid);
                }
            }
        });

        async function sendScan(uid) {
            if (uid === lastSubmitUid) return;
            lastSubmitUid = uid;

            uidInput.disabled = true;
            readerIndicator.innerHTML = `
                <svg class="h-8 w-8 text-blue-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>`;
            readerStatus.textContent = 'Memproses...';
            resultArea.classList.add('hidden');
            unrecognizedArea.classList.add('hidden');

            try {
                const response = await fetch('{{ route("rfid-bridge.scan") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        device_id: deviceSelect.value,
                        rfid_uid: uid
                    })
                });

                const result = await response.json();
                const data = result.data;

                if (data.child) {
                    const c = data.child;
                    document.getElementById('result-initial').textContent = c.child_name.charAt(0);
                    document.getElementById('result-name').textContent = c.child_name;
                    document.getElementById('result-meta').textContent = `${c.gender === 'L' ? 'Laki-laki' : 'Perempuan'} | ${c.age}`;
                    document.getElementById('result-uid').textContent = data.rfid_uid;
                    document.getElementById('result-mother').textContent = c.mother_name || '-';
                    document.getElementById('result-posyandu').textContent = '-';
                    document.getElementById('result-measure-link').href = `{{ route('measurements.create') }}?child_id=${c.id}&device_id=${deviceSelect.value}&source=iot`;
                    document.getElementById('result-child-link').href = '/children/' + c.id;
                    resultArea.classList.remove('hidden');

                    readerIndicator.innerHTML = `
                        <svg class="h-8 w-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>`;
                    readerStatus.textContent = 'Kartu terbaca!';
                    readerSub.textContent = data.rfid_uid;
                } else {
                    document.getElementById('unrecognized-uid').textContent = data.rfid_uid;
                    unrecognizedArea.classList.remove('hidden');

                    readerIndicator.innerHTML = `
                        <svg class="h-8 w-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01"/>
                        </svg>`;
                    readerStatus.textContent = 'Kartu tidak dikenal';
                    readerSub.textContent = data.rfid_uid;
                }
            } catch (error) {
                readerIndicator.innerHTML = `
                    <svg class="h-8 w-8 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>`;
                readerStatus.textContent = 'Gagal mengirim';
                readerSub.textContent = 'Coba lagi';
            }

            uidInput.disabled = false;
            uidInput.value = '';
            uidInput.focus();
            setTimeout(() => { lastSubmitUid = ''; }, 1000);
        }
    </script>
@endpush
