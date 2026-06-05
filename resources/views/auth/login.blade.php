<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-screen bg-slate-950">
    <div class="grid min-h-screen lg:grid-cols-[1.15fr_0.85fr]">
        <section class="hidden bg-[linear-gradient(135deg,_rgba(15,118,110,0.96),_rgba(15,23,42,0.98))] p-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.4em] text-teal-200">Kartu Pintar Posyandu</p>
                <h1 class="mt-6 max-w-xl text-5xl font-black leading-tight">Dashboard admin untuk data balita dan integrasi perangkat IoT.</h1>
                <p class="mt-6 max-w-lg text-base text-cyan-50">Satu panel untuk kelola posyandu, data anak, perangkat pintar, dan riwayat pengukuran.</p>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-3xl border border-white/15 bg-white/10 p-5">
                    <p class="text-sm text-teal-100">Master</p>
                    <p class="mt-2 text-2xl font-bold">Data</p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 p-5">
                    <p class="text-sm text-teal-100">Sensor</p>
                    <p class="mt-2 text-2xl font-bold">IoT</p>
                </div>
                <div class="rounded-3xl border border-white/15 bg-white/10 p-5">
                    <p class="text-sm text-teal-100">Panel</p>
                    <p class="mt-2 text-2xl font-bold">Admin</p>
                </div>
            </div>
        </section>

        <section class="flex items-center justify-center bg-slate-100 p-6">
            <div class="card w-full max-w-md p-8">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-teal-600">Portal Login</p>
                <h2 class="mt-3 text-3xl font-bold text-slate-900">Masuk ke dashboard</h2>
                <p class="mt-2 text-sm text-slate-500">Login sebagai admin puskesmas atau petugas posyandu sesuai peran Anda.</p>

                <form action="{{ route('login.attempt') }}" method="POST" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="email" class="label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="input" required autofocus>
                        @error('email')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="label">Password</label>
                        <input id="password" name="password" type="password" class="input" required>
                    </div>
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                        Ingat saya
                    </label>
                    <button type="submit" class="btn-primary w-full">Masuk</button>
                </form>

                <div class="mt-6 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-800">Akun awal</p>
                    <p>Email: <span class="font-mono">admin@posyandu.test</span></p>
                    <p>Password: <span class="font-mono">admin12345</span></p>
                    <p class="mt-3">Email: <span class="font-mono">petugas@posyandu.test</span></p>
                    <p>Password: <span class="font-mono">petugas12345</span></p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
