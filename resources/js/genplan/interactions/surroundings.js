import { createYandexV3Adapter } from '../map/yandex-v3-adapter';

const parseJson = (root, selector, fallback) => {
    try {
        return JSON.parse(root.querySelector(selector)?.textContent || '');
    } catch {
        return fallback;
    }
};

const errorMessages = {
    configuration_missing: 'Ключ карты не настроен. Список мест и ссылки маршрутов остаются доступны.',
    provider_disabled: 'Интерактивная карта отключена. Используйте список мест ниже.',
    provider_timeout: 'Карта загружалась слишком долго. Можно повторить попытку.',
    provider_unavailable: 'Сервис карты сейчас недоступен. Можно повторить попытку.',
    data_empty: 'Для карты пока нет опубликованных координат.',
    data_error: 'Данные карты не удалось подготовить. Используйте серверный список мест.',
};

export const createSurroundings = (root, { getState, commit }) => {
    const panel = root.querySelector('[data-surroundings-root]');
    if (!panel) return { ensure: () => {}, render: () => {}, destroy: () => {} };

    const mapContainer = panel.querySelector('[data-surroundings-map]');
    const loading = panel.querySelector('[data-map-loading]');
    const fallback = panel.querySelector('[data-map-fallback]');
    const fallbackMessage = panel.querySelector('[data-map-fallback-message]');
    const retry = panel.querySelector('[data-map-retry]');
    const list = panel.querySelector('[data-surroundings-list]');
    const empty = panel.querySelector('[data-surroundings-empty]');
    const filters = [...panel.querySelectorAll('[data-surrounding-category]')];
    const items = [...panel.querySelectorAll('[data-surrounding-item]')];
    const cards = [...panel.querySelectorAll('[data-surrounding-card]')];
    const places = parseJson(root, '[data-surroundings-data]', null);
    const config = parseJson(root, '[data-surroundings-config]', null);
    const settlement = parseJson(root, '[data-settlement-data]', null);
    let adapter = null;
    let initVersion = 0;
    let lastPlaceFocus = null;
    let renderedSelectedPlace = null;
    let resizeFrame = null;

    const dataValid = Array.isArray(places) && config && typeof config === 'object';
    const activePlaces = (state) => dataValid
        ? places.filter((place) => state.activeSurroundingCategories.includes(place.category))
        : [];

    const providerFailure = (error) => error?.code && errorMessages[error.code]
        ? error.code
        : 'provider_unavailable';

    const destroy = () => {
        initVersion++;
        adapter?.destroy();
        adapter = null;
        mapContainer?.replaceChildren();
    };

    const ensure = async (state, { retry: shouldRetry = false } = {}) => {
        if (state.activeTab !== 'surroundings' || adapter || state.mapLoading) return;
        if (!dataValid) {
            commit({ ...state, mapLoading: false, mapReady: false, mapError: 'data_error', providerStatus: 'data_error' });
            return;
        }
        if (!settlement && places.length === 0) {
            commit({ ...state, mapLoading: false, mapReady: false, mapError: 'data_empty', providerStatus: 'data_empty' });
            return;
        }

        const version = ++initVersion;
        commit({ ...state, mapLoading: true, mapReady: false, mapError: null, providerStatus: 'loading' });

        try {
            const nextAdapter = await createYandexV3Adapter({
                container: mapContainer,
                config,
                settlement,
                places: activePlaces(getState()),
                retry: shouldRetry,
                onSelect: (slug, trigger) => selectPlace(slug, trigger, true),
            });
            if (version !== initVersion || getState().activeTab !== 'surroundings') {
                nextAdapter.destroy();
                return;
            }
            adapter = nextAdapter;
            commit({ ...getState(), mapLoading: false, mapReady: true, mapError: null, providerStatus: 'ready' });
        } catch (error) {
            if (version !== initVersion || getState().activeTab !== 'surroundings') return;
            const failure = providerFailure(error);
            commit({ ...getState(), mapLoading: false, mapReady: false, mapError: failure, providerStatus: failure });
        }
    };

    const focusLastPlace = () => {
        const slug = lastPlaceFocus?.slug;
        if (!slug) return;
        const selector = lastPlaceFocus.map ? '[data-map-place]' : '[data-surrounding-trigger]';
        panel.querySelector(`${selector}[data-place-slug="${CSS.escape(slug)}"], ${selector}[data-map-place="${CSS.escape(slug)}"]`)?.focus({ preventScroll: true });
    };

    const selectPlace = (slug, trigger, fromMap = false) => {
        if (!places?.some((place) => place.slug === slug)) return;
        lastPlaceFocus = { slug, map: fromMap };
        commit({ ...getState(), activeTab: 'surroundings', selectedSurroundingPlace: slug });
        if (fromMap) {
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            panel.querySelector(`[data-surrounding-item][data-place-slug="${CSS.escape(slug)}"]`)?.scrollIntoView({ block: 'nearest', behavior: reducedMotion ? 'auto' : 'smooth' });
        }
        window.requestAnimationFrame(() => trigger?.focus({ preventScroll: true }));
    };

    const render = (state) => {
        const visible = activePlaces(state);
        const selected = state.selectedSurroundingPlace;
        panel.dataset.providerStatus = state.providerStatus;
        panel.classList.toggle('is-map-ready', state.mapReady);
        loading?.toggleAttribute('hidden', !state.mapLoading);
        mapContainer?.toggleAttribute('aria-busy', state.mapLoading);

        const failure = state.mapError || (!config?.enabled ? 'provider_disabled' : (!config?.apiKey ? 'configuration_missing' : null));
        fallback?.toggleAttribute('hidden', state.mapLoading || state.mapReady || !failure);
        if (fallbackMessage && failure) fallbackMessage.textContent = errorMessages[failure] || errorMessages.provider_unavailable;
        retry?.toggleAttribute('hidden', !['provider_timeout', 'provider_unavailable'].includes(failure));

        filters.forEach((control) => {
            const active = state.activeSurroundingCategories.includes(control.dataset.surroundingCategory);
            control.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        items.forEach((item) => {
            const categoryVisible = state.activeSurroundingCategories.includes(item.dataset.placeCategory);
            item.toggleAttribute('hidden', !categoryVisible);
            const trigger = item.querySelector('[data-surrounding-trigger]');
            const itemSelected = item.dataset.placeSlug === selected;
            item.classList.toggle('is-selected', itemSelected);
            trigger?.setAttribute('aria-pressed', itemSelected ? 'true' : 'false');
        });
        cards.forEach((card) => card.toggleAttribute('hidden', card.dataset.surroundingCard !== selected));
        empty?.toggleAttribute('hidden', visible.length > 0);
        list?.setAttribute('aria-label', visible.length > 0 ? `${visible.length} мест окружения` : 'Нет мест по выбранным категориям');

        adapter?.updatePlaces(visible);
        adapter?.selectMarker(selected, { focus: Boolean(selected && selected !== renderedSelectedPlace) });
        renderedSelectedPlace = selected;
    };

    panel.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-surrounding-trigger]');
        if (trigger) {
            event.preventDefault();
            selectPlace(trigger.dataset.placeSlug, trigger);
            return;
        }
        const category = event.target.closest('[data-surrounding-category]');
        if (category) {
            const state = getState();
            const value = category.dataset.surroundingCategory;
            const categories = state.activeSurroundingCategories.includes(value)
                ? state.activeSurroundingCategories.filter((item) => item !== value)
                : [...state.activeSurroundingCategories, value];
            const selectedPlace = places?.find((place) => place.slug === state.selectedSurroundingPlace);
            commit({
                ...state,
                activeSurroundingCategories: categories,
                selectedSurroundingPlace: selectedPlace && !categories.includes(selectedPlace.category) ? null : state.selectedSurroundingPlace,
            });
            return;
        }
        if (event.target.closest('[data-surrounding-show-all]')) {
            commit({ ...getState(), activeSurroundingCategories: [...new Set((places || []).map((place) => place.category))] });
        }
        if (event.target.closest('[data-surrounding-close]')) {
            commit({ ...getState(), selectedSurroundingPlace: null });
            focusLastPlace();
        }
        if (event.target.closest('[data-map-retry]')) ensure(getState(), { retry: true });
        if (event.target.closest('[data-map-fit]')) adapter?.fitBounds();
        if (event.target.closest('[data-map-settlement]')) adapter?.focusSettlement();
        if (event.target.closest('[data-map-zoom-in]')) adapter?.zoom(1);
        if (event.target.closest('[data-map-zoom-out]')) adapter?.zoom(-1);
    });

    panel.addEventListener('keydown', (event) => {
        const trigger = event.target.closest('[data-surrounding-trigger]');
        if (trigger && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault();
            selectPlace(trigger.dataset.placeSlug, trigger);
        }
    });

    window.addEventListener('resize', () => {
        if (resizeFrame) window.cancelAnimationFrame(resizeFrame);
        resizeFrame = window.requestAnimationFrame(() => adapter?.resize());
    }, { passive: true });

    return { ensure, render, destroy, focusLastPlace };
};
