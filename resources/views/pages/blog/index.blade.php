@extends('layouts.public')

@section('title', 'Блог')
@section('description', 'Новости, истории и полезные материалы о жизни в RelaxLand Можайский.')
@section('canonical', route('blog.index'))

@section('content')
    <section class="page-intro page-intro--blog">
        <div class="site-container">
            <x-breadcrumbs :items="$seo->breadcrumbs" />

            <h1>Блог</h1>
        </div>
    </section>

    <section class="blog-index section-spacing" aria-labelledby="blog-results-title">
        <div class="site-container">
            @php($preserved = request()->only(['q', 'date', 'sort']))
            <nav class="blog-categories" aria-label="Категории блога">
                <a @class(['is-active' => $filters['category'] === '']) href="{{ route('blog.index', $preserved) }}">Все</a>
                @foreach ($categories as $category)
                    <a
                        @class(['is-active' => $filters['category'] === $category->slug])
                        href="{{ route('blog.index', array_merge($preserved, ['category' => $category->slug])) }}"
                    >{{ $category->name }}</a>
                @endforeach
            </nav>

            <form class="blog-filters" action="{{ route('blog.index') }}" method="get" role="search">
                @if ($filters['category'])
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                @endif

                <label class="blog-filter blog-filter--search">
                    <span>Поиск по блогу</span>
                    <input type="search" name="q" value="{{ $filters['search'] }}" placeholder="Например, лес или стройка">
                </label>

                <label class="blog-filter">
                    <span>Месяц публикации</span>
                    <input type="month" name="date" value="{{ $filters['date']?->format('Y-m') }}">
                </label>

                <label class="blog-filter">
                    <span>По дате</span>
                    <select name="sort">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Сначала новые</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>Сначала старые</option>
                    </select>
                </label>

                <button class="button button--primary" type="submit">Показать</button>

                @if ($filters['category'] || $filters['search'] || $filters['date'] || $filters['sort'] !== 'newest')
                    <a class="blog-filters__reset" href="{{ route('blog.index') }}">Сбросить</a>
                @endif
            </form>

            <h2 class="sr-only" id="blog-results-title">Последние публикации</h2>

            @if ($posts->isEmpty())
                <div class="blog-empty">
                    <span class="eyebrow">Ничего не найдено</span>
                    <h2>Попробуйте изменить параметры</h2>
                    <p>В выбранной категории или периоде пока нет опубликованных статей.</p>
                    <x-button :href="route('blog.index')" variant="outline">Показать все</x-button>
                </div>
            @else
                <div class="blog-grid">
                    @foreach ($posts as $post)
                        <x-blog-card :post="$post" :featured="$loop->first && $posts->currentPage() === 1" />
                    @endforeach
                </div>

                <x-pagination :paginator="$posts" />
            @endif
        </div>
    </section>
@endsection
