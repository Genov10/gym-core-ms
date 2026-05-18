<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход — Gym Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin-body admin-login">
    <div class="admin-bg-glow" aria-hidden="true"></div>

    <div class="admin-login-card">
        <div class="admin-login-card__head">
            <h1>Вход в админку</h1>
        </div>

        <form method="POST" action="{{ url('/admin/login') }}">
            @csrf

            <div class="admin-field">
                <label for="login">Логин</label>
                <input id="login" name="login" type="text" autocomplete="username" value="{{ old('login') }}" required class="admin-input">
                @error('login')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field">
                <label for="password">Пароль</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required class="admin-input" placeholder="••••••••">
                @error('password')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="admin-btn admin-btn--primary admin-btn--block">Войти</button>
        </form>
    </div>
</body>
</html>
