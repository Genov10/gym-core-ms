@extends('admin.layout')

@section('title', 'Раздевалка #'.$room->id)
@section('subtitle', $room->name)

@section('content')
    <p class="admin-back">
        <a href="{{ url('/admin/rooms') }}" class="admin-btn admin-btn--ghost">← К списку раздевалок</a>
    </p>

    <section class="admin-panel">
        <h2>Параметры раздевалки</h2>
        <p class="hint">ID {{ $room->id }} · создано {{ $room->create_time?->format('Y-m-d H:i') ?? '—' }}</p>

        <form method="POST" action="{{ url('/admin/rooms/'.$room->id) }}" class="admin-form-grid admin-form-grid--rooms">
            @csrf
            @method('PUT')

            <div class="admin-field">
                <label for="name">Название</label>
                <input id="name" name="name" type="text" required value="{{ old('name', $room->name) }}" class="admin-input">
                @error('name')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field">
                <span class="admin-label">Пол</span>
                <div class="admin-radio-group">
                    <label>
                        <input type="radio" name="sex" value="male" {{ old('sex', $room->sex) === 'male' ? 'checked' : '' }}>
                        Мужская
                    </label>
                    <label>
                        <input type="radio" name="sex" value="female" {{ old('sex', $room->sex) === 'female' ? 'checked' : '' }}>
                        Женская
                    </label>
                </div>
                @error('sex')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field">
                <label for="locker_amount">Количество шкафчиков</label>
                <input id="locker_amount" name="locker_amount" type="number" min="0" required value="{{ old('locker_amount', $room->locker_amount) }}" class="admin-input">
                @error('locker_amount')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-form-actions admin-form-actions--stack">
                <label class="admin-check">
                    <input type="checkbox" name="is_staff" value="1" {{ old('is_staff', $room->is_staff) ? 'checked' : '' }}>
                    Для персонала
                </label>
                <label class="admin-check">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $room->is_active) ? 'checked' : '' }}>
                    Активна (доступна для выдачи шкафчиков)
                </label>
                <button type="submit" class="admin-btn admin-btn--primary admin-btn--block">Сохранить</button>
            </div>
        </form>
    </section>

    <section class="admin-panel">
        <div class="admin-toolbar">
            <div>
                <h2>Шкафчики</h2>
                <p class="hint">Статус по <code>locker_room_items</code> и активным визитам (<code>customer_visits</code>, <code>is_finished = 0</code>)</p>
            </div>
            <div class="admin-field">
                <label for="lockers-filter">Поиск</label>
                <input id="lockers-filter" type="search" class="admin-input" placeholder="Номер, клиент…" data-admin-table-filter="lockers-table">
            </div>
        </div>

        <div class="admin-table-wrap">
            <table id="lockers-table" class="admin-table">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Статус</th>
                        <th>В БД (is_free)</th>
                        <th>Клиент</th>
                        <th>Визит с</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lockers as $locker)
                        @php
                            $visit = $locker['visit'];
                            $customer = $visit?->customer;
                            $customerLabel = $customer
                                ? trim(($customer->name ?? '').' '.($customer->lastname ?? '')) ?: '—'
                                : null;
                            $search = implode(' ', array_filter([
                                $locker['number'],
                                ! $locker['is_free'] || $visit ? 'занят' : 'свободен',
                                $customerLabel,
                                $customer?->telegram_id,
                                $customer?->phone,
                            ]));
                        @endphp
                        <tr data-search="{{ $search }}">
                            <td><strong>{{ $locker['number'] }}</strong></td>
                            <td>
                                @if ($visit || ! $locker['is_free'])
                                    <span class="admin-badge admin-badge--warn">Занят</span>
                                @else
                                    <span class="admin-badge admin-badge--ok">Свободен</span>
                                @endif
                            </td>
                            <td>{{ $locker['is_free'] ? 'да' : 'нет' }}</td>
                            <td>
                                @if ($visit && $customer)
                                    {{ $customerLabel }}
                                    @if ($customer->telegram_id)
                                        <span class="hint">· tg {{ $customer->telegram_id }}</span>
                                    @endif
                                @elseif (! $locker['is_free'])
                                    <span class="hint">Нет активного визита</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="color:#94a3b8">{{ $visit?->start?->format('Y-m-d H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-empty">Шкафчиков нет. Увеличьте «Количество шкафчиков» и сохраните.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
