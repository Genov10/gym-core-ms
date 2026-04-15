<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="mx-auto max-w-4xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Админка</h1>
                <p class="mt-1 text-sm text-slate-300">Вы вошли как: <span class="font-mono">{{ auth()->user()->email }}</span></p>
            </div>

            <form method="POST" action="{{ url('/admin/logout') }}">
                @csrf
                <button class="rounded-xl border border-slate-700 px-4 py-2 text-sm hover:bg-slate-900">
                    Выйти
                </button>
            </form>
        </div>

        <div class="mt-8 rounded-2xl border border-slate-800 bg-slate-900/40 p-6">
            <p class="text-slate-200">позравляю, ты залогинен</p>
        </div>
    </div>
</body>
</html>

