@extends('layouts.public')

@section('title', 'Спасибо')

@section('content')
    <section class="status-page status-page--success">
        <div class="site-container status-page__center">
            <div class="status-page__content">
                <h1>Спасибо!</h1>
                <p>Ваша заявка успешно отправлена.<br>Мы свяжемся с вами в ближайшее время.</p>
                <x-button :href="route('home')" variant="primary">Вернуться на главную</x-button>
            </div>
        </div>
    </section>
@endsection
