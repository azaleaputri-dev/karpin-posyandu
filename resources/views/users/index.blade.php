@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Akses sistem',
            'title' => 'Manajemen User',
            'description' => 'Kelola akun admin puskesmas dan petugas posyandu.',
            'action' => new \Illuminate\Support\HtmlString('<a href="' . route('users.create') . '" class="btn-primary">Tambah User</a>'),
        ])

        <form method="GET" action="{{ route('users.index') }}" class="mt-6 grid gap-4 rounded-3xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-[1.2fr_0.8fr_1fr_auto]">
            <div>
                <label for="search" class="label">Cari user</label>
                <input id="search" name="search" value="{{ $filters['search'] }}" class="input" placeholder="Nama atau email">
            </div>
            <div>
                <label for="role" class="label">Role</label>
                <select id="role" name="role" class="input">
                    <option value="">Semua role</option>
                    <option value="admin" {{ $filters['role'] === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="petugas" {{ $filters['role'] === 'petugas' ? 'selected' : '' }}>Petugas</option>
                </select>
            </div>
            <div>
                <label for="posyandu_id" class="label">Posyandu</label>
                <select id="posyandu_id" name="posyandu_id" class="input">
                    <option value="">Semua posyandu</option>
                    <option value="none" {{ $filters['posyandu_id'] === 'none' ? 'selected' : '' }}>Tanpa posyandu</option>
                    @foreach ($posyandus as $posyandu)
                        <option value="{{ $posyandu->id }}" {{ (string) $filters['posyandu_id'] === (string) $posyandu->id ? 'selected' : '' }}>{{ $posyandu->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="btn-primary">Filter</button>
                <a href="{{ route('users.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="text-slate-400">
                    <tr>
                        <th class="pb-3 font-medium">Nama</th>
                        <th class="pb-3 font-medium">Email</th>
                        <th class="pb-3 font-medium">Role</th>
                        <th class="pb-3 font-medium">Posyandu</th>
                        <th class="pb-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr>
                            <td class="py-4 font-semibold text-slate-800">{{ $user->name }}</td>
                            <td class="py-4">{{ $user->email }}</td>
                            <td class="py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase {{ $user->role === 'admin' ? 'bg-cyan-100 text-cyan-700' : 'bg-emerald-100 text-emerald-700' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="py-4">{{ optional($user->posyandu)->name ?? 'Puskesmas' }}</td>
                            <td class="py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('users.edit', $user) }}" class="btn-secondary">Edit</a>
                                    <form action="{{ route('users.reset-password', $user) }}" method="POST" onsubmit="return confirm('Reset password user ini ke password default?')">
                                        @csrf
                                        <button type="submit" class="btn-secondary">Reset Password</button>
                                    </form>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Hapus</button>
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

        <div class="mt-6">
            @include('partials.pagination', ['paginator' => $users])
        </div>
    </section>
@endsection
