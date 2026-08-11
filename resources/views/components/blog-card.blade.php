@props(['post', 'featured' => false])

@php($fallbackCover = asset('assets/design/blog-'.str_pad((string) ((($post->id ?? 1) - 1) % 6 + 1), 2, '0', STR_PAD_LEFT).'.webp'))

<article @class(['blog-card', 'blog-card--featured' => $featured])>
    <a class="blog-card__cover" href="{{ route('blog.show', $post->slug) }}" tabindex="-1" aria-hidden="true">
        @if ($post->cover_image)
            <img src="{{ Storage::disk('public')->url($post->cover_image) }}" alt="" loading="lazy">
        @else
            <img src="{{ $fallbackCover }}" alt="" loading="lazy">
        @endif
    </a>

    <div class="blog-card__body">
        <h2><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h2>
        <div class="blog-card__meta">
            <span>{{ $post->category->name }}</span>
            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('d F Y') }}</time>
        </div>
    </div>
</article>
