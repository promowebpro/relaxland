@props(['post', 'featured' => false])

<article @class(['blog-card', 'blog-card--featured' => $featured])>
    <a class="blog-card__cover" href="{{ route('blog.show', $post->slug) }}" tabindex="-1" aria-hidden="true">
        @if ($post->cover_image)
            <img src="{{ Storage::disk('public')->url($post->cover_image) }}" alt="" loading="lazy">
        @else
            <span class="blog-image-placeholder" aria-hidden="true">RelaxLand</span>
        @endif
    </a>

    <div class="blog-card__body">
        <div class="blog-card__meta">
            <span>{{ $post->category->name }}</span>
            <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('d F Y') }}</time>
        </div>
        <h2><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h2>
        @if ($post->excerpt)
            <p>{{ $post->excerpt }}</p>
        @endif
        <a class="blog-card__link" href="{{ route('blog.show', $post->slug) }}">Читать статью <span aria-hidden="true">↗</span></a>
    </div>
</article>
