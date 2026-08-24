<?php

namespace App\Http\Controllers;

use App\Domain\Genplan\GenplanPageState;
use App\Domain\Genplan\GenplanPublicQuery;
use App\Domain\Genplan\NormalizedGeometry;
use App\Domain\Genplan\PlotFilters;
use App\Domain\Genplan\StraightLineDistance;
use App\Domain\Genplan\SurroundingCategory;
use App\Domain\Seo\SeoManager;
use App\Domain\Settings\SiteSettings;
use App\Http\Resources\PlotResource;
use App\Http\Resources\SurroundingPlaceResource;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GenplanController extends Controller
{
    public function index(
        Request $request,
        GenplanPublicQuery $query,
        NormalizedGeometry $geometry,
        SiteSettings $siteSettings,
        StraightLineDistance $distance,
        SeoManager $seoManager,
    ): View {
        $genplan = $query->overviewForAllModes();
        $infrastructure = $genplan ? $query->infrastructureForAllModes() : collect();
        $surroundings = $genplan ? $query->surroundings(genplan: $genplan) : collect();
        $state = GenplanPageState::resolve(
            view: $this->queryString($request, 'view'),
            mode: $this->queryString($request, 'mode'),
            quarter: $this->queryString($request, 'quarter'),
            point: $this->queryString($request, 'point'),
            place: $this->queryString($request, 'place'),
            quarters: $genplan?->quarters ?? collect(),
            infrastructure: $infrastructure,
            surroundings: $surroundings,
        );
        $plotFilters = PlotFilters::fromArray($request->query());
        $plots = collect();
        $visiblePlots = collect();

        if ($state->selectedQuarter) {
            $plots = $query->plots($state->selectedQuarter, $state->mode);
            $visiblePlots = $plotFilters->applyToCollection($plots);
            $state = $state->withSelectedPlot($this->queryString($request, 'plot'), $visiblePlots);
        }

        $plotPayload = $plots
            ->map(fn ($plot): array => (new PlotResource($plot))->resolve($request))
            ->values();
        $settings = $siteSettings->all();
        $settlementPoint = $siteSettings->settlementPoint();
        $surroundingPayload = $surroundings->map(function ($place) use ($request, $settlementPoint, $distance): array {
            $payload = (new SurroundingPlaceResource($place))->resolve($request);
            $placePoint = $place->geographicPoint();
            $payload['distance_label'] = $settlementPoint && $placePoint
                ? $distance->label($settlementPoint, $placePoint)
                : null;

            return $payload;
        })->values();
        $surroundingCategories = collect(SurroundingCategory::cases())
            ->filter(fn (SurroundingCategory $category): bool => $surroundings->contains(
                fn ($place): bool => $place->category === $category,
            ));

        return view('pages.genplan.index', [
            'genplan' => $genplan,
            'pageState' => $state,
            'infrastructure' => $infrastructure,
            'plots' => $plots,
            'visiblePlots' => $visiblePlots,
            'plotPayload' => $plotPayload,
            'plotFilters' => $plotFilters,
            'surroundings' => $surroundings,
            'surroundingPayload' => $surroundingPayload,
            'surroundingCategories' => $surroundingCategories,
            'settlementPoint' => $settlementPoint,
            'mapConfig' => $this->mapConfig(),
            'geometry' => $geometry,
            'settings' => $settings,
            'seo' => $seoManager->forPage(
                path: '/genplan',
                routeTitle: 'Генплан',
                seoDescription: 'Генплан RelaxLand Можайский: кварталы, инфраструктура и расположение территории.',
                indexable: ! $this->hasContentQuery($request),
                settings: $settings,
            ),
        ]);
    }

    private function queryString(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : null;
    }

    private function hasContentQuery(Request $request): bool
    {
        return collect(array_keys($request->query()))
            ->contains(fn (string|int $key): bool => ! is_string($key) || ! str_starts_with($key, 'utm_'));
    }

    /** @return array<string, bool|int|string|null> */
    private function mapConfig(): array
    {
        $locale = (string) config('surroundings.locale', 'ru_RU');
        $theme = (string) config('surroundings.theme', 'light');

        return [
            'provider' => 'yandex-js-v3',
            'enabled' => (bool) config('surroundings.enabled', true),
            'apiKey' => is_string(config('surroundings.api_key')) ? config('surroundings.api_key') : null,
            'locale' => in_array($locale, ['ru_RU', 'en_RU'], true) ? $locale : 'ru_RU',
            'defaultZoom' => max(7, min(17, (int) config('surroundings.default_zoom', 11))),
            'minZoom' => 7,
            'maxZoom' => 17,
            'theme' => in_array($theme, ['light', 'dark'], true) ? $theme : 'light',
            'timeoutMs' => max(2000, min(30000, (int) config('surroundings.timeout_ms', 10000))),
            'sdkUrl' => 'https://api-maps.yandex.ru/v3/',
        ];
    }
}
