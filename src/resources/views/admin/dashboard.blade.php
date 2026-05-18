@extends('admin.layout')

@section('title', 'Главная')
@section('subtitle', 'Обзор панели управления залом')

@section('content')
    <div class="admin-cards">
        <section class="admin-panel">
            <h2>Добро пожаловать</h2>
            <p class="hint" style="margin-top:0.5rem;color:#cbd5e1">
                Вы вошли в админку Gym Admin. Управляйте комнатами, шкафчиками и услугами из бокового меню.
            </p>
            <div class="admin-actions">
                <a href="{{ url('/admin/rooms') }}" class="admin-btn admin-btn--primary">Комнаты</a>
                <a href="{{ url('/admin/services') }}" class="admin-btn admin-btn--ghost">Услуги</a>
            </div>
        </section>

        <section class="admin-panel">
            <h2>Разделы</h2>
            <a href="{{ url('/admin/rooms') }}" class="admin-link-card">
                <span>Комнаты и шкафчики</span>
                <span>→</span>
            </a>
            <a href="{{ url('/admin/services') }}" class="admin-link-card">
                <span>Услуги и абонементы</span>
                <span>→</span>
            </a>
            <a href="{{ url('/admin/customers') }}" class="admin-link-card">
                <span>Клиенты</span>
                <span>→</span>
            </a>
        </section>
    </div>
@endsection
