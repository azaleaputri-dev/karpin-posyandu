@extends('layouts.app')

@section('content')
    <section class="card p-6">
        @include('partials.page-header', [
            'eyebrow' => 'Perbarui data',
            'title' => 'Edit Pengukuran',
            'description' => 'Sesuaikan data hasil pengukuran dan sumber identitas RFID bila diperlukan.',
        ])

        <form action="{{ route('measurements.update', $measurement) }}" method="POST" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            @include('measurements.form')
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Update</button>
                <a href="{{ route('measurements.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </section>
@endsection
