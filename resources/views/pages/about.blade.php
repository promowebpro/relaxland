@extends('layouts.public')

@section('title', $about['title'])
@section('description', $about['seo_description'])

@section('content')
    @php
        $image = fn ($key, $fallback) => \App\Models\AboutPage::imageUrl($about[$key] ?: $fallback);
    @endphp
    <div class="about-page">
        <section class="about-intro site-container">
            <x-breadcrumbs :items="$seo->breadcrumbs" />
            <h1>{{ $about['title'] }}</h1>
            <h2>{{ $about['trust_title'] }}</h2>
            <p>{{ $about['trust_text'] }}</p>
        </section>

        <section class="about-story site-container" aria-labelledby="about-care-title">
            <div class="about-story__collage">
                <img class="about-story__left" src="{{ $image('left_image', 'assets/design/about-01.webp') }}" alt="{{ $about['left_image_alt'] }}">
                <img class="about-story__main" src="{{ $image('main_image', 'assets/design/about-03.webp') }}" alt="{{ $about['main_image_alt'] }}">
                <img class="about-story__right" src="{{ $image('right_image', 'assets/design/about-02.webp') }}" alt="{{ $about['right_image_alt'] }}">
            </div>
            <div class="about-story__copy">
                <h2 id="about-care-title">{{ $about['care_title'] }}</h2>
                <p>{{ $about['care_text'] }}</p>
            </div>
        </section>

        <x-visit-map :content="$about" source="about" />

        <section class="about-experience site-container" aria-labelledby="about-experience-title">
            <h2 id="about-experience-title">{{ $about['numbers_title'] }}</h2>
            <div class="about-experience__grid">
                @foreach ($about['cards'] as $card)
                    <article class="about-experience__card">
                        <div class="about-experience__value"><strong>{{ $card['value'] }}</strong><span>{{ $card['label'] }}</span></div>
                        <p>{{ $card['text'] }}</p>
                        @if ($loop->last)
                            <img class="about-experience__mascot" src="{{ $image('cards_mascot', 'assets/design/about-skate-dog.svg') }}" alt="" aria-hidden="true" loading="lazy">
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    </div>
@endsection
