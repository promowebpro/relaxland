<?php

namespace App\Http\Controllers;

use App\Domain\Settings\SiteSettings;
use Illuminate\Contracts\View\View;

class PublicPageController extends Controller
{
    public function contacts(SiteSettings $siteSettings): View
    {
        return view('pages.contacts', [
            'settings' => $siteSettings->all(),
        ]);
    }

    public function success(SiteSettings $siteSettings): View
    {
        return view('pages.success', [
            'settings' => $siteSettings->all(),
        ]);
    }
}
