@extends('admin.layout')

@section('title', 'Комнаты')
@section('subtitle', 'Управление раздевалками и шкафчиками')

@section('content')
    <section class="admin-panel mb-6">
        <div class="mb-4">
            <h2 class="text-base font-semibold text-white">Добавить комнату</h2>
            <p class="mt-0.5 text-xs text-slate-400">
                Создаст запись в <span class="font-mono text-violet-300/90">locker_rooms</span>
                и шкафчики в <span class="font-mono text-violet-300/90">locker_room_items</span>
            </p>
        </div>

        <form method="POST" action="{{ url('/admin/rooms') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5 xl:items-end">
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
                    placeholder="Например: Мужская 1"
                >
                @error('name')
                    <p class="mt-1.5 text-sm text-red-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <p class="admin-label">Пол</p>
                <div class="mt-1.5 flex flex-wrap items-center gap-4 rounded-xl border border-slate-700/60 bg-slate-950/30 px-3 py-2.5">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                        <input type="radio" name="sex" value="male" class="border-slate-600 bg-slate-950/50 text-violet-600 focus:ring-violet-500/40" {{ old('sex', 'male') === 'male' ? 'checked' : '' }}>
                        Мужская
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                        <input type="radio" name="sex" value="female" class="border-slate-600 bg-slate-950/50 text-violet-600 focus:ring-violet-500/40" {{ old('sex') === 'female' ? 'checked' : '' }}>
                        Женская
                    </label>
                </div>
                @error('sex')
                    <p class="mt-1.5 text-sm text-red-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="locker_amount" class="admin-label">Количество шкафчиков</label>
                <input
                    id="locker_amount"
                    name="locker_amount"
                    type="number"
                    min="0"
                    required
                    value="{{ old('locker_amount', 0) }}"
                    class="admin-input mt-1.5"
                >
                @error('locker_amount')
                    <p class="mt-1.5 text-sm text-red-300">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center xl:flex-col xl:items-stretch">
                <label class="inline-flex min-h-[42px] items-center gap-2 rounded-xl border border-slate-700/60 bg-slate-950/30 px-3 text-sm text-slate-200">
                    <input type="checkbox" name="is_staff" value="1" class="rounded border-slate-600 bg-slate-950/50 text-violet-600 focus:ring-violet-500/40" {{ old('is_staff') ? 'checked' : '' }}>
                    Для персонала
                </label>
                <button type="submit" class="admin-btn-primary w-full sm:w-auto xl:w-full">
                    Добавить
                </button>
            </div>
        </form>
    </section>

    <section class="admin-panel">
        <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-base font-semibold text-white">Список комнат</h2>
            <div class="w-full sm:max-w-xs">
                <label for="rooms-filter" class="admin-label">Поиск</label>
                <input
                    id="rooms-filter"
                    type="search"
                    class="admin-input mt-1.5"
                    placeholder="Название, ID, пол…"
                    data-admin-table-filter="rooms-table"
                >
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
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rooms as $room)
                        <tr
                            data-search="{{ $room->id }} {{ $room->name }} {{ $room->sex === 'female' ? 'женская' : 'мужская' }} {{ $room->is_staff ? 'персонал' : 'клиенты' }}"
                        >
                            <td>{{ $room->id }}</td>
                            <td class="font-medium text-white">{{ $room->name }}</td>
                            <td>{{ $room->sex === 'female' ? 'Женская' : 'Мужская' }}</td>
                            <td>
                                <span class="inline-flex items-center rounded-full border border-slate-700/80 bg-slate-950/40 px-2.5 py-0.5 text-xs font-semibold text-slate-200">
                                    {{ $room->is_staff ? 'Персонал' : 'Клиенты' }}
                                </span>
                            </td>
                            <td>{{ $room->locker_amount }}</td>
                            <td class="text-slate-400">{{ $room->create_time?->format('Y-m-d H:i') }}</td>
                            <td class="text-right">
                                <form method="POST" action="{{ url('/admin/rooms/'.$room->id) }}" onsubmit="return confirm('Удалить комнату и все её шкафчики?')">
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
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                Комнат пока нет. Добавьте первую в форме выше.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
