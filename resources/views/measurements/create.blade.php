@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Tambah data',
            'title' => 'Tambah Pengukuran',
            'description' => 'Catat hasil timbang dan ukur terbaru, dengan opsi identitas dari kartu RFID.',
        ])

        <form action="{{ route('measurements.store') }}" method="POST" class="mt-6 space-y-6">
            @csrf
            @include('measurements.form')
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Simpan</button>
                <a href="{{ route('measurements.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
