@extends('layouts.app')

@section('content')
    <section class="card border-none p-8">
        @include('partials.page-header', [
            'eyebrow' => 'Registrasi RFID',
            'title' => 'Kartu RFID Terdaftar',
            'description' => 'Kelola kartu RFID yang dipakai sebagai identitas digital pengganti buku KIA.',
            'action' => new \Illuminate\Support\HtmlString('<a href="' . route('devices.create') . '" class="btn-primary px-8">Tambah Kartu RFID</a>'),
        ])

        <div class="relative mt-8 overflow-hidden rounded-[2rem] bg-slate-900 p-8 text-white shadow-xl shadow-brand-900/20">
            <div class="absolute right-0 top-0 -mr-16 -mt-16 h-64 w-64 rounded-full bg-brand-500/10 blur-3xl"></div>

            <div class="relative z-10">
                <span class="inline-block rounded-lg bg-brand-500/20 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-brand-300">RFID Card API</span>
                <h4 class="mt-4 text-2xl font-black tracking-tight">Endpoint Integrasi Kartu</h4>

                <div class="mt-8 grid gap-6 lg:grid-cols-2">
                    <div class="space-y-3">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">Pemeriksaan Koneksi</p>
                        <code class="block overflow-x-auto rounded-2xl bg-black/40 p-4 text-[11px] font-mono text-brand-200 ring-1 ring-white/10">
                            GET /api/iot/ping<br>
                            Header: X-Device-Token: {TOKEN_PERANGKAT}
                        </code>
                    </div>
                    <div class="space-y-3">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">Sinkronisasi Kartu dan Pencatatan</p>
                        <code class="block overflow-x-auto rounded-2xl bg-black/40 p-4 text-[11px] font-mono text-brand-200 ring-1 ring-white/10">
                            POST /api/iot/measurements<br>
                            Header: X-Device-Token: {TOKEN_PERANGKAT}
                        </code>
                    </div>
                </div>

                <p class="mt-6 text-sm font-medium italic leading-relaxed text-slate-400">
                    * Gunakan <span class="text-brand-300">child_nik</span> jika kartu hanya mengirim identitas anak tanpa ID internal sistem.
                </p>
            </div>
        </div>

        <div class="mt-8 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="pb-4 pl-4 font-black">Informasi Kartu</th>
                        <th class="pb-4 font-black">Lokasi Unit</th>
                        <th class="pb-4 font-black">Status</th>
                        <th class="pb-4 font-black">API Token</th>
                        <th class="pb-4 pr-4 text-right font-black">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($devices as $device)
                        <tr class="group transition-colors hover:bg-slate-50/50">
                            <td class="py-5 pl-4">
                                <p class="font-black text-slate-800">{{ $device->device_name }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-tight text-slate-400">{{ $device->device_code }} • {{ $device->device_type }}</p>
                            </td>
                            <td class="py-5 font-bold text-slate-600">{{ optional($device->posyandu)->name ?? 'Puskesmas' }}</td>
                            <td class="py-5">
                                <span class="rounded-lg px-3 py-1.5 text-[10px] font-black uppercase tracking-tighter {{ $device->status === 'online' ? 'bg-emerald-50 text-emerald-600' : ($device->status === 'maintenance' ? 'bg-amber-50 text-amber-600' : 'bg-slate-50 text-slate-500') }}">
                                    {{ $device->status }}
                                </span>
                            </td>
                            <td class="py-5">
                                <code class="rounded bg-slate-100 px-2 py-1 font-mono text-[11px] text-slate-500">{{ $device->api_token }}</code>
                            </td>
                            <td class="py-5 pr-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('devices.edit', $device) }}" class="btn-secondary px-4 py-2 text-xs">Edit</a>
                                    <form action="{{ route('devices.destroy', $device) }}" method="POST" onsubmit="return confirm('Hapus kartu RFID ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-secondary px-4 py-2 text-xs text-rose-600 hover:border-rose-200 hover:bg-rose-50">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @component('partials.empty-state', ['colspan' => 5])Belum ada kartu RFID terdaftar.@endcomponent
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            @include('partials.pagination', ['paginator' => $devices])
        </div>
    </section>
@endsection
