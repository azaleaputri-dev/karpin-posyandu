<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="label" for="posyandu_id">Posyandu</label>
        <select id="posyandu_id" name="posyandu_id" class="input">
            <option value="">Belum ditentukan</option>
            @foreach ($posyandus as $posyandu)
                <option value="{{ $posyandu->id }}" {{ old('posyandu_id', $device->posyandu_id ?? '') == $posyandu->id ? 'selected' : '' }}>{{ $posyandu->name }}</option>
            @endforeach
        </select>
        @include('partials.field-error', ['name' => 'posyandu_id'])
    </div>
    <div>
        <label class="label" for="device_code">Kode Perangkat</label>
        <input id="device_code" name="device_code" value="{{ old('device_code', $device->device_code ?? '') }}" class="input" required>
        @include('partials.field-error', ['name' => 'device_code'])
    </div>
    <div>
        <label class="label" for="device_name">Nama Perangkat</label>
        <input id="device_name" name="device_name" value="{{ old('device_name', $device->device_name ?? '') }}" class="input" required>
        @include('partials.field-error', ['name' => 'device_name'])
    </div>
    <div>
        <label class="label" for="device_type">Jenis Perangkat</label>
        <input id="device_type" name="device_type" value="{{ old('device_type', $device->device_type ?? 'timbangan-iot') }}" class="input" required>
        @include('partials.field-error', ['name' => 'device_type'])
    </div>
    <div>
        <label class="label" for="location">Lokasi</label>
        <input id="location" name="location" value="{{ old('location', $device->location ?? '') }}" class="input">
        @include('partials.field-error', ['name' => 'location'])
    </div>
    <div>
        <label class="label" for="status">Status</label>
        <select id="status" name="status" class="input" required>
            @foreach (['online' => 'Online', 'offline' => 'Offline', 'maintenance' => 'Maintenance'] as $value => $label)
                <option value="{{ $value }}" {{ old('status', $device->status ?? 'offline') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @include('partials.field-error', ['name' => 'status'])
    </div>
    <div class="md:col-span-2">
        <label class="label" for="last_seen_at">Terakhir terlihat</label>
        <input id="last_seen_at" name="last_seen_at" type="datetime-local" value="{{ old('last_seen_at', isset($device) && $device->last_seen_at ? $device->last_seen_at->format('Y-m-d\\TH:i') : '') }}" class="input">
        @include('partials.field-error', ['name' => 'last_seen_at'])
    </div>
</div>
