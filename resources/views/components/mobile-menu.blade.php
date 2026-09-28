@props(['settings' => []])

@php
    $phone = $settings['contacts.sales_phone'] ?: $settings['contacts.phone'];
    $phoneHref = \App\Domain\Settings\SiteSettings::phoneHref($phone);
    $presentationHref = $settings['documents.presentation_url'];

    if (! $presentationHref && $settings['documents.presentation_file']) {
        $presentationHref = \Illuminate\Support\Facades\Storage::disk('public')->url($settings['documents.presentation_file']);
    }

    $socials = [
        'Telegram' => $settings['social.telegram'],
        'WhatsApp' => $settings['social.whatsapp'],
        'MAX' => $settings['social.max'],
    ];
@endphp

<div class="mobile-menu" id="mobile-menu" hidden data-mobile-menu>
    <div class="mobile-menu__backdrop" data-menu-close></div>
    <div class="mobile-menu__panel" role="dialog" aria-modal="true" aria-label="Меню сайта">
        <div class="mobile-menu__top">
            <a class="mobile-menu__brand" href="{{ route('home') }}" aria-label="РелаксЛэнд — на главную">
                <span aria-hidden="true">РЛ</span>
                <small>Москва,<br>Можайский округ</small>
            </a>
            <button class="mobile-menu__close" type="button" data-menu-close>
                <span class="sr-only">Закрыть меню</span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </button>
        </div>

        <div class="mobile-menu__content">
            <nav class="mobile-nav" aria-label="Основная навигация меню">
                @foreach (config('navigation.header') as $item)
                    @if ($item['route'] && Route::has($item['route']))
                        <a class="mobile-nav__link" href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                    @else
                        <span class="mobile-nav__link is-disabled" aria-disabled="true">{{ $item['label'] }}</span>
                    @endif
                @endforeach
            </nav>

            <nav class="mobile-menu__secondary" aria-label="Дополнительная навигация меню">
                <a href="{{ route('blog.index') }}">Блог</a>
                <a href="{{ route('legal.index') }}">Документы</a>
                @if ($presentationHref)
                    <a href="{{ $presentationHref }}" target="_blank" rel="noopener noreferrer">Презентация PDF</a>
                @endif
            </nav>

            <div class="mobile-menu__contact">
                @if ($phoneHref)
                    <a class="mobile-menu__phone" href="{{ $phoneHref }}">{{ $phone }}</a>
                @endif
                @if ($settings['contacts.working_hours'])
                    <span>{{ $settings['contacts.working_hours'] }}</span>
                @endif
                <div class="mobile-menu__socials" aria-label="Социальные сети">
                    @foreach ($socials as $label => $url)
                        @if ($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $label }}</a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mobile-menu__bottom">
            <x-button
                href="#lead-form"
                variant="primary"
                data-lead-modal-trigger
                data-lead-source="header"
                data-lead-form-type="callback"
                data-lead-heading="Заказать обратный звонок"
            >Позвонить мечте</x-button>
            <a href="{{ route('legal.index') }}">Политика конфиденциальности</a>
            <span>{{ $settings['footer.copyright'] ?: '© 2024–2026. РелаксЛэнд' }}</span>
        </div>
    </div>
</div>
