@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'IoT registry',
            'title' => 'Perangkat IoT',
            'description' => 'Daftarkan alat yang akan mengirim data ke sistem.',
            'action' => new \Illuminate\Support\HtmlString('<a href="' . route('devices.create') . '" class="btn-primary">Tambah Perangkat</a>'),
        ])

        <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-950 p-5 text-slate-100">
            <p class="text-xs uppercase tracking-[0.35em] text-teal-300">IoT API</p>
            <h4 class="mt-3 text-lg font-bold">Endpoint perangkat</h4>
            <div class="mt-4 space-y-4 text-sm">
                <div>
                    <p class="font-semibold text-white">Cek token</p>
                    <code class="mt-2 block overflow-x-auto rounded-2xl bg-black/30 p-4 text-xs text-cyan-200">GET /api/iot/ping
Header: X-Device-Token: TOKEN_PERANGKAT</code>
                </div>
                <div>
                    <p class="font-semibold text-white">Kirim pengukuran</p>
                    <code class="mt-2 block overflow-x-auto rounded-2xl bg-black/30 p-4 text-xs text-cyan-200">POST /api/iot/measurements
Header: X-Device-Token: TOKEN_PERANGKAT
Content-Type: application/json

{
  "child_id": 1,
  "measured_at": "2026-06-02 21:00:00",
  "weight_kg": 12.4,
  "height_cm": 87.1,
  "temperature_c": 36.7,
  "notes": "Data dari alat timbang"
}</code>
                </div>
                <p class="text-slate-300">Perangkat juga bisa kirim <span class="font-mono text-cyan-200">child_nik</span> jika tidak memakai <span class="font-mono text-cyan-200">child_id</span>.</p>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="text-slate-400">
                    <tr>
                        <th class="pb-3 font-medium">Perangkat</th>
                        <th class="pb-3 font-medium">Posyandu</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Token</th>
                        <th class="pb-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($devices as $device)
                        <tr>
                            <td class="py-4">
                                <p class="font-semibold text-slate-800">{{ $device->device_name }}</p>
                                <p class="text-xs text-slate-500">{{ $device->device_code }} | {{ $device->device_type }}</p>
                            </td>
                            <td class="py-4">{{ optional($device->posyandu)->name ?? '-' }}</td>
                            <td class="py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase {{ $device->status === 'online' ? 'bg-emerald-100 text-emerald-700' : ($device->status === 'maintenance' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $device->status }}
                                </span>
                            </td>
                            <td class="py-4 font-mono text-xs text-slate-500">{{ $device->api_token }}</td>
                            <td class="py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('devices.edit', $device) }}" class="btn-secondary">Edit</a>
                                    <form action="{{ route('devices.destroy', $device) }}" method="POST" onsubmit="return confirm('Hapus perangkat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @component('partials.empty-state', ['colspan' => 5])Belum ada perangkat terdaftar.@endcomponent
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            @include('partials.pagination', ['paginator' => $devices])
        </div>
    </section>
@endsection
