<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — Gym Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @vite(['resources/js/app.js'])
</head>
<body class="admin-body">
    <div class="admin-bg-glow" aria-hidden="true"></div>

    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-sidebar__brand">
                <a href="{{ url('/admin') }}">
                    <strong>Gym Admin</strong>
                    <span>Панель керування</span>
                </a>
            </div>

            <nav class="admin-nav">
                <a href="{{ url('/admin') }}" class="{{ request()->is('admin') && ! request()->is('admin/*') ? 'is-active' : '' }}">
                    <span aria-hidden="true">⌂</span> Главная
                </a>
                <a href="{{ url('/admin/rooms') }}" class="{{ request()->is('admin/rooms*') ? 'is-active' : '' }}">
                    <span aria-hidden="true">◫</span> Комнаты
                </a>
                <a href="{{ url('/admin/services') }}" class="{{ request()->is('admin/services*') ? 'is-active' : '' }}">
                    <span aria-hidden="true">◎</span> Услуги
                </a>
            </nav>

            <div class="admin-sidebar__foot">
                <small>Вы вошли как</small>
                <div class="email">{{ auth()->user()->email }}</div>
                <form method="POST" action="{{ url('/admin/logout') }}" style="margin-top:0.75rem">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn--ghost admin-btn--block">Выйти</button>
                </form>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <nav class="admin-topnav">
                    <a href="{{ url('/admin') }}" class="{{ request()->is('admin') && ! request()->is('admin/*') ? 'is-active' : '' }}">Главная</a>
                    <a href="{{ url('/admin/rooms') }}" class="{{ request()->is('admin/rooms*') ? 'is-active' : '' }}">Комнаты</a>
                    <a href="{{ url('/admin/services') }}" class="{{ request()->is('admin/services*') ? 'is-active' : '' }}">Услуги</a>
                </nav>
                <h1>@yield('title', 'Admin')</h1>
                @hasSection('subtitle')
                    <p>@yield('subtitle')</p>
                @endif
            </header>

            <main class="admin-content">
                @if (session('status'))
                    <div class="admin-alert">{{ session('status') }}</div>
                @endif

                @yield('content')
            </main>

            <footer class="admin-footer">
                <span style="font-family:ui-monospace,monospace">/admin</span> · Laravel
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
