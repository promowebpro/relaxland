<?php

namespace App\Http\Controllers;

use App\Domain\Genplan\GenplanMode;
use App\Domain\Genplan\GenplanPublicQuery;
use App\Domain\Genplan\NormalizedGeometry;
use App\Domain\Settings\SiteSettings;
use Illuminate\Contracts\View\View;

class GenplanController extends Controller
{
    public function index(
        GenplanPublicQuery $query,
        NormalizedGeometry $geometry,
        SiteSettings $siteSettings,
    ): View {
        $genplan = $query->overviewForAllModes();

        return view('pages.genplan.index', [
            'genplan' => $genplan,
            'defaultMode' => GenplanMode::default(),
            'infrastructure' => $genplan ? $query->infrastructureForAllModes() : collect(),
            'surroundings' => $query->surroundings(),
            'geometry' => $geometry,
            'settings' => $siteSettings->all(),
        ]);
    }
}
