<div class="grid gap-8 md:grid-cols-2">
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="posyandu_id">Lokasi Penggunaan</label>
        <select id="posyandu_id" name="posyandu_id" class="input !bg-slate-50 focus:!bg-white">
            <option value="">Pilih unit kerja posyandu</option>
            @foreach ($posyandus as $posyandu)
                <option value="{{ $posyandu->id }}" {{ old('posyandu_id', $device->posyandu_id ?? '') == $posyandu->id ? 'selected' : '' }}>{{ $posyandu->name }}</option>
            @endforeach
        </select>
        @include('partials.field-error', ['name' => 'posyandu_id'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="device_code">Kode Kartu RFID</label>
        <input id="device_code" name="device_code" value="{{ old('device_code', $device->device_code ?? '') }}" class="input !bg-slate-50 focus:!bg-white" placeholder="Contoh: RFID-KIA-01" required>
        @include('partials.field-error', ['name' => 'device_code'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="device_name">Nama Kartu / Tag</label>
        <input id="device_name" name="device_name" value="{{ old('device_name', $device->device_name ?? '') }}" class="input !bg-slate-50 focus:!bg-white" placeholder="Input nama alias kartu RFID" required>
        @include('partials.field-error', ['name' => 'device_name'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="device_type">Tipe / Jenis Kartu</label>
        <input id="device_type" name="device_type" value="{{ old('device_type', $device->device_type ?? 'rfid-card') }}" class="input !bg-slate-50 focus:!bg-white" placeholder="Contoh: rfid-card, kia-digital-tag" required>
        @include('partials.field-error', ['name' => 'device_type'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="location">Lokasi Penyimpanan / Pemakaian</label>
        <input id="location" name="location" value="{{ old('location', $device->location ?? '') }}" class="input !bg-slate-50 focus:!bg-white" placeholder="Contoh: Meja registrasi, ruang posyandu">
        @include('partials.field-error', ['name' => 'location'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="status">Status Operasional</label>
        <select id="status" name="status" class="input !bg-slate-50 focus:!bg-white" required>
            @foreach (['online' => 'Online', 'offline' => 'Offline', 'maintenance' => 'Maintenance'] as $value => $label)
                <option value="{{ $value }}" {{ old('status', $device->status ?? 'offline') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @include('partials.field-error', ['name' => 'status'])
    </div>
    <div class="md:col-span-2 space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="last_seen_at">Terakhir Tersinkron</label>
        <input id="last_seen_at" name="last_seen_at" type="datetime-local" value="{{ old('last_seen_at', isset($device) && $device->last_seen_at ? $device->last_seen_at->format('Y-m-d\\TH:i') : '') }}" class="input !bg-slate-50 focus:!bg-white">
        @include('partials.field-error', ['name' => 'last_seen_at'])
    </div>
</div>
