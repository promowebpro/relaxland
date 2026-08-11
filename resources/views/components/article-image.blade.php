@props(['image', 'wide' => false])

<figure @class(['article-media', 'article-media--wide' => $wide])>
    <img
        src="{{ Storage::disk('public')->url($image['path']) }}"
        alt="{{ $image['alt'] }}"
        loading="lazy"
    >
    @if ($image['caption'])
        <figcaption>{{ $image['caption'] }}</figcaption>
    @endif
</figure>
