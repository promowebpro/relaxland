@php
    $phone = $settings['contacts.sales_phone'] ?: $settings['contacts.phone'];
    $phoneHref = \App\Domain\Settings\SiteSettings::phoneHref($phone);
    $ogPath = $home->og_image ?: $home->hero_image;
    $ogImage = $ogPath ? Storage::disk('public')->url($ogPath) : '';
    $routeLinks = collect([
        'Яндекс Карты' => $settings['routes.yandex'],
        'Google Maps' => $settings['routes.google'],
        '2GIS' => $settings['routes.two_gis'],
    ])->filter();
    $benefitImages = ['assets/design/home-forest.webp', 'assets/design/home-water.webp', 'assets/design/home-comfort.webp'];
    $scenarioImages = ['assets/design/home-life.webp', 'assets/design/home-bike.webp', 'assets/design/home-winter.webp'];
@endphp

@extends('layouts.public')

@section('title', $home->hero_title)
@section('meta_title', $home->seo_title ?: $home->hero_title)
@section('description', $home->seo_description ?: $home->hero_description)
@section('canonical', route('home'))
@section('og_image', $ogImage)
@section('body_class', 'home-page')

@section('content')
    <section class="home-hero" aria-labelledby="home-hero-title">
        <div class="home-hero__media">
            <x-responsive-image
                :path="$home->hero_image"
                :mobile-path="$home->hero_image_mobile"
                :alt="$home->hero_image_alt ?: ''"
                :eager="true"
                fallback="assets/design/home-hero.webp"
            />
        </div>
        <div class="site-container home-hero__content">
            <h1 class="sr-only" id="home-hero-title">{{ $home->hero_title }}</h1>
            <p class="home-hero__wordmark" aria-hidden="true">РелаксЛэнд Можайский</p>
        </div>
    </section>

    <section class="home-section home-intro" aria-labelledby="home-intro-title">
        <div class="site-container">
            <div class="home-heading home-heading--statement">
                <h2 id="home-intro-title">{{ $home->intro_title }}</h2>
                @if ($home->intro_text)<p>{{ $home->intro_text }}</p>@endif
            </div>
            @if ($home->benefits)
                <div class="home-benefits">
                    @foreach ($home->benefits as $index => $benefit)
                        <article class="home-benefit">
                            <img src="{{ asset($benefitImages[$index % count($benefitImages)]) }}" alt="" loading="lazy">
                            <h3>{{ $benefit['title'] ?? '' }}</h3>
                            @if ($benefit['text'] ?? null)<p>{{ $benefit['text'] }}</p>@endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="home-section home-atmosphere" aria-labelledby="home-atmosphere-title">
        <div class="site-container home-atmosphere__grid">
            <div class="home-atmosphere__copy">
                <p class="home-kicker">Тишина становится частью дня</p>
                <h2 id="home-atmosphere-title">{{ $home->atmosphere_title }}</h2>
                @if ($home->atmosphere_text)<p>{{ $home->atmosphere_text }}</p>@endif
            </div>
        </div>
    </section>

    @if ($home->life_scenarios)
        <section class="home-section home-scenarios" aria-labelledby="home-scenarios-title">
            <div class="site-container">
                <div class="home-heading">
                    <p class="home-kicker">Сценарии жизни</p>
                    <h2 id="home-scenarios-title">У каждого дня — свой ритм</h2>
                </div>
                <div class="home-scenarios__grid">
                    @foreach ($home->life_scenarios as $scenario)
                        <article class="home-scenario">
                            <x-responsive-image
                                class="home-scenario__media"
                                :path="$scenario['image'] ?? null"
                                :mobile-path="$scenario['image_mobile'] ?? null"
                                :alt="$scenario['image_alt'] ?? ''"
                                :fallback="$scenarioImages[$loop->index % count($scenarioImages)]"
                            />
                            <div class="home-scenario__copy">
                                @if ($scenario['label'] ?? null)<span>{{ $scenario['label'] }}</span>@endif
                                <h3>{{ $scenario['title'] ?? '' }}</h3>
                                @if ($scenario['text'] ?? null)<p>{{ $scenario['text'] }}</p>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="home-section home-care" aria-labelledby="home-care-title">
        <img class="home-care__background" src="{{ asset('assets/design/home-care.webp') }}" alt="" loading="lazy">
        <div class="site-container home-care__grid">
            <div class="home-heading">
                <p class="home-kicker">Сервис</p>
                <h2 id="home-care-title">{{ $home->care_title }}</h2>
                @if ($home->care_text)<p>{{ $home->care_text }}</p>@endif
            </div>
            <div class="home-care__list">
                @foreach ($home->care_items ?? [] as $index => $item)
                    <article>
                        <span>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h3>{{ $item['title'] ?? '' }}</h3>
                            @if ($item['text'] ?? null)<p>{{ $item['text'] }}</p>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    @if ($home->seasons)
        <section class="home-section home-seasons" aria-labelledby="home-seasons-title" data-season-tabs>
            <div class="site-container">
                <div class="home-heading home-heading--split">
                    <p class="home-kicker">Времена года</p>
                    <div>
                        <h2 id="home-seasons-title">{{ $home->seasons_title }}</h2>
                        @if ($home->seasons_text)<p>{{ $home->seasons_text }}</p>@endif
                    </div>
                </div>
                <div class="season-tabs" role="tablist" aria-label="Выберите сезон">
                    @foreach ($home->seasons as $index => $season)
                        <button
                            id="season-tab-{{ $index }}"
                            type="button"
                            role="tab"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                            aria-controls="season-panel-{{ $index }}"
                            tabindex="{{ $index === 0 ? '0' : '-1' }}"
                            data-season-tab
                        >{{ $season['label'] ?? $season['title'] ?? 'Сезон' }}</button>
                    @endforeach
                </div>
                @foreach ($home->seasons as $index => $season)
                    <article
                        id="season-panel-{{ $index }}"
                        class="season-panel"
                        role="tabpanel"
                        aria-labelledby="season-tab-{{ $index }}"
                        @if ($index !== 0) hidden @endif
                        data-season-panel
                    >
                        <x-responsive-image
                            class="season-panel__media"
                            :path="$season['image'] ?? null"
                            :mobile-path="$season['image_mobile'] ?? null"
                            :alt="$season['image_alt'] ?? ''"
                            :fallback="$index === 0 ? 'assets/design/home-summer.webp' : 'assets/design/home-winter.webp'"
                        />
                        <div class="season-panel__copy">
                            <span>{{ $season['label'] ?? '' }}</span>
                            <h3>{{ $season['title'] ?? '' }}</h3>
                            @if ($season['text'] ?? null)<p>{{ $season['text'] }}</p>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="home-callout">
        <div class="site-container home-callout__inner">
            <h2>{{ $home->cta_title }}</h2>
            @if ($home->cta_text)<p>{{ $home->cta_text }}</p>@endif
            <x-button
                href="#lead-form"
                variant="accent"
                data-lead-modal-trigger
                data-lead-source="home"
                data-lead-form-type="visit"
                data-lead-heading="Выбрать время для визита"
            >Выбрать время</x-button>
        </div>
    </section>

    <section class="home-section home-genplan" aria-labelledby="home-genplan-title">
        <div class="site-container">
            <div class="home-heading home-heading--split">
                <p class="home-kicker">Территория</p>
                <div>
                    <h2 id="home-genplan-title">{{ $home->genplan_title }}</h2>
                    @if ($home->genplan_text)<p>{{ $home->genplan_text }}</p>@endif
                </div>
            </div>
            <div class="home-genplan__frame">
                <x-responsive-image :path="$home->genplan_image" :alt="$home->genplan_image_alt ?: ''" fallback="assets/design/home-genplan.webp" />
                <x-button :disabled="true" variant="outline">Интерактивный генплан — скоро</x-button>
            </div>
        </div>
    </section>

    <section class="home-section home-purchase" aria-labelledby="home-purchase-title">
        <div class="site-container">
            <div class="home-heading">
                <p class="home-kicker">Путь к участку</p>
                <h2 id="home-purchase-title">{{ $home->purchase_title }}</h2>
                @if ($home->purchase_text)<p>{{ $home->purchase_text }}</p>@endif
            </div>
            <div class="home-purchase__grid">
                @foreach ($home->purchase_options ?? [] as $index => $option)
                    <article>
                        <span>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3>{{ $option['title'] ?? '' }}</h3>
                        @if ($option['text'] ?? null)<p>{{ $option['text'] }}</p>@endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-section home-stories" aria-labelledby="home-stories-title">
        <div class="site-container">
            <div class="home-heading home-heading--split">
                <p class="home-kicker">Люди и моменты</p>
                <div>
                    <h2 id="home-stories-title">{{ $home->stories_title }}</h2>
                    @if ($home->stories_text)<p>{{ $home->stories_text }}</p>@endif
                </div>
            </div>
            @if ($stories->isNotEmpty())
                <div class="home-stories__grid">
                    @foreach ($stories as $story)
                        <article class="story-card">
                            @if ($story->video)
                                <video controls preload="metadata" @if ($story->image) poster="{{ Storage::disk('public')->url($story->image) }}" @endif aria-label="{{ $story->title }}">
                                    <source src="{{ Storage::disk('public')->url($story->video) }}">
                                    Ваш браузер не поддерживает видео.
                                </video>
                            @else
                                <x-responsive-image class="story-card__media" :path="$story->image" :alt="$story->image_alt ?: ''" fallback="assets/design/home-story.webp" />
                            @endif
                            <div class="story-card__copy">
                                <h3>{{ $story->title }}</h3>
                                @if ($story->excerpt)<p>{{ $story->excerpt }}</p>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="home-empty">Первые истории скоро появятся здесь.</p>
            @endif
        </div>
    </section>

    <section class="home-visit" id="visit" aria-labelledby="home-visit-title">
        <div class="site-container home-visit__grid">
            <div>
                <p class="home-kicker">Визит</p>
                <h2 id="home-visit-title">{{ $home->visit_title }}</h2>
                @if ($home->visit_text)<p>{{ $home->visit_text }}</p>@endif
            </div>
            <div class="home-visit__details">
                @if ($settings['contacts.village_address'])
                    <p><span>Адрес посёлка</span>{{ $settings['contacts.village_address'] }}</p>
                @endif
                @if ($settings['contacts.working_hours'])
                    <p><span>Режим работы</span>{{ $settings['contacts.working_hours'] }}</p>
                @endif
                <div class="home-visit__actions">
                    <x-button
                        href="#lead-form"
                        variant="accent"
                        data-lead-modal-trigger
                        data-lead-source="home"
                        data-lead-form-type="visit"
                        data-lead-heading="Записаться на экскурсию"
                    >Записаться на экскурсию</x-button>
                    @if ($phoneHref)
                        <x-button :href="$phoneHref" variant="outline">{{ $phone }}</x-button>
                    @endif
                    <x-button :href="route('contacts')" variant="outline">Контакты и маршрут</x-button>
                </div>
                @if ($routeLinks->isNotEmpty())
                    <nav class="home-visit__routes" aria-label="Построить маршрут">
                        @foreach ($routeLinks as $label => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $label }} <span aria-hidden="true">↗</span></a>
                        @endforeach
                    </nav>
                @endif
            </div>
        </div>
    </section>

    <section class="home-section home-developer" aria-labelledby="home-developer-title">
        <div class="site-container home-developer__grid">
            <div>
                <p class="home-kicker">О проекте</p>
                <h2 id="home-developer-title">{{ $home->developer_title }}</h2>
                @if ($home->developer_text)<p>{{ $home->developer_text }}</p>@endif
            </div>
            <x-responsive-image class="home-developer__media" :path="$home->developer_image" :alt="$home->developer_image_alt ?: ''" fallback="assets/design/home-developer.webp" />
        </div>
    </section>

    <section class="home-section home-blog" aria-labelledby="home-blog-title">
        <div class="site-container">
            <div class="home-heading home-heading--with-link">
                <div>
                    <p class="home-kicker">Блог</p>
                    <h2 id="home-blog-title">{{ $home->blog_title }}</h2>
                    @if ($home->blog_text)<p>{{ $home->blog_text }}</p>@endif
                </div>
                <a href="{{ route('blog.index') }}">Все материалы <span aria-hidden="true">↗</span></a>
            </div>
            @if ($blogPosts->isNotEmpty())
                <div class="blog-grid home-blog__grid">
                    @foreach ($blogPosts as $post)
                        <x-blog-card :post="$post" />
                    @endforeach
                </div>
            @else
                <p class="home-empty">Опубликованные материалы скоро появятся.</p>
            @endif
        </div>
    </section>
@endsection
