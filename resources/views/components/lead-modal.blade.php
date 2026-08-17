@php
    $errors ??= new \Illuminate\Support\ViewErrorBag;
    $privacyDocument = app(\App\Domain\Leads\LeadConsentDocument::class)->current();
    $privacyUrl = $privacyDocument
        ? route('legal.show', $privacyDocument->slug)
        : route('legal.index');
    $defaultSource = match (true) {
        request()->routeIs('contacts') => \App\Domain\Leads\LeadSource::Contacts->value,
        request()->routeIs('about') => \App\Domain\Leads\LeadSource::About->value,
        default => \App\Domain\Leads\LeadSource::Home->value,
    };
    $hasLeadErrors = $errors->lead->any();
@endphp

<div
    id="lead-form"
    @class(['lead-modal', 'is-open' => $hasLeadErrors])
    data-lead-modal
    @if ($hasLeadErrors) data-lead-open-on-load @endif
>
    <a class="lead-modal__backdrop" href="#main-content" aria-label="Закрыть форму" data-lead-modal-close></a>
    <section class="lead-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="lead-modal-title" tabindex="-1">
        <div class="lead-modal__header">
            <div>
                <span class="eyebrow">Оставить заявку</span>
                <h2 id="lead-modal-title" data-lead-modal-title>{{ old('form_heading', 'Давайте знакомиться') }}</h2>
            </div>
            <a class="lead-modal__close" href="#main-content" aria-label="Закрыть форму" data-lead-modal-close>×</a>
        </div>

        <form class="lead-form" action="{{ route('leads.store') }}" method="POST" novalidate>
            @csrf
            <input type="hidden" name="source" value="{{ old('source', $defaultSource) }}" data-lead-source>
            <input type="hidden" name="form_type" value="{{ old('form_type', \App\Domain\Leads\LeadFormType::Generic->value) }}" data-lead-form-type>
            <input type="hidden" name="form_heading" value="{{ old('form_heading') }}" data-lead-form-heading>
            <input type="hidden" name="page_url" value="{{ old('page_url', url()->current()) }}">
            <input type="hidden" name="quarter" value="{{ old('quarter') }}" data-lead-quarter>
            <input type="hidden" name="plot" value="{{ old('plot') }}" data-lead-plot>

            <div class="lead-form__grid">
                <label class="form-field">
                    <span class="form-field__label" id="lead-name-label">Имя</span>
                    <input class="form-field__input" type="text" name="name" value="{{ old('name') }}" maxlength="120" autocomplete="name" aria-labelledby="lead-name-label" @error('name', 'lead') aria-invalid="true" aria-describedby="lead-name-error" @enderror data-lead-name>
                    @error('name', 'lead')<span class="form-field__error" id="lead-name-error">{{ $message }}</span>@enderror
                </label>

                <label class="form-field">
                    <span class="form-field__label" id="lead-phone-label">Телефон <span aria-hidden="true">*</span></span>
                    <input class="form-field__input" type="tel" name="phone" value="{{ old('phone') }}" maxlength="40" autocomplete="tel" inputmode="tel" required aria-required="true" aria-labelledby="lead-phone-label" @error('phone', 'lead') aria-invalid="true" aria-describedby="lead-phone-error" @enderror>
                    @error('phone', 'lead')<span class="form-field__error" id="lead-phone-error">{{ $message }}</span>@enderror
                </label>

                <label class="form-field lead-form__wide">
                    <span class="form-field__label" id="lead-email-label">Email</span>
                    <input class="form-field__input" type="email" name="email" value="{{ old('email') }}" maxlength="254" autocomplete="email" aria-labelledby="lead-email-label" @error('email', 'lead') aria-invalid="true" aria-describedby="lead-email-error" @enderror>
                    @error('email', 'lead')<span class="form-field__error" id="lead-email-error">{{ $message }}</span>@enderror
                </label>

                <label class="form-field lead-form__wide">
                    <span class="form-field__label" id="lead-message-label">Комментарий</span>
                    <textarea class="form-field__input form-field__textarea" name="message" maxlength="2000" rows="4" aria-labelledby="lead-message-label" @error('message', 'lead') aria-invalid="true" aria-describedby="lead-message-error" @enderror>{{ old('message') }}</textarea>
                    @error('message', 'lead')<span class="form-field__error" id="lead-message-error">{{ $message }}</span>@enderror
                </label>
            </div>

            <label class="lead-form__consent">
                <input type="checkbox" name="consent" value="1" @checked(old('consent')) required aria-required="true" @error('consent', 'lead') aria-invalid="true" aria-describedby="lead-consent-error" @enderror>
                <span>
                    Я согласен(на) на обработку персональных данных согласно
                    <a href="{{ $privacyUrl }}" target="_blank" rel="noopener noreferrer">{{ $privacyDocument?->title ?: 'политике конфиденциальности' }}</a>.
                </span>
            </label>
            @error('consent', 'lead')<span class="form-field__error lead-form__consent-error" id="lead-consent-error">{{ $message }}</span>@enderror

            <label class="lead-form__honeypot" aria-hidden="true">
                <span>Ваш сайт</span>
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off">
            </label>
            @error('website', 'lead')<span class="form-field__error">{{ $message }}</span>@enderror

            <button class="button button--primary lead-form__submit" type="submit">Отправить заявку</button>
        </form>
    </section>
</div>
