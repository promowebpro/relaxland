@props(['settings' => []])

@php
    $phone = $settings['contacts.sales_phone'] ?: $settings['contacts.phone'];
    $phoneHref = \App\Domain\Settings\SiteSettings::phoneHref($phone);
@endphp

<div class="mobile-menu" id="mobile-menu" hidden data-mobile-menu>
    <div class="mobile-menu__backdrop" data-menu-close></div>
    <div class="mobile-menu__panel" role="dialog" aria-modal="true" aria-label="Меню сайта">
        <div class="mobile-menu__top">
            <span class="mobile-menu__title">Меню</span>
            <button class="mobile-menu__close" type="button" data-menu-close>
                <span class="sr-only">Закрыть меню</span>
                <span aria-hidden="true">×</span>
            </button>
        </div>

        <nav class="mobile-nav" aria-label="Мобильная навигация">
            @foreach (config('navigation.header') as $item)
                @if ($item['route'] && Route::has($item['route']))
                    <a class="mobile-nav__link" href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                @else
                    <span class="mobile-nav__link is-disabled" aria-disabled="true">{{ $item['label'] }}</span>
                @endif
            @endforeach
        </nav>

        <div class="mobile-menu__contact">
            @if ($phoneHref)
                <a class="mobile-menu__phone" href="{{ $phoneHref }}">{{ $phone }}</a>
            @endif
            @if ($settings['contacts.working_hours'])
                <span>{{ $settings['contacts.working_hours'] }}</span>
            @endif
        </div>

        <x-button :href="$phoneHref" :disabled="! $phoneHref" variant="accent">Позвонить мечте</x-button>
    </div>
</div>
