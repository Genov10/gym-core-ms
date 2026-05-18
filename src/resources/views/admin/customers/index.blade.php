@extends('admin.layout')

@section('title', 'Клиенты')
@section('subtitle', 'Список зарегистрированных клиентов (таблица customers)')

@section('content')
    <section class="admin-panel">
        <div class="admin-toolbar admin-toolbar--stack">
            <div>
                <h2>Клиенты</h2>
                <p class="hint">Показано: {{ $customers->count() }}</p>
            </div>

            <form method="GET" action="{{ url('/admin/customers') }}" class="admin-filters admin-filters--customers">
                <div class="admin-field">
                    <label for="name">Имя</label>
                    <input
                        id="name"
                        name="name"
                        type="search"
                        class="admin-input"
                        value="{{ $filters['name'] }}"
                        placeholder="Часть имени"
                        autocomplete="off"
                    >
                </div>

                <div class="admin-field">
                    <label for="lastname">Фамилия</label>
                    <input
                        id="lastname"
                        name="lastname"
                        type="search"
                        class="admin-input"
                        value="{{ $filters['lastname'] }}"
                        placeholder="Часть фамилии"
                        autocomplete="off"
                    >
                </div>

                <div class="admin-field">
                    <label for="phone">Телефон</label>
                    <input
                        id="phone"
                        name="phone"
                        type="search"
                        class="admin-input"
                        value="{{ $filters['phone'] }}"
                        placeholder="+380…"
                        autocomplete="off"
                    >
                </div>

                <div class="admin-field">
                    <label for="email">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="search"
                        class="admin-input"
                        value="{{ $filters['email'] }}"
                        placeholder="example@mail.com"
                        autocomplete="off"
                    >
                </div>

                <div class="admin-field">
                    <label for="identity">Username / Telegram ID</label>
                    <input
                        id="identity"
                        name="identity"
                        type="search"
                        class="admin-input"
                        value="{{ $filters['identity'] }}"
                        placeholder="@username или 123456789"
                        autocomplete="off"
                    >
                </div>

                <div class="admin-field admin-field--search">
                    <label for="customers-filter">Поиск в таблице</label>
                    <input
                        id="customers-filter"
                        type="search"
                        class="admin-input"
                        placeholder="Быстрый поиск по строкам…"
                        data-admin-table-filter="customers-table"
                    >
                </div>

                <div class="admin-filter-actions">
                    <button type="submit" class="admin-btn admin-btn--primary">Применить</button>
                    @if (collect($filters)->filter()->isNotEmpty())
                        <a href="{{ url('/admin/customers') }}" class="admin-btn admin-btn--ghost">Сбросить</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table id="customers-table" class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Фамилия</th>
                        <th>Username</th>
                        <th>Пол</th>
                        <th>Telegram ID</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Тел. проверен</th>
                        <th>Статус</th>
                        <th>Создан</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr data-search="{{ $customer->id }} {{ $customer->name }} {{ $customer->lastname }} {{ $customer->username }} {{ $customer->telegram_id }} {{ $customer->phone }} {{ $customer->email }} {{ $customer->sex }}">
                            <td>{{ $customer->id }}</td>
                            <td class="name-cell">{{ $customer->name ?? '—' }}</td>
                            <td>{{ $customer->lastname ?? '—' }}</td>
                            <td>{{ $customer->username ? '@'.$customer->username : '—' }}</td>
                            <td>
                                @if ($customer->sex === 'female')
                                    Женский
                                @elseif ($customer->sex === 'male')
                                    Мужской
                                @else
                                    —
                                @endif
                            </td>
                            <td style="font-family:ui-monospace,monospace;font-size:0.8rem">{{ $customer->telegram_id ?? '—' }}</td>
                            <td>{{ $customer->phone ?? '—' }}</td>
                            <td>{{ $customer->email ?? '—' }}</td>
                            <td>
                                <span class="admin-badge {{ $customer->is_num_verified ? 'admin-badge--ok' : 'admin-badge--muted' }}">
                                    {{ $customer->is_num_verified ? 'Да' : 'Нет' }}
                                </span>
                            </td>
                            <td>
                                @if ($customer->is_banned)
                                    <span class="admin-badge admin-badge--danger">Заблокирован</span>
                                @else
                                    <span class="admin-badge admin-badge--ok">Активен</span>
                                @endif
                            </td>
                            <td style="color:#94a3b8;white-space:nowrap">{{ $customer->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ url('/admin/customers/'.$customer->id) }}" class="admin-btn admin-btn--ghost">Открыть</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="admin-empty">
                                @if (collect($filters)->filter()->isNotEmpty())
                                    По заданным фильтрам клиентов не найдено.
                                @else
                                    Клиентов пока нет.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
