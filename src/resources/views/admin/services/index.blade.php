@extends('admin.layout')

@section('title', 'Услуги')
@section('subtitle', 'Управление услугами (абонементы, посещения, доп. услуги)')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <div class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/40 p-6 shadow-xl shadow-black/20">
            <h2 class="text-lg font-semibold">Добавить услугу</h2>
            <p class="mt-1 text-sm text-slate-400">Создаст запись в <span class="font-mono">gym_services</span>.</p>

            <form method="POST" action="{{ url('/admin/services') }}" class="mt-5 space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-200">Название</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        required
                        value="{{ old('name') }}"
                        class="mt-2 w-full rounded-xl border border-slate-800 bg-slate-950/30 px-4 py-3 text-slate-100 shadow-inner shadow-black/20 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30"
                        placeholder="Например: Разовое посещение"
                    >
                    @error('name')
                        <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-slate-200">Цена</label>
                    <input
                        id="price"
                        name="price"
                        type="number"
                        min="0"
                        step="0.01"
                        value="{{ old('price') }}"
                        class="mt-2 w-full rounded-xl border border-slate-800 bg-slate-950/30 px-4 py-3 text-slate-100 shadow-inner shadow-black/20 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30"
                        placeholder="0.00"
                    >
                    @error('price')
                        <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-800 bg-slate-950/30" {{ old('is_active', '1') ? 'checked' : '' }}>
                        Активна
                    </label>
               
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="day_amount" class="block text-sm font-medium text-slate-200">Количество дней</label>
                        <input
                            id="day_amount"
                            name="day_amount"
                            type="number"
                            min="0"
                            value="{{ old('day_amount') }}"
                            class="mt-2 w-full rounded-xl border border-slate-800 bg-slate-950/30 px-4 py-3 text-slate-100 shadow-inner shadow-black/20 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30"
                        >
                        @error('day_amount')
                            <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="visit_amount" class="block text-sm font-medium text-slate-200">Количество посещений</label>
                        <input
                            id="visit_amount"
                            name="visit_amount"
                            type="number"
                            min="0"
                            value="{{ old('visit_amount') }}"
                            class="mt-2 w-full rounded-xl border border-slate-800 bg-slate-950/30 px-4 py-3 text-slate-100 shadow-inner shadow-black/20 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30"
                        >
                        @error('visit_amount')
                            <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white shadow-lg shadow-indigo-600/20 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                >
                    Добавить
                </button>
            </form>
        </div>

        <div class="lg:col-span-3 rounded-2xl border border-slate-800 bg-slate-900/40 p-6 shadow-xl shadow-black/20">
            <h2 class="text-lg font-semibold">Список услуг</h2>

            <div class="mt-5 overflow-x-auto rounded-xl border border-slate-800 bg-slate-950/20">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-950/40 text-slate-300">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">ID</th>
                            <th class="px-4 py-3 text-left font-medium">Название</th>
                            <th class="px-4 py-3 text-left font-medium">Цена</th>
                            <th class="px-4 py-3 text-left font-medium">Активна</th>
                            <th class="px-4 py-3 text-left font-medium">Период</th>
                            <th class="px-4 py-3 text-left font-medium">Дни</th>
                            <th class="px-4 py-3 text-left font-medium">Визиты</th>
                            <th class="px-4 py-3 text-right font-medium">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($services as $service)
                            <tr class="odd:bg-slate-950/10 hover:bg-slate-950/30">
                                <td class="px-4 py-3 text-slate-200">{{ $service->id }}</td>
                                <td class="px-4 py-3 text-slate-200">{{ $service->name }}</td>
                                <td class="px-4 py-3 text-slate-200">{{ $service->price ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full border border-slate-800 bg-slate-950/20 px-2.5 py-1 text-xs font-semibold text-slate-200">
                                        {{ $service->is_active ? 'Да' : 'Нет' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-200">{{ $service->is_periodical ? 'Да' : 'Нет' }}</td>
                                <td class="px-4 py-3 text-slate-200">{{ $service->day_amount ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-200">{{ $service->visit_amount ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ url('/admin/services/'.$service->id) }}" onsubmit="return confirm('Удалить услугу?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-red-900/60 bg-red-900/20 px-3 py-1.5 text-xs font-semibold text-red-200 hover:bg-red-900/30">
                                            Удалить
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                    Услуг пока нет. Добавь первую слева.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

