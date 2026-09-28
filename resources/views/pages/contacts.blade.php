@extends('layouts.public')

@section('title', 'Контакты')
@section('description', 'Контакты RelaxLand Можайский, адреса и способы построить маршрут.')

@section('content')
    @php
        $socials = [
            'max' => ['Макс', $settings['social.max']],
            'vk' => ['ВКонтакте', $settings['social.vk']],
            'telegram' => ['Телеграм', $settings['social.telegram']],
            'whatsapp' => ['Ватсап', $settings['social.whatsapp']],
        ];
    @endphp
    <div class="contacts-page">
        <header class="contacts-heading site-container">
            <x-breadcrumbs :items="$seo->breadcrumbs" />
            <h1>Контакты</h1>
        </header>
        <section class="contacts-summary site-container" aria-label="Контактные данные">
            <div class="contacts-summary__socials">
                <h2>Социальные медиа:</h2>
                <ul>
                    @foreach ($socials as $type => [$label, $url])
                        @if ($url)
                            <li><a href="{{ $url }}" target="_blank" rel="noopener noreferrer"><x-social-icon :type="$type" />{{ $label }}</a></li>
                        @endif
                    @endforeach
                </ul>
                @if (! collect($socials)->contains(fn ($social) => filled($social[1])))<p class="contacts-summary__empty">Ссылки скоро появятся</p>@endif
                @if ($settings['contacts.notice'])<p class="contacts-summary__notice">{{ $settings['contacts.notice'] }}</p>@endif
            </div>
            <div>
                <section class="contacts-summary__group">
                    <h2>Телефон:</h2>
                    @foreach (['contacts.phone' => 'По всем вопросам', 'contacts.sales_phone' => 'Отдел продаж'] as $key => $label)
                        @if ($settings[$key])<p class="contacts-summary__line"><a href="{{ \App\Domain\Settings\SiteSettings::phoneHref($settings[$key]) }}">{{ $settings[$key] }}</a><small>{{ $label }}</small></p>@endif
                    @endforeach
                    @if (! $settings['contacts.phone'] && ! $settings['contacts.sales_phone'])<p class="contacts-summary__empty">Телефон пока не указан</p>@endif
                </section>
                <section class="contacts-summary__group">
                    <h2>Посёлок:</h2>
                    <p>{{ $settings['contacts.village_address'] ?: 'Адрес пока не опубликован' }}</p>
                    @if ($settings['contacts.working_hours'])<small>{{ $settings['contacts.working_hours'] }}</small>@endif
                </section>
            </div>
            <div>
                <section class="contacts-summary__group">
                    <h2>Почта:</h2>
                    @foreach (['contacts.email' => 'По всем вопросам', 'contacts.support_email' => 'Тех. поддержка'] as $key => $label)
                        @if ($settings[$key])<p class="contacts-summary__line"><a href="mailto:{{ $settings[$key] }}">{{ $settings[$key] }}</a><small>{{ $label }}</small></p>@endif
                    @endforeach
                    @if (! $settings['contacts.email'] && ! $settings['contacts.support_email'])<p class="contacts-summary__empty">Почта пока не указана</p>@endif
                </section>
                <section class="contacts-summary__group">
                    <h2>Центральный офис:</h2>
                    <p>{{ $settings['contacts.office_address'] ?: 'Адрес пока не опубликован' }}</p>
                </section>
            </div>
        </section>
        <x-visit-map :content="$visitContent" source="contacts" />
    </div>
@endsection
