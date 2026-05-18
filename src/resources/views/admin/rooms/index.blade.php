@extends('admin.layout')

@section('title', 'Раздевалки')
@section('subtitle', 'Управление раздевалками и шкафчиками')

@section('content')
    <section class="admin-panel">
        <h2>Добавить раздевалку</h2>
        <p class="hint">
            Создаст запись в <code>locker_rooms</code> и шкафчики в <code>locker_room_items</code>
        </p>

        <form method="POST" action="{{ url('/admin/rooms') }}" class="admin-form-grid admin-form-grid--rooms">
            @csrf

            <div class="admin-field">
                <label for="name">Название</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}" class="admin-input" placeholder="Например: Мужская 1">
                @error('name')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field">
                <span class="admin-label">Пол</span>
                <div class="admin-radio-group">
                    <label>
                        <input type="radio" name="sex" value="male" {{ old('sex', 'male') === 'male' ? 'checked' : '' }}>
                        Мужская
                    </label>
                    <label>
                        <input type="radio" name="sex" value="female" {{ old('sex') === 'female' ? 'checked' : '' }}>
                        Женская
                    </label>
                </div>
                @error('sex')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field">
                <label for="locker_amount">Количество шкафчиков</label>
                <input id="locker_amount" name="locker_amount" type="number" min="0" required value="{{ old('locker_amount', 0) }}" class="admin-input">
                @error('locker_amount')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-form-actions admin-form-actions--stack">
                <label class="admin-check">
                    <input type="checkbox" name="is_staff" value="1" {{ old('is_staff') ? 'checked' : '' }}>
                    Для персонала
                </label>
                <button type="submit" class="admin-btn admin-btn--primary admin-btn--block">Добавить</button>
            </div>
        </form>
    </section>

    <section class="admin-panel">
        <div class="admin-toolbar">
            <h2>Список раздевалок</h2>
            <div class="admin-field">
                <label for="rooms-filter">Поиск</label>
                <input id="rooms-filter" type="search" class="admin-input" placeholder="Название, ID, пол…" data-admin-table-filter="rooms-table">
            </div>
        </div>

        <div class="admin-table-wrap">
            <table id="rooms-table" class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Пол</th>
                        <th>Персонал</th>
                        <th>Шкафчики</th>
                        <th>Создано</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rooms as $room)
                        <tr data-search="{{ $room->id }} {{ $room->name }} {{ $room->sex === 'female' ? 'женская' : 'мужская' }} {{ $room->is_staff ? 'персонал' : 'клиенты' }}">
                            <td>{{ $room->id }}</td>
                            <td class="name-cell">{{ $room->name }}</td>
                            <td>{{ $room->sex === 'female' ? 'Женская' : 'Мужская' }}</td>
                            <td>
                                <span class="admin-badge admin-badge--muted">
                                    {{ $room->is_staff ? 'Персонал' : 'Клиенты' }}
                                </span>
                            </td>
                            <td>{{ $room->locker_amount }}</td>
                            <td style="color:#94a3b8">{{ $room->create_time?->format('Y-m-d H:i') }}</td>
                            <td class="text-right">
                                <a href="{{ url('/admin/rooms/'.$room->id) }}" class="admin-btn admin-btn--ghost">Открыть</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="admin-empty">Раздевалок пока нет. Добавьте первую в форме выше.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
