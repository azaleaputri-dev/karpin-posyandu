@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Tambah data',
            'title' => 'Tambah Data Anak',
            'description' => 'Masukkan identitas anak dan keluarga.',
        ])

        <form action="{{ route('children.store') }}" method="POST" class="mt-6 space-y-6">
            @csrf
            @include('children.form')
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Simpan</button>
                <a href="{{ route('children.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
