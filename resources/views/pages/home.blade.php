@php
    $phone = $settings['contacts.sales_phone'] ?: $settings['contacts.phone'];
    $phoneHref = \App\Domain\Settings\SiteSettings::phoneHref($phone);
    $ogPath = $home->og_image ?: $home->hero_image;
    $ogImage = $ogPath ? Storage::disk('public')->url($ogPath) : '';
    $rhythmPresets = [
        ['slot' => 'morning', 'phase' => 'opening', 'label' => 'Утро', 'title' => 'Пробежка по лесу, воркаут и йога на траве', 'image_alt' => 'Пробежка по лесу', 'fallback' => 'assets/design/home-target-runners.webp', 'animal' => 'assets/design/home-rhythm-rabbit.svg'],
        ['slot' => 'coworking', 'phase' => 'opening', 'label' => 'День', 'title' => 'Работа из коворкинга, с оптоволоконным интернетом', 'image_alt' => 'Работа в коворкинге', 'fallback' => 'assets/design/home-target-coworking.webp', 'animal' => 'assets/design/home-rhythm-hedgehog.svg'],
        ['slot' => 'evening', 'phase' => 'middle', 'label' => 'Вечер', 'title' => 'Гулять с домашним питомцем', 'image_alt' => 'Вечерняя прогулка с домашним питомцем', 'fallback' => 'assets/design/home-rhythm-evening.webp'],
        ['slot' => 'walk', 'phase' => 'closing', 'label' => '', 'title' => 'Прогулка по лесу', 'image_alt' => 'Прогулка по вечернему лесу', 'fallback' => 'assets/design/home-rhythm-walk.webp'],
        ['slot' => 'dog-run', 'phase' => 'middle', 'label' => 'Выходные', 'title' => 'Отдых с домашним питомцем', 'image_alt' => 'Собака на лесной тропе', 'fallback' => 'assets/design/home-rhythm-dog-run.webp'],
        ['slot' => 'sauna', 'phase' => 'closing', 'label' => '', 'title' => 'Баня', 'image_alt' => 'Банный веник в парной', 'fallback' => 'assets/design/home-rhythm-sauna.webp', 'animal' => 'assets/design/home-rhythm-moose.svg'],
        ['slot' => 'fishing', 'phase' => 'closing', 'label' => '', 'title' => 'Рыбалка', 'image_alt' => 'Рыбалка на природе', 'fallback' => 'assets/design/home-rhythm-fishing.webp', 'animal' => 'assets/design/home-rhythm-dog.svg'],
    ];
    $rhythmContent = collect($home->life_scenarios ?? [])->values();
    $rhythmSlides = collect($rhythmPresets)->map(function (array $preset, int $index) use ($rhythmContent): array {
        $content = collect($rhythmContent->get($index, []))
            ->filter(fn (mixed $value): bool => is_string($value) && trim($value) !== '')
            ->all();

        return array_merge($preset, $content);
    });
@endphp

@extends('layouts.public')

@section('title', $home->hero_title)
@section('meta_title', $home->seo_title ?: $home->hero_title)
@section('description', $home->seo_description ?: ($home->hero_description ?: ''))
@section('canonical', route('home'))
@section('og_image', $ogImage)
@section('body_class', 'home-page home-page--design')

