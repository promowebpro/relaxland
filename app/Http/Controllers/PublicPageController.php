<?php

namespace App\Http\Controllers;

use App\Domain\Settings\SiteSettings;
use App\Models\BlogPost;
use App\Models\HomePage;
use App\Models\Story;
use Illuminate\Contracts\View\View;

class PublicPageController extends Controller
{
    public function home(SiteSettings $siteSettings): View
    {
        $home = HomePage::query()->active()->first();

        if (! $home) {
            $home = new HomePage(HomePage::defaultContent());
        }

        return view('pages.home', [
            'home' => $home,
            'settings' => $siteSettings->all(),
            'stories' => Story::query()->active()->orderBy('sort_order')->orderBy('id')->limit(6)->get(),
            'blogPosts' => BlogPost::query()
                ->select(['id', 'category_id', 'title', 'slug', 'excerpt', 'cover_image', 'reading_time', 'status', 'published_at'])
                ->with('category')
                ->publiclyVisible()
                ->latest('published_at')
                ->latest('id')
                ->limit(3)
                ->get(),
        ]);
    }

    public function contacts(SiteSettings $siteSettings): View
    {
        return view('pages.contacts', [
            'settings' => $siteSettings->all(),
        ]);
    }

    public function about(SiteSettings $siteSettings): View
    {
        $home = HomePage::query()->active()->first() ?: new HomePage(HomePage::defaultContent());

        return view('pages.about', [
            'home' => $home,
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
