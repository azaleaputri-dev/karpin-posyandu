@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Master data',
            'title' => 'Data Posyandu',
            'description' => 'Kelola lokasi dan identitas posyandu.',
            'action' => new \Illuminate\Support\HtmlString('<a href="' . route('posyandus.create') . '" class="btn-primary">Tambah Posyandu</a>'),
        ])

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="text-slate-400">
                    <tr>
                        <th class="pb-3 font-medium">Nama</th>
                        <th class="pb-3 font-medium">Kode</th>
                        <th class="pb-3 font-medium">Desa</th>
                        <th class="pb-3 font-medium">Kontak</th>
                        <th class="pb-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($posyandus as $posyandu)
                        <tr>
                            <td class="py-4 font-semibold text-slate-800">{{ $posyandu->name }}</td>
                            <td class="py-4">{{ $posyandu->code }}</td>
                            <td class="py-4">{{ $posyandu->village }}</td>
                            <td class="py-4">{{ $posyandu->contact_phone ?? '-' }}</td>
                            <td class="py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('posyandus.edit', $posyandu) }}" class="btn-secondary">Edit</a>
                                    <form action="{{ route('posyandus.destroy', $posyandu) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Hapus</button>
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

        <div class="mt-6">
            @include('partials.pagination', ['paginator' => $posyandus])
        </div>
    </section>
@endsection
