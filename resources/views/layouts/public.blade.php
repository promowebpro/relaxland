<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $sectionTitle = trim($__env->yieldContent('title'));
            $metaTitle = trim($__env->yieldContent('meta_title')) ?: ($sectionTitle ? $sectionTitle.' — '.config('app.name') : config('app.name'));
            $metaDescription = trim($__env->yieldContent('description')) ?: 'RelaxLand Можайский';
            $canonical = trim($__env->yieldContent('canonical'));
            $ogImage = trim($__env->yieldContent('og_image'));
        @endphp

        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">

        @if ($canonical)
            <link rel="canonical" href="{{ $canonical }}">
        @endif

        <meta property="og:title" content="{{ trim($__env->yieldContent('og_title')) ?: $metaTitle }}">
        <meta property="og:description" content="{{ trim($__env->yieldContent('og_description')) ?: $metaDescription }}">
        <meta property="og:type" content="@yield('og_type', 'website')">
        @if ($canonical)
            <meta property="og:url" content="{{ $canonical }}">
        @endif
        @if ($ogImage)
            <meta property="og:image" content="{{ $ogImage }}">
        @endif

        @stack('head')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php($settings ??= app(\App\Domain\Settings\SiteSettings::class)->all())
    <body class="site-body">
        <a class="skip-link" href="#main-content">Перейти к содержимому</a>

        <x-site-header :settings="$settings" />

        <main id="main-content">
            @yield('content')
        </main>

        <x-site-footer :settings="$settings" />
    </body>
</html>
