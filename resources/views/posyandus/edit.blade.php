@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Perbarui data',
            'title' => 'Edit Posyandu',
            'description' => 'Sesuaikan informasi posyandu.',
        ])

        <form action="{{ route('posyandus.update', $posyandu) }}" method="POST" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            @include('posyandus.form')
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Update</button>
                <a href="{{ route('posyandus.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
