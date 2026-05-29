<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Правила та умови — {{ $merchant['brand_name'] }}</title>
    <link rel="stylesheet" href="{{ asset('css/legal.css') }}">
</head>
<body>
    <main class="legal-page">
        <header class="legal-header">
            <h1>Правила та умови</h1>
            <p class="legal-lead">{{ $merchant['brand_name'] }} — інформація для клієнтів та платіжного сервісу</p>
        </header>

        <section class="legal-section">
            <h2>1. Загальні положення</h2>
            <p>
                Ця сторінка містить правила надання послуг, умови оплати, надання доступу
                та повернення коштів. Оплата здійснюється через платіжний сервіс WayForPay</strong>.
            </p>
        </section>

        <section class="legal-section">
            <h2>2. Правила та умови надання послуг</h2>
            <p>Ми надаємо послуги доступу до спортивного залу у форматі</p>
            <p><strong>Порядок надання послуги:</strong></p>
            <ol>
                <li>Клієнт обирає послугу в Telegram-боті та переходить на оплату.</li>
                <li>Після успішної оплати абонемент або пакет візитів активується автоматично в системі.</li>
                <li>Доступ до залу надається після реєстрації візиту через бот.</li>
            </ol>
            <p><strong>Строки надання послуги:</strong> активація — протягом кількох хвилин після підтвердження оплати; дія абонемента — згідно з обраним тарифом (кількість днів або візитів, зазначена при покупці).</p>
        </section>

        <section class="legal-section">
            <h2>3. Способи оплати</h2>
            <p><strong>Способи оплати:</strong></p>
            <ul>
                <li>банківська картка Visa / Mastercard через WayForPay;</li>
                <li>інші способи, доступні на платіжній сторінці WayForPay.</li>
            </ul>
            <p>Валюта розрахунків: <strong>UAH (гривня)</strong>.</p>
            <p><strong>Умови надання доступу (аналог доставки для цифрової послуги):</strong> послуга не потребує фізичної доставки; доступ надається дистанційно через обліковий запис клієнта в Telegram-боті та на території залу після ідентифікації клієнта.</p>
        </section>

        <section class="legal-section">
            <h2>4. Правила повернення коштів</h2>
            <p>
                Повернення коштів можливе у випадках, передбачених законодавством України та цими правилами.
            </p>
            <p><strong>Підстави для розгляду повернення:</strong></p>
            <ul>
                <li>подвійне або помилкове списання коштів;</li>
                <li>технічна помилка, через яку оплачена послуга не була активована протягом 24 годин;</li>
                <li>відмова від послуги до першого використання абонемента / візиту — за письмовим зверненням на email.</li>
            </ul>
            <p><strong>Порядок повернення:</strong> клієнт надсилає звернення на контактний email з датою оплати, сумою та ідентифікатором замовлення. Термін розгляду — до 14 робочих днів. Повернення здійснюється на ту ж платіжну карту, з якої була оплата, якщо інше не узгоджено з банком.</p>
            <p>
                <strong>Послуги, які вже використані</strong> (був зареєстрований візит / частково використаний абонемент),
                поверненню не підлягають, якщо інше не передбачено законом.
            </p>
        </section>

        <section class="legal-section legal-section--contacts">
            <h2>5. Контактна інформація</h2>
            <dl class="legal-dl">
                <div>
                    <dt>Повне ім'я (ФОП)</dt>
                    <dd>{{ $merchant['legal_name'] !== '' ? $merchant['legal_name'] : '—' }}</dd>
                </div>
                <div>
                    <dt>ІПН (податковий номер)</dt>
                    <dd>{{ $merchant['itn'] !== '' ? $merchant['itn'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Юридична адреса</dt>
                    <dd>{{ $merchant['legal_address'] !== '' ? $merchant['legal_address'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Фактична адреса</dt>
                    <dd>{{ $merchant['actual_address'] !== '' ? $merchant['actual_address'] : '—' }}</dd>
                </div>
                <div>
                    <dt>Телефон</dt>
                    <dd>
                        @if ($merchant['phone'] !== '')
                            <a href="tel:{{ preg_replace('/\s+/', '', $merchant['phone']) }}">{{ $merchant['phone'] }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>
                        @if ($merchant['email'] !== '')
                            <a href="mailto:{{ $merchant['email'] }}">{{ $merchant['email'] }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </section>

        <footer class="legal-footer">
            <p>Оновлено: {{ now()->format('d.m.Y') }}</p>
        </footer>
    </main>
</body>
</html>
