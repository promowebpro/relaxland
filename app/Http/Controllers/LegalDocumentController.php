<?php

namespace App\Http\Controllers;

use App\Domain\Content\LegalContentSanitizer;
use App\Domain\Settings\SiteSettings;
use App\Models\LegalDocument;
use Illuminate\Contracts\View\View;

class LegalDocumentController extends Controller
{
    public function index(SiteSettings $siteSettings): View
    {
        return view('pages.legal.index', [
            'documents' => LegalDocument::query()
                ->publiclyVisible()
                ->orderByDesc('published_at')
                ->orderBy('title')
                ->get(),
            'settings' => $siteSettings->all(),
        ]);
    }

    public function show(
        string $slug,
        SiteSettings $siteSettings,
        LegalContentSanitizer $sanitizer,
    ): View {
        $document = LegalDocument::query()
            ->publiclyVisible()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('pages.legal.show', [
            'document' => $document,
            'renderedContent' => $sanitizer->sanitize($document->content),
            'settings' => $siteSettings->all(),
        ]);
    }
}
