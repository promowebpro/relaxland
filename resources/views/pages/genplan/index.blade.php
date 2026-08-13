@extends('layouts.public')

@section('title', 'Генплан')
@section('description', 'Генплан RelaxLand Можайский: кварталы, инфраструктура и расположение территории.')
@section('body_class', 'genplan-page')

@php
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $initialMode = $pageState->mode;
    $initialQuarter = $pageState->selectedQuarter?->slug;
    $initialPoint = $pageState->selectedInfrastructure?->slug;
    $initialImage = $genplan ? $disk->url($initialMode === \App\Domain\Genplan\GenplanMode::TwoD ? $genplan->image_2d : $genplan->image_3d) : null;
    $image3d = $genplan ? $disk->url($genplan->image_3d) : null;
    $image2d = $genplan ? $disk->url($genplan->image_2d) : null;
    $mobileImage3d = $genplan?->mobile_image_3d && $genplan?->mobile_image_3d_is_compatible ? $disk->url($genplan->mobile_image_3d) : null;
    $mobileImage2d = $genplan?->mobile_image_2d && $genplan?->mobile_image_2d_is_compatible ? $disk->url($genplan->mobile_image_2d) : null;
    $initialMobileImage = $initialMode === \App\Domain\Genplan\GenplanMode::TwoD ? $mobileImage2d : $mobileImage3d;
    $stageRatio = $genplan?->original_width && $genplan?->original_height
        ? $genplan->original_width.' / '.$genplan->original_height
        : '16 / 10';
    $quarterNumbers = $genplan?->quarters->values()->mapWithKeys(fn ($quarter, $index) => [$quarter->slug => str_pad($index + 1, 2, '0', STR_PAD_LEFT)]) ?? collect();
    $stateUrl = function (array $state = []) use ($initialMode, $initialQuarter, $initialPoint) {
        $query = array_merge([
            'mode' => $initialMode->value,
            'quarter' => $initialQuarter,
            'point' => $initialPoint,
        ], $state);

        $query = array_filter($query, fn ($value) => $value !== null && $value !== '' && $value !== 'genplan');

        return route('genplan.index').($query ? '?'.http_build_query($query) : '');
    };
@endphp

