@extends('layouts.app')

@section('content')
    <section class="card border-none p-8">
        @include('partials.page-header', [
            'eyebrow' => 'Master Data',
            'title' => 'Data Anak',
            'description' => 'Kelola profil anak untuk Kartu Pintar Posyandu.',
            'action' => new \Illuminate\Support\HtmlString('<a href="' . route('children.create') . '" class="btn-primary px-8">Tambah Anak</a>'),
        ])

        <form method="GET" action="{{ route('children.index') }}" class="mt-8 grid gap-6 rounded-[2rem] bg-slate-50/50 p-6 ring-1 ring-black/5 md:grid-cols-[1.2fr_0.8fr_1fr_auto]">
            <div class="space-y-1.5">
                <label for="search" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Cari Anak</label>
                <input id="search" name="search" value="{{ $filters['search'] }}" class="input" placeholder="Nama, NIK, atau Ibu">
            </div>
            <div class="space-y-1.5">
                <label for="gender" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Gender</label>
                <select id="gender" name="gender" class="input">
                    <option value="">Semua</option>
                    <option value="L" {{ $filters['gender'] === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ $filters['gender'] === 'P' ? 'selected' : '' }}>Perempuan</option>
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
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1 px-6">Filter</button>
                <a href="{{ route('children.index') }}" class="btn-secondary px-4" title="Reset">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
        </form>

        <div class="mt-8 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="pb-4 pl-4 font-black">Nama Lengkap</th>
                        <th class="pb-4 font-black">Posyandu</th>
                        <th class="pb-4 font-black">Lahir</th>
                        <th class="pb-4 font-black">Ibu</th>
                        <th class="pb-4 pr-4 text-right font-black">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($children as $child)
                        <tr class="group transition-colors hover:bg-slate-50/50">
                            <td class="py-5 pl-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $child->gender === 'L' ? 'bg-brand-50 text-brand-600' : 'bg-rose-50 text-rose-600' }} font-black">
                                        {{ substr($child->child_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-800">{{ $child->child_name }}</p>
                                        <p class="text-[10px] font-bold uppercase tracking-tight text-slate-400">{{ $child->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 font-bold text-slate-600">{{ $child->posyandu->name }}</td>
                            <td class="py-5 font-bold text-slate-600">{{ $child->birth_date->format('d/m/Y') }}</td>
                            <td class="py-5 font-bold text-slate-600">{{ $child->mother_name }}</td>
                            <td class="py-5 pr-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('children.show', $child) }}" class="btn-secondary px-4 py-2 text-xs" title="Lihat Grafik">Grafik</a>
                                    <a href="{{ route('children.edit', $child) }}" class="btn-secondary px-4 py-2 text-xs" title="Edit Data">Edit</a>
                                    <form action="{{ route('children.destroy', $child) }}" method="POST" onsubmit="return confirm('Hapus data ini beserta riwayat pengukurannya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-secondary px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 hover:border-rose-200" title="Hapus Data">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @component('partials.empty-state', ['colspan' => 5])Belum ada data anak.@endcomponent
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            @include('partials.pagination', ['paginator' => $children])
        </div>
    </section>
@endsection
