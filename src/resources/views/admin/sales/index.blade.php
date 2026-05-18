@extends('admin.layout')

@section('title', 'Статистика продаж')
@section('subtitle', 'Заказы оплаты (payment_orders) с данными клиентов и услуг')

@section('content')
    <section class="admin-stats">
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Успешные продажи</span>
            <strong class="admin-stat-card__value">{{ number_format($stats['approved_sum'], 2, '.', ' ') }} UAH</strong>
            <span class="admin-stat-card__hint">{{ $stats['approved_count'] }} оплаченных заказов</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Всего заказов</span>
            <strong class="admin-stat-card__value">{{ $stats['total_orders'] }}</strong>
            <span class="admin-stat-card__hint">в payment_orders</span>
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
        <div class="admin-toolbar">
            <div>
                <h2>История заказов</h2>
                <p class="hint">Показано: {{ $orders->count() }}</p>
            </div>
            <form method="GET" action="{{ url('/admin/sales') }}" class="admin-filters">
                <div class="admin-field">
                    <label for="status">Статус</label>
                    <select id="status" name="status" class="admin-input" onchange="this.form.submit()">
                        <option value="" @selected($statusFilter === '')>Все</option>
                        <option value="approved" @selected($statusFilter === 'approved')>Подтверждён</option>
                        <option value="created" @selected($statusFilter === 'created')>Создан</option>
                        <option value="declined" @selected($statusFilter === 'declined')>Отклонён</option>
                        <option value="refunded" @selected($statusFilter === 'refunded')>Возврат</option>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="sales-filter">Поиск</label>
                    <input
                        id="sales-filter"
                        type="search"
                        class="admin-input"
                        placeholder="Клиент, услуга, номер заказа…"
                        data-admin-table-filter="sales-table"
                    >
                </div>
            </form>
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
                                @if ($customer && $customer->phone && str_contains($customerLabel, $customer->phone) === false)
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
                            <td colspan="8" class="admin-empty">Заказов пока нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
