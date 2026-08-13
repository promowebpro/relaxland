@extends('layouts.public')

@section('title', 'Генплан')
@section('description', 'Генплан RelaxLand Можайский: кварталы, инфраструктура и расположение территории.')
@section('body_class', 'genplan-page')

@php
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $image3d = $genplan ? $disk->url($genplan->image_3d) : null;
    $image2d = $genplan ? $disk->url($genplan->image_2d) : null;
    $mobileImage3d = $genplan?->mobile_image_3d ? $disk->url($genplan->mobile_image_3d) : null;
    $mobileImage2d = $genplan?->mobile_image_2d ? $disk->url($genplan->mobile_image_2d) : null;
    $stageRatio = $genplan?->original_width && $genplan?->original_height
        ? $genplan->original_width.' / '.$genplan->original_height
        : '16 / 10';
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

    <section class="genplan-shell" data-genplan-foundation>
        <div class="site-container">
            <div class="genplan-view-tabs" role="tablist" aria-label="Режим территории" data-genplan-view-tabs>
                <button type="button" role="tab" aria-selected="true" aria-controls="genplan-panel" id="genplan-tab" data-genplan-view="plan">Генплан</button>
                <button type="button" role="tab" aria-selected="false" aria-controls="surroundings-panel" id="surroundings-tab" tabindex="-1" data-genplan-view="surroundings">Окружение</button>
            </div>

            <div id="genplan-panel" role="tabpanel" aria-labelledby="genplan-tab" data-genplan-panel="plan">
                @if ($genplan)
                    <div class="genplan-toolbar">
                        <div>
                            <span class="eyebrow">{{ $genplan->name }}</span>
                            <p>Геометрия кварталов одинакова для всех режимов.</p>
                        </div>
                        <div class="genplan-mode-switch" aria-label="Вид генплана">
                            <button type="button" aria-pressed="false" data-genplan-mode="2d">2D</button>
                            <button type="button" aria-pressed="true" data-genplan-mode="3d">3D</button>
                        </div>
                    </div>

                    <div class="genplan-layout">
                        <div
                            @class(['genplan-stage', 'has-mobile-background' => (bool) $mobileImage3d])
                            style="--genplan-aspect: {{ $stageRatio }}"
                            data-genplan-stage
                            data-image-3d="{{ $image3d }}"
                            data-image-2d="{{ $image2d }}"
                            data-mobile-image-3d="{{ $mobileImage3d }}"
                            data-mobile-image-2d="{{ $mobileImage2d }}"
                        >
                            <img src="{{ $image3d }}" alt="{{ $genplan->name }}, вид 3D" data-genplan-image>
                            <svg class="genplan-overlay" viewBox="0 0 1000 1000" preserveAspectRatio="none" aria-label="Кварталы и инфраструктура генплана">
                                <g class="genplan-quarter-layer" aria-label="Кварталы">
                                    @foreach ($genplan->quarters as $quarter)
                                        <polygon
                                            points="{{ $geometry->svgPoints($quarter->polygon_data) }}"
                                            @class(['genplan-quarter', 'genplan-quarter--'.$quarter->status->value, 'is-selected' => $loop->first])
                                            role="button"
                                            tabindex="0"
                                            aria-label="{{ $quarter->name }} — {{ $quarter->status->label() }}"
                                            aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                            data-quarter-target="quarter-{{ $quarter->id }}"
                                        />
                                    @endforeach
                                </g>
                                <g class="genplan-marker-layer" aria-label="Объекты инфраструктуры">
                                    @foreach ($infrastructure as $point)
                                        <g
                                            class="genplan-marker"
                                            role="img"
                                            aria-label="{{ $point->name }} — {{ $point->category->label() }}"
                                            transform="translate({{ (float) $point->marker_x * 1000 }} {{ (float) $point->marker_y * 1000 }})"
                                            data-show-2d="{{ $point->show_on_2d ? 'true' : 'false' }}"
                                            data-show-3d="{{ $point->show_on_3d ? 'true' : 'false' }}"
                                        >
                                            <circle r="17" />
                                            <text text-anchor="middle" dominant-baseline="central">•</text>
                                        </g>
                                    @endforeach
                                </g>
                            </svg>
                        </div>

                        <aside class="genplan-sidebar" aria-label="Кварталы">
                            <div class="genplan-quarter-list" aria-label="Выберите квартал">
                                @forelse ($genplan->quarters as $quarter)
                                    <button
                                        type="button"
                                        @class(['is-selected' => $loop->first])
                                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                        data-quarter-target="quarter-{{ $quarter->id }}"
                                    >
                                        <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                        {{ $quarter->name }}
                                    </button>
                                @empty
                                    <p class="genplan-empty-copy">Активные кварталы пока не опубликованы.</p>
                                @endforelse
                            </div>

                            @foreach ($genplan->quarters as $quarter)
                                <article
                                    id="quarter-{{ $quarter->id }}"
                                    class="genplan-quarter-card"
                                    data-quarter-card
                                    @if (! $loop->first) hidden @endif
                                >
                                    <span class="genplan-status genplan-status--{{ $quarter->status->value }}">{{ $quarter->status->label() }}</span>
                                    <h2>{{ $quarter->name }}</h2>
                                    <p>{{ $quarter->description ?: 'Описание квартала будет опубликовано после наполнения генплана.' }}</p>
                                    <button class="button button--outline" type="button" disabled>Выбор участков — следующий этап</button>
                                </article>
                            @endforeach
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

            <div id="surroundings-panel" role="tabpanel" aria-labelledby="surroundings-tab" data-genplan-panel="surroundings" hidden>
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
                            <li>
                                <span>{{ $place->category->label() }}</span>
                                <strong>{{ $place->name }}</strong>
                            </li>
                        @empty
                            <li class="genplan-empty-copy">Активные места окружения пока не опубликованы.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </section>
@endsection
