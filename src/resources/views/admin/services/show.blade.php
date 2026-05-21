@extends('admin.layout')

@section('title', $service->name)
@section('subtitle', 'Услуга #'.$service->id)

@section('content')
    <p class="admin-back">
        <a href="{{ url('/admin/services') }}" class="admin-btn admin-btn--ghost">← К списку услуг</a>
    </p>

    <section class="admin-panel">
        <h2>Данные услуги</h2>
        <p class="hint">
            ID {{ $service->id }}
            · создано {{ $service->created_at?->format('Y-m-d H:i') ?? '—' }}
            · периодическая: {{ $service->is_periodical ? 'да' : 'нет' }}
        </p>

        <form method="POST" action="{{ url('/admin/services/'.$service->id) }}" class="admin-form-grid admin-form-grid--service-edit">
            @csrf
            @method('PUT')

            <div class="admin-field">
                <label for="name">Название</label>
                <input id="name" name="name" type="text" required value="{{ old('name', $service->name) }}" class="admin-input">
                @error('name')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field admin-field--span">
                <label for="description">Описание</label>
                <textarea id="description" name="description" rows="4" class="admin-input admin-textarea">{{ old('description', $service->description) }}</textarea>
                @error('description')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field">
                <label for="price">Цена (UAH)</label>
                <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $service->price) }}" class="admin-input">
                @error('price')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field">
                <label for="day_amount">Количество дней</label>
                <input id="day_amount" name="day_amount" type="number" min="0" step="1" value="{{ old('day_amount', $service->day_amount) }}" class="admin-input">
                @error('day_amount')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field">
                <label for="visit_amount">Количество посещений</label>
                <input id="visit_amount" name="visit_amount" type="number" min="0" step="1" value="{{ old('visit_amount', $service->visit_amount) }}" class="admin-input">
                @error('visit_amount')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <div class="admin-field admin-field--span admin-panel admin-panel--nested">
                <h3>Скидки, %</h3>
                <p class="hint">Целое число от 0 до 100 (без дробной части)</p>

                <div class="admin-form-grid admin-form-grid--sales">
                    <div class="admin-field">
                        <label for="sales_default">По умолчанию</label>
                        <input
                            id="sales_default"
                            name="sales_default"
                            type="number"
                            min="0"
                            max="100"
                            step="1"
                            required
                            value="{{ old('sales_default', $service->sales_default ?? 0) }}"
                            class="admin-input"
                        >
                        @error('sales_default')<p class="admin-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="admin-field">
                        <label for="sales_military_member">Военнослужащие</label>
                        <input
                            id="sales_military_member"
                            name="sales_military_member"
                            type="number"
                            min="0"
                            max="100"
                            step="1"
                            required
                            value="{{ old('sales_military_member', $service->sales_military_member ?? 0) }}"
                            class="admin-input"
                        >
                        @error('sales_military_member')<p class="admin-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="admin-field">
                        <label for="sales_student">Студенты</label>
                        <input
                            id="sales_student"
                            name="sales_student"
                            type="number"
                            min="0"
                            max="100"
                            step="1"
                            required
                            value="{{ old('sales_student', $service->sales_student ?? 0) }}"
                            class="admin-input"
                        >
                        @error('sales_student')<p class="admin-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="admin-form-actions admin-form-actions--stack admin-field--span">
                <label class="admin-check">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                    Активна (доступна для покупки)
                </label>
                <button type="submit" class="admin-btn admin-btn--primary admin-btn--block">Сохранить</button>
            </div>
        </form>
    </section>

    @if ($service->is_active)
        <section class="admin-panel">
            <form method="POST" action="{{ url('/admin/services/'.$service->id.'/deactivate') }}" onsubmit="return confirm('Деактивировать услугу?')">
                @csrf
                <button type="submit" class="admin-btn admin-btn--danger">Деактивировать услугу</button>
            </form>
        </section>
    @endif
@endsection
