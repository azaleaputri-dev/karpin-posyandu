@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Tambah user',
            'title' => 'Tambah Akun Pengguna',
            'description' => 'Buat akun admin puskesmas atau petugas posyandu baru.',
        ])

        <form action="{{ route('users.store') }}" method="POST" class="mt-6 space-y-6">
            @csrf
            @include('users.form')
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Simpan</button>
                <a href="{{ route('users.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
