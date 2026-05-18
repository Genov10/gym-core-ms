@extends('admin.layout')

@section('title', 'Услуги')
@section('subtitle', 'Управление услугами (абонементы, посещения, доп. услуги)')

@section('content')
    <section class="admin-panel">
        <h2>Добавить услугу</h2>
        <p class="hint">Создаст запись в <code>gym_services</code></p>

        <form method="POST" action="{{ url('/admin/services') }}" class="admin-form-grid admin-form-grid--services">
            @csrf

            <div class="admin-field">
                <label for="name">Название</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}" class="admin-input" placeholder="Например: Разовое посещение">
                @error('name')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field admin-field--span">
                <label for="description">Описание</label>
                <textarea id="description" name="description" rows="3" class="admin-input admin-textarea" placeholder="Краткое описание услуги для клиента">{{ old('description') }}</textarea>
                @error('description')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field">
                <label for="price">Цена</label>
                <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price') }}" class="admin-input" placeholder="0.00">
                @error('price')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field">
                <label for="day_amount">Количество дней</label>
                <input id="day_amount" name="day_amount" type="number" min="0" value="{{ old('day_amount') }}" class="admin-input">
                @error('day_amount')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field">
                <label for="visit_amount">Количество посещений</label>
                <input id="visit_amount" name="visit_amount" type="number" min="0" value="{{ old('visit_amount') }}" class="admin-input">
                @error('visit_amount')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-form-actions admin-form-actions--stack">
                <label class="admin-check">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                    Активна
                </label>
                <button type="submit" class="admin-btn admin-btn--primary admin-btn--block">Добавить</button>
            </div>
        </form>
    </section>

    <section class="admin-panel">
        <div class="admin-toolbar">
            <h2>Список услуг</h2>
            <div class="admin-field">
                <label for="services-filter">Поиск</label>
                <input id="services-filter" type="search" class="admin-input" placeholder="Название, ID, цена…" data-admin-table-filter="services-table">
            </div>
        </div>

        <div class="admin-table-wrap">
            <table id="services-table" class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Описание</th>
                        <th>Цена</th>
                        <th>Активна</th>
                        <th>Период</th>
                        <th>Дни</th>
                        <th>Визиты</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr data-search="{{ $service->id }} {{ $service->name }} {{ $service->description }} {{ $service->price }} {{ $service->is_active ? 'да' : 'нет' }}">
                            <td>{{ $service->id }}</td>
                            <td class="name-cell">{{ $service->name }}</td>
                            <td class="admin-cell-desc">{{ $service->description ? \Illuminate\Support\Str::limit($service->description, 80) : '—' }}</td>
                            <td>{{ $service->price ?? '—' }}</td>
                            <td>
                                <span class="admin-badge {{ $service->is_active ? 'admin-badge--ok' : 'admin-badge--muted' }}">
                                    {{ $service->is_active ? 'Да' : 'Нет' }}
                                </span>
                            </td>
                            <td>{{ $service->is_periodical ? 'Да' : 'Нет' }}</td>
                            <td>{{ $service->day_amount ?? '—' }}</td>
                            <td>{{ $service->visit_amount ?? '—' }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ url('/admin/services/'.$service->id) }}" onsubmit="return confirm('Удалить услугу?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-btn admin-btn--danger">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="admin-empty">Услуг пока нет. Добавьте первую в форме выше.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
