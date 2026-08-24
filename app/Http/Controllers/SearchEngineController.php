<?php

namespace App\Http\Controllers;

use App\Domain\Seo\CanonicalUrl;
use App\Models\BlogPost;
use Illuminate\Http\Response;

class SearchEngineController extends Controller
{
    public function robots(CanonicalUrl $canonicalUrl): Response
    {
        if (! config('seo.indexing_enabled')) {
            return response("User-agent: *\nDisallow: /\n", 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Cache-Control' => 'no-cache, must-revalidate',
            ]);
        }

        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /api',
            'Disallow: /thanks',
            'Disallow: /up',
            'Sitemap: '.$canonicalUrl->to('/sitemap.xml'),
            '',
        ]);

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    public function sitemap(CanonicalUrl $canonicalUrl): Response
    {
        $static = collect(['/', '/about', '/contacts', '/blog', '/genplan'])
            ->map(fn (string $path): array => ['loc' => $canonicalUrl->to($path), 'lastmod' => null]);
        $articles = BlogPost::query()
            ->select(['slug', 'published_at', 'updated_at'])
            ->publiclyVisible()
            ->orderBy('slug')
            ->get()
            ->map(fn (BlogPost $post): array => [
                'loc' => $canonicalUrl->to('/blog/'.$post->slug),
                'lastmod' => ($post->updated_at ?: $post->published_at)?->toDateString(),
            ]);

        return response()->view(
            'seo.sitemap',
            ['urls' => $static->concat($articles)->values()],
            200,
            [
                'Content-Type' => 'application/xml; charset=UTF-8',
                'Cache-Control' => 'no-cache, must-revalidate',
            ],
        );
    }
}
