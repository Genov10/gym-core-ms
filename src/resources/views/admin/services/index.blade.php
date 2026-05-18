@extends('admin.layout')

@section('title', 'Услуги')
@section('subtitle', 'Управление услугами (абонементы, посещения, доп. услуги)')

@section('content')
    <section class="admin-panel mb-6">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-white">Добавить услугу</h2>
                <p class="mt-0.5 text-xs text-slate-400">Создаст запись в <span class="font-mono text-violet-300/90">gym_services</span></p>
            </div>
        </div>

        <form method="POST" action="{{ url('/admin/services') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6 xl:items-end">
            @csrf

            <div class="xl:col-span-2">
                <label for="name" class="admin-label">Название</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    required
                    value="{{ old('name') }}"
                    class="admin-input mt-1.5"
                    placeholder="Например: Разовое посещение"
                >
                @error('name')
                    <p class="mt-1.5 text-sm text-red-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price" class="admin-label">Цена</label>
                <input
                    id="price"
                    name="price"
                    type="number"
                    min="0"
                    step="0.01"
                    value="{{ old('price') }}"
                    class="admin-input mt-1.5"
                    placeholder="0.00"
                >
                @error('price')
                    <p class="mt-1.5 text-sm text-red-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="day_amount" class="admin-label">Количество дней</label>
                <input
                    id="day_amount"
                    name="day_amount"
                    type="number"
                    min="0"
                    value="{{ old('day_amount') }}"
                    class="admin-input mt-1.5"
                >
                @error('day_amount')
                    <p class="mt-1.5 text-sm text-red-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="visit_amount" class="admin-label">Количество посещений</label>
                <input
                    id="visit_amount"
                    name="visit_amount"
                    type="number"
                    min="0"
                    value="{{ old('visit_amount') }}"
                    class="admin-input mt-1.5"
                >
                @error('visit_amount')
                    <p class="mt-1.5 text-sm text-red-300">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center xl:flex-col xl:items-stretch">
                <label class="inline-flex min-h-[42px] items-center gap-2 rounded-xl border border-slate-700/60 bg-slate-950/30 px-3 text-sm text-slate-200">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-600 bg-slate-950/50 text-violet-600 focus:ring-violet-500/40" {{ old('is_active', '1') ? 'checked' : '' }}>
                    Активна
                </label>
                <button type="submit" class="admin-btn-primary w-full sm:w-auto xl:w-full">
                    Добавить
                </button>
            </div>
        </form>
    </section>

    <section class="admin-panel">
        <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-base font-semibold text-white">Список услуг</h2>
            <div class="w-full sm:max-w-xs">
                <label for="services-filter" class="admin-label">Поиск</label>
                <input
                    id="services-filter"
                    type="search"
                    class="admin-input mt-1.5"
                    placeholder="Название, ID, цена…"
                    data-admin-table-filter="services-table"
                >
            </div>
        </div>

        <div class="admin-table-wrap">
            <table id="services-table" class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
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
                        <tr
                            data-search="{{ $service->id }} {{ $service->name }} {{ $service->price }} {{ $service->is_active ? 'да' : 'нет' }}"
                        >
                            <td>{{ $service->id }}</td>
                            <td class="font-medium text-white">{{ $service->name }}</td>
                            <td>{{ $service->price ?? '—' }}</td>
                            <td>
                                <span class="inline-flex items-center rounded-full border border-slate-700/80 bg-slate-950/40 px-2.5 py-0.5 text-xs font-semibold {{ $service->is_active ? 'text-emerald-300' : 'text-slate-400' }}">
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
                                    <button type="submit" class="admin-btn-danger">
                                        Удалить
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">
                                Услуг пока нет. Добавьте первую в форме выше.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
