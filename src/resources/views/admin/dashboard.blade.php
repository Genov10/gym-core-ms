@extends('admin.layout')

@section('title', 'Admin')
@section('subtitle', 'Главная админки')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/40 p-6 shadow-xl shadow-black/20">
            <h1 class="text-2xl font-semibold tracking-tight">Админка</h1>
            <p class="mt-3 text-slate-200">позравляю, ты залогинен</p>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <a href="{{ url('/admin/rooms') }}" class="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
                    Перейти к комнатам
                </a>
                <a href="{{ url('/admin/services') }}" class="inline-flex items-center rounded-xl border border-slate-800 bg-slate-900/40 px-4 py-2 text-sm font-semibold text-slate-100 hover:bg-slate-900/60">
                    Перейти к услугам
                </a>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6 shadow-xl shadow-black/20">
            <div class="text-sm font-semibold text-slate-200">Быстрые ссылки</div>
            <div class="mt-4 space-y-2 text-sm">
                <a href="{{ url('/admin/rooms') }}" class="block rounded-xl border border-slate-800 bg-slate-950/20 px-4 py-3 hover:bg-slate-950/40">
                    Комнаты
                </a>
                <a href="{{ url('/admin/services') }}" class="block rounded-xl border border-slate-800 bg-slate-950/20 px-4 py-3 hover:bg-slate-950/40">
                    Услуги
                </a>
            </div>
        </div>
    </div>
@endsection

