@extends('admin.layout')

@section('title', 'Рассылки')
@section('subtitle', 'Массовое сообщение клиентам в Telegram')

@section('content')
    <section class="admin-panel">
        <h2>Новая рассылка</h2>
        <p class="hint">
            Сообщение уйдёт на <code>notification-by-admin</code> всем клиентам с <code>telegram_id</code> в таблице <code>customers</code>.
            Получателей: <strong>{{ $recipientsCount }}</strong>
        </p>

        <form method="POST" action="{{ url('/admin/broadcasts') }}" class="admin-broadcast-form" onsubmit="return confirm('Отправить рассылку {{ $recipientsCount }} получателям?')">
            @csrf

            <div class="admin-field">
                <label for="message">Текст сообщения</label>
                <textarea
                    id="message"
                    name="message"
                    rows="8"
                    required
                    class="admin-input admin-textarea"
                    placeholder="Введите текст для клиентов…"
                >{{ old('message') }}</textarea>
                @error('message')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="admin-btn admin-btn--primary" {{ $recipientsCount === 0 ? 'disabled' : '' }}>
                Отправить
            </button>
        </form>
    </section>
@endsection
