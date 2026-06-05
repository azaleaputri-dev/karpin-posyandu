@extends('layouts.app')

@section('content')
    <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="card p-6">
            @include('partials.page-header', [
                'eyebrow' => 'Akun saya',
                'title' => 'Profil Saya',
                'description' => 'Perbarui identitas akun dan password Anda sendiri.',
            ])

            <form action="{{ route('profile.update') }}" method="POST" class="mt-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="label" for="name">Nama Lengkap</label>
                        <input id="name" name="name" value="{{ old('name', $user->name) }}" class="input" required>
                        @include('partials.field-error', ['name' => 'name'])
                    </div>
                    <div>
                        <label class="label" for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="input" required>
                        @include('partials.field-error', ['name' => 'email'])
                    </div>
                    <div>
                        <label class="label" for="current_password">Password Saat Ini</label>
                        <input id="current_password" name="current_password" type="password" class="input">
                        <p class="mt-2 text-xs text-slate-500">Isi hanya jika ingin mengganti password.</p>
                        @include('partials.field-error', ['name' => 'current_password'])
                    </div>
                    <div>
                        <label class="label" for="password">Password Baru</label>
                        <input id="password" name="password" type="password" class="input">
                        @include('partials.field-error', ['name' => 'password'])
                    </div>
                    <div class="md:col-span-2">
                        <label class="label" for="password_confirmation">Konfirmasi Password Baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="input">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <p class="text-sm text-slate-500">Ringkasan akun</p>
                <h3 class="text-xl font-bold text-slate-900">Informasi akses</h3>
                <div class="mt-5 space-y-4 text-sm">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-slate-500">Role</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ $user->isAdmin() ? 'Admin Puskesmas' : 'Petugas Posyandu' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-slate-500">Posyandu terkait</p>
                        <p class="mt-1 font-semibold text-slate-900">{{ optional($user->posyandu)->name ?? 'Akses lintas posyandu' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-slate-500">Panduan</p>
                        <p class="mt-1 text-slate-700">Gunakan password minimal 8 karakter. Jika hanya ingin mengganti nama atau email, kolom password boleh dikosongkan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
