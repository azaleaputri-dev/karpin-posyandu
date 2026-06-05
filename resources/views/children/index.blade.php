@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Master data',
            'title' => 'Data Anak',
            'description' => 'Kelola profil anak untuk Kartu Pintar Posyandu.',
            'action' => auth()->user()->isAdmin()
                ? null
                : new \Illuminate\Support\HtmlString('<a href="' . route('children.create') . '" class="btn-primary">Tambah Anak</a>'),
        ])

        <form method="GET" action="{{ route('children.index') }}" class="mt-6 grid gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[1.2fr_0.8fr_1fr_auto]">
            <div>
                <label for="search" class="label">Cari anak</label>
                <input id="search" name="search" value="{{ $filters['search'] }}" class="input" placeholder="Nama anak, NIK, atau nama ibu">
            </div>
            <div>
                <label for="gender" class="label">Jenis Kelamin</label>
                <select id="gender" name="gender" class="input">
                    <option value="">Semua</option>
                    <option value="L" {{ $filters['gender'] === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ $filters['gender'] === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div>
                <label for="posyandu_id" class="label">Posyandu</label>
                @if (auth()->user()->isAdmin())
                    <select id="posyandu_id" name="posyandu_id" class="input">
                        <option value="">Semua posyandu</option>
                        @foreach ($posyandus as $posyandu)
                            <option value="{{ $posyandu->id }}" {{ (string) $filters['posyandu_id'] === (string) $posyandu->id ? 'selected' : '' }}>{{ $posyandu->name }}</option>
                        @endforeach
                    </select>
                @else
                    <input class="input bg-slate-100" value="{{ optional($posyandus->first())->name }}" disabled>
                @endif
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('children.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="text-slate-400">
                    <tr>
                        <th class="pb-3 font-medium">Nama</th>
                        <th class="pb-3 font-medium">Posyandu</th>
                        <th class="pb-3 font-medium">Tanggal Lahir</th>
                        <th class="pb-3 font-medium">Ibu</th>
                        <th class="pb-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($children as $child)
                        <tr>
                            <td class="py-4">
                                <p class="font-semibold text-slate-800">{{ $child->child_name }}</p>
                                <p class="text-xs text-slate-500">{{ $child->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                            </td>
                            <td class="py-4">{{ $child->posyandu->name }}</td>
                            <td class="py-4">{{ $child->birth_date->format('d M Y') }}</td>
                            <td class="py-4">{{ $child->mother_name }}</td>
                            <td class="py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('children.show', $child) }}" class="btn-secondary">Grafik</a>
                                    @unless (auth()->user()->isAdmin())
                                        <a href="{{ route('children.edit', $child) }}" class="btn-secondary">Edit</a>
                                        <form action="{{ route('children.destroy', $child) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger">Hapus</button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        @component('partials.empty-state', ['colspan' => 5])Belum ada data anak.@endcomponent
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            @include('partials.pagination', ['paginator' => $children])
        </div>
    </section>
@endsection
