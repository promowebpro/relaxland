const slug = (value) => typeof value === 'string' ? value : null;
const value = (url, key) => url.searchParams.get(key) || null;

export const stateFromUrl = (url = new URL(window.location.href)) => ({
    activeTab: url.searchParams.get('view') === 'surroundings' ? 'surroundings' : 'genplan',
    mode: url.searchParams.get('mode') || '3d',
    selectedQuarter: slug(url.searchParams.get('quarter')),
    selectedInfrastructure: slug(url.searchParams.get('point')),
    selectedPlot: slug(url.searchParams.get('plot')),
    plotFilters: {
        status: value(url, 'status'),
        areaMin: value(url, 'area_min'),
        areaMax: value(url, 'area_max'),
        priceMin: value(url, 'price_min'),
        priceMax: value(url, 'price_max'),
    },
    plotSort: value(url, 'sort') || 'default',
    plotsLoading: false,
    plotsError: null,
    plotsLoadedFor: null,
    plotsOpen: Boolean(url.searchParams.get('quarter')),
    loading: false,
    error: null,
});

export const writeStateUrl = (state, { replace = false } = {}) => {
    const url = new URL(window.location.href);
    ['view', 'mode', 'quarter', 'point', 'plot', 'status', 'area_min', 'area_max', 'price_min', 'price_max', 'sort'].forEach((key) => url.searchParams.delete(key));

    if (state.activeTab === 'surroundings') {
        url.searchParams.set('view', 'surroundings');
    } else {
        url.searchParams.set('mode', state.mode);
        if (state.selectedQuarter) url.searchParams.set('quarter', state.selectedQuarter);
        if (state.selectedInfrastructure) url.searchParams.set('point', state.selectedInfrastructure);
        if (state.selectedPlot) url.searchParams.set('plot', state.selectedPlot);
        if (state.selectedQuarter) {
            if (state.plotFilters.status) url.searchParams.set('status', state.plotFilters.status);
            if (state.plotFilters.areaMin) url.searchParams.set('area_min', state.plotFilters.areaMin);
            if (state.plotFilters.areaMax) url.searchParams.set('area_max', state.plotFilters.areaMax);
            if (state.plotFilters.priceMin) url.searchParams.set('price_min', state.plotFilters.priceMin);
            if (state.plotFilters.priceMax) url.searchParams.set('price_max', state.plotFilters.priceMax);
            if (state.plotSort !== 'default') url.searchParams.set('sort', state.plotSort);
        }
    }

    window.history[replace ? 'replaceState' : 'pushState']({ genplan: true }, '', url);
};
