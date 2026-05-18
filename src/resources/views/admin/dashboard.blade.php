@extends('admin.layout')

@section('title', 'Главная')
@section('subtitle', 'Обзор панели управления залом')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="admin-panel lg:col-span-2">
            <h2 class="text-lg font-semibold text-white">Добро пожаловать</h2>
            <p class="mt-2 text-slate-300">Вы вошли в админку Gym Admin. Управляйте комнатами, шкафчиками и услугами из бокового меню.</p>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ url('/admin/rooms') }}" class="admin-btn-primary">
                    Комнаты
                </a>
                <a href="{{ url('/admin/services') }}" class="inline-flex items-center rounded-xl border border-violet-800/50 bg-violet-950/30 px-4 py-2.5 text-sm font-semibold text-violet-100 transition hover:bg-violet-900/40">
                    Услуги
                </a>
            </div>
        </section>

        <section class="admin-panel">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Разделы</h2>
            <ul class="mt-4 space-y-2">
                <li>
                    <a href="{{ url('/admin/rooms') }}" class="flex items-center justify-between rounded-xl border border-slate-800/80 bg-slate-950/30 px-4 py-3 text-sm text-slate-200 transition hover:border-violet-700/50 hover:bg-violet-950/20">
                        <span>Комнаты и шкафчики</span>
                        <span class="text-violet-400">→</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/admin/services') }}" class="flex items-center justify-between rounded-xl border border-slate-800/80 bg-slate-950/30 px-4 py-3 text-sm text-slate-200 transition hover:border-violet-700/50 hover:bg-violet-950/20">
                        <span>Услуги и абонементы</span>
                        <span class="text-violet-400">→</span>
                    </a>
                </li>
            </ul>
        </section>
    </div>
@endsection
