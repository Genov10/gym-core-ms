@extends('admin.layout')

@section('title', 'Посещения')
@section('subtitle', 'Визиты клиентов за выбранный период (customer_visits)')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
@endpush

@section('content')
    <section class="admin-stats admin-stats--3">
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Всего посещений</span>
            <strong class="admin-stat-card__value">{{ $stats['total'] }}</strong>
            <span class="admin-stat-card__hint">{{ $dateFrom }} — {{ $dateTo }}</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Завершённые</span>
            <strong class="admin-stat-card__value">{{ $stats['finished'] }}</strong>
            <span class="admin-stat-card__hint">is_finished = да</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Активные / в зале</span>
            <strong class="admin-stat-card__value">{{ $stats['active'] }}</strong>
            <span class="admin-stat-card__hint">ещё не завершены</span>
        </div>
        <div class="admin-stat-card">
            <span class="admin-stat-card__label">Уникальных клиентов</span>
            <strong class="admin-stat-card__value">{{ $stats['unique_customers'] }}</strong>
            <span class="admin-stat-card__hint">за период</span>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-toolbar admin-toolbar--stack">
            <div>
                <h2>Список посещений</h2>
                <p class="hint">Показано: {{ $visits->count() }}</p>
            </div>

            <form method="GET" action="{{ url('/admin/visits') }}" class="admin-filters admin-filters--sales">
                <div class="admin-date-range">
                    <div class="admin-field">
                        <label for="date_from">Дата от</label>
                        <input id="date_from" name="date_from" type="text" class="admin-input admin-datepicker" value="{{ $dateFrom }}" placeholder="дд.мм.рррр" autocomplete="off">
                    </div>
                    <span class="admin-date-range__sep" aria-hidden="true">—</span>
                    <div class="admin-field">
                        <label for="date_to">Дата до</label>
                        <input id="date_to" name="date_to" type="text" class="admin-input admin-datepicker" value="{{ $dateTo }}" placeholder="дд.мм.рррр" autocomplete="off">
                    </div>
                </div>

                <div class="admin-field">
                    <label for="finished">Статус визита</label>
                    <select id="finished" name="finished" class="admin-input">
                        <option value="" @selected($finishedFilter === '')>Все</option>
                        <option value="1" @selected($finishedFilter === '1')>Завершён</option>
                        <option value="0" @selected($finishedFilter === '0')>Активный</option>
                    </select>
                </div>

                <div class="admin-field admin-field--search">
                    <label for="visits-filter">Поиск в таблице</label>
                    <input id="visits-filter" type="search" class="admin-input" placeholder="Клиент, услуга, шкафчик…" data-admin-table-filter="visits-table">
                </div>

                <div class="admin-filter-actions">
                    <button type="submit" class="admin-btn admin-btn--primary">Применить</button>
                </div>
            </form>

            <div class="admin-date-presets">
                @php
                    $presetQuery = fn (string $from, string $to): string => url('/admin/visits').'?'.http_build_query(array_filter([
                        'date_from' => $from,
                        'date_to' => $to,
                        'finished' => $finishedFilter !== '' ? $finishedFilter : null,
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
            <table id="visits-table" class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Клиент</th>
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
                        @php
                            $customer = $visit->customer;
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
                            $serviceName = $visit->gymService?->name ?? '—';
                            $lockerRoomName = $visit->lockerRoom?->name ?? '—';
                            $lockerNum = $visit->locker_number !== null && $visit->locker_number !== '' ? (string) $visit->locker_number : '—';
                        @endphp
                        <tr data-search="{{ $visit->id }} {{ $customerLabel }} {{ $serviceName }} {{ $lockerRoomName }} {{ $lockerNum }} {{ $visit->is_finished ? 'завершён' : 'активный' }}">
                            <td>{{ $visit->id }}</td>
                            <td class="name-cell">{{ $customerLabel }}</td>
                            <td>{{ $serviceName }}</td>
                            <td style="color:#94a3b8;white-space:nowrap">{{ $visit->start?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td style="color:#94a3b8;white-space:nowrap">{{ $visit->finish?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $lockerRoomName }}</td>
                            <td>{{ $lockerNum }}</td>
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
                            <td colspan="8" class="admin-empty">За выбранный период посещений нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('scripts')
    @include('admin.partials.flatpickr-scripts')
@endpush
