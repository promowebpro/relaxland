@extends('layouts.public')

@section('title', 'Страница не найдена')

@section('content')
    <section class="status-page status-page--error">
        <div class="site-container status-page__error-layout">
            <div class="error-number" aria-hidden="true">404</div>
            <div class="status-page__content">
                <span class="status-page__code">Ошибка 404</span>
                <h1>Такой страницы нет</h1>
                <p>Возможно, она была перемещена или адрес введён неверно.</p>
                <x-button :href="route('home')" variant="primary">На главную</x-button>
            </div>
        </div>
    </section>
@endsection
