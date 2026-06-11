@extends('layouts.app')

@section('content')
    <section class="card border-none p-8">
        @include('partials.page-header', [
            'eyebrow' => 'Akses Sistem',
            'title' => 'Manajemen User',
            'description' => 'Kelola akun admin puskesmas dan petugas posyandu.',
            'action' => new \Illuminate\Support\HtmlString('<a href="' . route('users.create') . '" class="btn-primary px-8">Tambah User</a>'),
        ])

        <form method="GET" action="{{ route('users.index') }}" class="mt-8 grid gap-6 rounded-[2rem] bg-slate-50/50 p-6 ring-1 ring-black/5 md:grid-cols-[1.2fr_0.8fr_1fr_auto]">
            <div class="space-y-1.5">
                <label for="search" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Cari User</label>
                <input id="search" name="search" value="{{ $filters['search'] }}" class="input" placeholder="Nama atau email">
            </div>
            <div class="space-y-1.5">
                <label for="role" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Role</label>
                <select id="role" name="role" class="input">
                    <option value="">Semua role</option>
                    <option value="admin" {{ $filters['role'] === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="petugas" {{ $filters['role'] === 'petugas' ? 'selected' : '' }}>Petugas</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label for="posyandu_id" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Unit Kerja</label>
                <select id="posyandu_id" name="posyandu_id" class="input">
                    <option value="">Semua posyandu</option>
                    <option value="none" {{ $filters['posyandu_id'] === 'none' ? 'selected' : '' }}>Tanpa posyandu</option>
                    @foreach ($posyandus as $posyandu)
                        <option value="{{ $posyandu->id }}" {{ (string) $filters['posyandu_id'] === (string) $posyandu->id ? 'selected' : '' }}>{{ $posyandu->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1 px-6">Filter</button>
                <a href="{{ route('users.index') }}" class="btn-secondary px-4">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
        </form>

        <div class="mt-8 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="pb-4 pl-4 font-black">Identitas</th>
                        <th class="pb-4 font-black">Email</th>
                        <th class="pb-4 font-black">Akses</th>
                        <th class="pb-4 font-black">Penempatan</th>
                        <th class="pb-4 pr-4 text-right font-black">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($users as $user)
                        <tr class="group transition-colors hover:bg-slate-50/50">
                            <td class="py-5 pl-4">
                                <p class="font-black text-slate-800">{{ $user->name }}</p>
                            </td>
                            <td class="py-5 font-bold text-slate-600">{{ $user->email }}</td>
                            <td class="py-5">
                                <span class="rounded-lg px-3 py-1.5 text-[10px] font-black uppercase tracking-tighter {{ $user->role === 'admin' ? 'bg-brand-50 text-brand-600' : 'bg-emerald-50 text-emerald-600' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="py-5 font-bold text-slate-600">{{ optional($user->posyandu)->name ?? 'Puskesmas' }}</td>
                            <td class="py-5 pr-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('users.edit', $user) }}" class="btn-secondary px-4 py-2 text-xs">Edit</a>
                                    <form action="{{ route('users.reset-password', $user) }}" method="POST" onsubmit="return confirm('Reset password user ini?')">
                                        @csrf
                                        <button type="submit" class="btn-secondary px-4 py-2 text-xs text-brand-600">Reset</button>
                                    </form>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-secondary px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 hover:border-rose-200">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @component('partials.empty-state', ['colspan' => 5])Belum ada akun pengguna.@endcomponent
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            @include('partials.pagination', ['paginator' => $users])
        </div>
    </section>
@endsection
