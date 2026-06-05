@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Perbarui data',
            'title' => 'Edit Data Anak',
            'description' => 'Sesuaikan profil anak.',
        ])

        <form action="{{ route('children.update', $child) }}" method="POST" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            @include('children.form')
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Update</button>
                <a href="{{ route('children.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
