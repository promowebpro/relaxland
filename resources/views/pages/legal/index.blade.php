@extends('layouts.public')

@section('title', 'Конфиденциальность')
@section('description', 'Юридические документы RelaxLand.')

@section('content')
    <section class="page-hero page-hero--legal">
        <div class="site-container">
            <x-breadcrumbs :items="[
                ['label' => 'Главная', 'url' => route('home')],
                ['label' => 'Конфиденциальность'],
            ]" />
            <div class="page-hero__row">
                <h1>Конфиденциальность</h1>
                <p>Актуальные документы и условия обработки персональных данных.</p>
            </div>
        </div>
    </section>

    <section class="legal-list section-spacing">
        <div class="site-container">
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
    </section>
@endsection
