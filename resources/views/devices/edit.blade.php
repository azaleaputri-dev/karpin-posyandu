@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Perbarui kartu',
            'title' => 'Edit Kartu RFID',
            'description' => 'Sesuaikan lokasi, status, dan identitas kartu RFID.',
        ])

        <form action="{{ route('devices.update', $device) }}" method="POST" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            @include('devices.form')
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Update</button>
                <a href="{{ route('devices.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
