<div class="grid gap-8 md:grid-cols-2">
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="name">Nama Lengkap</label>
        <input id="name" name="name" value="{{ old('name', $user->name ?? '') }}" class="input !bg-slate-50 focus:!bg-white" placeholder="Input nama lengkap user" required>
        @include('partials.field-error', ['name' => 'name'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="email">Alamat Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" class="input !bg-slate-50 focus:!bg-white" placeholder="nama@email.com" required>
        @include('partials.field-error', ['name' => 'email'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="role">Hak Akses (Role)</label>
        <select id="role" name="role" class="input !bg-slate-50 focus:!bg-white" required>
            <option value="admin" {{ old('role', $user->role ?? 'petugas') === 'admin' ? 'selected' : '' }}>Admin Puskesmas</option>
            <option value="petugas" {{ old('role', $user->role ?? 'petugas') === 'petugas' ? 'selected' : '' }}>Petugas Posyandu</option>
        </select>
        @include('partials.field-error', ['name' => 'role'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="posyandu_id">Lokasi Penempatan</label>
        <select id="posyandu_id" name="posyandu_id" class="input !bg-slate-50 focus:!bg-white">
            <option value="">Pilih unit kerja posyandu</option>
            @foreach ($posyandus as $posyandu)
                <option value="{{ $posyandu->id }}" {{ old('posyandu_id', $user->posyandu_id ?? '') == $posyandu->id ? 'selected' : '' }}>{{ $posyandu->name }}</option>
            @endforeach
        </select>
        <p class="mt-2 text-[10px] font-bold text-slate-400 italic leading-relaxed">* Wajib dipilih untuk role Petugas Posyandu.</p>
        @include('partials.field-error', ['name' => 'posyandu_id'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="password">Kata Sandi (Password)</label>
        <input id="password" name="password" type="password" class="input !bg-slate-50 focus:!bg-white" placeholder="••••••••" {{ isset($user) ? '' : 'required' }}>
        @if (isset($user))
            <p class="mt-2 text-[10px] font-bold text-slate-400 italic">* Kosongkan jika tidak ingin mengubah password.</p>
        @endif
        @include('partials.field-error', ['name' => 'password'])
    </div>
    <div class="space-y-1.5">
        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1" for="password_confirmation">Konfirmasi Kata Sandi</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="input !bg-slate-50 focus:!bg-white" placeholder="••••••••" {{ isset($user) ? '' : 'required' }}>
    </div>
</div>
