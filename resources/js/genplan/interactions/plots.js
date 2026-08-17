const SVG_NS = 'http://www.w3.org/2000/svg';
const EMPTY_FILTERS = { status: null, areaMin: null, areaMax: null, priceMin: null, priceMax: null };

const decimal = (value) => value === null || value === '' ? null : Number(value);
const plotKey = (state) => state.selectedQuarter ? `${state.selectedQuarter}|${state.mode}` : null;

const filteredPlots = (plots, state) => {
    const filters = state.plotFilters;
    const items = plots.filter((plot) => {
        const area = decimal(plot.area);
        const price = decimal(plot.price);
        if (filters.status && plot.status !== filters.status) return false;
        if (filters.areaMin !== null && area < decimal(filters.areaMin)) return false;
        if (filters.areaMax !== null && area > decimal(filters.areaMax)) return false;
        if (filters.priceMin !== null && (price === null || price < decimal(filters.priceMin))) return false;
        if (filters.priceMax !== null && (price === null || price > decimal(filters.priceMax))) return false;
        return true;
    });

    const byNumber = (left, right) => String(left.number).localeCompare(String(right.number), 'ru', { numeric: true });
    if (state.plotSort === 'price_asc') items.sort((left, right) => {
        if (left.price === null) return 1;
        if (right.price === null) return -1;
        return decimal(left.price) - decimal(right.price) || byNumber(left, right);
    });
    if (state.plotSort === 'area_asc') items.sort((left, right) => decimal(left.area) - decimal(right.area) || byNumber(left, right));
    if (state.plotSort === 'area_desc') items.sort((left, right) => decimal(right.area) - decimal(left.area) || byNumber(left, right));
    if (state.plotSort === 'default') items.sort(byNumber);
    return items;
};

const element = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
};

