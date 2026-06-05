<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="label" for="child_id">Anak</label>
        <select id="child_id" name="child_id" class="input" required>
            <option value="">Pilih anak</option>
            @foreach ($children as $childOption)
                <option value="{{ $childOption->id }}" {{ old('child_id', $measurement->child_id ?? '') == $childOption->id ? 'selected' : '' }}>{{ $childOption->child_name }}</option>
            @endforeach
        </select>
        @include('partials.field-error', ['name' => 'child_id'])
    </div>
    <div>
        <label class="label" for="device_id">Perangkat</label>
        <select id="device_id" name="device_id" class="input">
            <option value="">Manual input</option>
            @foreach ($devices as $deviceOption)
                <option value="{{ $deviceOption->id }}" {{ old('device_id', $measurement->device_id ?? '') == $deviceOption->id ? 'selected' : '' }}>{{ $deviceOption->device_name }}</option>
            @endforeach
        </select>
        @include('partials.field-error', ['name' => 'device_id'])
    </div>
    <div>
        <label class="label" for="measured_at">Waktu Pengukuran</label>
        <input id="measured_at" name="measured_at" type="datetime-local" value="{{ old('measured_at', isset($measurement) && $measurement->measured_at ? $measurement->measured_at->format('Y-m-d\\TH:i') : '') }}" class="input" required>
        @include('partials.field-error', ['name' => 'measured_at'])
    </div>
    <div>
        <label class="label" for="source">Sumber Data</label>
        @if (auth()->user()->isAdmin())
            <select id="source" name="source" class="input" required>
                <option value="manual" {{ old('source', $measurement->source ?? 'manual') === 'manual' ? 'selected' : '' }}>Manual</option>
                <option value="iot" {{ old('source', $measurement->source ?? '') === 'iot' ? 'selected' : '' }}>IoT</option>
            </select>
        @else
            <input type="hidden" name="source" value="manual">
            <input class="input bg-slate-100" value="Manual input oleh petugas" disabled>
        @endif
        @include('partials.field-error', ['name' => 'source'])
    </div>
    <div>
        <label class="label" for="weight_kg">Berat (kg)</label>
        <input id="weight_kg" name="weight_kg" type="number" step="0.01" min="0" value="{{ old('weight_kg', $measurement->weight_kg ?? '') }}" class="input" required>
        @include('partials.field-error', ['name' => 'weight_kg'])
    </div>
    <div>
        <label class="label" for="height_cm">Tinggi (cm)</label>
        <input id="height_cm" name="height_cm" type="number" step="0.01" min="0" value="{{ old('height_cm', $measurement->height_cm ?? '') }}" class="input" required>
        @include('partials.field-error', ['name' => 'height_cm'])
    </div>
    <div>
        <label class="label" for="temperature_c">Suhu (C)</label>
        <input id="temperature_c" name="temperature_c" type="number" step="0.01" min="0" value="{{ old('temperature_c', $measurement->temperature_c ?? '') }}" class="input">
        @include('partials.field-error', ['name' => 'temperature_c'])
    </div>
    <div class="md:col-span-2">
        <label class="label" for="notes">Catatan</label>
        <textarea id="notes" name="notes" class="input" rows="4">{{ old('notes', $measurement->notes ?? '') }}</textarea>
        @include('partials.field-error', ['name' => 'notes'])
    </div>
</div>
