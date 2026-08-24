@extends('layouts.public')

@section('title', 'О нас')
@section('description', 'RelaxLand Можайский — посёлок, которому доверяют заботу о загородной жизни.')

@section('content')
    <section class="page-intro">
        <div class="site-container">
            <x-breadcrumbs :items="$seo->breadcrumbs" />
            <h1>О нас</h1>
        </div>
    </section>

    <section class="about-trust site-container" aria-labelledby="about-trust-title">
        <div class="about-trust__copy">
            <p class="section-label">Нам доверяют</p>
            <h2 id="about-trust-title">Здесь начинается жизнь, которую хочется проживать не спеша</h2>
            <p>{{ $home->developer_text ?: 'Мы создаём не просто участки, а цельную среду — с лесом рядом, понятным сервисом и вниманием к повседневным деталям.' }}</p>
        </div>
        <div class="about-collage" aria-label="Атмосфера RelaxLand">
            <img src="{{ asset('assets/design/about-01.webp') }}" alt="Девушка отдыхает в лесу" loading="eager">
            <img src="{{ asset('assets/design/about-02.webp') }}" alt="Стрекоза среди трав" loading="lazy">
            <img src="{{ asset('assets/design/about-03.webp') }}" alt="Летний ужин на природе" loading="lazy">
        </div>
    </section>

    <section class="about-care" aria-labelledby="about-care-title">
        <div class="site-container about-care__inner">
            <p class="section-label">Наш подход</p>
            <div>
                <h2 id="about-care-title">Забота о клиенте</h2>
                <p>Команда RelaxLand сопровождает знакомство с проектом, помогает увидеть территорию своими глазами и остаётся рядом после выбора участка.</p>
            </div>
        </div>
    </section>

    <section class="about-visit site-container" aria-labelledby="about-visit-title">
        <div class="about-map" style="--about-map: url('{{ asset('assets/design/about-map.webp') }}')">
            <div class="about-map__card">
                <p class="section-label">Познакомимся?</p>
                <h2 id="about-visit-title">Приезжайте в RelaxLand</h2>
                <p>{{ $settings['contacts.village_address'] ?: 'Можайский городской округ' }}</p>
                <div class="about-map__actions">
                    <x-button
                        href="#lead-form"
                        variant="primary"
                        data-lead-modal-trigger
                        data-lead-source="about"
                        data-lead-form-type="visit"
                        data-lead-heading="Записаться на знакомство"
                    >Записаться</x-button>
                    <x-button :href="route('contacts')" variant="outline">Как добраться</x-button>
                </div>
            </div>
        </div>
    </section>

    <section class="about-numbers site-container" aria-label="Опыт команды">
        <article><strong>15+</strong><span>лет в девелопменте</span></article>
        <article><strong>24/7</strong><span>служба заботы</span></article>
        <article><strong>1</strong><span>час от Москвы</span></article>
        <article><strong>4</strong><span>сезона для жизни</span></article>
    </section>
@endsection
