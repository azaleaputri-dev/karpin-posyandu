<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="label" for="name">Nama Posyandu</label>
        <input id="name" name="name" value="{{ old('name', $posyandu->name ?? '') }}" class="input" required>
        @include('partials.field-error', ['name' => 'name'])
    </div>
    <div>
        <label class="label" for="code">Kode Posyandu</label>
        <input id="code" name="code" value="{{ old('code', $posyandu->code ?? '') }}" class="input" required>
        @include('partials.field-error', ['name' => 'code'])
    </div>
    <div class="md:col-span-2">
        <label class="label" for="address">Alamat</label>
        <textarea id="address" name="address" class="input" rows="4" required>{{ old('address', $posyandu->address ?? '') }}</textarea>
        @include('partials.field-error', ['name' => 'address'])
    </div>
    <div>
        <label class="label" for="village">Desa / Kelurahan</label>
        <input id="village" name="village" value="{{ old('village', $posyandu->village ?? '') }}" class="input" required>
        @include('partials.field-error', ['name' => 'village'])
    </div>
    <div>
        <label class="label" for="contact_phone">No. Kontak</label>
        <input id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $posyandu->contact_phone ?? '') }}" class="input">
        @include('partials.field-error', ['name' => 'contact_phone'])
    </div>
    <div class="md:col-span-2">
        <label class="label" for="notes">Catatan</label>
        <textarea id="notes" name="notes" class="input" rows="4">{{ old('notes', $posyandu->notes ?? '') }}</textarea>
        @include('partials.field-error', ['name' => 'notes'])
    </div>
</div>
