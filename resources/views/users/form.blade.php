<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label class="label" for="name">Nama Lengkap</label>
        <input id="name" name="name" value="{{ old('name', $user->name ?? '') }}" class="input" required>
        @include('partials.field-error', ['name' => 'name'])
    </div>
    <div>
        <label class="label" for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" class="input" required>
        @include('partials.field-error', ['name' => 'email'])
    </div>
    <div>
        <label class="label" for="role">Role</label>
        <select id="role" name="role" class="input" required>
            <option value="admin" {{ old('role', $user->role ?? 'petugas') === 'admin' ? 'selected' : '' }}>Admin Puskesmas</option>
            <option value="petugas" {{ old('role', $user->role ?? 'petugas') === 'petugas' ? 'selected' : '' }}>Petugas Posyandu</option>
        </select>
        @include('partials.field-error', ['name' => 'role'])
    </div>
    <div>
        <label class="label" for="posyandu_id">Posyandu</label>
        <select id="posyandu_id" name="posyandu_id" class="input">
            <option value="">Tidak diikat ke posyandu</option>
            @foreach ($posyandus as $posyandu)
                <option value="{{ $posyandu->id }}" {{ old('posyandu_id', $user->posyandu_id ?? '') == $posyandu->id ? 'selected' : '' }}>{{ $posyandu->name }}</option>
            @endforeach
        </select>
        <p class="mt-2 text-xs text-slate-500">Wajib dipilih untuk role petugas, akan diabaikan untuk admin.</p>
        @include('partials.field-error', ['name' => 'posyandu_id'])
    </div>
    <div>
        <label class="label" for="password">Password</label>
        <input id="password" name="password" type="password" class="input" {{ isset($user) ? '' : 'required' }}>
        @if (isset($user))
            <p class="mt-2 text-xs text-slate-500">Kosongkan jika password tidak diubah.</p>
        @endif
        @include('partials.field-error', ['name' => 'password'])
    </div>
    <div>
        <label class="label" for="password_confirmation">Konfirmasi Password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="input" {{ isset($user) ? '' : 'required' }}>
    </div>
</div>
