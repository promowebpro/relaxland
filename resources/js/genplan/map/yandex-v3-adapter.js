let sdkPromise = null;
let sdkScript = null;

const providerError = (code) => Object.assign(new Error(code), { code });

export const loadYandexMaps = (config, { retry = false } = {}) => {
    if (!config.enabled) return Promise.reject(providerError('provider_disabled'));
    if (!config.apiKey) return Promise.reject(providerError('configuration_missing'));
    if (window.ymaps3?.ready) return Promise.resolve(window.ymaps3.ready).then(() => window.ymaps3);
    if (sdkPromise && !retry) return sdkPromise;

    if (retry && sdkScript) {
        sdkScript.remove();
        sdkScript = null;
        sdkPromise = null;
    }

    const source = new URL(config.sdkUrl);
    source.searchParams.set('apikey', config.apiKey);
    source.searchParams.set('lang', config.locale);

    sdkPromise = new Promise((resolve, reject) => {
        let settled = false;
        const finish = (callback, value) => {
            if (settled) return;
            settled = true;
            window.clearTimeout(timeout);
            callback(value);
        };
        const timeout = window.setTimeout(
            () => finish(reject, providerError('provider_timeout')),
            config.timeoutMs,
        );

        sdkScript = document.createElement('script');
        sdkScript.id = 'relaxland-yandex-maps-v3';
        sdkScript.async = true;
        sdkScript.src = source.toString();
        sdkScript.addEventListener('error', () => finish(reject, providerError('provider_unavailable')), { once: true });
        sdkScript.addEventListener('load', async () => {
            try {
                if (!window.ymaps3?.ready) throw providerError('provider_unavailable');
                await window.ymaps3.ready;
                finish(resolve, window.ymaps3);
            } catch (error) {
                finish(reject, error?.code ? error : providerError('provider_unavailable'));
            }
        }, { once: true });
        document.head.append(sdkScript);
    }).catch((error) => {
        sdkScript?.remove();
        sdkScript = null;
        sdkPromise = null;
        throw error;
    });

    return sdkPromise;
};

const duration = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 240;
const coordinates = (point) => [Number(point.longitude), Number(point.latitude)];

export const createYandexV3Adapter = async ({ container, config, settlement, places, onSelect, retry = false }) => {
    const ymaps3 = await loadYandexMaps(config, { retry });
    const { YMap, YMapDefaultFeaturesLayer, YMapDefaultSchemeLayer, YMapMarker } = ymaps3;
    const initialPoint = settlement || places[0]?.coordinates;

    if (!initialPoint) throw providerError('data_empty');

    const mobile = window.matchMedia('(max-width: 47.99rem)').matches;
    const map = new YMap(container, {
        location: { center: coordinates(initialPoint), zoom: config.defaultZoom },
        zoomRange: { min: config.minZoom, max: config.maxZoom },
        margin: mobile ? [24, 24, 190, 24] : [32, 32, 32, 32],
        behaviors: mobile ? ['pinchZoom'] : ['drag', 'dblClick'],
        theme: config.theme,
    });

    map.addChild(new YMapDefaultSchemeLayer({ theme: config.theme }));
    map.addChild(new YMapDefaultFeaturesLayer());

    const placeMarkers = new Map();
    let settlementMarker = null;
    let visiblePlaces = [];
    let visibleSignature = null;

    const markerContent = ({ className, label, symbol, slug = null }) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.setAttribute('aria-label', label);
        if (slug) {
            button.dataset.mapPlace = slug;
            button.dataset.placeSlug = slug;
            button.setAttribute('aria-pressed', 'false');
            button.addEventListener('click', () => onSelect(slug, button));
        }
        const glyph = document.createElement('span');
        glyph.setAttribute('aria-hidden', 'true');
        glyph.textContent = symbol;
        button.append(glyph);

        return button;
    };

    if (settlement) {
        const content = markerContent({
            className: 'surroundings-map-marker surroundings-map-marker--settlement',
            label: 'Посёлок RelaxLand',
            symbol: 'R',
        });
        settlementMarker = new YMapMarker({ coordinates: coordinates(settlement), zIndex: 30 }, content);
        map.addChild(settlementMarker);
    }

    const clearPlaceMarkers = () => {
        placeMarkers.forEach(({ marker }) => map.removeChild(marker));
        placeMarkers.clear();
    };

    const updatePlaces = (nextPlaces) => {
        const nextSignature = nextPlaces.map((place) => place.slug).join('|');
        if (nextSignature === visibleSignature) return;
        visibleSignature = nextSignature;
        clearPlaceMarkers();
        visiblePlaces = nextPlaces.filter((place) => place.coordinates);
        visiblePlaces.forEach((place) => {
            const content = markerContent({
                className: `surroundings-map-marker surroundings-map-marker--${place.category}`,
                label: `${place.name} — ${place.category_label}`,
                symbol: place.category_symbol,
                slug: place.slug,
            });
            const marker = new YMapMarker({ coordinates: coordinates(place.coordinates), zIndex: 20 }, content);
            placeMarkers.set(place.slug, { marker, content, place });
            map.addChild(marker);
        });
    };

    const visiblePoints = () => [settlement, ...visiblePlaces.map((place) => place.coordinates)].filter(Boolean);

    const fitBounds = () => {
        const points = visiblePoints();
        if (points.length === 0) return;
        if (points.length === 1) {
            map.setLocation({ center: coordinates(points[0]), zoom: config.defaultZoom, duration: duration() });
            return;
        }
        const longitudes = points.map((point) => Number(point.longitude));
        const latitudes = points.map((point) => Number(point.latitude));
        map.setLocation({
            bounds: [
                [Math.min(...longitudes), Math.min(...latitudes)],
                [Math.max(...longitudes), Math.max(...latitudes)],
            ],
            duration: duration(),
        });
    };

    const focusSettlement = () => {
        if (settlement) map.setLocation({ center: coordinates(settlement), zoom: config.defaultZoom + 1, duration: duration() });
    };

    const selectMarker = (slug, { focus = true } = {}) => {
        placeMarkers.forEach(({ content }, markerSlug) => {
            const selected = markerSlug === slug;
            content.classList.toggle('is-selected', selected);
            content.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });
        const selected = placeMarkers.get(slug);
        if (selected && focus) {
            map.setLocation({
                center: coordinates(selected.place.coordinates),
                zoom: Math.max(config.defaultZoom + 1, Math.min(map.zoom, config.maxZoom)),
                duration: duration(),
            });
        }
    };

    const zoom = (delta) => map.setLocation({
        zoom: Math.max(config.minZoom, Math.min(config.maxZoom, map.zoom + delta)),
        duration: duration(),
    });

    updatePlaces(places);
    fitBounds();

    return {
        destroy: () => {
            clearPlaceMarkers();
            if (settlementMarker) map.removeChild(settlementMarker);
            map.destroy();
        },
        updatePlaces,
        selectMarker,
        fitBounds,
        focusSettlement,
        zoom,
        resize: () => map.update({ margin: window.matchMedia('(max-width: 47.99rem)').matches ? [24, 24, 190, 24] : [32, 32, 32, 32] }),
    };
};
