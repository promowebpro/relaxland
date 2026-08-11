@props(['items' => []])

<nav class="breadcrumbs" aria-label="Хлебные крошки">
    <ol>
        @foreach ($items as $item)
            <li>
                @if (! $loop->last && isset($item['url']))
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    <span @if ($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
