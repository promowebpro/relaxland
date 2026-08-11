@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
    'disabled' => false,
])

@php
    $classes = 'button button--'.$variant.($disabled ? ' is-disabled' : '');
@endphp

@if ($href && ! $disabled)
    <a {{ $attributes->class($classes) }} href="{{ $href }}">{{ $slot }}</a>
@else
    <button {{ $attributes->class($classes) }} type="{{ $type }}" @disabled($disabled)>{{ $slot }}</button>
@endif
