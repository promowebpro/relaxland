@extends('layouts.public')

@section('title', 'Страница не найдена')
@section('robots', 'noindex, nofollow')

@section('content')
    <section class="error-page site-container">
        <x-breadcrumbs :items="[['label' => 'Главная', 'url' => route('home')], ['label' => 'Ошибка 404']]" />
        <div class="error-page__content">
            <h1 class="sr-only">Ошибка 404. Такой страницы нет</h1>
            <img class="error-page__art" src="{{ asset('assets/design/error-404-art.png') }}" width="860" height="460" alt="404 — лось отдыхает в тени цифр">
            <p>Возможно, запрашиваемая вами страница была<br class="error-page__break"> перенесена или удалена</p>
            <x-button :href="route('home')" variant="primary">На главную</x-button>
        </div>
    </section>
@endsection
