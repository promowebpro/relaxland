<?php

namespace App\Http\Controllers;

use App\Domain\Genplan\GenplanPageState;
use App\Domain\Genplan\GenplanPublicQuery;
use App\Domain\Genplan\NormalizedGeometry;
use App\Domain\Settings\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GenplanController extends Controller
{
    public function index(
        Request $request,
        GenplanPublicQuery $query,
        NormalizedGeometry $geometry,
        SiteSettings $siteSettings,
    ): View {
        $genplan = $query->overviewForAllModes();
        $infrastructure = $genplan ? $query->infrastructureForAllModes() : collect();
        $state = GenplanPageState::resolve(
            view: $this->queryString($request, 'view'),
            mode: $this->queryString($request, 'mode'),
            quarter: $this->queryString($request, 'quarter'),
            point: $this->queryString($request, 'point'),
            quarters: $genplan?->quarters ?? collect(),
            infrastructure: $infrastructure,
        );

        return view('pages.genplan.index', [
            'genplan' => $genplan,
            'pageState' => $state,
            'infrastructure' => $infrastructure,
            'surroundings' => $query->surroundings(),
            'geometry' => $geometry,
            'settings' => $siteSettings->all(),
        ]);
    }

    private function queryString(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : null;
    }
}
