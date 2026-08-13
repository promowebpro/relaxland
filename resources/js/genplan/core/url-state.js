const slug = (value) => typeof value === 'string' ? value : null;

export const stateFromUrl = (url = new URL(window.location.href)) => ({
    activeTab: url.searchParams.get('view') === 'surroundings' ? 'surroundings' : 'genplan',
    mode: url.searchParams.get('mode') || '3d',
    selectedQuarter: slug(url.searchParams.get('quarter')),
    selectedInfrastructure: slug(url.searchParams.get('point')),
    loading: false,
    error: null,
});

export const writeStateUrl = (state, { replace = false } = {}) => {
    const url = new URL(window.location.href);
    ['view', 'mode', 'quarter', 'point'].forEach((key) => url.searchParams.delete(key));

    if (state.activeTab === 'surroundings') {
        url.searchParams.set('view', 'surroundings');
    } else {
        url.searchParams.set('mode', state.mode);
        if (state.selectedQuarter) url.searchParams.set('quarter', state.selectedQuarter);
        if (state.selectedInfrastructure) url.searchParams.set('point', state.selectedInfrastructure);
    }

    window.history[replace ? 'replaceState' : 'pushState']({ genplan: true }, '', url);
};
