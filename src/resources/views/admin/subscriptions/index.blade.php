@extends('admin.layout')

@section('title', 'Абонементы')
@section('subtitle', 'Таблица customer_gym_services')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
@endpush

@section('content')
    <section class="admin-panel">
        <div class="admin-toolbar admin-toolbar--stack">
            <div>
                <h2>Абонементы клиентов</h2>
            </div>

            <form method="GET" action="{{ url('/admin/subscriptions') }}" class="admin-filters admin-filters--subscriptions">
                <input type="hidden" name="filtered" value="1">

                <div class="admin-field">
                    <label for="customer">Пользователь</label>
                    <input
                        id="customer"
                        name="customer"
                        type="search"
                        class="admin-input"
                        value="{{ $filters['customer'] }}"
                        placeholder="Имя, телефон, TG ID…"
                        autocomplete="off"
                    >
                </div>

                <div class="admin-date-range">
                    <div class="admin-field">
                        <label for="date_from">Начало от</label>
                        <input
                            id="date_from"
                            name="date_from"
                            type="text"
                            class="admin-input admin-datepicker"
                            value="{{ $filters['date_from'] }}"
                            placeholder="дд.мм.рррр"
                            autocomplete="off"
                        >
                    </div>
                    <span class="admin-date-range__sep" aria-hidden="true">—</span>
                    <div class="admin-field">
                        <label for="date_to">Заканчивается до</label>
                        <input
                            id="date_to"
                            name="date_to"
                            type="text"
                            class="admin-input admin-datepicker"
                            value="{{ $filters['date_to'] }}"
                            placeholder="дд.мм.рррр"
                            autocomplete="off"
                        >
                    </div>
                </div>

                <div class="admin-field">
                    <label for="type">Тип абонемента</label>
                    <select id="type" name="type" class="admin-input">
                        <option value="" @selected($filters['type'] === '')>Все</option>
                        <option value="periodical" @selected($filters['type'] === 'periodical')>Период</option>
                        <option value="package" @selected($filters['type'] === 'package')>Пакет</option>
                    </select>
                </div>

                <div class="admin-field">
                    <label for="service_id">Вид услуги</label>
                    <select id="service_id" name="service_id" class="admin-input">
                        <option value="" @selected((int) $filters['service_id'] === 0)>Все</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" @selected((int) $filters['service_id'] === (int) $service->id)>
                                {{ $service->name }}@if (! $service->is_active) (неактивна)@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="admin-field">
                    <label>Посещений</label>
                    <div class="admin-date-range">
                        <input
                            id="visits_from"
                            name="visits_from"
                            type="number"
                            min="0"
                            step="1"
                            class="admin-input"
                            value="{{ $filters['visits_from'] === null ? '' : $filters['visits_from'] }}"
                            placeholder="от"
                            autocomplete="off"
                        >
                        <span class="admin-date-range__sep" aria-hidden="true">—</span>
                        <input
                            id="visits_to"
                            name="visits_to"
                            type="number"
                            min="0"
                            step="1"
                            class="admin-input"
                            value="{{ $filters['visits_to'] === null ? '' : $filters['visits_to'] }}"
                            placeholder="до"
                            autocomplete="off"
                        >
                    </div>
                </div>

                <div class="admin-field">
                    <label class="admin-check" style="margin-top:1.55rem">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ $filters['is_active'] ? 'checked' : '' }}
                        >
                        Только активные
                    </label>
                </div>

                <div class="admin-field admin-field--search">
                    <label for="subscriptions-filter">Поиск в таблице</label>
                    <input
                        id="subscriptions-filter"
                        type="search"
                        class="admin-input"
                        placeholder="Быстрый поиск по строкам…"
                        data-admin-table-filter="subscriptions-table"
                    >
                </div>

                <div class="admin-filter-actions">
                    <button type="submit" class="admin-btn admin-btn--primary">Применить</button>
                    <a href="{{ url('/admin/subscriptions') }}" class="admin-btn admin-btn--ghost">Сбросить</a>
                </div>
            </form>
        </div>

        <div class="admin-result-count">
            Найдено записей: <strong>{{ $foundCount }}</strong>
        </div>

        <div class="admin-table-wrap">
            <table id="subscriptions-table" class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Клиент</th>
                        <th>Услуга</th>
                        <th>Тип</th>
                        <th>Куплено</th>
                        <th>Начало</th>
                        <th>Истекает</th>
                        <th>Визитов</th>
                        <th>Статус</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subscriptions as $sub)
                        @php
                            $customer = $sub->customer;
                            $service = $sub->gymService;
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
                            $serviceName = $service?->name ?? '—';
                            $typeLabel = $service?->is_periodical ? 'Период' : 'Пакет';
                        @endphp
                        <tr data-search="{{ $sub->id }} {{ $customerLabel }} {{ $customer?->phone }} {{ $customer?->telegram_id }} {{ $serviceName }} {{ $typeLabel }}">
                            <td>{{ $sub->id }}</td>
                            <td>
                                <span class="name-cell">{{ $customerLabel }}</span>
                                @if ($customer)
                                    <br>
                                    <span style="font-size:0.75rem;color:#94a3b8">
                                        #{{ $customer->id }}
                                        @if ($customer->phone)
                                            · {{ $customer->phone }}
                                        @endif
                                    </span>
                                @endif
                            </td>
                            <td>{{ $serviceName }}</td>
                            <td>{{ $typeLabel }}</td>
                            <td style="color:#94a3b8;white-space:nowrap">{{ $sub->purchase_date?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td style="color:#94a3b8;white-space:nowrap">{{ $sub->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td style="color:#94a3b8;white-space:nowrap">{{ $sub->expired_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $sub->finished_visits_amount ?? 0 }}</td>
                            <td>
                                <span class="admin-badge {{ $sub->is_active ? 'admin-badge--ok' : 'admin-badge--muted' }}">
                                    {{ $sub->is_active ? 'Активен' : 'Неактивен' }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if ($customer)
                                    <a href="{{ url('/admin/customers/'.$customer->id) }}" class="admin-btn admin-btn--ghost">Клиент</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="admin-empty">По заданным фильтрам абонементов нет.</td>
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

            flatpickr(fromInput, {
                ...common,
                defaultDate: fromInput.value || undefined,
            });

            flatpickr(toInput, {
                ...common,
                defaultDate: toInput.value || undefined,
            });
        })();
    </script>
@endpush
