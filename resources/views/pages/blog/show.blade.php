@extends('layouts.public')

@section('title', $post->title)

@section('content')
    <article class="blog-article">
        <header class="article-header">
            <div class="site-container">
                <x-breadcrumbs :items="$seo->breadcrumbs" />

                <h1>{{ $post->title }}</h1>

                <div class="article-header__cover">
                    @if ($post->cover_image)
                        <img src="{{ Storage::disk('public')->url($post->cover_image) }}" alt="{{ $post->title }}" fetchpriority="high">
                    @else
                        <img src="{{ asset('assets/design/blog-'.str_pad((string) (($post->id - 1) % 6 + 1), 2, '0', STR_PAD_LEFT).'.webp') }}" alt="{{ $post->title }}" fetchpriority="high">
                    @endif
                </div>
            </div>
        </header>

        <div class="article-layout site-container">
            <aside class="article-sidebar">
                <div class="article-sidebar__details">
                <h2>Детали статьи</h2>
                <dl>
                    <div><dt>Дата</dt><dd><time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('d.m.Y') }}</time></dd></div>
                    <div><dt>Теги</dt><dd class="article-sidebar__tags">@foreach ($post->tags->isNotEmpty() ? $post->tags : collect([$post->category]) as $tag)<a href="{{ route('blog.index', ['category' => $tag->slug]) }}">{{ $tag->name }}</a>@endforeach</dd></div>
                    @if ($post->reading_time)<div><dt>Чтение</dt><dd>{{ $post->reading_time }} минут</dd></div>@endif
                </dl>
                </div>
                @php($headings = collect($blocks)->where('type', 'heading'))
                @if ($headings->isNotEmpty())
                    <nav aria-label="Содержание статьи">
                        @foreach ($headings as $index => $heading)<a href="#article-section-{{ $index }}">{{ $heading['data']['text'] }}</a>@endforeach
                    </nav>
                @endif
            </aside>
            <div class="article-content">
            @foreach ($blocks as $blockIndex => $block)
                @switch($block['type'])
                    @case('heading')
                        @if ($block['data']['level'] === 3)
                            <h3 id="article-section-{{ $blockIndex }}">{{ $block['data']['text'] }}</h3>
                        @else
                            <h2 id="article-section-{{ $blockIndex }}">{{ $block['data']['text'] }}</h2>
                        @endif
                        @break

                    @case('rich_text')
                        <div class="article-rich-text">{!! $block['data']['html'] !!}</div>
                        @break

                    @case('list')
                        @if ($block['data']['style'] === 'ordered')
                            <ol>@foreach ($block['data']['items'] as $item)<li>{{ $item }}</li>@endforeach</ol>
                        @else
                            <ul>@foreach ($block['data']['items'] as $item)<li>{{ $item }}</li>@endforeach</ul>
                        @endif
                        @break

                    @case('image')
                        <x-article-image :image="$block['data']" />
                        @break

                    @case('wide_image')
                        <x-article-image :image="$block['data']" wide />
                        @break

                    @case('gallery')
                        <div class="article-gallery">
                            @foreach ($block['data']['images'] as $image)
                                <x-article-image :image="$image" />
                            @endforeach
                        </div>
                        @break
                @endswitch
            @endforeach
            </div>
        </div>

        <nav class="article-navigation site-container" aria-label="Другие статьи">
            @if ($previousPost)
                <a class="article-navigation__item" href="{{ route('blog.show', $previousPost->slug) }}" rel="prev">
                    <span>Предыдущий</span>
                    <strong>{{ $previousPost->title }}</strong>
                </a>
            @else
                <span></span>
            @endif

            @if ($nextPost)
                <a class="article-navigation__item article-navigation__item--next" href="{{ route('blog.show', $nextPost->slug) }}" rel="next">
                    <span>Следующий</span>
                    <strong>{{ $nextPost->title }}</strong>
                </a>
            @endif
        </nav>
    </article>
@endsection
