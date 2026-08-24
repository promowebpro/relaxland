@extends('layouts.public')

@section('title', 'Контакты')
@section('description', 'Контакты RelaxLand Можайский, адреса и способы построить маршрут.')

@php
    $primaryPhone = $settings['contacts.phone'];
    $salesPhone = $settings['contacts.sales_phone'];
    $socials = [
        'Telegram' => $settings['social.telegram'],
        'VK' => $settings['social.vk'],
        'WhatsApp' => $settings['social.whatsapp'],
        'MAX' => $settings['social.max'],
    ];
    $routes = [
        'Яндекс Карты' => $settings['routes.yandex'],
        'Google Maps' => $settings['routes.google'],
        '2GIS' => $settings['routes.two_gis'],
    ];
@endphp

@section('content')
    <section class="page-intro page-intro--contacts">
        <div class="site-container">
            <x-breadcrumbs :items="$seo->breadcrumbs" />
            <h1>Контакты</h1>
        </div>
    </section>

    <section class="contact-overview section-spacing">
        <div class="site-container contact-overview__grid">
            <div class="contact-socials">
                <span class="eyebrow">Социальные сети</span>
                <div class="contact-socials__links">
                    @forelse ($socials as $label => $url)
                        @if ($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $label }}</a>
                        @endif
                    @empty
                    @endforelse
                    @if (! collect($socials)->filter()->count())
                        <span class="empty-value">Не указаны</span>
                    @endif
                </div>
            </div>

            <div class="contact-details">
                <article class="contact-card">
                    <span class="contact-card__number">01</span>
                    <h2>Связаться</h2>
                    @if ($salesPhone)
                        <a class="contact-card__value" href="{{ \App\Domain\Settings\SiteSettings::phoneHref($salesPhone) }}">{{ $salesPhone }}</a>
                        <span>Отдел продаж</span>
                    @endif
                    @if ($primaryPhone)
                        <a class="contact-card__value" href="{{ \App\Domain\Settings\SiteSettings::phoneHref($primaryPhone) }}">{{ $primaryPhone }}</a>
                    @endif
                    @if ($settings['contacts.email'])
                        <a href="mailto:{{ $settings['contacts.email'] }}">{{ $settings['contacts.email'] }}</a>
                    @endif
                    @if (! $salesPhone && ! $primaryPhone && ! $settings['contacts.email'])
                        <span class="empty-value">Контакты пока не опубликованы</span>
                    @endif
                </article>

                <article class="contact-card">
                    <span class="contact-card__number">02</span>
                    <h2>Посёлок</h2>
                    <p>{{ $settings['contacts.village_address'] ?: 'Адрес пока не опубликован' }}</p>
                    @if ($settings['contacts.working_hours'])
                        <span>{{ $settings['contacts.working_hours'] }}</span>
                    @endif
                </article>

                <article class="contact-card">
                    <span class="contact-card__number">03</span>
                    <h2>Центральный офис</h2>
                    <p>{{ $settings['contacts.office_address'] ?: 'Адрес пока не опубликован' }}</p>
                    @if ($settings['contacts.support_email'])
                        <a href="mailto:{{ $settings['contacts.support_email'] }}">{{ $settings['contacts.support_email'] }}</a>
                    @endif
                </article>
            </div>

            @if ($settings['contacts.notice'])
                <aside class="contact-notice">
                    <span aria-hidden="true">!</span>
                    <p>{{ $settings['contacts.notice'] }}</p>
                </aside>
            @endif
        </div>
    </section>

    <section class="visit-invite" style="--visit-image: url('{{ asset('assets/design/home-atmosphere.webp') }}')">
        <div class="site-container visit-invite__inner">
            <span class="eyebrow">Время увидеть всё своими глазами</span>
            <h2>Приезжайте знакомиться с RelaxLand</h2>
            <p>Постройте удобный маршрут и свяжитесь с нами перед поездкой.</p>
        </div>
    </section>

    <section class="location-section section-spacing">
        <div class="site-container">
            <div class="location-section__heading">
                <div>
                    <span class="eyebrow">Как добраться</span>
                    <h2>RelaxLand Можайский</h2>
                </div>
                @if ($settings['contacts.travel_time'])
                    <p class="travel-time">{{ $settings['contacts.travel_time'] }}</p>
                @endif
            </div>

            <div class="map-card" aria-label="Информация о расположении посёлка">
                <div class="map-card__visual" style="--map-image: url('{{ asset('assets/design/contacts-map.webp') }}')" aria-hidden="true">
                    <div class="map-card__grid"></div>
                    <span class="map-card__marker"></span>
                    <span class="map-card__label">RelaxLand</span>
                </div>
                <div class="map-card__details">
                    <h3>Координаты</h3>
                    @if ($settings['contacts.village_latitude'] && $settings['contacts.village_longitude'])
                        <p>{{ $settings['contacts.village_latitude'] }}, {{ $settings['contacts.village_longitude'] }}</p>
                    @else
                        <p class="empty-value">Координаты пока не опубликованы</p>
                    @endif

                    <div class="route-links" aria-label="Построить маршрут">
                        @foreach ($routes as $label => $url)
                            @if ($url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $label }}</a>
                            @endif
                        @endforeach
                        @if (! collect($routes)->filter()->count())
                            <span class="empty-value">Ссылки на маршруты пока не добавлены</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="contact-cta">
                <div>
                    <span class="eyebrow">Запланировать визит</span>
                    <h2>Запишитесь на знакомство с посёлком</h2>
                </div>
                <x-button
                    href="#lead-form"
                    variant="accent"
                    data-lead-modal-trigger
                    data-lead-source="contacts"
                    data-lead-form-type="visit"
                    data-lead-heading="Записаться на знакомство"
                >
                    Записаться
                </x-button>
            </div>
        </div>
    </section>
@endsection
