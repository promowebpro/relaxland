@props(['content', 'source' => 'about'])
@php
    $about = $content;
    $image = fn ($key, $fallback) => \App\Models\AboutPage::imageUrl($about[$key] ?: $fallback);
    $latitude = $about['map_latitude'];
    $longitude = $about['map_longitude'];
    $routes = [
        'Яндекс' => $about['route_yandex'] ?: 'https://yandex.ru/maps/?rtext=~'.$latitude.','.$longitude.'&rtt=auto',
        'Гугл' => $about['route_google'] ?: 'https://www.google.com/maps/dir/?api=1&destination='.$latitude.','.$longitude,
        '2ГИС' => $about['route_two_gis'] ?: 'https://2gis.ru/routeSearch/rsType/car/to/'.$longitude.','.$latitude,
    ];
@endphp
        <section class="about-location site-container" aria-labelledby="about-visit-title">
            <div class="about-location__frame">
                <div class="about-location__map" data-about-map data-latitude="{{ $latitude }}" data-longitude="{{ $longitude }}" data-zoom="{{ $about['map_zoom'] }}" data-label="{{ $about['map_label'] }}" aria-label="Карта расположения посёлка" tabindex="0"></div>
                <p class="about-location__status" data-about-map-status role="status" hidden>Не удалось загрузить карту. Воспользуйтесь ссылками для построения маршрута.</p>
                <article class="about-location__invite">
                    <h2 id="about-visit-title">{{ $about['visit_title'] }}</h2>
                    <p>{{ $about['visit_text'] }}</p>
                    <ul>@foreach ($about['visit_tags'] as $tag)<li>{{ $tag['label'] }}</li>@endforeach</ul>
                    <x-button href="#lead-form" variant="primary" data-lead-modal-trigger data-lead-source="{{ $source }}" data-lead-form-type="visit" :data-lead-heading="$about['visit_title']">{{ $about['visit_button'] }}</x-button>
                </article>
                <article class="about-location__route">
                    <h3>{{ $about['route_title'] }}</h3>
                    <nav aria-label="Сервисы маршрутов">@foreach ($routes as $label => $url)<a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $label }}</a>@endforeach</nav>
                    <div><span aria-hidden="true">↗</span><p><strong>{{ $about['travel_title'] }}</strong><small>{{ $about['travel_text'] }}</small></p></div>
                    <div><span aria-hidden="true">⌖</span><p><strong>{{ $about['coordinates_title'] }}</strong><small>{{ $latitude }}, {{ $longitude }}</small></p></div>
                </article>
            </div>
            <img class="about-location__mascot" src="{{ $image('map_mascot', 'assets/design/about-hedgehog.svg') }}" alt="" aria-hidden="true" loading="lazy">
        </section>

