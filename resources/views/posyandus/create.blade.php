@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Tambah data',
            'title' => 'Tambah Posyandu',
            'description' => 'Masukkan identitas posyandu baru.',
        ])

        <form action="{{ route('posyandus.store') }}" method="POST" class="mt-6 space-y-6">
            @csrf
            @include('posyandus.form')
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Simpan</button>
                <a href="{{ route('posyandus.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
