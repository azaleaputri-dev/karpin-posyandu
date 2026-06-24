@extends('layouts.app')

@section('content')
    <section class="card border-none p-8">
        @include('partials.page-header', [
            'eyebrow' => 'Registrasi Kartu Anak',
            'title' => 'Kartu RFID',
            'description' => 'Kelola kartu RFID yang sudah dipasangkan ke data anak.',
            'action' => new \Illuminate\Support\HtmlString('<a href="' . route('rfid-cards.create') . '" class="btn-primary px-8">Daftarkan Kartu Baru</a>'),
        ])

        <div class="mt-8 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="pb-4 pl-4 font-black">Anak</th>
                        <th class="pb-4 font-black">UID Kartu</th>
                        <th class="pb-4 font-black">Posyandu</th>
                        <th class="pb-4 pr-4 text-right font-black">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($children as $child)
                        <tr>
                            <td class="py-5 pl-4">
                                <p class="font-black text-slate-800">{{ $child->child_name }}</p>
                                <p class="text-[10px] font-bold uppercase text-slate-400">{{ $child->nik ?: 'Tanpa NIK' }}</p>
                            </td>
                            <td class="py-5">
                                <code class="bg-slate-100 px-3 py-2 font-mono text-xs font-bold text-slate-700">{{ $child->rfid_uid }}</code>
                            </td>
                            <td class="py-5 font-bold text-slate-600">{{ $child->posyandu->name }}</td>
                            <td class="py-5 pr-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('children.edit', $child) }}" class="btn-secondary px-4 py-2 text-xs">Edit Anak</a>
                                    <form action="{{ route('rfid-cards.destroy', $child) }}" method="POST" onsubmit="return confirm('Lepas kartu RFID dari anak ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-secondary px-4 py-2 text-xs text-rose-600 hover:border-rose-200 hover:bg-rose-50">Lepas Kartu</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @component('partials.empty-state', ['colspan' => 4])Belum ada kartu RFID yang dipasang ke data anak.@endcomponent
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            @include('partials.pagination', ['paginator' => $children])
        </div>
    </section>
@endsection
