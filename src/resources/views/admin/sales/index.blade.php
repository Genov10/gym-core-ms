@extends('admin.layout')

@section('title', 'Статистика продаж')
@section('subtitle', 'Заказы оплаты за выбранный период')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
@endpush

@section('content')
    <section class="admin-stats">
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Успешные продажи</span>
            <strong class="admin-stat-card__value">{{ number_format($stats['approved_sum'], 2, '.', ' ') }} UAH</strong>
            <span class="admin-stat-card__hint">{{ $stats['approved_count'] }} оплаченных · {{ $dateFrom }} — {{ $dateTo }}</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Всего заказов</span>
            <strong class="admin-stat-card__value">{{ $stats['total_orders'] }}</strong>
            <span class="admin-stat-card__hint">за период</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Ожидают оплаты</span>
            <strong class="admin-stat-card__value">{{ $stats['created_count'] }}</strong>
            <span class="admin-stat-card__hint">статус created</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Отклонено</span>
            <strong class="admin-stat-card__value">{{ $stats['declined_count'] }}</strong>
            <span class="admin-stat-card__hint">статус declined</span>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-toolbar admin-toolbar--stack">
            <div>
                <h2>История заказов</h2>
                <p class="hint">Показано: {{ $orders->count() }}</p>
            </div>

            <form method="GET" action="{{ url('/admin/sales') }}" class="admin-filters admin-filters--sales" id="sales-filters-form">
                <div class="admin-date-range">
                    <div class="admin-field">
                        <label for="date_from">Дата от</label>
                        <input
                            id="date_from"
                            name="date_from"
                            type="text"
                            class="admin-input admin-datepicker"
                            value="{{ $dateFrom }}"
                            placeholder="дд.мм.рррр"
                            autocomplete="off"
                        >
                    </div>
                    <span class="admin-date-range__sep" aria-hidden="true">—</span>
                    <div class="admin-field">
                        <label for="date_to">Дата до</label>
                        <input
                            id="date_to"
                            name="date_to"
                            type="text"
                            class="admin-input admin-datepicker"
                            value="{{ $dateTo }}"
                            placeholder="дд.мм.рррр"
                            autocomplete="off"
                        >
                    </div>
                </div>

                <div class="admin-field">
                    <label for="status">Статус</label>
                    <select id="status" name="status" class="admin-input">
                        <option value="" @selected($statusFilter === '')>Все</option>
                        <option value="approved" @selected($statusFilter === 'approved')>Подтверждён</option>
                        <option value="created" @selected($statusFilter === 'created')>Создан</option>
                        <option value="declined" @selected($statusFilter === 'declined')>Отклонён</option>
                        <option value="refunded" @selected($statusFilter === 'refunded')>Возврат</option>
                    </select>
                </div>

                <div class="admin-field admin-field--search">
                    <label for="sales-filter">Поиск в таблице</label>
                    <input
                        id="sales-filter"
                        type="search"
                        class="admin-input"
                        placeholder="Клиент, услуга, номер заказа…"
                        data-admin-table-filter="sales-table"
                    >
                </div>

                <div class="admin-filter-actions">
                    <button type="submit" class="admin-btn admin-btn--primary">Применить</button>
                </div>
            </form>

            <div class="admin-date-presets">
                @php
                    $presetQuery = fn (string $from, string $to): string => url('/admin/sales').'?'.http_build_query(array_filter([
                        'date_from' => $from,
                        'date_to' => $to,
                        'status' => $statusFilter !== '' ? $statusFilter : null,
                    ]));
                    $todayStr = now()->toDateString();
                    $yesterday = now()->subDay()->toDateString();
                    $weekAgo = now()->subDays(6)->toDateString();
                    $monthAgo = now()->subDays(29)->toDateString();
                @endphp
                <a href="{{ $presetQuery($todayStr, $todayStr) }}" class="admin-date-preset {{ $dateFrom === $todayStr && $dateTo === $todayStr ? 'is-active' : '' }}">Сегодня</a>
                <a href="{{ $presetQuery($yesterday, $yesterday) }}" class="admin-date-preset">Вчера</a>
                <a href="{{ $presetQuery($weekAgo, $todayStr) }}" class="admin-date-preset">7 дней</a>
                <a href="{{ $presetQuery($monthAgo, $todayStr) }}" class="admin-date-preset">30 дней</a>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table id="sales-table" class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Заказ</th>
                        <th>Клиент</th>
                        <th>Услуга</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Создан</th>
                        <th>Обновлён</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $customer = $order->customer;
                            $customerLabel = '—';
                            if ($customer) {
                                $fullName = trim(($customer->name ?? '').' '.($customer->lastname ?? ''));
                                if ($fullName !== '') {
                                    $customerLabel = $fullName;
                                } elseif ($customer->phone) {
                                    $customerLabel = $customer->phone;
                                } elseif ($customer->telegram_id) {
                                    $customerLabel = 'TG '.$customer->telegram_id;
                                } else {
                                    $customerLabel = '#'.$customer->id;
                                }
                            }
                            $serviceName = $order->gymService?->name ?? '—';
                            $statusClass = match ($order->status) {
                                'approved' => 'admin-badge--ok',
                                'declined' => 'admin-badge--danger',
                                'created' => 'admin-badge--warn',
                                default => 'admin-badge--muted',
                            };
                            $statusLabel = match ($order->status) {
                                'approved' => 'Подтверждён',
                                'created' => 'Создан',
                                'declined' => 'Отклонён',
                                'refunded' => 'Возврат',
                                default => $order->status,
                            };
                        @endphp
                        <tr data-search="{{ $order->id }} {{ $order->order_reference }} {{ $customerLabel }} {{ $serviceName }} {{ $order->status }} {{ $order->amount }}">
                            <td>{{ $order->id }}</td>
                            <td style="font-family:ui-monospace,monospace;font-size:0.8rem">{{ $order->order_reference }}</td>
                            <td>
                                <span class="name-cell">{{ $customerLabel }}</span>
                                @if ($customer && $customer->phone && ! str_contains($customerLabel, (string) $customer->phone))
                                    <br><span style="font-size:0.75rem;color:#94a3b8">{{ $customer->phone }}</span>
                                @endif
                            </td>
                            <td>{{ $serviceName }}</td>
                            <td style="white-space:nowrap">{{ number_format((float) $order->amount, 2, '.', ' ') }} {{ $order->currency }}</td>
                            <td>
                                <span class="admin-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td style="color:#94a3b8;white-space:nowrap">{{ $order->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td style="color:#94a3b8;white-space:nowrap">{{ $order->updated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="admin-empty">За выбранный период заказов нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/uk.js"></script>
    <script>
        (function () {
            const fromInput = document.getElementById('date_from');
            const toInput = document.getElementById('date_to');
            if (!fromInput || !toInput || typeof flatpickr === 'undefined') return;

            const common = {
                locale: 'uk',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd.m.Y',
                allowInput: true,
                disableMobile: true,
            };

            const fromPicker = flatpickr(fromInput, {
                ...common,
                defaultDate: fromInput.value || undefined,
                onChange: function (selectedDates) {
                    if (selectedDates[0]) {
                        toPicker.set('minDate', selectedDates[0]);
                    }
                },
            });

            const toPicker = flatpickr(toInput, {
                ...common,
                defaultDate: toInput.value || undefined,
                onChange: function (selectedDates) {
                    if (selectedDates[0]) {
                        fromPicker.set('maxDate', selectedDates[0]);
                    }
                },
            });

            if (fromInput.value) {
                toPicker.set('minDate', fromInput.value);
            }
            if (toInput.value) {
                fromPicker.set('maxDate', toInput.value);
            }
        })();
    </script>
@endpush
