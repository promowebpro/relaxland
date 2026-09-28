<?php

namespace App\Http\Controllers;

use App\Domain\Seo\SeoManager;
use App\Domain\Settings\SiteSettings;
use App\Models\AboutPage;
use App\Models\BlogPost;
use App\Models\HomePage;
use App\Models\Story;
use Illuminate\Contracts\View\View;

class PublicPageController extends Controller
{
    public function home(SiteSettings $siteSettings, SeoManager $seoManager): View
    {
        $home = HomePage::query()->active()->first();
        $settings = $siteSettings->all();

        if (! $home) {
            $home = new HomePage(HomePage::defaultContent());
        }

        return view('pages.home', [
            'home' => $home,
            'settings' => $settings,
            'stories' => Story::query()->active()->orderBy('sort_order')->orderBy('id')->limit(6)->get(),
            'blogPosts' => BlogPost::query()
                ->select(['id', 'category_id', 'title', 'slug', 'excerpt', 'cover_image', 'reading_time', 'status', 'published_at'])
                ->with('category')
                ->publiclyVisible()
                ->latest('published_at')
                ->latest('id')
                ->limit(4)
                ->get(),
            'seo' => $seoManager->forPage(
                path: '/',
                seoTitle: $home->seo_title,
                entityTitle: $home->hero_title,
                routeTitle: 'RelaxLand Можайский',
                seoDescription: $home->seo_description,
                summary: $home->hero_description,
                ogImagePath: $home->og_image ?: $home->hero_image,
                settings: $settings,
            ),
        ]);
    }

    public function contacts(SiteSettings $siteSettings, SeoManager $seoManager): View
    {
        $settings = $siteSettings->all();
        $visitContent = AboutPage::pageContent();
        foreach ([
            'map_latitude' => 'contacts.village_latitude',
            'map_longitude' => 'contacts.village_longitude',
            'travel_text' => 'contacts.travel_time',
            'route_yandex' => 'routes.yandex',
            'route_google' => 'routes.google',
            'route_two_gis' => 'routes.two_gis',
        ] as $field => $setting) {
            if (filled($settings[$setting])) {
                $visitContent[$field] = $settings[$setting];
            }
        }

        return view('pages.contacts', [
            'settings' => $settings,
            'visitContent' => $visitContent,
            'seo' => $seoManager->forPage(
                path: '/contacts',
                routeTitle: 'Контакты',
                seoDescription: 'Контакты RelaxLand Можайский, адреса и способы построить маршрут.',
                breadcrumbs: [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Контакты'],
                ],
                settings: $settings,
            ),
        ]);
    }

    public function about(SiteSettings $siteSettings, SeoManager $seoManager): View
    {
        $about = AboutPage::pageContent();
        $settings = $siteSettings->all();

        return view('pages.about', [
            'about' => $about,
            'settings' => $settings,
            'seo' => $seoManager->forPage(
                path: '/about',
                routeTitle: $about['title'],
                seoTitle: $about['seo_title'],
                seoDescription: $about['seo_description'],
                summary: $about['trust_text'],
                breadcrumbs: [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'О нас'],
                ],
                settings: $settings,
            ),
        ]);
    }

    public function success(SiteSettings $siteSettings, SeoManager $seoManager): View
    {
        $settings = $siteSettings->all();

        return view('pages.success', [
            'settings' => $settings,
            'seo' => $seoManager->forPage(
                path: '/thanks',
                routeTitle: 'Спасибо',
                seoDescription: 'Заявка успешно отправлена.',
                indexable: false,
                follow: false,
                settings: $settings,
            ),
        ]);
    }
}
