@extends('layouts.app')

@section('content')
    <section class="card border-none p-8">
        @include('partials.page-header', [
            'eyebrow' => 'Master Data',
            'title' => 'Data Posyandu',
            'description' => 'Kelola lokasi dan identitas posyandu di wilayah kerja.',
            'action' => new \Illuminate\Support\HtmlString('<a href="' . route('posyandus.create') . '" class="btn-primary px-8">Tambah Posyandu</a>'),
        ])

        <div class="mt-8 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="pb-4 pl-4 font-black">Nama Posyandu</th>
                        <th class="pb-4 font-black">Kode Unit</th>
                        <th class="pb-4 font-black">Wilayah Desa</th>
                        <th class="pb-4 font-black">Kontak</th>
                        <th class="pb-4 pr-4 text-right font-black">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($posyandus as $posyandu)
                        <tr class="group transition-colors hover:bg-slate-50/50">
                            <td class="py-5 pl-4 font-black text-slate-800">{{ $posyandu->name }}</td>
                            <td class="py-5 font-bold text-slate-600">{{ $posyandu->code }}</td>
                            <td class="py-5 font-bold text-slate-600">{{ $posyandu->village }}</td>
                            <td class="py-5 font-bold text-slate-600">{{ $posyandu->contact_phone ?? '-' }}</td>
                            <td class="py-5 pr-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('posyandus.edit', $posyandu) }}" class="btn-secondary px-4 py-2 text-xs">Edit</a>
                                    <form action="{{ route('posyandus.destroy', $posyandu) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-secondary px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 hover:border-rose-200">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @component('partials.empty-state', ['colspan' => 5])Belum ada data posyandu.@endcomponent
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            @include('partials.pagination', ['paginator' => $posyandus])
        </div>
    </section>
@endsection
