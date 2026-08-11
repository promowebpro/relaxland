@props(['paginator'])

@if ($paginator->hasPages())
    <nav class="public-pagination" aria-label="Пагинация блога">
        @if ($paginator->onFirstPage())
            <span class="public-pagination__direction is-disabled" aria-disabled="true">Назад</span>
        @else
            <a class="public-pagination__direction" href="{{ $paginator->previousPageUrl() }}" rel="prev">Назад</a>
        @endif

        <div class="public-pagination__pages">
            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                @if ($page === $paginator->currentPage())
                    <span class="public-pagination__page is-current" aria-current="page">{{ $page }}</span>
                @else
                    <a class="public-pagination__page" href="{{ $url }}" aria-label="Страница {{ $page }}">{{ $page }}</a>
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a class="public-pagination__direction" href="{{ $paginator->nextPageUrl() }}" rel="next">Вперёд</a>
        @else
            <span class="public-pagination__direction is-disabled" aria-disabled="true">Вперёд</span>
        @endif
    </nav>
@endif
