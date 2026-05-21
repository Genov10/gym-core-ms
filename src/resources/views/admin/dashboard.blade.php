@extends('admin.layout')

@section('title', 'Главная')
@section('subtitle', 'Обзор панели управления залом')

@section('content')
    <div class="admin-cards">
        <section class="admin-panel">
            <h2>Разделы</h2>
            <a href="{{ url('/admin/rooms') }}" class="admin-link-card">
                <span>Раздевалки и шкафчики</span>
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
            <a href="{{ url('/admin/sales') }}" class="admin-link-card">
                <span>Статистика продаж</span>
                <span>→</span>
            </a>
            <a href="{{ url('/admin/visits') }}" class="admin-link-card">
                <span>Посещения</span>
                <span>→</span>
            </a>
            <a href="{{ url('/admin/broadcasts') }}" class="admin-link-card">
                <span>Рассылки</span>
                <span>→</span>
            </a>
        </section>
    </div>
@endsection
