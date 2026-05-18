<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-[#0c0618] p-6 text-slate-100">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -top-40 left-1/2 h-[520px] w-[520px] -translate-x-1/2 rounded-full bg-indigo-700/20 blur-3xl"></div>
        <div class="absolute -bottom-48 right-[-120px] h-[520px] w-[520px] rounded-full bg-fuchsia-700/10 blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md">
        <div class="rounded-2xl border border-violet-900/40 bg-slate-900/50 shadow-xl shadow-black/30 backdrop-blur-sm">
            <div class="px-8 pt-8 pb-6">
                <h1 class="text-2xl font-semibold tracking-tight">Вход в админку</h1>
                <p class="mt-1 text-sm text-slate-300">`/admin`</p>
            </div>

            <form method="POST" action="{{ url('/admin/login') }}" class="px-8 pb-8 space-y-5">
                @csrf

                <div>
                    <label for="login" class="block text-sm font-medium text-slate-200">Логин</label>
                    <input
                        id="login"
                        name="login"
                        type="text"
                        autocomplete="username"
                        value="{{ old('login') }}"
                        required
                        class="admin-input mt-2"
                        placeholder="gym_admin"
                    >
                    @error('login')
                        <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-200">Пароль</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="admin-input mt-2"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                        <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-950/50">
                        Запомнить
                    </label>
                </div>

                <button
                    type="submit"
                    class="admin-btn-primary w-full"
                >
                    Войти
                </button>
            </form>
        </div>

        <p class="mt-4 text-xs text-slate-400">
            По умолчанию: <span class="font-mono">gym_admin</span> / <span class="font-mono">gym_admin</span>
        </p>
    </div>
</body>
</html>

