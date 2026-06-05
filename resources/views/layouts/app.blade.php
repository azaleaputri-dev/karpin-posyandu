<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(20,184,166,0.15),_transparent_35%),linear-gradient(180deg,_#f8fafc_0%,_#eef2ff_100%)]">
        <div class="mx-auto flex min-h-screen max-w-7xl flex-col gap-6 px-4 py-6 lg:flex-row lg:px-6">
            <aside class="card w-full overflow-hidden lg:sticky lg:top-6 lg:h-[calc(100vh-3rem)] lg:w-72">
                <div class="bg-gradient-to-br from-teal-600 via-cyan-600 to-slate-900 p-6 text-white">
                    <p class="text-xs uppercase tracking-[0.35em] text-teal-100">{{ auth()->user()->isAdmin() ? 'Admin Puskesmas' : 'Petugas Posyandu' }}</p>
                    <h1 class="mt-3 text-2xl font-bold leading-tight">{{ config('app.name') }}</h1>
                    <p class="mt-3 text-sm text-cyan-50">{{ auth()->user()->isAdmin() ? 'Monitoring lintas posyandu dan perangkat IoT di wilayah puskesmas.' : 'Input data balita dan pengukuran untuk posyandu yang Anda tangani.' }}</p>
                </div>
                <nav class="space-y-2 p-4">
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['route']) ? 'bg-teal-600 text-white shadow-glow' : 'text-slate-600 hover:bg-slate-100' }} block rounded-2xl px-4 py-3 text-sm font-semibold transition">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <main class="flex-1 space-y-6">
                <header class="card flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Selamat datang</p>
                        <h2 class="text-2xl font-bold text-slate-900">{{ auth()->user()->name }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ auth()->user()->isAdmin() ? 'Akses monitoring puskesmas' : 'Petugas ' . optional(auth()->user()->posyandu)->name }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-secondary">Keluar</button>
                    </form>
                </header>

                @if (session('status'))
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <p class="font-semibold">Periksa kembali input yang belum valid.</p>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
@stack('scripts')
</html>
