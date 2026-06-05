<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="label" for="posyandu_id">Posyandu</label>
        @if (auth()->user()->isAdmin())
            <select id="posyandu_id" name="posyandu_id" class="input" required>
                <option value="">Pilih posyandu</option>
                @foreach ($posyandus as $posyandu)
                    <option value="{{ $posyandu->id }}" {{ old('posyandu_id', $child->posyandu_id ?? '') == $posyandu->id ? 'selected' : '' }}>{{ $posyandu->name }}</option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="posyandu_id" value="{{ old('posyandu_id', $child->posyandu_id ?? optional($posyandus->first())->id) }}">
            <input class="input bg-slate-100" value="{{ optional($posyandus->first())->name }}" disabled>
        @endif
        @include('partials.field-error', ['name' => 'posyandu_id'])
    </div>
    <div>
        <label class="label" for="nik">NIK</label>
        <input id="nik" name="nik" value="{{ old('nik', $child->nik ?? '') }}" class="input">
        @include('partials.field-error', ['name' => 'nik'])
    </div>
    <div>
        <label class="label" for="child_name">Nama Anak</label>
        <input id="child_name" name="child_name" value="{{ old('child_name', $child->child_name ?? '') }}" class="input" required>
        @include('partials.field-error', ['name' => 'child_name'])
    </div>
    <div>
        <label class="label" for="gender">Jenis Kelamin</label>
        <select id="gender" name="gender" class="input" required>
            <option value="">Pilih jenis kelamin</option>
            <option value="L" {{ old('gender', $child->gender ?? '') === 'L' ? 'selected' : '' }}>Laki-laki</option>
            <option value="P" {{ old('gender', $child->gender ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
        </select>
        @include('partials.field-error', ['name' => 'gender'])
    </div>
    <div>
        <label class="label" for="birth_date">Tanggal Lahir</label>
        <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', isset($child) && $child->birth_date ? $child->birth_date->format('Y-m-d') : '') }}" class="input" required>
        @include('partials.field-error', ['name' => 'birth_date'])
    </div>
    <div>
        <label class="label" for="blood_type">Golongan Darah</label>
        <input id="blood_type" name="blood_type" value="{{ old('blood_type', $child->blood_type ?? '') }}" class="input">
        @include('partials.field-error', ['name' => 'blood_type'])
    </div>
    <div>
        <label class="label" for="mother_name">Nama Ibu</label>
        <input id="mother_name" name="mother_name" value="{{ old('mother_name', $child->mother_name ?? '') }}" class="input" required>
        @include('partials.field-error', ['name' => 'mother_name'])
    </div>
    <div>
        <label class="label" for="father_name">Nama Ayah</label>
        <input id="father_name" name="father_name" value="{{ old('father_name', $child->father_name ?? '') }}" class="input">
        @include('partials.field-error', ['name' => 'father_name'])
    </div>
    <div>
        <label class="label" for="guardian_phone">No. HP Wali</label>
        <input id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone', $child->guardian_phone ?? '') }}" class="input">
        @include('partials.field-error', ['name' => 'guardian_phone'])
    </div>
    <div class="md:col-span-2">
        <label class="label" for="address">Alamat</label>
        <textarea id="address" name="address" class="input" rows="4">{{ old('address', $child->address ?? '') }}</textarea>
        @include('partials.field-error', ['name' => 'address'])
    </div>
    <div class="md:col-span-2">
        <label class="label" for="notes">Catatan</label>
        <textarea id="notes" name="notes" class="input" rows="4">{{ old('notes', $child->notes ?? '') }}</textarea>
        @include('partials.field-error', ['name' => 'notes'])
    </div>
</div>
