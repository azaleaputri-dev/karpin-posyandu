@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Perbarui user',
            'title' => 'Edit Akun Pengguna',
            'description' => 'Sesuaikan role, posyandu, dan kredensial pengguna.',
        ])

        <form action="{{ route('users.update', $user) }}" method="POST" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            @include('users.form')
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Update</button>
                <a href="{{ route('users.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
