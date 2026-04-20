@extends('admin.layout')

@section('title', 'Комнаты')
@section('subtitle', 'Управление раздевалками и шкафчиками')

@section('content')
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            <div class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/40 p-6 shadow-xl shadow-black/20">
                <h2 class="text-lg font-semibold">Добавить комнату</h2>
                <p class="mt-1 text-sm text-slate-400">Создаст запись в <span class="font-mono">locker_rooms</span> и шкафчики в <span class="font-mono">locker_rooms_items</span>.</p>

                <form method="POST" action="{{ url('/admin/rooms') }}" class="mt-5 space-y-4">
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
                            placeholder="Например: Мужская 1"
                        >
                        @error('name')
                            <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <p class="block text-sm font-medium text-slate-200">Пол</p>
                        <div class="mt-2 flex items-center gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                                <input type="radio" name="sex" value="male" class="border-slate-800 bg-slate-950/30" {{ old('sex', 'male') === 'male' ? 'checked' : '' }}>
                                Мужская
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                                <input type="radio" name="sex" value="female" class="border-slate-800 bg-slate-950/30" {{ old('sex') === 'female' ? 'checked' : '' }}>
                                Женская
                            </label>
                        </div>
                        @error('sex')
                            <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-200">
                            <input type="checkbox" name="is_staff" value="1" class="rounded border-slate-800 bg-slate-950/30" {{ old('is_staff') ? 'checked' : '' }}>
                            Для персонала
                        </label>
                    </div>

                    <div>
                        <label for="locker_amount" class="block text-sm font-medium text-slate-200">Количество шкафчиков</label>
                        <input
                            id="locker_amount"
                            name="locker_amount"
                            type="number"
                            min="0"
                            required
                            value="{{ old('locker_amount', 0) }}"
                            class="mt-2 w-full rounded-xl border border-slate-800 bg-slate-950/30 px-4 py-3 text-slate-100 shadow-inner shadow-black/20 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30"
                        >
                        @error('locker_amount')
                            <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
                        @enderror
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
                <h2 class="text-lg font-semibold">Список комнат</h2>

                <div class="mt-5 overflow-x-auto rounded-xl border border-slate-800 bg-slate-950/20">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-950/40 text-slate-300">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">ID</th>
                                <th class="px-4 py-3 text-left font-medium">Название</th>
                                <th class="px-4 py-3 text-left font-medium">Пол</th>
                                <th class="px-4 py-3 text-left font-medium">Персонал</th>
                                <th class="px-4 py-3 text-left font-medium">Шкафчики</th>
                                <th class="px-4 py-3 text-left font-medium">Создано</th>
                                <th class="px-4 py-3 text-right font-medium">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse ($rooms as $room)
                                <tr class="odd:bg-slate-950/10 hover:bg-slate-950/30">
                                    <td class="px-4 py-3 text-slate-200">{{ $room->id }}</td>
                                    <td class="px-4 py-3 text-slate-200">{{ $room->name }}</td>
                                    <td class="px-4 py-3 text-slate-200">
                                        {{ $room->sex === 'female' ? 'Женская' : 'Мужская' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full border border-slate-800 bg-slate-950/20 px-2.5 py-1 text-xs font-semibold text-slate-200">
                                            {{ $room->is_staff ? 'Персонал' : 'Клиенты' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-200">{{ $room->locker_amount }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ $room->create_time?->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ url('/admin/rooms/'.$room->id) }}" onsubmit="return confirm('Удалить комнату и все её шкафчики?')">
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
                                    <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                                        Комнат пока нет. Добавь первую слева.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection

