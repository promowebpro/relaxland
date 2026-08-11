@props(['settings' => []])

@php
    $phone = $settings['contacts.sales_phone'] ?: $settings['contacts.phone'];
    $phoneHref = \App\Domain\Settings\SiteSettings::phoneHref($phone);
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

        <a class="header-home" href="{{ route('home') }}" aria-label="RelaxLand — на главную">RelaxLand</a>

        <div class="header-contact">
            @if ($phoneHref || $settings['contacts.working_hours'])
                <div class="header-contact__meta">
                    @if ($phoneHref)
                        <a class="header-contact__phone" href="{{ $phoneHref }}">{{ $phone }}</a>
                    @endif

                    @if ($settings['contacts.working_hours'])
                        <span class="header-contact__hours">{{ $settings['contacts.working_hours'] }}</span>
                    @endif
                </div>
            @endif

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
