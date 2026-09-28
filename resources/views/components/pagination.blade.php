@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="public-pagination" aria-label="Пагинация блога">
        @if ($paginator->onFirstPage())
            <span class="public-pagination__direction is-disabled" aria-disabled="true">Назад</span>
        @else
            <a class="public-pagination__direction" href="{{ $paginator->previousPageUrl() }}" rel="prev">Назад</a>
        @endif

        <div class="public-pagination__pages">
            @php
                $pages = collect([1, $paginator->lastPage()])
                    ->merge(range(max(1, $paginator->currentPage() - 1), min($paginator->lastPage(), $paginator->currentPage() + 1)))
                    ->unique()->sort()->values();
                $previousPage = 0;
            @endphp
            @foreach ($pages as $page)
                @if ($previousPage && $page - $previousPage > 1)
                    <span class="public-pagination__ellipsis" aria-hidden="true">…</span>
                @endif
                @if ($page === $paginator->currentPage())
                    <span class="public-pagination__page is-current" aria-current="page">{{ $page }}</span>
                @else
                    <a class="public-pagination__page" href="{{ $paginator->url($page) }}" aria-label="Страница {{ $page }}">{{ $page }}</a>
                @endif
                @php($previousPage = $page)
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a class="public-pagination__direction" href="{{ $paginator->nextPageUrl() }}" rel="next">Вперёд</a>
        @else
            <span class="public-pagination__direction is-disabled" aria-disabled="true">Вперёд</span>
        @endif
    </nav>
@endif
