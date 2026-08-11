@extends('layouts.public')

@section('title', 'Спасибо')

@section('content')
    <section class="status-page status-page--success">
        <div class="site-container">
            <x-breadcrumbs :items="[
                ['label' => 'Главная', 'url' => route('home')],
                ['label' => 'Спасибо'],
            ]" />
            <div class="status-page__content">
                <span class="status-page__code">Готово</span>
                <h1>Спасибо!</h1>
                <p>Ваша заявка успешно отправлена. Мы свяжемся с вами в ближайшее время.</p>
                <x-button :href="route('home')" variant="accent">На главную</x-button>
            </div>
        </div>
    </section>
@endsection
