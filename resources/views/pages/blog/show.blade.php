@extends('layouts.public')

@php
    $description = $post->seo_description ?: $post->excerpt ?: 'Статья блога RelaxLand Можайский';
    $imagePath = $post->og_image ?: $post->cover_image;
    $ogImage = $imagePath ? url(Storage::disk('public')->url($imagePath)) : null;
@endphp

@section('title', $post->title)
@section('meta_title', $post->seo_title ?: $post->title)
@section('description', $description)
@section('canonical', route('blog.show', $post->slug))
@section('og_title', $post->seo_title ?: $post->title)
@section('og_description', $description)
@section('og_type', 'article')
@if ($ogImage)
    @section('og_image', $ogImage)
@endif

@push('head')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post->title,
        'description' => $description,
        'datePublished' => $post->published_at->toAtomString(),
        'dateModified' => $post->updated_at->toAtomString(),
        'mainEntityOfPage' => route('blog.show', $post->slug),
        'image' => $ogImage,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <article class="blog-article">
        <header class="article-header">
            <div class="site-container">
                <x-breadcrumbs :items="[
                    ['label' => 'Главная', 'url' => route('home')],
                    ['label' => 'Блог', 'url' => route('blog.index')],
                    ['label' => $post->title],
                ]" />

                <div class="article-header__meta">
                    <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}">{{ $post->category->name }}</a>
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('d F Y') }}</time>
                    @if ($post->reading_time)
                        <span>{{ $post->reading_time }} мин чтения</span>
                    @endif
                </div>

                <h1>{{ $post->title }}</h1>

                @if ($post->excerpt)
                    <p class="article-header__lead">{{ $post->excerpt }}</p>
                @endif

                <div class="article-header__cover">
                    @if ($post->cover_image)
                        <img src="{{ Storage::disk('public')->url($post->cover_image) }}" alt="" fetchpriority="high">
                    @else
                        <span class="blog-image-placeholder" aria-hidden="true">RelaxLand</span>
                    @endif
                </div>
            </div>
        </header>

        <div class="article-content site-container">
            @foreach ($blocks as $block)
                @switch($block['type'])
                    @case('heading')
                        @if ($block['data']['level'] === 3)
                            <h3>{{ $block['data']['text'] }}</h3>
                        @else
                            <h2>{{ $block['data']['text'] }}</h2>
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

        <nav class="article-navigation site-container" aria-label="Другие статьи">
            @if ($previousPost)
                <a class="article-navigation__item" href="{{ route('blog.show', $previousPost->slug) }}" rel="prev">
                    <span>Предыдущая статья</span>
                    <strong>{{ $previousPost->title }}</strong>
                </a>
            @else
                <span></span>
            @endif

            @if ($nextPost)
                <a class="article-navigation__item article-navigation__item--next" href="{{ route('blog.show', $nextPost->slug) }}" rel="next">
                    <span>Следующая статья</span>
                    <strong>{{ $nextPost->title }}</strong>
                </a>
            @endif
        </nav>
    </article>
@endsection
