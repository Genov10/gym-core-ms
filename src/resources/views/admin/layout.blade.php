<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-40 left-1/2 h-[520px] w-[520px] -translate-x-1/2 rounded-full bg-indigo-700/20 blur-3xl"></div>
        <div class="absolute -bottom-48 right-[-120px] h-[520px] w-[520px] rounded-full bg-fuchsia-700/10 blur-3xl"></div>
    </div>

    <div class="relative mx-auto max-w-6xl px-6 py-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <a href="{{ url('/admin') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-800 bg-slate-900/40 px-4 py-2 text-sm font-semibold hover:bg-slate-900/60">
                        Gym Admin
                    </a>
                    <nav class="hidden md:flex items-center gap-2 text-sm">
                        <a href="{{ url('/admin') }}" class="rounded-lg px-3 py-2 text-slate-200 hover:bg-slate-900/50">Главная</a>
                        <a href="{{ url('/admin/rooms') }}" class="rounded-lg px-3 py-2 text-slate-200 hover:bg-slate-900/50">Комнаты</a>
                    </nav>
                </div>
                <p class="mt-2 text-xs text-slate-400">
                    @yield('subtitle')
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-right">
                    <div class="text-xs text-slate-400">Вы вошли как</div>
                    <div class="text-sm font-semibold text-slate-200 font-mono">{{ auth()->user()->email }}</div>
                </div>

                <form method="POST" action="{{ url('/admin/logout') }}">
                    @csrf
                    <button class="rounded-xl border border-slate-800 bg-slate-900/40 px-4 py-2 text-sm hover:bg-slate-900/60">
                        Выйти
                    </button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-2xl border border-emerald-900/50 bg-emerald-900/20 px-5 py-4 text-sm text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-6">
            @yield('content')
        </div>

        <div class="mt-10 border-t border-slate-900/80 pt-6 text-xs text-slate-500">
            <span class="font-mono">/admin</span> • Laravel
        </div>
    </div>
</body>
</html>

