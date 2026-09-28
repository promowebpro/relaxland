@props(['settings' => []])

@php
    $phone = $settings['contacts.sales_phone'] ?: $settings['contacts.phone'] ?: (request()->routeIs('home') ? '+7 968 000-00-00' : null);
    $phoneHref = \App\Domain\Settings\SiteSettings::phoneHref($phone);
    $workingHours = $settings['contacts.working_hours'] ?: (request()->routeIs('home') ? 'Ежедневно с 9:00 до 18:00 (Мск)' : null);
@endphp

<header @class(['site-header', 'site-header--overlay' => request()->routeIs('home')]) data-site-header>
    <div class="site-container site-header__inner">
        <nav class="desktop-nav" aria-label="Основная навигация">
            @foreach (config('navigation.header') as $item)
                @if ($item['route'] && Route::has($item['route']))
                    <a @class(['desktop-nav__link', 'is-active' => request()->routeIs($item['route'])]) href="{{ route($item['route']) }}">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="desktop-nav__link is-disabled" aria-disabled="true">{{ $item['label'] }}</span>
                @endif
            @endforeach
        </nav>

        <a class="header-home" href="{{ route('home') }}" aria-label="РелаксЛэнд — на главную">
            <span class="header-home__monogram" aria-hidden="true">РЛ</span>
        </a>

        <div class="header-contact">
            @if ($phoneHref || $workingHours)
                <div class="header-contact__meta">
                    @if ($phoneHref)
                        <a class="header-contact__phone" href="{{ $phoneHref }}">{{ $phone }}</a>
                    @endif

                    @if ($workingHours)
                        <span class="header-contact__hours">{{ $workingHours }}</span>
                    @endif
                </div>
            @endif

            <x-button
                href="#lead-form"
                variant="accent"
                class="header-contact__cta"
                data-lead-modal-trigger
                data-lead-source="header"
                data-lead-form-type="callback"
                data-lead-heading="Заказать обратный звонок"
            >
                Позвонить мечте
            </x-button>
        </div>

        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu" data-menu-toggle>
            <span class="sr-only">Открыть меню</span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>
    </div>

    <x-mobile-menu :settings="$settings" />
</header>
