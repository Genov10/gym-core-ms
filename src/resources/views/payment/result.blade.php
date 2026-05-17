<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $success ? 'Оплата успішна' : 'Оплата не пройшла' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #f4f6f8;
            color: #1a1a1a;
            padding: 1.5rem;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            padding: 2rem 1.75rem;
            max-width: 22rem;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        }
        .icon {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }
        .icon--ok {
            background: #e8f7ee;
            color: #1a9d4d;
        }
        .icon--fail {
            background: #fdecec;
            color: #d93025;
        }
        h1 {
            font-size: 1.15rem;
            font-weight: 600;
            margin: 0 0 0.5rem;
            line-height: 1.35;
        }
        p {
            margin: 0;
            color: #555;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .ref {
            margin-top: 1rem;
            font-size: 0.8rem;
            color: #888;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="card">
        @if ($success)
            <div class="icon icon--ok" aria-hidden="true">✓</div>
            <h1>Ваш платіж підтверджено</h1>
            <p>Поверніться до бота.</p>
        @else
            <div class="icon icon--fail" aria-hidden="true">✕</div>
            <h1>Ваш платіж не підтверджено</h1>
            <p>Поверніться до бота та спробуйте ще раз.</p>
        @endif
        @if ($orderReference !== '')
            <p class="ref">{{ $orderReference }}</p>
        @endif
    </div>
</body>
</html>
