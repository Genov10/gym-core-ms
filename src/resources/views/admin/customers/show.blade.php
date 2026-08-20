@extends('admin.layout')

@section('title', $displayName)
@section('subtitle', ($listMode === 'staff' ? 'Сотрудник #' : 'Клиент #').$customer->id)

@section('content')
    <p class="admin-back">
        <a href="{{ $listBaseUrl }}" class="admin-btn admin-btn--ghost">
            ← {{ $listMode === 'staff' ? 'К списку персонала' : 'К списку клиентов' }}
        </a>
    </p>

    @if ($customer->is_banned)
        <div class="admin-alert admin-alert--banned">
            <strong>Клиент заблокирован</strong>
            <span>Доступ к API и покупкам отключён (is_banned = true).</span>
        </div>
    @endif

    @if ($customer->is_staff)
        <div class="admin-alert">
            <strong>В персонале</strong>
            <span>is_staff = true — отображается в разделе «Персонал».</span>
        </div>
    @endif

    <section class="admin-panel admin-customer-actions">
        <div class="admin-customer-actions__row">
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

            <form method="POST" action="{{ url('/admin/customers/'.$customer->id.'/toggle-staff') }}">
                @csrf
                @if ($customer->is_staff)
                    <button type="submit" class="admin-btn admin-btn--ghost" onclick="return confirm('Убрать из персонала?')">
                        Убрать из персонала
                    </button>
                @else
                    <button type="submit" class="admin-btn admin-btn--primary">
                        Добавить в персонал
                    </button>
                @endif
            </form>
        </div>
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

                <div class="admin-customer-flags__actions">
                    <button type="submit" class="admin-btn admin-btn--primary">Сохранить</button>

                    <section class="admin-inline-sell-service" aria-labelledby="sell-service-title">
                        <div class="admin-inline-sell-service__head">
                            <span class="admin-label" id="sell-service-title">Продать услугу</span>
                            <span class="hint">Ссылка WayForPay как в боте</span>
                        </div>

                        <div class="admin-inline-sell-service__controls">
                            <select id="sell-service-select" class="admin-input admin-input--select">
                                <option value="">Загрузка…</option>
                            </select>
                            <button type="button" class="admin-btn admin-btn--primary" id="sell-service-generate" disabled>
                                Создать ссылку
                            </button>
                        </div>

                        <div id="sell-service-link-wrap" class="admin-sell-link hidden">
                            <label class="admin-label" for="sell-service-link">Ссылка на оплату</label>
                            <div class="admin-telegram-link__row">
                                <input
                                    type="text"
                                    class="admin-input"
                                    id="sell-service-link"
                                    readonly
                                    onclick="this.select()"
                                >
                                <button type="button" class="admin-btn admin-btn--primary" id="sell-service-copy">
                                    Копировать
                                </button>
                            </div>
                        </div>

                        <p id="sell-service-status" class="hint hidden"></p>
                        <p id="sell-service-error" class="admin-alert admin-alert--danger hidden"></p>
                    </section>
                </div>
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
                            <th>Действия</th>
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
                                <td>
                                    @if ($sub->is_active && $service?->is_periodical)
                                        <details class="admin-freeze-details">
                                            <summary class="admin-btn admin-btn--ghost admin-btn--sm">Заморозка абонемента</summary>
                                            <form
                                                method="POST"
                                                action="{{ url('/admin/customers/'.$customer->id.'/subscriptions/'.$sub->id.'/freeze') }}"
                                                class="admin-freeze-form"
                                            >
                                                @csrf
                                                <label for="freeze-days-{{ $sub->id }}">Дней продления</label>
                                                <div class="admin-freeze-form__row">
                                                    <input
                                                        id="freeze-days-{{ $sub->id }}"
                                                        name="days"
                                                        type="number"
                                                        min="1"
                                                        max="365"
                                                        step="1"
                                                        required
                                                        class="admin-input"
                                                        placeholder="Например: 7"
                                                        value="{{ old('days') }}"
                                                    >
                                                    <button type="submit" class="admin-btn admin-btn--primary admin-btn--sm">Продлить</button>
                                                </div>
                                            </form>
                                        </details>
                                    @else
                                        <span class="hint">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="admin-empty">Абонементов нет.</td>
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

