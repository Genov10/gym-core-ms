<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — Gym Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0c0618] text-slate-100 antialiased">
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-48 left-1/4 h-[560px] w-[560px] rounded-full bg-violet-700/25 blur-3xl"></div>
        <div class="absolute top-1/3 -right-32 h-[480px] w-[480px] rounded-full bg-fuchsia-700/15 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/2 h-[400px] w-[600px] -translate-x-1/2 rounded-full bg-indigo-800/20 blur-3xl"></div>
    </div>

    <div class="relative flex min-h-screen">
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-violet-900/30 bg-slate-950/70 backdrop-blur-md lg:flex">
            <div class="border-b border-violet-900/30 px-5 py-5">
                <a href="{{ url('/admin') }}" class="block">
                    <span class="text-lg font-bold tracking-tight text-white">Gym Admin</span>
                    <span class="mt-0.5 block text-xs text-violet-300/80">Панель керування</span>
                </a>
            </div>

            <nav class="flex-1 space-y-1 px-3 py-4">
                <a
                    href="{{ url('/admin') }}"
                    class="admin-nav-link {{ request()->is('admin') && ! request()->is('admin/*') ? 'admin-nav-link--active' : '' }}"
                >
                    <span class="text-violet-300" aria-hidden="true">⌂</span>
                    Главная
                </a>
                <a
                    href="{{ url('/admin/rooms') }}"
                    class="admin-nav-link {{ request()->is('admin/rooms*') ? 'admin-nav-link--active' : '' }}"
                >
                    <span class="text-violet-300" aria-hidden="true">◫</span>
                    Комнаты
                </a>
                <a
                    href="{{ url('/admin/services') }}"
                    class="admin-nav-link {{ request()->is('admin/services*') ? 'admin-nav-link--active' : '' }}"
                >
                    <span class="text-violet-300" aria-hidden="true">◎</span>
                    Услуги
                </a>
            </nav>

            <div class="border-t border-violet-900/30 p-4">
                <p class="truncate text-xs text-slate-500">Вы вошли как</p>
                <p class="mt-0.5 truncate font-mono text-xs text-slate-300">{{ auth()->user()->email }}</p>
                <form method="POST" action="{{ url('/admin/logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full rounded-xl border border-slate-700/80 bg-slate-900/60 px-3 py-2 text-sm text-slate-200 transition hover:bg-slate-800/80">
                        Выйти
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col lg:pl-64">
            <header class="sticky top-0 z-20 border-b border-violet-900/20 bg-[#0c0618]/80 px-4 py-4 backdrop-blur-md sm:px-8 sm:py-5">
                <nav class="mb-3 flex flex-wrap gap-2 lg:hidden">
                    <a href="{{ url('/admin') }}" class="rounded-lg px-3 py-1.5 text-xs font-medium {{ request()->is('admin') && ! request()->is('admin/*') ? 'bg-violet-600/30 text-white' : 'text-slate-400' }}">Главная</a>
                    <a href="{{ url('/admin/rooms') }}" class="rounded-lg px-3 py-1.5 text-xs font-medium {{ request()->is('admin/rooms*') ? 'bg-violet-600/30 text-white' : 'text-slate-400' }}">Комнаты</a>
                    <a href="{{ url('/admin/services') }}" class="rounded-lg px-3 py-1.5 text-xs font-medium {{ request()->is('admin/services*') ? 'bg-violet-600/30 text-white' : 'text-slate-400' }}">Услуги</a>
                </nav>
                <h1 class="text-xl font-semibold tracking-tight text-white sm:text-2xl">@yield('title', 'Admin')</h1>
                @hasSection('subtitle')
                    <p class="mt-1 text-sm text-slate-400">@yield('subtitle')</p>
                @endif
            </header>

            <main class="flex-1 px-8 py-6">
                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-800/50 bg-emerald-950/40 px-5 py-4 text-sm text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="border-t border-violet-900/20 px-8 py-4 text-xs text-slate-600">
                <span class="font-mono">/admin</span> · Laravel
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
