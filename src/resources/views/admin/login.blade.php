<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="rounded-2xl border border-slate-800 bg-slate-900/40 shadow-xl">
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
                        class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-3 text-slate-100 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30"
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
                        class="mt-2 w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-3 text-slate-100 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30"
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
                    class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
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

