<?php

namespace App\Http\Controllers;

use App\Domain\Blog\ArticleContentBlocks;
use App\Domain\Settings\SiteSettings;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request, SiteSettings $siteSettings): View
    {
        $category = trim((string) $request->query('category', ''));
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $date = $this->validMonth((string) $request->query('date', ''));
        $sort = $request->query('sort') === 'oldest' ? 'oldest' : 'newest';

        $posts = BlogPost::query()
            ->select([
                'id', 'category_id', 'title', 'slug', 'excerpt', 'cover_image',
                'reading_time', 'published_at', 'created_at',
            ])
            ->with('category:id,name,slug')
            ->publiclyVisible()
            ->when($category !== '', fn (Builder $query): Builder => $query->whereHas(
                'category',
                fn (Builder $query): Builder => $query->active()->where('slug', $category),
            ))
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

        return view('pages.blog.index', [
            'categories' => BlogCategory::query()->active()->orderBy('sort_order')->orderBy('name')->get(),
            'posts' => $posts->paginate((int) config('blog.per_page', 6))->withQueryString(),
            'filters' => compact('category', 'search', 'date', 'sort'),
            'settings' => $siteSettings->all(),
        ]);
    }

    public function show(
        string $slug,
        SiteSettings $siteSettings,
        ArticleContentBlocks $contentBlocks,
    ): View {
        $post = BlogPost::query()
            ->with('category:id,name,slug')
            ->publiclyVisible()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('pages.blog.show', [
            'post' => $post,
            'blocks' => $contentBlocks->sanitize($post->content),
            'previousPost' => $this->previousPost($post),
            'nextPost' => $this->nextPost($post),
            'settings' => $siteSettings->all(),
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
}
