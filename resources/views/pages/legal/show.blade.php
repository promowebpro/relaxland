@extends('layouts.public')

@section('title', $document->title)

@section('content')
    <section class="page-hero page-hero--document">
        <div class="site-container">
            <x-breadcrumbs :items="$seo->breadcrumbs" />
            <div class="document-heading">
                <h1>{{ $document->title }}</h1>
                <div>
                    @if ($document->version)<span>Версия {{ $document->version }}</span>@endif
                    @if ($document->published_at)<time datetime="{{ $document->published_at->toDateString() }}">{{ $document->published_at->translatedFormat('d F Y') }}</time>@endif
                </div>
            </div>
        </div>
    </section>

    <section class="document-section section-spacing">
        <div class="site-container document-section__inner">
            @if ($renderedContent)
                <article class="legal-content">{!! $renderedContent !!}</article>
            @endif

            @if ($document->file_path)
                <div class="document-download">
                    <p>Документ доступен в формате PDF.</p>
                    <x-button href="{{ Storage::disk('public')->url($document->file_path) }}" variant="outline">Открыть PDF</x-button>
                </div>
            @endif
        </div>
    </section>
@endsection
