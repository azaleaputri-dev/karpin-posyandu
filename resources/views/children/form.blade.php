<div class="grid gap-8 md:grid-cols-2">
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="posyandu_id">Lokasi Posyandu</label>
        @if (auth()->user()->isAdmin())
            <select id="posyandu_id" name="posyandu_id" class="input !bg-slate-50 focus:!bg-white transition-all" required>
                <option value="">Pilih posyandu</option>
                @foreach ($posyandus as $posyandu)
                    <option value="{{ $posyandu->id }}" {{ old('posyandu_id', $child->posyandu_id ?? '') == $posyandu->id ? 'selected' : '' }}>{{ $posyandu->name }}</option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="posyandu_id" value="{{ old('posyandu_id', $child->posyandu_id ?? optional($posyandus->first())->id) }}">
            <div class="input bg-slate-100/50 cursor-not-allowed font-bold text-slate-500 ring-1 ring-black/5">
                {{ optional($posyandus->first())->name }}
            </div>
        @endif
        @include('partials.field-error', ['name' => 'posyandu_id'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="nik">Nomor Induk Kependudukan (NIK)</label>
        <input id="nik" name="nik" value="{{ old('nik', $child->nik ?? '') }}" class="input !bg-slate-50 focus:!bg-white transition-all" placeholder="16 digit nomor NIK">
        @include('partials.field-error', ['name' => 'nik'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="child_name">Nama Lengkap Anak</label>
        <input id="child_name" name="child_name" value="{{ old('child_name', $child->child_name ?? '') }}" class="input !bg-slate-50 focus:!bg-white transition-all" placeholder="Input nama lengkap sesuai akta" required>
        @include('partials.field-error', ['name' => 'child_name'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="gender">Jenis Kelamin</label>
        <select id="gender" name="gender" class="input !bg-slate-50 focus:!bg-white transition-all" required>
            <option value="">Pilih jenis kelamin</option>
            <option value="L" {{ old('gender', $child->gender ?? '') === 'L' ? 'selected' : '' }}>Laki-laki</option>
            <option value="P" {{ old('gender', $child->gender ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
        </select>
        @include('partials.field-error', ['name' => 'gender'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="birth_date">Tanggal Lahir</label>
        <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', isset($child) && $child->birth_date ? $child->birth_date->format('Y-m-d') : '') }}" class="input !bg-slate-50 focus:!bg-white transition-all" required>
        @include('partials.field-error', ['name' => 'birth_date'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="blood_type">Golongan Darah</label>
        <input id="blood_type" name="blood_type" value="{{ old('blood_type', $child->blood_type ?? '') }}" class="input !bg-slate-50 focus:!bg-white transition-all" placeholder="Contoh: A, B, AB, O">
        @include('partials.field-error', ['name' => 'blood_type'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="mother_name">Nama Ibu Kandung</label>
        <input id="mother_name" name="mother_name" value="{{ old('mother_name', $child->mother_name ?? '') }}" class="input !bg-slate-50 focus:!bg-white transition-all" placeholder="Input nama ibu kandung" required>
        @include('partials.field-error', ['name' => 'mother_name'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="father_name">Nama Ayah</label>
        <input id="father_name" name="father_name" value="{{ old('father_name', $child->father_name ?? '') }}" class="input !bg-slate-50 focus:!bg-white transition-all" placeholder="Input nama ayah">
        @include('partials.field-error', ['name' => 'father_name'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="guardian_phone">No. HP Wali/Orang Tua</label>
        <input id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone', $child->guardian_phone ?? '') }}" class="input !bg-slate-50 focus:!bg-white transition-all" placeholder="Contoh: 08123456789">
        @include('partials.field-error', ['name' => 'guardian_phone'])
    </div>
    <div class="md:col-span-2 space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="address">Alamat Lengkap</label>
        <textarea id="address" name="address" class="input !bg-slate-50 focus:!bg-white transition-all" rows="3" placeholder="Input alamat tempat tinggal saat ini">{{ old('address', $child->address ?? '') }}</textarea>
        @include('partials.field-error', ['name' => 'address'])
    </div>
    <div class="md:col-span-2 space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="notes">Catatan Tambahan</label>
        <textarea id="notes" name="notes" class="input !bg-slate-50 focus:!bg-white transition-all" rows="3" placeholder="Input catatan jika ada (riwayat alergi, dsb)">{{ old('notes', $child->notes ?? '') }}</textarea>
        @include('partials.field-error', ['name' => 'notes'])
    </div>
</div>
