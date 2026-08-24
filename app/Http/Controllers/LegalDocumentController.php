<?php

namespace App\Http\Controllers;

use App\Domain\Content\LegalContentSanitizer;
use App\Domain\Seo\SeoManager;
use App\Domain\Settings\SiteSettings;
use App\Models\LegalDocument;
use Illuminate\Contracts\View\View;

class LegalDocumentController extends Controller
{
    public function index(SiteSettings $siteSettings, SeoManager $seoManager): View
    {
        $settings = $siteSettings->all();

        return view('pages.legal.index', [
            'documents' => LegalDocument::query()
                ->publiclyVisible()
                ->orderByDesc('published_at')
                ->orderBy('title')
                ->get(),
            'settings' => $settings,
            'seo' => $seoManager->forPage(
                path: '/privacy',
                routeTitle: 'Конфиденциальность',
                seoDescription: 'Юридические документы RelaxLand.',
                indexable: false,
                breadcrumbs: [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Конфиденциальность'],
                ],
                settings: $settings,
            ),
        ]);
    }

    public function show(
        string $slug,
        SiteSettings $siteSettings,
        LegalContentSanitizer $sanitizer,
        SeoManager $seoManager,
    ): View {
        $document = LegalDocument::query()
            ->publiclyVisible()
            ->where('slug', $slug)
            ->firstOrFail();
        $settings = $siteSettings->all();

        return view('pages.legal.show', [
            'document' => $document,
            'renderedContent' => $sanitizer->sanitize($document->content),
            'settings' => $settings,
            'seo' => $seoManager->forPage(
                path: '/privacy/'.$document->slug,
                entityTitle: $document->title,
                routeTitle: 'Юридический документ',
                seoDescription: 'Актуальный юридический документ RelaxLand.',
                indexable: false,
                breadcrumbs: [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Конфиденциальность', 'url' => '/privacy'],
                    ['label' => $document->title],
                ],
                settings: $settings,
            ),
        ]);
    }
}
