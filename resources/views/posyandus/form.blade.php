<div class="grid gap-8 md:grid-cols-2">
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="name">Nama Posyandu</label>
        <input id="name" name="name" value="{{ old('name', $posyandu->name ?? '') }}" class="input !bg-slate-50 focus:!bg-white" placeholder="Input nama posyandu" required>
        @include('partials.field-error', ['name' => 'name'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="code">Kode Posyandu</label>
        <input id="code" name="code" value="{{ old('code', $posyandu->code ?? '') }}" class="input !bg-slate-50 focus:!bg-white" placeholder="Contoh: POS-001" required>
        @include('partials.field-error', ['name' => 'code'])
    </div>
    <div class="md:col-span-2 space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="address">Alamat Lengkap</label>
        <textarea id="address" name="address" class="input !bg-slate-50 focus:!bg-white" rows="3" placeholder="Input alamat lokasi posyandu" required>{{ old('address', $posyandu->address ?? '') }}</textarea>
        @include('partials.field-error', ['name' => 'address'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="village">Desa / Kelurahan</label>
        <input id="village" name="village" value="{{ old('village', $posyandu->village ?? '') }}" class="input !bg-slate-50 focus:!bg-white" placeholder="Input nama desa/kelurahan" required>
        @include('partials.field-error', ['name' => 'village'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="contact_phone">Nomor Kontak (WhatsApp)</label>
        <input id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $posyandu->contact_phone ?? '') }}" class="input !bg-slate-50 focus:!bg-white" placeholder="Contoh: 08123456789">
        @include('partials.field-error', ['name' => 'contact_phone'])
    </div>
    <div class="md:col-span-2 space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="notes">Catatan Tambahan</label>
        <textarea id="notes" name="notes" class="input !bg-slate-50 focus:!bg-white" rows="3" placeholder="Input informasi tambahan jika diperlukan">{{ old('notes', $posyandu->notes ?? '') }}</textarea>
        @include('partials.field-error', ['name' => 'notes'])
    </div>
</div>
