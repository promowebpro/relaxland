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
                            href="#lead-form"
                            variant="light"
                            class="site-footer__cta"
                            data-lead-modal-trigger
                            data-lead-source="footer"
                            data-lead-form-type="callback"
                            data-lead-heading="Заказать обратный звонок"
                        >Позвонить мечте</x-button>
                    @endif
                </nav>
            @endforeach

            <section class="footer-contacts" aria-labelledby="footer-contacts-title">
                <h2 class="footer-nav__title" id="footer-contacts-title">Контакты</h2>
                <dl>
                    @if ($phone)
                        <div><dt>Телефон</dt><dd><a href="{{ $phoneHref }}">{{ $phone }}</a></dd></div>
                    @endif
                    @if ($settings['contacts.email'])
                        <div><dt>Email</dt><dd><a href="mailto:{{ $settings['contacts.email'] }}">{{ $settings['contacts.email'] }}</a></dd></div>
                    @endif
                    @if ($settings['contacts.working_hours'])
                        <div><dt>Режим работы</dt><dd>{{ $settings['contacts.working_hours'] }}</dd></div>
                    @endif
                    @if ($settings['contacts.office_address'])
                        <div><dt>Офис</dt><dd>{{ $settings['contacts.office_address'] }}</dd></div>
                    @endif
                    @if ($settings['contacts.village_address'])
                        <div><dt>Посёлок</dt><dd>{{ $settings['contacts.village_address'] }}</dd></div>
                    @endif
                </dl>
            </section>
        </div>

        <div class="site-footer__socials" aria-label="Социальные сети">
            @foreach ($socials as $label => $url)
                @if ($url)
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $label }}</a>
                @endif
            @endforeach
        </div>

        <div class="site-footer__wordmark" aria-hidden="true">
            <span>РелаксЛэнд Можайский</span>
        </div>

        <div class="site-footer__bottom">
            <span>{{ $settings['footer.copyright'] ?: 'RelaxLand' }}</span>
            <a href="{{ route('legal.index') }}">Политика конфиденциальности</a>
            @if ($settings['footer.disclaimer'])
                <p>{!! nl2br(e($settings['footer.disclaimer'])) !!}</p>
            @endif
            <span class="site-footer__credit">Создано с заботой</span>
        </div>
    </div>
</footer>
