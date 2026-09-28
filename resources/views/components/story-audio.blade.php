@props([
    'src',
    'duration' => null,
    'label' => 'Воспроизвести аудио',
])

@php
    $bars = [18, 28, 42, 34, 52, 38, 46, 30, 56, 40, 48, 26, 44, 36, 50, 32, 54, 28, 46, 38, 42, 24, 48, 34, 40, 30, 52, 36, 44, 28, 50, 32];
    $durationAttr = filled($duration) ? (int) $duration : null;
    $timeLabel = $durationAttr !== null
        ? sprintf('%d:%02d', intdiv($durationAttr, 60), $durationAttr % 60)
        : '0:00';
@endphp

<div {{ $attributes->class('story-audio') }} data-story-audio>
    <audio
        preload="metadata"
        src="{{ $src }}"
        data-story-audio-element
        @if ($durationAttr !== null) data-duration="{{ $durationAttr }}" @endif
    ></audio>
    <button type="button" class="story-audio__play" data-story-audio-toggle aria-label="{{ $label }}">
        <span class="story-audio__icon story-audio__icon--play" aria-hidden="true"></span>
        <span class="story-audio__icon story-audio__icon--pause" aria-hidden="true"></span>
    </button>
    <button type="button" class="story-audio__wave" data-story-audio-seek aria-label="Перемотать аудио">
        @foreach ($bars as $index => $height)
            <span style="--bar: {{ $height }}; --i: {{ $index }}"></span>
        @endforeach
    </button>
    <span class="story-audio__time" data-story-audio-time>{{ $timeLabel }}</span>
</div>