@push('scripts')
    <script>
        (function () {
            const customerId = @json($customer->id);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const select = document.getElementById('sell-service-select');
            const generateBtn = document.getElementById('sell-service-generate');
            const linkWrap = document.getElementById('sell-service-link-wrap');
            const linkInput = document.getElementById('sell-service-link');
            const copyBtn = document.getElementById('sell-service-copy');
            const statusEl = document.getElementById('sell-service-status');
            const errorEl = document.getElementById('sell-service-error');

            function hideError() {
                errorEl.textContent = '';
                errorEl.classList.add('hidden');
            }

            function showError(message) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
            }

            function setStatus(message) {
                if (!message) {
                    statusEl.textContent = '';
                    statusEl.classList.add('hidden');
                    return;
                }
                statusEl.textContent = message;
                statusEl.classList.remove('hidden');
            }

            function resetLink() {
                linkInput.value = '';
                linkWrap.classList.add('hidden');
                setStatus('');
            }

            function formatServiceLabel(service) {
                if (service.price < service.sale_from) {
                    return `${service.name} — ${service.price} UAH (было ${service.sale_from})`;
                }
                return `${service.name} — ${service.price} UAH`;
            }

            async function loadServices() {
                hideError();
                resetLink();
                select.innerHTML = '<option value="">Загрузка…</option>';
                select.disabled = true;
                generateBtn.disabled = true;

                try {
                    const response = await fetch(`/admin/customers/${customerId}/sellable-services`, {
                        headers: { Accept: 'application/json' },
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload.message || 'Не удалось загрузить услуги');
                    }

                    const services = payload.data ?? [];

                    if (services.length === 0) {
                        select.innerHTML = '<option value="">Нет доступных услуг</option>';
                        return;
                    }

                    select.innerHTML = '<option value="">Выберите услугу</option>';
                    services.forEach((service) => {
                        const option = document.createElement('option');
                        option.value = String(service.id);
                        option.textContent = formatServiceLabel(service);
                        select.appendChild(option);
                    });
                    select.disabled = false;
                } catch (error) {
                    select.innerHTML = '<option value="">Ошибка загрузки</option>';
                    showError(error.message || 'Ошибка загрузки услуг');
                }
            }

            async function generateLink(serviceId) {
                hideError();
                resetLink();
                setStatus('Генерация ссылки…');

                try {
                    const response = await fetch(`/admin/customers/${customerId}/payment-link`, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ service_id: Number(serviceId) }),
                    });

                    const payload = await response.json();

                    if (!response.ok || !payload.success) {
                        throw new Error(payload.message || 'Не удалось создать ссылку');
                    }

                    linkInput.value = payload.url;
                    linkWrap.classList.remove('hidden');
                    setStatus(`Заказ: ${payload.orderReference}`);
                } catch (error) {
                    setStatus('');
                    showError(error.message || 'Не удалось создать ссылку');
                }
            }

            select?.addEventListener('change', () => {
                generateBtn.disabled = !select.value;

                if (!select.value) {
                    resetLink();
                    hideError();
                }
            });

            generateBtn?.addEventListener('click', () => {
                if (!select.value) {
                    return;
                }

                generateLink(select.value);
            });

            copyBtn?.addEventListener('click', async () => {
                if (!linkInput.value) {
                    return;
                }

                try {
                    await navigator.clipboard.writeText(linkInput.value);
                    setStatus('Ссылка скопирована');
                } catch (error) {
                    linkInput.select();
                    document.execCommand('copy');
                    setStatus('Ссылка скопирована');
                }
            });

            loadServices();
        })();
    </script>
@endpush
