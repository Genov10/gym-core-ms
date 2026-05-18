<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — Gym Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
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
                    <span aria-hidden="true">◫</span> Раздевалки
                </a>
                <a href="{{ url('/admin/services') }}" class="{{ request()->is('admin/services*') ? 'is-active' : '' }}">
                    <span aria-hidden="true">◎</span> Услуги
                </a>
                <a href="{{ url('/admin/customers') }}" class="{{ request()->is('admin/customers*') ? 'is-active' : '' }}">
                    <span class="admin-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M5 20c0-4 3.5-6 7-6s7 2 7 6"/>
                        </svg>
                    </span>
                    Клиенты
                </a>
                <a href="{{ url('/admin/sales') }}" class="{{ request()->is('admin/sales*') ? 'is-active' : '' }}">
                    <span class="admin-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19V5"/>
                            <path d="M4 19h16"/>
                            <path d="M8 17v-5"/>
                            <path d="M12 17V8"/>
                            <path d="M16 17v-3"/>
                        </svg>
                    </span>
                    Продажи
                </a>
                <a href="{{ url('/admin/visits') }}" class="{{ request()->is('admin/visits*') ? 'is-active' : '' }}">
                    <span class="admin-nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </span>
                    Посещения
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
                    <a href="{{ url('/admin/rooms') }}" class="{{ request()->is('admin/rooms*') ? 'is-active' : '' }}">Раздевалки</a>
                    <a href="{{ url('/admin/services') }}" class="{{ request()->is('admin/services*') ? 'is-active' : '' }}">Услуги</a>
                    <a href="{{ url('/admin/customers') }}" class="{{ request()->is('admin/customers*') ? 'is-active' : '' }}">Клиенты</a>
                    <a href="{{ url('/admin/sales') }}" class="{{ request()->is('admin/sales*') ? 'is-active' : '' }}">Продажи</a>
                    <a href="{{ url('/admin/visits') }}" class="{{ request()->is('admin/visits*') ? 'is-active' : '' }}">Посещения</a>
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
