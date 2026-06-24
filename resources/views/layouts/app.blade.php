<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-rounded.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon-rounded.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="overflow-x-hidden bg-slate-100 text-slate-900">
    <div class="min-h-screen lg:flex">
        <aside class="w-full shrink-0 bg-slate-900 text-white lg:sticky lg:top-0 lg:flex lg:h-screen lg:w-64 lg:flex-col">
            <div class="flex h-[6rem] flex-col justify-center bg-brand-600 px-6 py-3 lg:h-[6.5rem]">
                <p class="text-[9px] font-bold uppercase tracking-[0.26em] text-brand-100/80">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Health Worker' }}</p>
                <h1 class="mt-1.5 text-[1.9rem] font-black leading-[0.94] tracking-tight text-white lg:text-[2.05rem]">Kartu Pintar Posyandu</h1>
            </div>

            <nav class="space-y-1 overflow-x-auto px-4 py-5 lg:flex-1 lg:overflow-y-auto lg:overflow-x-hidden">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['route']) ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white' }} group flex items-center rounded-xl px-4 py-3 text-[11px] font-bold uppercase tracking-[0.18em] transition-colors duration-200">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 px-4 py-4 lg:mt-auto">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl border border-white/10 px-4 py-3 text-[11px] font-bold uppercase tracking-[0.18em] text-rose-300 transition-colors hover:bg-white/5 hover:text-rose-200">
                        Keluar Sesi
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex min-w-0 flex-1 flex-col">
            <header class="animate-float-in flex h-[6rem] shrink-0 items-center justify-between border-b border-slate-200 bg-white px-6 py-4 lg:h-[6.5rem] lg:px-8">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 overflow-hidden rounded-full ring-2 ring-brand-100">
                        <img src="{{ asset('logo-posyandu-smart-card.jpeg') }}" alt="Logo Posyandu Smart Card" class="h-full w-full object-cover object-center">
                    </div>
                    <div>
                        <h2 class="text-[1.05rem] font-black leading-none tracking-tight text-slate-900">{{ auth()->user()->name }}</h2>
                        <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ auth()->user()->isAdmin() ? 'Puskesmas Admin' : optional(auth()->user()->posyandu)->name }}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-[11px] font-bold uppercase tracking-[0.16em] text-emerald-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Sistem Aktif
                </span>
            </header>

            <div class="animate-float-in-slow animate-delay-2 flex-1 min-w-0 space-y-6 p-6 lg:p-8">
                @if (session('status'))
                    <div class="flex items-center gap-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-bold text-brand-700">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-[11px] text-white">✓</span>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-rose-600 text-[10px] text-white">!</span>
                        Periksa kembali input yang belum valid.
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
@stack('scripts')
</html>
