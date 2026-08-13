const VALID_MODES = new Set(['2d', '3d']);
const VALID_TABS = new Set(['genplan', 'surroundings']);

const cleanSlug = (value) => typeof value === 'string' && value.trim().length > 0 && value.length <= 255 ? value : null;

export const stateFromRoot = (root) => ({
    activeTab: VALID_TABS.has(root.dataset.initialTab) ? root.dataset.initialTab : 'genplan',
    mode: VALID_MODES.has(root.dataset.initialMode) ? root.dataset.initialMode : '3d',
    selectedQuarter: cleanSlug(root.dataset.initialQuarter),
    selectedInfrastructure: cleanSlug(root.dataset.initialPoint),
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
        loading: Boolean(candidate.loading),
        error: candidate.error || null,
        incomplete: false,
    };

    if (state.activeTab === 'surroundings') {
        state.selectedQuarter = null;
        state.selectedInfrastructure = null;
    } else if (state.selectedQuarter && stage.hasQuarter(state.selectedQuarter, state.mode)) {
        state.selectedInfrastructure = null;
    } else {
        state.selectedQuarter = null;

        if (!state.selectedInfrastructure || !stage.hasPoint(state.selectedInfrastructure, state.mode)) {
            state.selectedInfrastructure = null;
        }
    }

    state.incomplete = !stage.hasAnyGeometry(state.mode);

    return state;
};
