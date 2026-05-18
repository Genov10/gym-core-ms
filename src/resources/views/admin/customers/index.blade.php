@extends('admin.layout')

@section('title', 'Клиенты')
@section('subtitle', 'Список зарегистрированных клиентов (таблица customers)')

@section('content')
    <section class="admin-panel">
        <div class="admin-toolbar">
            <div>
                <h2>Клиенты</h2>
                <p class="hint">Всего: {{ $customers->count() }}</p>
            </div>
            <div class="admin-field">
                <label for="customers-filter">Поиск</label>
                <input
                    id="customers-filter"
                    type="search"
                    class="admin-input"
                    placeholder="Имя, телефон, email, telegram…"
                    data-admin-table-filter="customers-table"
                >
            </div>
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
                        <th>Создан</th>
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
                            <td style="color:#94a3b8;white-space:nowrap">{{ $customer->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="admin-empty">Клиентов пока нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