@section('content')
    <section class="genplan-hero">
        <div class="site-container genplan-hero__inner">
            <div>
                <span class="eyebrow">Территория</span>
                <h1>Генплан</h1>
            </div>
            <p>Изучите расположение кварталов и основу будущей инфраструктуры RelaxLand.</p>
        </div>
    </section>

    <section
        class="genplan-shell"
        data-genplan-foundation
        data-initial-tab="{{ $pageState->activeTab }}"
        data-initial-mode="{{ $initialMode->value }}"
        data-initial-quarter="{{ $initialQuarter }}"
        data-initial-point="{{ $initialPoint }}"
        data-initial-incomplete="{{ $pageState->incomplete ? 'true' : 'false' }}"
        data-loading="false"
    >
        <div class="site-container">
            <div class="genplan-view-tabs" role="tablist" aria-label="Режим территории" data-genplan-view-tabs>
                <a href="{{ $stateUrl(['view' => null]) }}" role="tab" aria-selected="{{ $pageState->activeTab === 'genplan' ? 'true' : 'false' }}" aria-controls="genplan-panel" id="genplan-tab" @if ($pageState->activeTab !== 'genplan') tabindex="-1" @endif data-genplan-view="genplan">Генплан</a>
                <a href="{{ $stateUrl(['view' => 'surroundings', 'quarter' => null, 'point' => null]) }}" role="tab" aria-selected="{{ $pageState->activeTab === 'surroundings' ? 'true' : 'false' }}" aria-controls="surroundings-panel" id="surroundings-tab" @if ($pageState->activeTab !== 'surroundings') tabindex="-1" @endif data-genplan-view="surroundings">Окружение</a>
            </div>

            <div id="genplan-panel" role="tabpanel" aria-labelledby="genplan-tab" data-genplan-panel="genplan" @if ($pageState->activeTab !== 'genplan') hidden @endif>
                @if ($genplan)
                    <div class="genplan-toolbar">
                        <div>
                            <span class="eyebrow">{{ $genplan->name }}</span>
                            <p>Каждый режим использует собственную геометрию своей проекции.</p>
                        </div>
                        <div class="genplan-mode-switch" aria-label="Вид генплана">
                            @foreach (\App\Domain\Genplan\GenplanMode::cases() as $mode)
                                @php
                                    $modeQuarter = $pageState->selectedQuarter?->geometryFor($mode) ? $initialQuarter : null;
                                    $modePointGeometry = $pageState->selectedInfrastructure?->geometryFor($mode);
                                    $modePointVisible = $pageState->selectedInfrastructure && ($mode === \App\Domain\Genplan\GenplanMode::TwoD ? $pageState->selectedInfrastructure->show_on_2d : $pageState->selectedInfrastructure->show_on_3d);
                                    $modePoint = $modePointGeometry && $modePointVisible ? $initialPoint : null;
                                @endphp
                                <a href="{{ $stateUrl(['mode' => $mode->value, 'quarter' => $modeQuarter, 'point' => $modePoint]) }}" role="button" aria-pressed="{{ $mode === $initialMode ? 'true' : 'false' }}" data-genplan-mode="{{ $mode->value }}">{{ strtoupper($mode->value) }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class="genplan-layout">
                        <div
                            @class(['genplan-stage', 'has-mobile-background' => (bool) $initialMobileImage])
                            style="--genplan-aspect: {{ $stageRatio }}"
                            data-genplan-stage
                            data-image-3d="{{ $image3d }}"
                            data-image-2d="{{ $image2d }}"
                            data-mobile-image-3d="{{ $mobileImage3d }}"
                            data-mobile-image-2d="{{ $mobileImage2d }}"
                        >
                            <img src="{{ $initialImage }}" alt="{{ $genplan->name }}, вид {{ strtoupper($initialMode->value) }}" data-genplan-image>
                            <svg class="genplan-overlay" viewBox="0 0 1000 1000" preserveAspectRatio="none" aria-label="Кварталы генплана">
                                @foreach (\App\Domain\Genplan\GenplanMode::cases() as $mode)
                                    <g data-geometry-mode="{{ $mode->value }}" data-geometry-kind="quarters" @if ($mode !== $initialMode) hidden @endif>
                                        @foreach ($genplan->quarters as $quarter)
                                            @if ($quarterGeometry = $quarter->geometryFor($mode))
                                                <polygon
                                                    points="{{ $geometry->svgPoints($quarterGeometry->polygon_data) }}"
                                                    @class(['genplan-quarter', 'genplan-quarter--'.$quarter->status->value, 'is-selected' => $initialQuarter === $quarter->slug])
                                                    role="button"
                                                    tabindex="0"
                                                    aria-label="{{ $quarter->name }} — {{ $quarter->status->label() }}, {{ $mode->label() }}"
                                                    aria-pressed="{{ $initialQuarter === $quarter->slug ? 'true' : 'false' }}"
                                                    data-quarter-trigger
                                                    data-quarter-slug="{{ $quarter->slug }}"
                                                    data-trigger-mode="{{ $mode->value }}"
                                                />
                                                @if ($quarterGeometry->label_x !== null && $quarterGeometry->label_y !== null)
                                                    <text class="genplan-quarter-label" x="{{ (float) $quarterGeometry->label_x * 1000 }}" y="{{ (float) $quarterGeometry->label_y * 1000 }}" text-anchor="middle" dominant-baseline="central" aria-hidden="true" data-quarter-label="{{ $quarter->slug }}">{{ $quarterNumbers[$quarter->slug] }}</text>
                                                @endif
                                            @endif
                                        @endforeach
                                    </g>
                                @endforeach
                            </svg>

                            @foreach (\App\Domain\Genplan\GenplanMode::cases() as $mode)
                                <div class="genplan-marker-layer" data-geometry-mode="{{ $mode->value }}" data-geometry-kind="points" aria-label="Объекты инфраструктуры {{ $mode->label() }}" @if ($mode !== $initialMode) hidden @endif>
                                    @foreach ($infrastructure as $point)
                                        @php
                                            $pointGeometry = $point->geometryFor($mode);
                                            $showPoint = $mode === \App\Domain\Genplan\GenplanMode::TwoD ? $point->show_on_2d : $point->show_on_3d;
                                        @endphp
                                        @if ($pointGeometry && $showPoint)
                                            <a
                                                href="{{ $stateUrl(['mode' => $mode->value, 'quarter' => null, 'point' => $point->slug]) }}"
                                                @class(['genplan-marker', 'is-selected' => $initialPoint === $point->slug])
                                                style="--marker-x: {{ (float) $pointGeometry->marker_x * 100 }}%; --marker-y: {{ (float) $pointGeometry->marker_y * 100 }}%"
                                                role="button"
                                                aria-label="{{ $point->name }} — {{ $point->category->label() }}, {{ $mode->label() }}"
                                                aria-pressed="{{ $initialPoint === $point->slug ? 'true' : 'false' }}"
                                                data-point-trigger
                                                data-point-slug="{{ $point->slug }}"
                                                data-trigger-mode="{{ $mode->value }}"
                                            ><span aria-hidden="true">•</span></a>
                                        @endif
                                    @endforeach
                                </div>
                                @php
                                    $hasModeGeometry = $genplan->quarters->contains(fn ($quarter) => $quarter->geometryFor($mode))
                                        || $infrastructure->contains(function ($point) use ($mode) {
                                            $visible = $mode === \App\Domain\Genplan\GenplanMode::TwoD ? $point->show_on_2d : $point->show_on_3d;
                                            return $visible && $point->geometryFor($mode);
                                        });
                                @endphp
                                <p class="genplan-geometry-empty" data-genplan-geometry-empty="{{ $mode->value }}" data-has-geometry="{{ $hasModeGeometry ? 'true' : 'false' }}" @if ($mode !== $initialMode || $hasModeGeometry) hidden @endif>
                                    Геометрия {{ $mode->label() }} ещё не заполнена. Фон остаётся доступен без неверной подсветки.
                                </p>
                            @endforeach
                        </div>

                        <aside class="genplan-sidebar" aria-label="Выбор объекта генплана">
                            <div class="genplan-quarter-list" aria-label="Выберите квартал">
                                @forelse ($genplan->quarters as $quarter)
                                    @php $availableModes = collect(\App\Domain\Genplan\GenplanMode::cases())->filter(fn ($mode) => $quarter->geometryFor($mode))->map->value->implode(','); @endphp
                                    <a
                                        href="{{ $stateUrl(['quarter' => $quarter->slug, 'point' => null]) }}"
                                        @class(['is-selected' => $initialQuarter === $quarter->slug])
                                        aria-pressed="{{ $initialQuarter === $quarter->slug ? 'true' : 'false' }}"
                                        aria-disabled="{{ $quarter->geometryFor($initialMode) ? 'false' : 'true' }}"
                                        data-quarter-trigger
                                        data-quarter-slug="{{ $quarter->slug }}"
                                        data-quarter-modes="{{ $availableModes }}"
                                    >
                                        <span class="genplan-quarter-list__number">{{ $quarterNumbers[$quarter->slug] }}</span>
                                        <span><strong>{{ $quarter->name }}</strong><small>{{ $quarter->status->label() }}</small></span>
                                    </a>
                                @empty
                                    <p class="genplan-empty-copy">Активные кварталы пока не опубликованы.</p>
                                @endforelse
                            </div>

                            <div class="genplan-selection" aria-live="polite" data-genplan-selection>
                                <div class="genplan-selection-empty" data-selection-empty @if ($initialQuarter || $initialPoint) hidden @endif>
                                    <span class="eyebrow">Интерактивный генплан</span>
                                    <p>Выберите квартал на плане или объект инфраструктуры, чтобы увидеть подробности.</p>
                                </div>

                                @foreach ($genplan->quarters as $quarter)
                                    <article class="genplan-quarter-card" data-quarter-card="{{ $quarter->slug }}" @if ($initialQuarter !== $quarter->slug) hidden @endif>
                                        <button class="genplan-card-close" type="button" aria-label="Закрыть карточку {{ $quarter->name }}" data-selection-close>×</button>
                                        <span class="genplan-status genplan-status--{{ $quarter->status->value }}">{{ $quarter->status->label() }}</span>
                                        <h2>{{ $quarter->name }}</h2>
                                        <p>{{ $quarter->description ?: 'Описание квартала будет опубликовано после наполнения генплана.' }}</p>
                                        <x-button href="#lead-form" variant="outline" data-lead-modal-trigger data-lead-source="genplan-preview" data-lead-form-type="consultation" data-lead-heading="Узнать о квартале {{ $quarter->name }}">Получить консультацию</x-button>
                                    </article>
                                @endforeach

                                @foreach ($infrastructure as $point)
                                    <article class="genplan-point-card" data-point-card="{{ $point->slug }}" @if ($initialPoint !== $point->slug) hidden @endif>
                                        <button class="genplan-card-close" type="button" aria-label="Закрыть карточку {{ $point->name }}" data-selection-close>×</button>
                                        <span class="genplan-point-category">{{ $point->category->label() }}</span>
                                        @if ($point->image)
                                            <img src="{{ $disk->url($point->image) }}" alt="" loading="lazy">
                                        @endif
                                        <h2>{{ $point->name }}</h2>
                                        <p>{{ $point->description ?: 'Описание объекта инфраструктуры будет опубликовано позже.' }}</p>
                                    </article>
                                @endforeach
                            </div>
                        </aside>
                    </div>
                @else
                    <div class="genplan-empty-state">
                        <span class="eyebrow">Данные готовятся</span>
                        <h2>Генплан пока не опубликован</h2>
                        <p>Мы покажем кварталы и инфраструктуру после проверки исходных материалов.</p>
                        <x-button href="#lead-form" variant="primary" data-lead-modal-trigger data-lead-source="genplan-preview" data-lead-form-type="consultation" data-lead-heading="Узнать о территории">Узнать о проекте</x-button>
                    </div>
                @endif
            </div>

            <div id="surroundings-panel" role="tabpanel" aria-labelledby="surroundings-tab" data-genplan-panel="surroundings" @if ($pageState->activeTab !== 'surroundings') hidden @endif>
                <div class="surroundings-foundation">
                    <div>
                        <span class="eyebrow">Окружение</span>
                        <h2>Важные места рядом</h2>
                        <p>Картографический провайдер будет выбран на отдельном этапе. Сейчас доступен проверяемый список опубликованных мест.</p>
                    </div>
                    <div class="surroundings-foundation__map" aria-label="Карта окружения появится в Release 8">
                        <span>Provider-neutral map foundation</span>
                    </div>
                    <ul class="surroundings-list">
                        @forelse ($surroundings as $place)
                            <li><span>{{ $place->category->label() }}</span><strong>{{ $place->name }}</strong></li>
                        @empty
                            <li class="genplan-empty-copy">Активные места окружения пока не опубликованы.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endsection
