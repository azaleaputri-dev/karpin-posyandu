<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-6">
    
    <div class="w-full max-w-5xl grid lg:grid-cols-2 bg-brand-50 rounded-[3rem] shadow-[0_32px_120px_-20px_rgba(14,140,233,0.3)] overflow-hidden border-2 border-brand-200">
        
        <!-- Left Side: Branding & Info -->
        <div class="hidden lg:flex flex-col justify-between p-12 bg-gradient-to-br from-brand-600 to-indigo-700 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mr-16 -mt-16 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-16 -mb-16 h-64 w-64 rounded-full bg-brand-400/20 blur-3xl"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-md">
                        <span class="text-xl">📊</span>
                    </div>
                    <span class="text-xs font-black uppercase tracking-[0.3em] text-brand-100">Smart Posyandu</span>
                </div>
                <h1 class="mt-10 text-5xl font-black leading-[1.1] tracking-tighter">Monitoring Tumbuh Kembang Lebih Akurat.</h1>
                <p class="mt-6 text-lg font-medium leading-relaxed text-brand-100/80">Platform terintegrasi untuk pemantauan data kesehatan balita secara real-time dan transparan.</p>
            </div>

            <div class="relative z-10 space-y-6">
                <div class="flex items-center gap-4 group cursor-default">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20 transition-all group-hover:bg-white/20">
                        <span class="text-xl">🔒</span>
                    </div>
                    <div>
                        <p class="font-black tracking-tight">Keamanan Data</p>
                        <p class="text-sm text-brand-100/60">Akses terenkripsi dan aman.</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 group cursor-default">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20 transition-all group-hover:bg-white/20">
                        <span class="text-xl">⚡</span>
                    </div>
                    <div>
                        <p class="font-black tracking-tight">Real-time Sync</p>
                        <p class="text-sm text-brand-100/60">Identitas anak dapat tersinkron dari kartu RFID digital.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="p-10 md:p-16 flex flex-col justify-center bg-white">
            <div class="w-full max-w-sm mx-auto">
                <div class="mb-10">
                    <span class="inline-block rounded-lg bg-brand-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-brand-600">Portal Akses</span>
                    <h2 class="mt-4 text-4xl font-black tracking-tighter text-slate-800">Masuk Aplikasi</h2>
                    <p class="mt-2 text-sm font-medium text-slate-400">Gunakan akun terdaftar untuk melanjutkan.</p>
                </div>

                <form action="{{ route('login.attempt') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="space-y-1.5">
                        <label for="email" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Email Address</label>
                        <div class="relative group">
                            <input id="email" name="email" type="email" value="{{ old('email') }}" class="input !bg-slate-50 focus:!bg-white !pl-12" placeholder="admin@posyandu.test" required autofocus>
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-brand-500 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            </span>
                        </div>
                        @error('email')
                            <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="password" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Password</label>
                        <div class="relative group">
                            <input id="password" name="password" type="password" class="input !bg-slate-50 focus:!bg-white !pl-12" placeholder="••••••••" required>
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-brand-500 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2.5 text-xs font-bold text-slate-500 cursor-pointer group">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded-lg border-slate-200 text-brand-600 focus:ring-brand-500 transition-all">
                            <span class="group-hover:text-slate-800 transition-colors">Ingat saya untuk 30 hari</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-primary w-full py-4 text-base tracking-tight shadow-xl shadow-brand-500/20">
                        Masuk Sekarang
                    </button>
                </form>

                <div class="mt-12 pt-8 border-t border-slate-100">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-300 mb-4">Akses Uji Coba</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-slate-50/50 ring-1 ring-black/5">
                            <p class="text-[9px] font-black text-slate-400 uppercase">Administrator</p>
                            <p class="mt-1 text-xs font-black text-slate-700">admin12345</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-50/50 ring-1 ring-black/5">
                            <p class="text-[9px] font-black text-slate-400 uppercase">Petugas</p>
                            <p class="mt-1 text-xs font-black text-slate-700">petugas12345</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
