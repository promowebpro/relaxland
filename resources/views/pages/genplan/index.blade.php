@extends('layouts.public')

@section('title', 'Генплан')
@section('description', 'Генплан RelaxLand Можайский: кварталы, инфраструктура и расположение территории.')
@section('body_class', 'genplan-page')

@php
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $initialMode = $pageState->mode;
    $initialQuarter = $pageState->selectedQuarter?->slug;
    $initialPoint = $pageState->selectedInfrastructure?->slug;
    $initialPlot = $pageState->selectedPlot?->slug;
    $initialPlace = $pageState->selectedSurroundingPlace?->slug;
    $plotsOpen = (bool) $pageState->selectedQuarter;
    $initialPlotKey = $plotsOpen ? $initialQuarter.'|'.$initialMode->value : null;
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
    $initialSurroundingCategories = $surroundingCategories->map->value->implode(',');
    $stateUrl = function (array $state = []) use ($pageState, $initialMode, $initialQuarter, $initialPoint, $initialPlot, $initialPlace, $plotFilters) {
        $query = array_merge([
            'view' => $pageState->activeTab === 'surroundings' ? 'surroundings' : null,
            'mode' => $initialMode->value,
            'quarter' => $initialQuarter,
            'point' => $initialPoint,
            'plot' => $initialPlot,
            'place' => $initialPlace,
            ...$plotFilters->query(),
        ], $state);

        if (($query['view'] ?? null) === 'surroundings') {
            $query = [
                'view' => 'surroundings',
                'place' => $query['place'] ?? null,
            ];
        } else {
            unset($query['view'], $query['place']);
        }

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
        data-initial-plot="{{ $initialPlot }}"
        data-initial-place="{{ $initialPlace }}"
        data-initial-surrounding-categories="{{ $initialSurroundingCategories }}"
        data-initial-plots-open="{{ $plotsOpen ? 'true' : 'false' }}"
        data-initial-plots-loaded-for="{{ $initialPlotKey }}"
        data-initial-plot-status="{{ $plotFilters->status?->value }}"
        data-initial-area-min="{{ $plotFilters->areaMin }}"
        data-initial-area-max="{{ $plotFilters->areaMax }}"
        data-initial-price-min="{{ $plotFilters->priceMin }}"
        data-initial-price-max="{{ $plotFilters->priceMax }}"
        data-initial-plot-sort="{{ $plotFilters->sort }}"
        data-plots-endpoint-template="{{ route('api.genplan.quarters.plots', '__quarter__') }}"
        data-initial-incomplete="{{ $pageState->incomplete ? 'true' : 'false' }}"
        data-loading="false"
    >
        <div class="site-container">
            <div class="genplan-view-tabs" role="tablist" aria-label="Режим территории" data-genplan-view-tabs>
                <a href="{{ $stateUrl(['view' => null, 'place' => null]) }}" role="tab" aria-selected="{{ $pageState->activeTab === 'genplan' ? 'true' : 'false' }}" aria-controls="genplan-panel" id="genplan-tab" @if ($pageState->activeTab !== 'genplan') tabindex="-1" @endif data-genplan-view="genplan">Генплан</a>
                <a href="{{ $stateUrl(['view' => 'surroundings', 'quarter' => null, 'point' => null, 'plot' => null, 'place' => null]) }}" role="tab" aria-selected="{{ $pageState->activeTab === 'surroundings' ? 'true' : 'false' }}" aria-controls="surroundings-panel" id="surroundings-tab" @if ($pageState->activeTab !== 'surroundings') tabindex="-1" @endif data-genplan-view="surroundings">Окружение</a>
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
                                <a href="{{ $stateUrl(['mode' => $mode->value, 'quarter' => $modeQuarter, 'point' => $modePoint, 'plot' => $modeQuarter ? $initialPlot : null]) }}" role="button" aria-pressed="{{ $mode === $initialMode ? 'true' : 'false' }}" data-genplan-mode="{{ $mode->value }}">{{ strtoupper($mode->value) }}</a>
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
                                <g data-plot-layer aria-label="Участки выбранного квартала">
                                    @foreach ($visiblePlots as $plot)
                                        @php $plotGeometry = $plot->geometries->first(); @endphp
                                        @if ($plotGeometry?->polygon_data)
                                            <polygon
                                                points="{{ $geometry->svgPoints($plotGeometry->polygon_data) }}"
                                                @class(['genplan-plot', 'genplan-plot--'.$plot->status->value, 'is-selected' => $initialPlot === $plot->slug])
                                                role="button" tabindex="0"
                                                aria-label="Участок №{{ $plot->number }}, {{ $plot->status->label() }}, {{ \App\Domain\Genplan\PlotPresentation::area($plot->area) }}"
                                                aria-pressed="{{ $initialPlot === $plot->slug ? 'true' : 'false' }}"
                                                data-plot-trigger data-plot-slug="{{ $plot->slug }}"
                                            />
                                        @elseif ($plotGeometry && $plotGeometry->marker_x !== null && $plotGeometry->marker_y !== null)
                                            <circle
                                                cx="{{ (float) $plotGeometry->marker_x * 1000 }}" cy="{{ (float) $plotGeometry->marker_y * 1000 }}" r="22"
                                                @class(['genplan-plot-marker', 'genplan-plot-marker--'.$plot->status->value, 'is-selected' => $initialPlot === $plot->slug])
                                                role="button" tabindex="0"
                                                aria-label="Участок №{{ $plot->number }}, {{ $plot->status->label() }}, {{ \App\Domain\Genplan\PlotPresentation::area($plot->area) }}"
                                                aria-pressed="{{ $initialPlot === $plot->slug ? 'true' : 'false' }}"
                                                data-plot-trigger data-plot-slug="{{ $plot->slug }}"
                                            />
                                        @endif
                                    @endforeach
                                </g>
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
                                                href="{{ $stateUrl(['mode' => $mode->value, 'quarter' => null, 'point' => $point->slug, 'plot' => null]) }}"
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
                                        href="{{ $stateUrl(['quarter' => $quarter->slug, 'point' => null, 'plot' => null]) }}"
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

                            <div class="genplan-selection" aria-live="polite" data-genplan-selection @if ($plotsOpen) hidden @endif>
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
                                        <x-button :href="$stateUrl(['quarter' => $quarter->slug, 'point' => null, 'plot' => null]).'#plot-selection'" variant="outline" data-plots-open data-quarter-slug="{{ $quarter->slug }}">Выбрать участок</x-button>
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

                            <section class="genplan-plots" id="plot-selection" aria-labelledby="plot-selection-heading" data-plots-panel @if (! $plotsOpen) hidden @endif>
                                <header class="genplan-plots__header">
                                    <button type="button" class="genplan-plots__back" data-plots-back>← К кварталу</button>
                                    <div><span class="eyebrow">Выбор участка</span><h2 id="plot-selection-heading">{{ $pageState->selectedQuarter ? 'Участки — '.$pageState->selectedQuarter->name : 'Участки квартала' }}</h2></div>
                                </header>

                                <form class="genplan-plot-filters" method="get" action="{{ route('genplan.index') }}" data-plot-filters>
                                    <input type="hidden" name="mode" value="{{ $initialMode->value }}" data-filter-mode>
                                    <input type="hidden" name="quarter" value="{{ $initialQuarter }}" data-filter-quarter>
                                    <label>Статус
                                        <select name="status">
                                            <option value="">Все публичные</option>
                                            @foreach (\App\Domain\Genplan\PlotStatus::cases() as $status)
                                                @if ($status !== \App\Domain\Genplan\PlotStatus::Hidden)
                                                    <option value="{{ $status->value }}" @selected($plotFilters->status === $status)>{{ $status->label() }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </label>
                                    <label>Площадь от, сот.<input type="number" name="area_min" min="0" step="0.01" value="{{ $plotFilters->areaMin }}" inputmode="decimal"></label>
                                    <label>Площадь до, сот.<input type="number" name="area_max" min="0" step="0.01" value="{{ $plotFilters->areaMax }}" inputmode="decimal"></label>
                                    <label>Цена от, ₽<input type="number" name="price_min" min="0" step="0.01" value="{{ $plotFilters->priceMin }}" inputmode="decimal"></label>
                                    <label>Цена до, ₽<input type="number" name="price_max" min="0" step="0.01" value="{{ $plotFilters->priceMax }}" inputmode="decimal"></label>
                                    <label>Сортировка
                                        <select name="sort">
                                            <option value="default" @selected($plotFilters->sort === 'default')>По номеру</option>
                                            <option value="price_asc" @selected($plotFilters->sort === 'price_asc')>Сначала дешевле</option>
                                            <option value="area_asc" @selected($plotFilters->sort === 'area_asc')>Площадь: меньше</option>
                                            <option value="area_desc" @selected($plotFilters->sort === 'area_desc')>Площадь: больше</option>
                                        </select>
                                    </label>
                                    <p class="genplan-plot-filters__error" role="alert" data-plot-filter-error hidden></p>
                                    <div class="genplan-plot-filters__actions"><button type="submit">Применить</button><button type="button" data-plot-filters-reset>Сбросить</button></div>
                                </form>

                                <p class="genplan-plots__state" role="status" data-plots-loading hidden>Загружаем участки…</p>
                                <div class="genplan-plots__state genplan-plots__state--error" role="alert" data-plots-error hidden><p>Не удалось загрузить участки. Проверьте соединение и попробуйте ещё раз.</p><button type="button" data-plots-retry>Повторить</button></div>
                                <div class="genplan-plots__state" data-plots-empty @if ($visiblePlots->isNotEmpty() || ! $plotsOpen) hidden @endif><p>По выбранным условиям участков нет.</p><button type="button" data-plot-filters-reset>Сбросить фильтры</button></div>

                                <div class="genplan-plot-list" data-plot-list aria-label="Участки квартала">
                                    @foreach ($visiblePlots as $plot)
                                        @php $plotGeometry = $plot->geometries->first(); @endphp
                                        <a href="{{ $stateUrl(['plot' => $plot->slug]) }}#plot-selection" @class(['genplan-plot-item', 'is-selected' => $initialPlot === $plot->slug]) aria-pressed="{{ $initialPlot === $plot->slug ? 'true' : 'false' }}" data-plot-item data-plot-trigger data-plot-slug="{{ $plot->slug }}">
                                            <span><strong>Участок №{{ $plot->number }}</strong><small>{{ \App\Domain\Genplan\PlotPresentation::area($plot->area) }}</small></span>
                                            <span><strong>{{ \App\Domain\Genplan\PlotPresentation::money($plot->price) }}</strong><small>{{ $plot->status->label() }}</small></span>
                                            @if (! $plotGeometry)<small class="genplan-plot-item__geometry">На этом виде нет отметки</small>@endif
                                        </a>
                                    @endforeach
                                </div>

                                <article class="genplan-plot-card" data-plot-card @if (! $initialPlot) hidden @endif>
                                    <button class="genplan-card-close" type="button" aria-label="Закрыть карточку участка" data-plot-close>×</button>
                                    @if ($pageState->selectedPlot)
                                        <span class="genplan-status genplan-status--{{ $pageState->selectedPlot->status->value }}" data-plot-card-status>{{ $pageState->selectedPlot->status->label() }}</span>
                                        <h3 data-plot-card-title>Участок №{{ $pageState->selectedPlot->number }}</h3>
                                        <dl>
                                            <div><dt>Площадь</dt><dd data-plot-card-area>{{ \App\Domain\Genplan\PlotPresentation::area($pageState->selectedPlot->area) }}</dd></div>
                                            <div><dt>Цена</dt><dd data-plot-card-price>{{ \App\Domain\Genplan\PlotPresentation::money($pageState->selectedPlot->price) }}</dd></div>
                                            <div><dt>За сотку</dt><dd data-plot-card-unit>{{ \App\Domain\Genplan\PlotPresentation::moneyPerSotka($pageState->selectedPlot->price_per_sotka) }}</dd></div>
                                        </dl>
                                        <p data-plot-card-description>{{ $pageState->selectedPlot->description ?: 'Подробности участка уточнит менеджер проекта.' }}</p>
                                        @if ($pageState->selectedPlot->status->canInquire())
                                            <x-button href="#lead-form" variant="primary" data-plot-card-cta data-lead-modal-trigger data-lead-source="genplan-preview" data-lead-form-type="consultation" data-lead-heading="Узнать об участке №{{ $pageState->selectedPlot->number }}" data-lead-quarter="{{ $initialQuarter }}" data-lead-plot="{{ $initialPlot }}">Узнать об участке</x-button>
                                        @endif
                                    @endif
                                </article>
                            </section>
                        </aside>
                    </div>
                    <script type="application/json" data-initial-plots>@json($plotPayload)</script>
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
                <section class="surroundings" aria-labelledby="surroundings-heading" data-surroundings-root data-provider-status="idle">
                    <header class="surroundings__header">
                        <div>
                            <span class="eyebrow">Окружение</span>
                            <h2 id="surroundings-heading">Самое важное рядом</h2>
                        </div>
                        <p>Посмотрите, где расположен RelaxLand и какие места доступны поблизости. Расстояния указаны по прямой, а не по автомобильному маршруту.</p>
                    </header>

                    <div class="surroundings__location">
                        <span class="surroundings-category-icon surroundings-category-icon--settlement" aria-hidden="true">R</span>
                        <div>
                            <strong>Посёлок RelaxLand</strong>
                            <p>{{ $settings['contacts.village_address'] ?: 'Адрес посёлка пока не опубликован.' }}</p>
                            @if ($settlementPoint)
                                <small>{{ $settlementPoint->latitude }}, {{ $settlementPoint->longitude }}</small>
                            @else
                                <small class="surroundings__incomplete">Координаты посёлка ещё не заполнены — список мест остаётся доступным.</small>
                            @endif
                        </div>
                    </div>

                    @if ($surroundingCategories->isNotEmpty())
                        <div class="surroundings-filters" aria-label="Категории мест">
                            @foreach ($surroundingCategories as $category)
                                <button type="button" aria-pressed="true" data-surrounding-category="{{ $category->value }}">
                                    <span class="surroundings-category-icon surroundings-category-icon--{{ $category->value }}" aria-hidden="true">{{ $category->symbol() }}</span>
                                    {{ $category->label() }}
                                </button>
                            @endforeach
                            <button type="button" class="surroundings-filters__all" data-surrounding-show-all>Показать все</button>
                        </div>
                    @endif

                    <div class="surroundings-layout">
                        <div class="surroundings-map-shell">
                            <div class="surroundings-map" aria-label="Интерактивная карта окружения" aria-describedby="surroundings-map-help" data-surroundings-map></div>
                            <p class="surroundings-map__loading" role="status" data-map-loading hidden>Загружаем карту…</p>
                            <div class="surroundings-map__fallback" role="status" data-map-fallback>
                                <strong>Карта дополняет список</strong>
                                <p data-map-fallback-message>Интерактивная карта загрузится только при настроенном публичном ключе Яндекс Карт.</p>
                                <button type="button" data-map-retry hidden>Повторить</button>
                            </div>
                            <div class="surroundings-map-controls" aria-label="Управление картой">
                                <button type="button" aria-label="Увеличить масштаб" data-map-zoom-in>+</button>
                                <button type="button" aria-label="Уменьшить масштаб" data-map-zoom-out>−</button>
                                <button type="button" data-map-fit>Показать все</button>
                                @if ($settlementPoint)<button type="button" data-map-settlement>К посёлку</button>@endif
                            </div>
                            <p class="surroundings-map__help" id="surroundings-map-help">На телефоне карта масштабируется двумя пальцами; страницу можно прокручивать обычным жестом.</p>
                        </div>

                        <aside class="surroundings-sidebar" aria-label="Места рядом">
                            <ul class="surroundings-list" data-surroundings-list>
                                @forelse ($surroundings as $place)
                                    @php $placeData = $surroundingPayload->firstWhere('slug', $place->slug); @endphp
                                    <li
                                        @class(['surroundings-list__item', 'is-selected' => $initialPlace === $place->slug])
                                        data-surrounding-item
                                        data-place-slug="{{ $place->slug }}"
                                        data-place-category="{{ $place->category->value }}"
                                    >
                                        <a
                                            href="{{ $stateUrl(['view' => 'surroundings', 'place' => $place->slug]) }}#surroundings-place-{{ $place->slug }}"
                                            aria-pressed="{{ $initialPlace === $place->slug ? 'true' : 'false' }}"
                                            data-surrounding-trigger
                                            data-place-slug="{{ $place->slug }}"
                                            data-place-category="{{ $place->category->value }}"
                                        >
                                            <span class="surroundings-category-icon surroundings-category-icon--{{ $place->category->value }}" aria-hidden="true">{{ $place->category->symbol() }}</span>
                                            <span><small>{{ $place->category->label() }}</small><strong>{{ $place->name }}</strong>@if ($placeData['distance_label'])<em>{{ $placeData['distance_label'] }}</em>@endif</span>
                                        </a>
                                        @if ($placeData['external_url'])
                                            <a class="surroundings-list__route" href="{{ $placeData['external_url'] }}" target="_blank" rel="noopener noreferrer">Маршрут<span class="sr-only"> к месту {{ $place->name }}</span></a>
                                        @endif
                                    </li>
                                @empty
                                    <li class="genplan-empty-copy">Активные места окружения пока не опубликованы.</li>
                                @endforelse
                            </ul>
                            <div class="surroundings-empty" data-surroundings-empty hidden>
                                <p>В выбранных категориях мест нет.</p>
                                <button type="button" data-surrounding-show-all>Показать все категории</button>
                            </div>

                            @foreach ($surroundings as $place)
                                @php $placeData = $surroundingPayload->firstWhere('slug', $place->slug); @endphp
                                <article
                                    class="surroundings-card"
                                    id="surroundings-place-{{ $place->slug }}"
                                    data-surrounding-card="{{ $place->slug }}"
                                    @if ($initialPlace !== $place->slug) hidden @endif
                                >
                                    <button type="button" class="genplan-card-close" aria-label="Закрыть карточку {{ $place->name }}" data-surrounding-close>×</button>
                                    @if ($placeData['image_url'])
                                        <img src="{{ $placeData['image_url'] }}" alt="" loading="lazy">
                                    @endif
                                    <span class="genplan-point-category">{{ $place->category->label() }}</span>
                                    <h3>{{ $place->name }}</h3>
                                    @if ($place->description)<p>{{ $place->description }}</p>@endif
                                    @if ($placeData['distance_label'])<p class="surroundings-card__distance">{{ $placeData['distance_label'] }}</p>@endif
                                    @if ($placeData['external_url'])
                                        <a class="button button--primary" href="{{ $placeData['external_url'] }}" target="_blank" rel="noopener noreferrer">Открыть маршрут</a>
                                    @endif
                                </article>
                            @endforeach
                        </aside>
                    </div>
                </section>
            </div>
            <script type="application/json" data-surroundings-data>@json($surroundingPayload)</script>
            <script type="application/json" data-surroundings-config>@json($mapConfig)</script>
            <script type="application/json" data-settlement-data>@json($settlementPoint?->numeric())</script>
        </div>
    </section>
@endsection