export const createPlots = (root, { getState, commit }) => {
    const panel = root.querySelector('[data-plots-panel]');
    const layer = root.querySelector('[data-plot-layer]');
    const list = root.querySelector('[data-plot-list]');
    const card = root.querySelector('[data-plot-card]');
    const form = root.querySelector('[data-plot-filters]');
    const loading = root.querySelector('[data-plots-loading]');
    const error = root.querySelector('[data-plots-error]');
    const empty = root.querySelector('[data-plots-empty]');
    const filterError = root.querySelector('[data-plot-filter-error]');
    const heading = root.querySelector('#plot-selection-heading');
    const cache = new Map();
    let requestController = null;
    let requestVersion = 0;
    let activeRequestKey = null;
    let lastPlotFocus = null;

    const initialKey = root.dataset.initialPlotsLoadedFor;
    const initialJson = root.querySelector('[data-initial-plots]');
    if (initialKey && initialJson) {
        try { cache.set(initialKey, JSON.parse(initialJson.textContent)); } catch { cache.set(initialKey, []); }
    }

    const renderLayer = (items, state) => {
        if (!layer) return;
        layer.replaceChildren();
        items.forEach((plot) => {
            if (!plot.geometry) return;
            let shape = null;
            if (Array.isArray(plot.geometry.polygon) && plot.geometry.polygon.length >= 3) {
                shape = document.createElementNS(SVG_NS, 'polygon');
                shape.setAttribute('points', plot.geometry.polygon.map((point) => `${Number(point.x) * 1000},${Number(point.y) * 1000}`).join(' '));
                shape.classList.add('genplan-plot', `genplan-plot--${plot.status}`);
            } else if (plot.geometry.marker?.x !== null && plot.geometry.marker?.y !== null) {
                shape = document.createElementNS(SVG_NS, 'circle');
                shape.setAttribute('cx', Number(plot.geometry.marker.x) * 1000);
                shape.setAttribute('cy', Number(plot.geometry.marker.y) * 1000);
                shape.setAttribute('r', '22');
                shape.classList.add('genplan-plot-marker', `genplan-plot-marker--${plot.status}`);
            }
            if (!shape) return;
            const selected = state.selectedPlot === plot.slug;
            shape.classList.toggle('is-selected', selected);
            shape.setAttribute('role', 'button');
            shape.setAttribute('tabindex', '0');
            shape.setAttribute('aria-label', `Участок №${plot.number}, ${plot.status_label}, ${plot.area_label}`);
            shape.setAttribute('aria-pressed', selected ? 'true' : 'false');
            shape.dataset.plotTrigger = '';
            shape.dataset.plotSlug = plot.slug;
            layer.append(shape);
        });
    };

    const renderList = (items, state) => {
        if (!list) return;
        list.replaceChildren();
        items.forEach((plot) => {
            const item = element('a', 'genplan-plot-item');
            item.href = '#plot-selection';
            item.dataset.plotItem = '';
            item.dataset.plotTrigger = '';
            item.dataset.plotSlug = plot.slug;
            item.classList.toggle('is-selected', state.selectedPlot === plot.slug);
            item.setAttribute('aria-pressed', state.selectedPlot === plot.slug ? 'true' : 'false');
            const identity = element('span');
            identity.append(element('strong', '', `Участок №${plot.number}`), element('small', '', plot.area_label));
            const commerce = element('span');
            commerce.append(element('strong', '', plot.price_label), element('small', '', plot.status_label));
            item.append(identity, commerce);
            if (!plot.geometry) item.append(element('small', 'genplan-plot-item__geometry', 'На этом виде нет отметки'));
            list.append(item);
        });
    };

    const renderCard = (plot, state) => {
        if (!card) return;
        card.toggleAttribute('hidden', !plot);
        if (!plot) return;
        card.replaceChildren();
        const close = element('button', 'genplan-card-close', '×');
        close.type = 'button';
        close.dataset.plotClose = '';
        close.setAttribute('aria-label', `Закрыть карточку участка №${plot.number}`);
        const status = element('span', `genplan-status genplan-status--${plot.status}`, plot.status_label);
        const title = element('h3', '', `Участок №${plot.number}`);
        const details = element('dl');
        [['Площадь', plot.area_label], ['Цена', plot.price_label], ['За сотку', plot.price_per_sotka_label]].forEach(([term, value]) => {
            const row = element('div');
            row.append(element('dt', '', term), element('dd', '', value));
            details.append(row);
        });
        const description = element('p', '', plot.description || 'Подробности участка уточнит менеджер проекта.');
        card.append(close, status, title, details, description);
        if (plot.can_inquire) {
            const cta = element('a', 'button button--primary', 'Узнать об участке');
            cta.href = '#lead-form';
            cta.dataset.leadModalTrigger = '';
            cta.dataset.leadSource = 'genplan-preview';
            cta.dataset.leadFormType = 'consultation';
            cta.dataset.leadHeading = `Узнать об участке №${plot.number}`;
            cta.dataset.leadQuarter = state.selectedQuarter;
            cta.dataset.leadPlot = plot.slug;
            card.append(cta);
        }
    };

    const syncForm = (state) => {
        if (!form) return;
        form.elements.mode.value = state.mode;
        form.elements.quarter.value = state.selectedQuarter || '';
        form.elements.status.value = state.plotFilters.status || '';
        form.elements.area_min.value = state.plotFilters.areaMin || '';
        form.elements.area_max.value = state.plotFilters.areaMax || '';
        form.elements.price_min.value = state.plotFilters.priceMin || '';
        form.elements.price_max.value = state.plotFilters.priceMax || '';
        form.elements.sort.value = state.plotSort;
    };

    const render = (state) => {
        if (!panel) return;
        const key = plotKey(state);
        if (requestController && activeRequestKey && activeRequestKey !== key) {
            requestController.abort();
            requestController = null;
            activeRequestKey = null;
            requestVersion++;
        }
        const source = key ? cache.get(key) || [] : [];
        const items = filteredPlots(source, state);
        const selected = items.find((plot) => plot.slug === state.selectedPlot) || null;
        loading?.toggleAttribute('hidden', !state.plotsLoading);
        error?.toggleAttribute('hidden', !state.plotsError);
        empty?.toggleAttribute('hidden', state.plotsLoading || Boolean(state.plotsError) || items.length > 0 || !state.plotsOpen);
        if (heading && state.selectedQuarter) {
            const quarterName = root.querySelector(`[data-quarter-trigger][data-quarter-slug="${CSS.escape(state.selectedQuarter)}"] strong`)?.textContent?.trim();
            heading.textContent = `Участки — ${quarterName || state.selectedQuarter}`;
        }
        syncForm(state);
        renderList(items, state);
        renderLayer(items, state);
        renderCard(selected, state);
    };

    const ensure = async (candidate, { force = false } = {}) => {
        const key = plotKey(candidate);
        if (!key) return;
        if (!force && cache.has(key)) {
            commit({ ...candidate, plotsOpen: true, plotsLoading: false, plotsError: null, plotsLoadedFor: key });
            return;
        }

        requestController?.abort();
        requestController = new AbortController();
        activeRequestKey = key;
        const version = ++requestVersion;
        commit({ ...candidate, plotsOpen: true, plotsLoading: true, plotsError: null, plotsLoadedFor: key });

        try {
            const endpoint = root.dataset.plotsEndpointTemplate.replace('__quarter__', encodeURIComponent(candidate.selectedQuarter));
            const response = await fetch(`${endpoint}?mode=${encodeURIComponent(candidate.mode)}`, {
                headers: { Accept: 'application/json' },
                signal: requestController.signal,
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const payload = await response.json();
            if (version !== requestVersion || plotKey(getState()) !== key) return;
            cache.set(key, Array.isArray(payload.data) ? payload.data : []);
            requestController = null;
            activeRequestKey = null;
            commit({ ...getState(), plotsLoading: false, plotsError: null, plotsLoadedFor: key });
        } catch (fetchError) {
            if (fetchError.name === 'AbortError' || version !== requestVersion) return;
            requestController = null;
            activeRequestKey = null;
            commit({ ...getState(), plotsLoading: false, plotsError: 'load_failed', plotsLoadedFor: key });
        }
    };

    const selectPlot = (trigger) => {
        lastPlotFocus = { slug: trigger.dataset.plotSlug, map: Boolean(trigger.closest('[data-plot-layer]')) };
        commit({ ...getState(), selectedPlot: trigger.dataset.plotSlug, plotsOpen: true });
        window.requestAnimationFrame(() => {
            const scope = lastPlotFocus.map ? '[data-plot-layer]' : '[data-plot-list]';
            root.querySelector(`${scope} [data-plot-slug="${CSS.escape(lastPlotFocus.slug)}"]`)?.focus({ preventScroll: true });
        });
    };

    const focusLastPlot = () => {
        if (!lastPlotFocus) return;
        const scope = lastPlotFocus.map ? '[data-plot-layer]' : '[data-plot-list]';
        root.querySelector(`${scope} [data-plot-slug="${CSS.escape(lastPlotFocus.slug)}"]`)?.focus({ preventScroll: true });
    };

    root.addEventListener('click', (event) => {
        const state = getState();
        const trigger = event.target.closest('[data-plot-trigger]');
        if (trigger) {
            event.preventDefault();
            selectPlot(trigger);
            return;
        }
        if (event.target.closest('[data-plot-close]')) {
            commit({ ...state, selectedPlot: null });
            focusLastPlot();
        }
        if (event.target.closest('[data-plots-back]')) {
            commit({ ...state, selectedPlot: null, plotsOpen: false });
            root.querySelector(`[data-quarter-trigger][data-quarter-slug="${CSS.escape(state.selectedQuarter || '')}"]`)?.focus({ preventScroll: true });
        }
        if (event.target.closest('[data-plots-retry]')) ensure(state, { force: true });
        if (event.target.closest('[data-plot-filters-reset]')) {
            commit({ ...state, selectedPlot: null, plotFilters: { ...EMPTY_FILTERS }, plotSort: 'default' });
        }
    });

    root.addEventListener('keydown', (event) => {
        const trigger = event.target.closest('[data-plot-trigger]');
        if (trigger && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault();
            selectPlot(trigger);
        }
    });

    ['pointerover', 'focusin'].forEach((eventName) => root.addEventListener(eventName, (event) => {
        const trigger = event.target.closest('[data-plot-trigger]');
        if (!trigger) return;
        root.querySelectorAll(`[data-plot-trigger][data-plot-slug="${CSS.escape(trigger.dataset.plotSlug)}"]`)
            .forEach((item) => item.classList.add('is-hovered'));
    }));
    ['pointerout', 'focusout'].forEach((eventName) => root.addEventListener(eventName, (event) => {
        const trigger = event.target.closest('[data-plot-trigger]');
        if (!trigger) return;
        root.querySelectorAll(`[data-plot-trigger][data-plot-slug="${CSS.escape(trigger.dataset.plotSlug)}"]`)
            .forEach((item) => item.classList.remove('is-hovered'));
    }));

    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        filterError.hidden = true;
        if (!form.reportValidity()) return;
        const data = new FormData(form);
        const filters = {
            status: data.get('status') || null,
            areaMin: data.get('area_min') || null,
            areaMax: data.get('area_max') || null,
            priceMin: data.get('price_min') || null,
            priceMax: data.get('price_max') || null,
        };
        if ((filters.areaMin && filters.areaMax && decimal(filters.areaMin) > decimal(filters.areaMax))
            || (filters.priceMin && filters.priceMax && decimal(filters.priceMin) > decimal(filters.priceMax))) {
            filterError.textContent = 'Значение «от» не может быть больше значения «до».';
            filterError.hidden = false;
            return;
        }
        commit({ ...getState(), selectedPlot: null, plotFilters: filters, plotSort: data.get('sort') || 'default' });
    });

    return { ensure, render, plotKey };
};
