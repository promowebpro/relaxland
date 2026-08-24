@extends('layouts.public')

@section('title', 'Конфиденциальность')
@section('description', 'Юридические документы RelaxLand.')

@section('content')
    <section class="page-intro page-intro--legal">
        <div class="site-container">
            <x-breadcrumbs :items="$seo->breadcrumbs" />
            <h1>Конфиденциальность</h1>
        </div>
    </section>

    <section class="legal-list section-spacing">
        <div class="site-container legal-list__layout">
            <p class="legal-list__intro">Актуальные документы и условия обработки персональных данных.</p>
            <div>
            @forelse ($documents as $document)
                <a class="legal-list__item" href="{{ route('legal.show', $document->slug) }}">
                    <span class="legal-list__index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="legal-list__content">
                        <strong>{{ $document->title }}</strong>
                        @if ($document->version)<small>Версия {{ $document->version }}</small>@endif
                    </span>
                    <span class="legal-list__arrow" aria-hidden="true">↗</span>
                </a>
            @empty
                <div class="empty-state">
                    <h2>Документы пока не опубликованы</h2>
                    <p>После публикации в административной панели они появятся на этой странице.</p>
                </div>
            @endforelse
            </div>
        </div>
    </section>
@endsection
