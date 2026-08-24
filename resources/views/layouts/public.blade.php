<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $sectionTitle = trim($__env->yieldContent('title'));
            $metaTitle = $seo->title ?? (trim($__env->yieldContent('meta_title')) ?: ($sectionTitle ?: config('app.name')));
            $metaDescription = $seo->description ?? (trim($__env->yieldContent('description')) ?: 'RelaxLand Можайский');
            $canonical = $seo->canonical ?? (trim($__env->yieldContent('canonical')) ?: null);
            $requestedRobots = $seo->robots ?? (trim($__env->yieldContent('robots')) ?: 'index, follow');
            $robots = config('seo.indexing_enabled')
                ? $requestedRobots
                : (str_contains($requestedRobots, 'nofollow') ? 'noindex, nofollow' : 'noindex, follow');
            $ogTitle = $seo->title ?? (trim($__env->yieldContent('og_title')) ?: $metaTitle);
            $ogDescription = $seo->description ?? (trim($__env->yieldContent('og_description')) ?: $metaDescription);
            $ogType = $seo->ogType ?? (trim($__env->yieldContent('og_type')) ?: 'website');
            $ogImage = $seo->ogImage ?? (trim($__env->yieldContent('og_image')) ?: null);
            $siteName = $seo->siteName ?? config('app.name');
            $ogLocale = $seo->locale ?? config('seo.default_locale', 'ru_RU');
            $jsonLd = isset($seo) ? $seo->jsonLd() : null;
        @endphp

        <title>{{ $metaTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <meta name="robots" content="{{ $robots }}">

        @if ($canonical)
            <link rel="canonical" href="{{ $canonical }}">
        @endif

        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:type" content="{{ $ogType }}">
        <meta property="og:site_name" content="{{ $siteName }}">
        <meta property="og:locale" content="{{ $ogLocale }}">
        @if ($canonical)
            <meta property="og:url" content="{{ $canonical }}">
        @endif
        @if ($ogImage)
            <meta property="og:image" content="{{ $ogImage }}">
        @endif
        @if (($seo->articlePublishedTime ?? null))
            <meta property="article:published_time" content="{{ $seo->articlePublishedTime }}">
        @endif
        @if (($seo->articleModifiedTime ?? null))
            <meta property="article:modified_time" content="{{ $seo->articleModifiedTime }}">
        @endif

        @if ($jsonLd)
            <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        @endif

        @stack('head')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php($settings ??= app(\App\Domain\Settings\SiteSettings::class)->all())
    <body class="site-body @yield('body_class')">
        <a class="skip-link" href="#main-content">Перейти к содержимому</a>

        <x-site-header :settings="$settings" />

        <main id="main-content">
            @yield('content')
        </main>

        <x-site-footer :settings="$settings" />
        <x-lead-modal />
    </body>
</html>