@section('content')
    <section class="home-hero" aria-labelledby="home-hero-title">
        <div class="home-hero__media">
            <x-responsive-image
                :path="$home->hero_image"
                :mobile-path="$home->hero_image_mobile"
                :alt="$home->hero_image_alt ?: ''"
                :eager="true"
                fallback="assets/design/home-target-foliage.webp"
            />
        </div>
        <div class="site-container home-hero__content">
            <h1 class="sr-only" id="home-hero-title">{{ $home->hero_title }}</h1>
            <p class="home-hero__wordmark" aria-hidden="true">РелаксЛэнд Можайский</p>
        </div>
    </section>

    <section class="home-section home-intro" aria-labelledby="home-intro-title" data-home-intro-motion>
        <div class="site-container">
            <h2 class="home-design-heading" id="home-intro-title">
                <span>Релакс Лэнд Можайский коттеджный</span>
                <em>посёлок с преимуществами города<br>для жизни круглый год</em>
            </h2>
            <div class="home-intro-collage">
                <figure class="home-intro-card home-intro-card--moose">
                    <img src="{{ asset('assets/design/home-target-moose.jpg') }}" alt="Лось в лесу" loading="lazy">
                    <figcaption>Лес, в котором живут лоси и зайцы</figcaption>
                </figure>
                <figure class="home-intro-card home-intro-card--water">
                    <img src="{{ asset('assets/design/home-water.webp') }}" alt="Водоём рядом с посёлком" loading="lazy">
                    <figcaption>Свой пляж<br>у водохранилища</figcaption>
                </figure>
                <figure class="home-intro-card home-intro-card--care">
                    <img src="{{ asset('assets/design/home-target-bark.jpg') }}" alt="Прикосновение к коре дерева" loading="lazy">
                    <figcaption>Служба заботы 24/7</figcaption>
                </figure>
                <figure class="home-intro-card home-intro-card--comfort">
                    <img src="{{ asset('assets/design/home-target-textile.jpg') }}" alt="Уютный интерьер" loading="lazy">
                    <figcaption>Бизнес-класс по цене комфорта<br>от 300 тыс. ₽/сотку*</figcaption>
                    <small>*Вместо 500 тыс. ₽/сотку при средней цене участка на этой локации около водохранилища</small>
                </figure>
            </div>
            <div class="sr-only" aria-label="Контент преимуществ из административной панели">
                @foreach ($home->benefits ?? [] as $benefit)
                    <span>{{ $benefit['title'] ?? '' }} {{ $benefit['text'] ?? '' }}</span>
                @endforeach
            </div>
        </div>
    </section>

    <section class="home-quote home-quote--full" aria-label="Цитата Мацуо Басё" data-home-motion>
        <p class="home-quote__mark">松尾芭蕉</p>
        <blockquote>«Бабочки полет<br>будит тихую поляну<br>в солнечных лучах»</blockquote>
        <cite>Мацуо Басё</cite>
    </section>

    <section class="home-section home-rhythm" aria-labelledby="home-rhythm-title" data-home-rhythm>
        <div class="home-rhythm__sticky">
            <div class="site-container">
                <h2 class="home-design-heading" id="home-rhythm-title">
                    <span>Все, что есть в клубном доме,<br>есть и здесь, но</span>
                    <em>в экологически<br>чистой среде</em>
                </h2>
            </div>
            <div class="home-rhythm__scroller" data-home-rhythm-scroll tabindex="0" aria-label="Сценарии жизни в посёлке. Прокручивайте колесо мыши, чтобы двигаться по горизонтали">
                <div class="home-rhythm__track">
                    @foreach ($rhythmSlides as $slide)
                        <article
                            class="home-rhythm-card home-rhythm-card--{{ $slide['slot'] }}"
                            data-rhythm-phase="{{ $slide['phase'] }}"
                        >
                            @if (! empty($slide['label']))
                                <p class="home-rhythm-card__time">{{ $slide['label'] }}</p>
                            @endif
                            <div class="home-rhythm-card__visual">
                                <figure>
                                    <x-responsive-image
                                        class="home-rhythm-card__media"
                                        :path="$slide['image'] ?? null"
                                        :mobile-path="$slide['image_mobile'] ?? null"
                                        :alt="$slide['image_alt']"
                                        :fallback="$slide['fallback']"
                                    />
                                    <figcaption>{{ $slide['title'] }}</figcaption>
                                </figure>
                                @if (! empty($slide['animal']))
                                    <img
                                        class="home-rhythm-card__animal home-rhythm-card__animal--{{ $slide['slot'] }}"
                                        src="{{ asset($slide['animal']) }}"
                                        alt=""
                                        decoding="async"
                                        aria-hidden="true"
                                    >
                                @endif
                                @if (! empty($slide['text']))
                                    <span class="sr-only">{{ $slide['text'] }}</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
            <div class="site-container home-rhythm__controls">
                <span>Прокручивайте колесо мыши</span>
                <span class="home-rhythm__progress" aria-hidden="true"><i data-home-rhythm-progress></i></span>
                <div class="home-rhythm__buttons">
                    <button type="button" data-home-rhythm-prev aria-label="Предыдущая сцена">←</button>
                    <button type="button" data-home-rhythm-next aria-label="Следующая сцена">→</button>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section home-quote-wrap">
        <div class="site-container">
            <div class="home-quote" aria-label="Цитата Мацуо Басё" data-home-motion>
                <p class="home-quote__mark">松尾芭蕉</p>
                <blockquote>«Как тихо…<br>звон цикады<br>пронзает скалу»</blockquote>
                <cite>Мацуо Басё</cite>
            </div>
        </div>
    </section>

    @php
        $careSlides = collect($home->care_items ?: \App\Models\HomePage::defaultContent()['care_items'])->take(5)->values();
        $carePresetImages = [
            'service' => 'assets/design/home-care-service.webp',
            'utilities' => 'assets/design/home-care-utilities.webp',
            'family' => 'assets/design/home-care-family.webp',
            'sport' => 'assets/design/home-care-sport.webp',
            'security' => 'assets/design/home-care-security.webp',
        ];
    @endphp

    <section class="home-section home-care" aria-labelledby="home-care-title" data-home-motion>
        <div class="site-container">
            <div class="home-care__heading">
                <h2 class="home-design-heading" id="home-care-title">
                    <span>Поселок</span> <em>удивляет городским<br>комфортом</em> <span>посреди природы</span>
                </h2>
                <p>от коворкинга с оптоволоконным интернетом до круглосуточной службы заботы</p>
            </div>
            <div class="home-care__slider" data-home-care>
                <div class="home-care__slides" aria-live="polite">
                    @foreach ($careSlides as $index => $slide)
                        @php
                            $slideNumber = $index + 1;
                            $presetImage = $carePresetImages[$slide['image_preset'] ?? ''] ?? $carePresetImages['service'];
                            $listItems = collect(preg_split('/\r\n|\r|\n/', (string) ($slide['text'] ?? '')))
                                ->map(fn (string $item): string => trim($item))
                                ->filter();
                        @endphp
                        <article
                            @class(['home-care__slide', 'is-active' => $index === 0])
                            id="home-care-slide-{{ $slideNumber }}"
                            data-home-care-slide
                            aria-labelledby="home-care-slide-title-{{ $slideNumber }}"
                            @if ($index !== 0) hidden @endif
                        >
                            <x-responsive-image
                                class="home-care__media"
                                :path="$slide['image'] ?? null"
                                :mobile-path="$slide['image_mobile'] ?? null"
                                :alt="$slide['image_alt'] ?? ''"
                                :fallback="$presetImage"
                            />
                            <div class="home-care__shade"></div>
                            <h3 id="home-care-slide-title-{{ $slideNumber }}">{{ $slide['title'] ?? '' }}</h3>
                            <span class="home-care__icon" data-care-icon="{{ $slide['icon'] ?? 'heart' }}" aria-hidden="true">
                                @switch($slide['icon'] ?? 'heart')
                                    @case('utilities')
                                        <svg viewBox="0 0 24 24"><path d="M9 18h6M10 22h4M8.8 15.5c-1.7-1.1-2.8-3-2.8-5.1a6 6 0 1 1 12 0c0 2.1-1.1 4-2.8 5.1-.8.5-1.2 1.2-1.2 2.1h-4c0-.9-.4-1.6-1.2-2.1Z"/></svg>
                                        @break
                                    @case('home')
                                        <svg viewBox="0 0 24 24"><path d="m4 11 8-7 8 7v9h-6v-6h-4v6H4v-9Z"/></svg>
                                        @break
                                    @case('sport')
                                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="M6.5 7.8c3.5 1.3 5.5 4.5 5.5 8.2M17.5 16.2C14 14.9 12 11.7 12 8"/></svg>
                                        @break
                                    @case('security')
                                        <svg viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.4 2.8 7.8 7 10 4.2-2.2 7-5.6 7-10V6l-7-3Z"/><circle cx="12" cy="11" r="1.5"/><path d="M12 12.5V16"/></svg>
                                        @break
                                    @default
                                        <svg viewBox="0 0 24 24"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/></svg>
                                @endswitch
                            </span>
                            @if ($listItems->isNotEmpty())
                                <ul>
                                    @foreach ($listItems as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            @if (! empty($slide['note']))
                                <p class="home-care__note">{{ $slide['note'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
                <div class="home-care__pager" role="tablist" aria-label="Слайды заботы и сервиса">
                    @foreach ($careSlides as $index => $slide)
                        <button
                            type="button"
                            role="tab"
                            data-home-care-tab
                            aria-label="Показать слайд {{ $index + 1 }}: {{ str_replace("\n", ' ', $slide['title'] ?? '') }}"
                            aria-controls="home-care-slide-{{ $index + 1 }}"
                            aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                            tabindex="{{ $index === 0 ? '0' : '-1' }}"
                        >{{ $index + 1 }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="home-section home-seasons is-seasons-pinned" aria-labelledby="home-seasons-title" data-home-seasons data-season-tabs>
        <div class="home-seasons__track">
            <div class="home-seasons__sticky">
                <div class="site-container">
                    <div class="home-seasons__scene" data-home-seasons-scene>
                        <h2 class="home-design-heading home-design-heading--center" id="home-seasons-title">
                            <span>На территории организовано</span>
                            <span>пространство <em>для счастливой</em></span>
                            <em>жизни круглый год</em>
                        </h2>
                        <div class="sr-only" role="tablist" aria-label="Времена года">
                            <button type="button" role="tab" aria-selected="true" data-season-tab>Лето</button>
                            <button type="button" role="tab" aria-selected="false" data-season-tab>Зима</button>
                        </div>
                        <div class="home-seasons__stage">
                            <article class="home-season-card home-season-card--summer" data-season-panel data-season-card="summer" tabindex="0">
                                <span class="home-season-card__backdrop" aria-hidden="true"></span>
                                <img class="home-season-card__mascot home-season-card__mascot--moose" src="{{ asset('assets/design/home-rhythm-moose.svg') }}" alt="" decoding="async" aria-hidden="true">
                                <img class="home-season-card__mascot home-season-card__mascot--dog" src="{{ asset('assets/design/home-rhythm-dog.svg') }}" alt="" decoding="async" aria-hidden="true">
                                <div class="home-season-card__media">
                                    <img src="{{ asset('assets/design/home-target-hammock.webp') }}" alt="Гамак в летнем лесу" loading="lazy">
                                    <div class="home-season-card__copy">
                                        <h3>лето</h3>
                                        <p>Устраивайте перезагрузку каждые выходные, а не 1–2 раза в году</p>
                                        <small>Рыбалка, грибы, баня — все это будет в вашей жизни регулярно</small>
                                    </div>
                                </div>
                            </article>
                            <article class="home-season-card home-season-card--winter" data-season-panel data-season-card="winter" tabindex="0">
                                <span class="home-season-card__backdrop" aria-hidden="true"></span>
                                <img class="home-season-card__mascot home-season-card__mascot--hedgehog" src="{{ asset('assets/design/home-rhythm-hedgehog.svg') }}" alt="" decoding="async" aria-hidden="true">
                                <img class="home-season-card__mascot home-season-card__mascot--rabbit" src="{{ asset('assets/design/home-rhythm-rabbit.svg') }}" alt="" decoding="async" aria-hidden="true">
                                <div class="home-season-card__media">
                                    <img src="{{ asset('assets/design/home-target-winter.webp') }}" alt="Зимний вечер у дома" loading="lazy">
                                    <div class="home-season-card__copy">
                                        <h3>зима</h3>
                                        <p>Морозное утро в лесу и тихий вечер в тепле — больше не надо выбирать</p>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <x-button href="#lead-form" variant="primary" class="home-seasons__cta" data-lead-modal-trigger data-lead-source="home" data-lead-form-type="visit" data-lead-heading="Записаться на экскурсию">Записаться</x-button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section home-genplan" aria-labelledby="home-genplan-title">
        <div class="site-container">
            <div class="home-genplan__frame">
                <img src="{{ asset('assets/design/home-target-genplan.webp') }}" alt="Генеральный план посёлка" loading="lazy">
                <div class="home-genplan__overlay">
                    <h2 id="home-genplan-title">Генплан</h2>
                    <p>Разноформатные участки в окружении природы создают пространство для спокойной и осознанной жизни</p>
                    <a class="home-genplan__mode" href="{{ route('genplan.index') }}">3D <span>генплан⌄</span></a>
                    <strong>Москва,<br>Можайский округ</strong>
                    <nav aria-label="Режим карты">
                        <a class="is-active" href="{{ route('genplan.index') }}">Генплан</a>
                        <a href="{{ route('genplan.index', ['view' => 'surroundings']) }}">Окружение</a>
                    </nav>
                </div>
                <a class="sr-only" href="{{ route('genplan.index') }}">Открыть генплан</a>
            </div>
        </div>
    </section>

    <section class="home-section home-purchase" aria-labelledby="home-purchase-title">
        <div class="site-container home-purchase__layout">
            <h2 class="home-design-heading" id="home-purchase-title">
                <span>Работают<br>все варианты</span><br><em>приобретения</em>
            </h2>
            <div class="home-purchase__grid">
                @php
                    $purchaseLabels = ['Ипотечное кредитование в любом банке', 'Материнский капитал', 'Трейд ин', 'Договор купли-продажи'];
                @endphp
                @foreach ($purchaseLabels as $index => $label)
                    <article><span aria-hidden="true">{{ ['▣', '▰', '⇄', '▧'][$index] }}</span><h3>{{ $label }}</h3></article>
                @endforeach
            </div>
            <div class="sr-only">
                @foreach ($home->purchase_options ?? [] as $option)<span>{{ $option['title'] ?? '' }} {{ $option['text'] ?? '' }}</span>@endforeach
            </div>
        </div>
    </section>

    <section class="home-section home-stories" aria-labelledby="home-stories-title" data-home-stories>
        <div class="home-stories__track">
            <div class="home-stories__sticky">
                <div class="site-container">
                    <h2 class="home-design-heading" id="home-stories-title"><span>Истории из жизни</span><br><em>в Релакс Лэнд Можайский</em></h2>
                    <div class="home-stories__viewport" data-home-stories-viewport>
                        <div class="home-stories__scene" data-home-stories-scene>
                            <div class="home-stories__board">
                                <div class="home-stories__overlays" aria-hidden="true">
                                    <img class="home-stories__overlay home-stories__overlay--rabbit" src="{{ asset('assets/design/home-story-overlay-rabbit.png') }}" alt="" decoding="async">
                                    <img class="home-stories__overlay home-stories__overlay--moose" src="{{ asset('assets/design/home-story-overlay-moose.png') }}" alt="" decoding="async">
                                    <img class="home-stories__overlay home-stories__overlay--cloud-idle" src="{{ asset('assets/design/home-story-overlay-cloud-2.png') }}" alt="" decoding="async">
                                </div>

                                <div class="home-stories__list">
                                    @forelse ($stories as $index => $story)
                                        @php
                                            $imageUrl = $story->imageUrl();
                                            $audioUrl = $story->audioUrl();
                                            $videoUrl = $story->videoUrl();
                                            $isMedia = filled($videoUrl) || (filled($imageUrl) && blank($story->excerpt));
                                            $slot = ($index % 6) + 1;
                                            $mediaDuration = $story->video_duration ?: $story->audio_duration;
                                        @endphp
                                        <article class="home-story home-story--slot-{{ $slot }} {{ $isMedia ? 'home-story--media' : 'home-story--quote' }}" data-home-story>
                                            @if ($story->short_phrase)
                                                <p class="home-story__phrase">
                                                    <img src="{{ asset('assets/design/home-story-overlay-cloud-'.(($index % 3) + 1).'.png') }}" alt="" decoding="async" aria-hidden="true">
                                                    <span>{{ $story->short_phrase }}</span>
                                                </p>
                                            @endif

                                            @if ($isMedia)
                                                <div class="home-story__media">
                                                    @if ($videoUrl)
                                                        <video class="home-story__video" src="{{ $videoUrl }}" poster="{{ $imageUrl }}" playsinline preload="metadata" data-story-video-element></video>
                                                    @elseif ($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $story->image_alt ?: $story->title }}" loading="lazy">
                                                    @endif

                                                    @if ($audioUrl)
                                                        <x-story-audio class="home-story__audio" :src="$audioUrl" :duration="$mediaDuration" :label="'Слушать историю '.$story->title" />
                                                    @elseif ($videoUrl)
                                                        <button type="button" class="home-story__video-play" data-story-video-toggle aria-label="Смотреть видео {{ $story->title }}">▶</button>
                                                        @if ($mediaDuration)
                                                            <span class="home-story__duration">{{ $story->formattedVideoDuration() ?: $story->formattedAudioDuration() }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="home-story__meta">
                                                    <strong>{{ $story->title }}</strong>
                                                    @if ($story->subtitle)<small>{{ $story->subtitle }}</small>@endif
                                                </div>
                                            @else
                                                @if ($story->excerpt)
                                                    <p class="home-story__quote">{{ $story->excerpt }}</p>
                                                @endif
                                                <div class="home-story__person">
                                                    @if ($imageUrl)
                                                        <img src="{{ $imageUrl }}" alt="{{ $story->image_alt ?: $story->title }}">
                                                    @endif
                                                    <span>
                                                        <strong>{{ $story->title }}</strong>
                                                        @if ($story->subtitle)<small>{{ $story->subtitle }}</small>@endif
                                                    </span>
                                                </div>
                                            @endif
                                        </article>
                                    @empty
                                        <p class="home-stories__empty">Истории скоро появятся.</p>
                                    @endforelse
                                </div>
                            </div>

                            <x-button href="#lead-form" variant="primary" class="home-stories__cta" data-lead-modal-trigger data-lead-source="home" data-lead-form-type="visit" data-lead-heading="Приехать в посёлок">Приехать</x-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-visit-map :content="$visitContent" source="home" id="visit" />

    <section class="home-section home-developer" aria-labelledby="home-developer-title">
        <div class="site-container home-developer__layout">
            <div>
                <h2 class="home-design-heading" id="home-developer-title"><span>Забота о комфорте<br>клиента —</span> <em>наш приоритет</em></h2>
                <p>{{ $home->developer_text ?: 'Мы тщательно выбирали безопасную локацию в экологически чистом районе Подмосковья в районе Можайского водохранилища в окружении леса.' }}</p>
                <p>Наша многолетняя экспертиза позволила сразу определить ключевые детали: удобное расположение участков, дорог и подготовить центральные инженерные коммуникации. Это читается в каждом метре посёлка: начиная от места на карте и заканчивая службой заботы 24/7.</p>
                <a href="{{ route('about') }}">Подробнее о нас</a>
            </div>
            <div class="home-developer__years"><strong>25<sup>+</sup></strong><span>лет в недвижимости</span></div>
        </div>
    </section>

    <section class="home-section home-blog" aria-labelledby="home-blog-title">
        <div class="site-container">
            <h2 class="home-design-heading" id="home-blog-title">Блог</h2>
            <div class="home-blog__mosaic">
                @forelse ($blogPosts as $post)
                    <a @class(['home-blog-card', 'home-blog-card--lead' => $loop->first, 'home-blog-card--wide' => $loop->index === 3]) href="{{ route('blog.show', $post->slug) }}">
                        <img src="{{ $post->cover_image ? Storage::disk('public')->url($post->cover_image) : asset('assets/design/blog-'.str_pad((string) (($post->id - 1) % 6 + 1), 2, '0', STR_PAD_LEFT).'.webp') }}" alt="" loading="lazy">
                        <span>{{ $post->title }}</span>
                    </a>
                @empty
                    <p>Публикации скоро появятся.</p>
                @endforelse
            </div>
            <x-button :href="route('blog.index')" variant="primary" class="home-blog__cta">Читать блог</x-button>
        </div>
    </section>
@endsection
