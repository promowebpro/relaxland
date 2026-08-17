const VALID_MODES = new Set(['2d', '3d']);
const VALID_TABS = new Set(['genplan', 'surroundings']);
const VALID_PLOT_STATUSES = new Set(['available', 'reserved', 'sold']);
const VALID_PLOT_SORTS = new Set(['default', 'price_asc', 'area_asc', 'area_desc']);

const cleanSlug = (value) => typeof value === 'string' && value.trim().length > 0 && value.length <= 255 ? value : null;
const cleanNumber = (value) => typeof value === 'string' && value !== '' && /^\d+(\.\d{1,2})?$/.test(value) ? value : null;

const filtersFrom = (source) => ({
    status: VALID_PLOT_STATUSES.has(source.status) ? source.status : null,
    areaMin: cleanNumber(source.areaMin),
    areaMax: cleanNumber(source.areaMax),
    priceMin: cleanNumber(source.priceMin),
    priceMax: cleanNumber(source.priceMax),
});

export const stateFromRoot = (root) => ({
    activeTab: VALID_TABS.has(root.dataset.initialTab) ? root.dataset.initialTab : 'genplan',
    mode: VALID_MODES.has(root.dataset.initialMode) ? root.dataset.initialMode : '3d',
    selectedQuarter: cleanSlug(root.dataset.initialQuarter),
    selectedInfrastructure: cleanSlug(root.dataset.initialPoint),
    selectedPlot: cleanSlug(root.dataset.initialPlot),
    plotFilters: filtersFrom({
        status: root.dataset.initialPlotStatus,
        areaMin: root.dataset.initialAreaMin,
        areaMax: root.dataset.initialAreaMax,
        priceMin: root.dataset.initialPriceMin,
        priceMax: root.dataset.initialPriceMax,
    }),
    plotSort: VALID_PLOT_SORTS.has(root.dataset.initialPlotSort) ? root.dataset.initialPlotSort : 'default',
    plotsLoading: false,
    plotsError: null,
    plotsLoadedFor: cleanSlug(root.dataset.initialPlotsLoadedFor),
    plotsOpen: root.dataset.initialPlotsOpen === 'true',
    loading: false,
    error: null,
    incomplete: root.dataset.initialIncomplete === 'true',
});

export const normalizeState = (candidate, stage) => {
    const state = {
        activeTab: VALID_TABS.has(candidate.activeTab) ? candidate.activeTab : 'genplan',
        mode: VALID_MODES.has(candidate.mode) ? candidate.mode : '3d',
        selectedQuarter: cleanSlug(candidate.selectedQuarter),
        selectedInfrastructure: cleanSlug(candidate.selectedInfrastructure),
        selectedPlot: cleanSlug(candidate.selectedPlot),
        plotFilters: filtersFrom(candidate.plotFilters || {}),
        plotSort: VALID_PLOT_SORTS.has(candidate.plotSort) ? candidate.plotSort : 'default',
        plotsLoading: Boolean(candidate.plotsLoading),
        plotsError: candidate.plotsError || null,
        plotsLoadedFor: cleanSlug(candidate.plotsLoadedFor),
        plotsOpen: Boolean(candidate.plotsOpen),
        loading: Boolean(candidate.loading),
        error: candidate.error || null,
        incomplete: false,
    };

    if (state.activeTab === 'surroundings') {
        state.selectedQuarter = null;
        state.selectedInfrastructure = null;
        state.selectedPlot = null;
        state.plotsOpen = false;
    } else if (state.selectedQuarter && stage.hasQuarter(state.selectedQuarter, state.mode)) {
        state.selectedInfrastructure = null;
    } else {
        state.selectedQuarter = null;
        state.selectedPlot = null;
        state.plotsOpen = false;

        if (!state.selectedInfrastructure || !stage.hasPoint(state.selectedInfrastructure, state.mode)) {
            state.selectedInfrastructure = null;
        }
    }

    if (!state.selectedQuarter) state.selectedPlot = null;

    state.incomplete = !stage.hasAnyGeometry(state.mode);

    return state;
};
