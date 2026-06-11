<div class="grid gap-8 md:grid-cols-2">
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="child_id">Identitas Anak</label>
        <select id="child_id" name="child_id" class="input !bg-slate-50 focus:!bg-white" required>
            <option value="">Pilih data anak</option>
            @foreach ($children as $childOption)
                <option value="{{ $childOption->id }}" {{ old('child_id', $measurement->child_id ?? '') == $childOption->id ? 'selected' : '' }}>{{ $childOption->child_name }}</option>
            @endforeach
        </select>
        @include('partials.field-error', ['name' => 'child_id'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="device_id">Kartu RFID / Sumber Scan</label>
        <select id="device_id" name="device_id" class="input !bg-slate-50 focus:!bg-white">
            <option value="">Manual input (Petugas)</option>
            @foreach ($devices as $deviceOption)
                <option value="{{ $deviceOption->id }}" {{ old('device_id', $measurement->device_id ?? '') == $deviceOption->id ? 'selected' : '' }}>{{ $deviceOption->device_name }}</option>
            @endforeach
        </select>
        @include('partials.field-error', ['name' => 'device_id'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="measured_at">Waktu Pengukuran</label>
        <input id="measured_at" name="measured_at" type="datetime-local" value="{{ old('measured_at', isset($measurement) && $measurement->measured_at ? $measurement->measured_at->format('Y-m-d\\TH:i') : '') }}" class="input !bg-slate-50 focus:!bg-white" required>
        @include('partials.field-error', ['name' => 'measured_at'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="source">Kategori Sumber</label>
        @if (auth()->user()->isAdmin())
            <select id="source" name="source" class="input !bg-slate-50 focus:!bg-white" required>
                <option value="manual" {{ old('source', $measurement->source ?? 'manual') === 'manual' ? 'selected' : '' }}>Entri Manual</option>
                <option value="iot" {{ old('source', $measurement->source ?? '') === 'iot' ? 'selected' : '' }}>Scan RFID / Sinkronisasi</option>
            </select>
        @else
            <input type="hidden" name="source" value="manual">
            <div class="input bg-slate-100/50 cursor-not-allowed font-bold text-slate-500 ring-1 ring-black/5">Entri Manual oleh Petugas</div>
        @endif
        @include('partials.field-error', ['name' => 'source'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="weight_kg">Berat Badan (kg)</label>
        <input id="weight_kg" name="weight_kg" type="number" step="0.01" min="0" value="{{ old('weight_kg', $measurement->weight_kg ?? '') }}" class="input !bg-slate-50 focus:!bg-white" placeholder="Contoh: 12.5" required>
        @include('partials.field-error', ['name' => 'weight_kg'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="height_cm">Tinggi Badan (cm)</label>
        <input id="height_cm" name="height_cm" type="number" step="0.01" min="0" value="{{ old('height_cm', $measurement->height_cm ?? '') }}" class="input !bg-slate-50 focus:!bg-white" placeholder="Contoh: 85.0" required>
        @include('partials.field-error', ['name' => 'height_cm'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="temperature_c">Suhu Tubuh (°C)</label>
        <input id="temperature_c" name="temperature_c" type="number" step="0.01" min="0" value="{{ old('temperature_c', $measurement->temperature_c ?? '') }}" class="input !bg-slate-50 focus:!bg-white" placeholder="Contoh: 36.5">
        @include('partials.field-error', ['name' => 'temperature_c'])
    </div>
    <div class="md:col-span-2 space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="notes">Catatan Observasi</label>
        <textarea id="notes" name="notes" class="input !bg-slate-50 focus:!bg-white" rows="3" placeholder="Input catatan jika ada kondisi khusus">{{ old('notes', $measurement->notes ?? '') }}</textarea>
        @include('partials.field-error', ['name' => 'notes'])
    </div>
</div>
