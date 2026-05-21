@extends('admin.layout')

@section('title', $displayName)
@section('subtitle', 'Клиент #'.$customer->id)

@section('content')
    <p class="admin-back">
        <a href="{{ url('/admin/customers') }}" class="admin-btn admin-btn--ghost">← К списку клиентов</a>
    </p>

    @if ($customer->is_banned)
        <div class="admin-alert admin-alert--banned">
            <strong>Клиент заблокирован</strong>
            <span>Доступ к API и покупкам отключён (is_banned = true).</span>
        </div>
    @endif

    <section class="admin-panel admin-customer-actions">
        <form method="POST" action="{{ url('/admin/customers/'.$customer->id.'/toggle-ban') }}">
            @csrf
            @if ($customer->is_banned)
                <button type="submit" class="admin-btn admin-btn--primary">Разблокировать</button>
            @else
                <button type="submit" class="admin-btn admin-btn--danger" onclick="return confirm('Заблокировать клиента? Он не сможет пользоваться API.')">
                    Заблокировать
                </button>
            @endif
        </form>
    </section>

    <section class="admin-panel admin-customer-header">
        <div class="admin-customer-header__main">
            <h2>{{ $displayName }}</h2>
            <dl class="admin-dl admin-dl--inline">
                <div>
                    <dt>Телефон</dt>
                    <dd>{{ $customer->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>{{ $customer->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Telegram ID</dt>
                    <dd style="font-family:ui-monospace,monospace">{{ $customer->telegram_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Username</dt>
                    <dd>{{ $customer->username ? '@'.$customer->username : '—' }}</dd>
                </div>
                <div>
                    <dt>Регистрация</dt>
                    <dd>{{ $customer->created_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                </div>
            </dl>

            <form method="POST" action="{{ url('/admin/customers/'.$customer->id.'/flags') }}" class="admin-customer-flags">
                @csrf
                @method('PUT')

                <span class="admin-label">Скидочные статусы</span>
                <div class="admin-customer-flags__checks">
                    <label class="admin-check">
                        <input type="checkbox" name="is_military_member" value="1" {{ old('is_military_member', $customer->is_military_member) ? 'checked' : '' }}>
                        Военнослужащий
                    </label>
                    <label class="admin-check">
                        <input type="checkbox" name="is_student" value="1" {{ old('is_student', $customer->is_student) ? 'checked' : '' }}>
                        Студент
                    </label>
                </div>
                <button type="submit" class="admin-btn admin-btn--primary">Сохранить</button>
            </form>
        </div>

        @if ($telegramUrl)
            <div class="admin-telegram-link">
                <span class="admin-label">Ссылка в Telegram</span>
                <div class="admin-telegram-link__row">
                    <input
                        type="text"
                        class="admin-input"
                        value="{{ $telegramUrl }}"
                        readonly
                        id="telegram-url"
                        onclick="this.select()"
                    >
                    <a href="{{ $telegramUrl }}" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn--primary">
                        Открыть
                    </a>
                </div>
                <p class="hint">По username: <code>t.me/{{ ltrim($customer->username, '@') }}</code></p>
            </div>
        @elseif ($customer->telegram_id)
            <p class="hint">Username не указан — ссылку t.me сформировать нельзя. Telegram ID: {{ $customer->telegram_id }}</p>
        @else
            <p class="hint">Telegram не привязан.</p>
        @endif
    </section>

    @if ($activeVisit)
        <div class="admin-alert admin-alert--active">
            <strong>Сейчас тренируется</strong>
            <span>
                с {{ $activeVisit->start?->format('Y-m-d H:i') ?? '—' }}
                @if ($activeVisit->locker_number !== null)
                    · шкафчик <strong>{{ $activeVisit->locker_number }}</strong>
                    @if ($activeVisit->lockerRoom)
                        ({{ $activeVisit->lockerRoom->name }})
                    @endif
                @endif
                @if ($activeVisit->gymService)
                    · {{ $activeVisit->gymService->name }}
                @endif
            </span>
        </div>
    @else
        <div class="admin-alert admin-alert--muted">
            Сейчас не в зале — активного визита нет.
        </div>
    @endif

    <details class="admin-collapse" open>
        <summary>
            <span>Статистика покупок абонементов</span>
            <span class="admin-collapse__meta">{{ $purchaseStats['total'] }} заказов</span>
        </summary>
        <div class="admin-collapse__body">
            <section class="admin-stats admin-stats--3">
                <div class="admin-stat-card">
                    <span class="admin-stat-card__label">Оплачено</span>
                    <strong class="admin-stat-card__value">{{ number_format($purchaseStats['approved_sum'], 2, '.', ' ') }} UAH</strong>
                    <span class="admin-stat-card__hint">{{ $purchaseStats['approved_count'] }} заказов</span>
                </div>
                <div class="admin-stat-card">
                    <span class="admin-stat-card__label">Всего заказов</span>
                    <strong class="admin-stat-card__value">{{ $purchaseStats['total'] }}</strong>
                    <span class="admin-stat-card__hint">payment_orders</span>
                </div>
                <div class="admin-stat-card">
                    <span class="admin-stat-card__label">Ожидают / отклонены</span>
                    <strong class="admin-stat-card__value">{{ $purchaseStats['created_count'] }} / {{ $purchaseStats['declined_count'] }}</strong>
                    <span class="admin-stat-card__hint">created / declined</span>
                </div>
            </section>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Услуга</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th>Заказ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->gymService?->name ?? '—' }}</td>
                                <td>{{ number_format((float) $order->amount, 2, '.', ' ') }} {{ $order->currency }}</td>
                                <td>
                                    @php
                                        $statusClass = match ($order->status) {
                                            'approved' => 'admin-badge--ok',
                                            'declined' => 'admin-badge--danger',
                                            'created' => 'admin-badge--warn',
                                            default => 'admin-badge--muted',
                                        };
                                    @endphp
                                    <span class="admin-badge {{ $statusClass }}">{{ $order->status }}</span>
                                </td>
                                <td style="color:#94a3b8;white-space:nowrap">{{ $order->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td style="font-family:ui-monospace,monospace;font-size:0.75rem">{{ $order->order_reference }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="admin-empty">Покупок пока нет.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </details>

    <details class="admin-collapse">
        <summary>
            <span>История абонементов</span>
            <span class="admin-collapse__meta">{{ $subscriptionStats['total'] }} записей · активных {{ $subscriptionStats['active'] }}</span>
        </summary>
        <div class="admin-collapse__body">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Услуга</th>
                            <th>Цена</th>
                            <th>Тип</th>
                            <th>Лимит</th>
                            <th>Использовано визитов</th>
                            <th>Выдан</th>
                            <th>Истекает</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subscriptions as $sub)
                            @php
                                $service = $sub->gymService;
                                $limit = $service?->is_periodical
                                    ? ($service->day_amount ? $service->day_amount.' дн.' : 'период')
                                    : ($service?->visit_amount ? $service->visit_amount.' виз.' : '—');
                            @endphp
                            <tr>
                                <td>{{ $sub->id }}</td>
                                <td class="name-cell">{{ $service?->name ?? '—' }}</td>
                                <td>{{ $service ? number_format((float) $service->price, 2, '.', ' ').' UAH' : '—' }}</td>
                                <td>{{ $service?->is_periodical ? 'Период' : 'Пакет' }}</td>
                                <td>{{ $limit }}</td>
                                <td>{{ $sub->finished_visits_amount ?? 0 }}</td>
                                <td style="color:#94a3b8;white-space:nowrap">{{ $sub->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td style="color:#94a3b8;white-space:nowrap">{{ $sub->expired_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>
                                    <span class="admin-badge {{ $sub->is_active ? 'admin-badge--ok' : 'admin-badge--muted' }}">
                                        {{ $sub->is_active ? 'Активен' : 'Неактивен' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="admin-empty">Абонементов нет.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </details>

    <details class="admin-collapse">
        <summary>
            <span>История визитов</span>
            <span class="admin-collapse__meta">{{ $visits->count() }} визитов</span>
        </summary>
        <div class="admin-collapse__body">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Услуга</th>
                            <th>Начало</th>
                            <th>Окончание</th>
                            <th>Раздевалка</th>
                            <th>Шкафчик</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($visits as $visit)
                            <tr>
                                <td>{{ $visit->id }}</td>
                                <td>{{ $visit->gymService?->name ?? '—' }}</td>
                                <td style="color:#94a3b8;white-space:nowrap">{{ $visit->start?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td style="color:#94a3b8;white-space:nowrap">{{ $visit->finish?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>{{ $visit->lockerRoom?->name ?? '—' }}</td>
                                <td>{{ $visit->locker_number ?? '—' }}</td>
                                <td>
                                    @if ($visit->is_finished)
                                        <span class="admin-badge admin-badge--ok">Завершён</span>
                                    @else
                                        <span class="admin-badge admin-badge--warn">Активный</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="admin-empty">Визитов нет.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </details>
@endsection
