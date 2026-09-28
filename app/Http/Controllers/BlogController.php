<?php

namespace App\Http\Controllers;

use App\Domain\Blog\ArticleContentBlocks;
use App\Domain\Seo\SeoManager;
use App\Domain\Settings\SiteSettings;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request, SiteSettings $siteSettings, SeoManager $seoManager): View
    {
        $this->validatePage($request);
        $category = trim($this->queryString($request, 'category'));
        $search = mb_substr(trim($this->queryString($request, 'q')), 0, 100);
        $date = $this->validMonth($this->queryString($request, 'date'));
        $sort = $this->queryString($request, 'sort') === 'oldest' ? 'oldest' : 'newest';

        $posts = BlogPost::query()
            ->select([
                'id', 'category_id', 'title', 'slug', 'excerpt', 'cover_image',
                'reading_time', 'published_at', 'created_at',
            ])
            ->with('category:id,name,slug')
            ->publiclyVisible()
            ->when($category !== '', fn (Builder $query): Builder => $query->inRubric($category))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $escaped = addcslashes($search, '\\%_');

                $query->where(function (Builder $query) use ($escaped): void {
                    $query
                        ->where('title', 'like', "%{$escaped}%")
                        ->orWhere('excerpt', 'like', "%{$escaped}%");
                });
            })
            ->when($date, fn (Builder $query, CarbonImmutable $month): Builder => $query->whereBetween(
                'published_at',
                [$month->startOfMonth(), $month->endOfMonth()],
            ));

        $sort === 'oldest'
            ? $posts->orderBy('published_at')->orderBy('id')
            : $posts->orderByDesc('published_at')->orderByDesc('id');

        $paginator = $posts->paginate((int) config('blog.per_page', 12))->appends(array_filter([
            'category' => $category ?: null,
            'q' => $search ?: null,
            'date' => $date?->format('Y-m'),
            'sort' => $sort === 'oldest' ? $sort : null,
        ]));

        abort_if($paginator->currentPage() > 1 && $paginator->currentPage() > $paginator->lastPage(), 404);
        $settings = $siteSettings->all();

        return view('pages.blog.index', [
            'categories' => BlogCategory::query()->active()->orderBy('sort_order')->orderBy('name')->get(),
            'posts' => $paginator,
            'filters' => compact('category', 'search', 'date', 'sort'),
            'settings' => $settings,
            'seo' => $seoManager->forPage(
                path: '/blog',
                routeTitle: $paginator->currentPage() > 1 ? 'Блог — страница '.$paginator->currentPage() : 'Блог',
                seoDescription: 'Новости, истории и полезные материалы о жизни в RelaxLand Можайский.',
                indexable: ! $this->hasContentQuery($request),
                breadcrumbs: [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Блог'],
                ],
                settings: $settings,
            ),
        ]);
    }

    public function show(
        string $slug,
        SiteSettings $siteSettings,
        ArticleContentBlocks $contentBlocks,
        SeoManager $seoManager,
    ): View {
        $post = BlogPost::query()
            ->with(['category:id,name,slug', 'tags' => fn ($query) => $query->active()])
            ->publiclyVisible()
            ->where('slug', $slug)
            ->firstOrFail();
        $settings = $siteSettings->all();

        return view('pages.blog.show', [
            'post' => $post,
            'blocks' => $contentBlocks->sanitize($post->content),
            'previousPost' => $this->previousPost($post),
            'nextPost' => $this->nextPost($post),
            'settings' => $settings,
            'seo' => $seoManager->forPage(
                path: '/blog/'.$post->slug,
                seoTitle: $post->seo_title,
                entityTitle: $post->title,
                routeTitle: 'Статья',
                seoDescription: $post->seo_description,
                summary: $post->excerpt,
                ogType: 'article',
                ogImagePath: $post->og_image ?: $post->cover_image,
                breadcrumbs: [
                    ['label' => 'Главная', 'url' => '/'],
                    ['label' => 'Блог', 'url' => '/blog'],
                    ['label' => $post->title],
                ],
                article: $post,
                settings: $settings,
            ),
        ]);
    }

    private function previousPost(BlogPost $post): ?BlogPost
    {
        return BlogPost::query()
            ->select(['id', 'title', 'slug', 'published_at'])
            ->publiclyVisible()
            ->where(function (Builder $query) use ($post): void {
                $query
                    ->where('published_at', '<', $post->published_at)
                    ->orWhere(function (Builder $query) use ($post): void {
                        $query->where('published_at', $post->published_at)->where('id', '<', $post->id);
                    });
            })
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();
    }

    private function nextPost(BlogPost $post): ?BlogPost
    {
        return BlogPost::query()
            ->select(['id', 'title', 'slug', 'published_at'])
            ->publiclyVisible()
            ->where(function (Builder $query) use ($post): void {
                $query
                    ->where('published_at', '>', $post->published_at)
                    ->orWhere(function (Builder $query) use ($post): void {
                        $query->where('published_at', $post->published_at)->where('id', '>', $post->id);
                    });
            })
            ->orderBy('published_at')
            ->orderBy('id')
            ->first();
    }

    private function validMonth(string $value): ?CarbonImmutable
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value)) {
            return null;
        }

        return CarbonImmutable::createFromFormat('!Y-m', $value) ?: null;
    }

    private function queryString(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : '';
    }

    private function validatePage(Request $request): void
    {
        $page = $request->query('page');

        abort_if($page !== null && (! is_string($page) || ! preg_match('/^[1-9]\d*$/', $page)), 404);
    }

    private function hasContentQuery(Request $request): bool
    {
        return collect(array_keys($request->query()))
            ->contains(fn (string|int $key): bool => ! is_string($key) || ! str_starts_with($key, 'utm_'));
    }
}
