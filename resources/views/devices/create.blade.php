@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Tambah kartu',
            'title' => 'Tambah Kartu RFID',
            'description' => 'Registrasikan kartu RFID baru dan siapkan token sinkronisasinya.',
        ])

        <form action="{{ route('devices.store') }}" method="POST" class="mt-6 space-y-6">
            @csrf
            @include('devices.form')
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Simpan</button>
                <a href="{{ route('devices.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
