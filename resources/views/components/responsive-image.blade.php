@props([
    'path' => null,
    'mobilePath' => null,
    'alt' => '',
    'eager' => false,
])

@php
    $src = $path ? Storage::disk('public')->url($path) : null;
    $mobileSrc = $mobilePath ? Storage::disk('public')->url($mobilePath) : null;
@endphp

@if ($src || $mobileSrc)
    <picture {{ $attributes }}>
        @if ($mobileSrc)
            <source media="(max-width: 47.99rem)" srcset="{{ $mobileSrc }}">
        @endif
        <img
            src="{{ $src ?: $mobileSrc }}"
            alt="{{ $alt }}"
            loading="{{ $eager ? 'eager' : 'lazy' }}"
            decoding="async"
            @if ($eager) fetchpriority="high" @endif
        >
    </picture>
@else
    <div {{ $attributes->class('home-media-placeholder') }} role="img" aria-label="{{ $alt ?: 'Изображение RelaxLand ожидает утверждённый материал' }}">
        <span aria-hidden="true">RelaxLand</span>
    </div>
@endif
