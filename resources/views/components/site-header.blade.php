@props(['settings' => []])

@php
    $phone = $settings['contacts.sales_phone'] ?: $settings['contacts.phone'];
    $phoneHref = \App\Domain\Settings\SiteSettings::phoneHref($phone);
@endphp

<header class="site-header" data-site-header>
    <div class="site-container site-header__inner">
        <a class="brand-mark" href="{{ route('home') }}" aria-label="RelaxLand — на главную">
            <span class="brand-mark__name">RelaxLand</span>
            <span class="brand-mark__place">Можайский</span>
        </a>

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

        <div class="header-contact">
            <div class="header-contact__meta">
                @if ($phoneHref)
                    <a class="header-contact__phone" href="{{ $phoneHref }}">{{ $phone }}</a>
                @else
                    <span class="header-contact__phone">Телефон не указан</span>
                @endif

                @if ($settings['contacts.working_hours'])
                    <span class="header-contact__hours">{{ $settings['contacts.working_hours'] }}</span>
                @endif
            </div>

            <x-button :href="$phoneHref" :disabled="! $phoneHref" variant="accent" class="header-contact__cta">
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
