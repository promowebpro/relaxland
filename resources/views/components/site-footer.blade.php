@props(['settings' => []])

@php
    $homeFallbacks = request()->routeIs('home');
    $phone = $settings['contacts.sales_phone'] ?: $settings['contacts.phone'] ?: ($homeFallbacks ? '8 (800) 000-00-00' : null);
    $phoneHref = \App\Domain\Settings\SiteSettings::phoneHref($phone);
    $email = $settings['contacts.email'] ?: ($homeFallbacks ? 'info@ffffff.ru' : null);
    $workingHours = $settings['contacts.working_hours'] ?: ($homeFallbacks ? '09:00—21:00' : null);
    $officeAddress = $settings['contacts.office_address'] ?: ($homeFallbacks ? 'Москва, 1-й проезд Поля, д. 2, стр. 3 (м. Перово)' : null);
    $villageAddress = $settings['contacts.village_address'] ?: ($homeFallbacks ? 'Московская область, Можайский округ' : null);
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

<footer class="site-footer">
    <div class="site-container">
        <div class="site-footer__grid">
            @foreach (config('navigation.footer') as $group => $items)
                <nav class="footer-nav" aria-label="{{ $group }}">
                    <h2 class="footer-nav__title">{{ $group }}</h2>
                    <ul class="footer-nav__list">
                        @foreach ($items as $item)
                            <li>
                                @if ($item['route'] && Route::has($item['route']))
                                    <a href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                                @else
                                    <span aria-disabled="true">{{ $item['label'] }}</span>
                                @endif
                            </li>
                        @endforeach
                        @if ($group === 'Партнёрам')
                            <li>
                                @if ($presentationHref)
                                    <a href="{{ $presentationHref }}" target="_blank" rel="noopener noreferrer">Презентация PDF</a>
                                @else
                                    <span aria-disabled="true">Презентация PDF</span>
                                @endif
                            </li>
                        @endif
                    </ul>
                    @if ($loop->first)
                        <x-button
                            href="{{ route('genplan.index') }}"
                            variant="light"
                            class="site-footer__cta"
                        ><span class="site-footer__cta-icon" aria-hidden="true">⌂</span>Выбрать участок</x-button>
                        @if ($settings['footer.disclaimer'])
                            <p class="site-footer__disclaimer">{!! nl2br(e($settings['footer.disclaimer'])) !!}</p>
                        @endif
                    @endif
                </nav>
            @endforeach

            <section class="footer-contacts" aria-labelledby="footer-contacts-title">
                <h2 class="footer-nav__title" id="footer-contacts-title">Контакты</h2>
                <dl>
                    @if ($phone)
                        <div><dt>Телефон</dt><dd><a href="{{ $phoneHref }}">{{ $phone }}</a></dd></div>
                    @endif
                    @if ($email)
                        <div><dt>Email</dt><dd><a href="mailto:{{ $email }}">{{ $email }}</a></dd></div>
                    @endif
                    @if ($workingHours)
                        <div><dt>Режим работы</dt><dd>{{ $workingHours }}</dd></div>
                    @endif
                    @if ($officeAddress)
                        <div><dt>Офис</dt><dd>{{ $officeAddress }}</dd></div>
                    @endif
                    @if ($villageAddress)
                        <div><dt>Посёлок</dt><dd>{{ $villageAddress }}</dd></div>
                    @endif
                </dl>
            </section>
        </div>

        <div class="site-footer__wordmark" aria-hidden="true">
            <span>РелаксЛэнд Можайский</span>
        </div>

        <div class="site-footer__bottom">
            <div class="site-footer__socials" aria-label="Социальные сети">
                @foreach ($socials as $label => $url)
                    @if ($url)
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $label }}</a>
                    @endif
                @endforeach
            </div>
            <a href="{{ route('legal.index') }}">Политика конфиденциальности</a>
            <span>{{ $settings['footer.copyright'] ?: '© 2024–2026. РелаксЛэнд' }}</span>
            <span class="site-footer__credit">Сайт сделали МОИ</span>
        </div>
    </div>
</footer>
