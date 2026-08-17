import { normalizeState, stateFromRoot } from './core/state';
import { createStage } from './core/stage';
import { stateFromUrl, writeStateUrl } from './core/url-state';
import { createPlots } from './interactions/plots';

document.querySelectorAll('[data-genplan-foundation]').forEach((root) => {
    const stage = createStage(root);
    let state = normalizeState(stateFromRoot(root), stage);
    let lastSelectionTrigger = null;
    let plots = null;

    const commit = (candidate, options = {}) => {
        state = normalizeState(candidate, stage);
        stage.render(state);
        plots?.render(state);
        if (options.history !== false) writeStateUrl(state, { replace: options.replace });
    };

    plots = createPlots(root, { getState: () => state, commit });

    const selectQuarter = (trigger) => {
        if (trigger.getAttribute('aria-disabled') === 'true') return;
        lastSelectionTrigger = trigger;
        commit({ ...state, activeTab: 'genplan', selectedQuarter: trigger.dataset.quarterSlug, selectedInfrastructure: null, selectedPlot: null, plotsOpen: false, plotsLoading: false, plotsError: null });
    };

    const selectPoint = (trigger) => {
        lastSelectionTrigger = trigger;
        commit({ ...state, activeTab: 'genplan', selectedQuarter: null, selectedInfrastructure: trigger.dataset.pointSlug, selectedPlot: null, plotsOpen: false, plotsLoading: false, plotsError: null });
    };

    root.addEventListener('click', (event) => {
        const view = event.target.closest('[data-genplan-view]');
        const mode = event.target.closest('[data-genplan-mode]');
        const quarter = event.target.closest('[data-quarter-trigger]');
        const point = event.target.closest('[data-point-trigger]');
        const close = event.target.closest('[data-selection-close]');
        const plotsOpen = event.target.closest('[data-plots-open]');

        if (view) {
            event.preventDefault();
            commit({ ...state, activeTab: view.dataset.genplanView, selectedQuarter: null, selectedInfrastructure: null, selectedPlot: null, plotsOpen: false });
        } else if (mode) {
            event.preventDefault();
            commit({ ...state, mode: mode.dataset.genplanMode });
            if (state.plotsOpen) plots.ensure(state);
        } else if (plotsOpen) {
            event.preventDefault();
            plots.ensure({ ...state, selectedQuarter: plotsOpen.dataset.quarterSlug, selectedPlot: null, plotsOpen: true });
        } else if (quarter) {
            event.preventDefault();
            selectQuarter(quarter);
        } else if (point) {
            event.preventDefault();
            selectPoint(point);
        } else if (close) {
            commit({ ...state, selectedQuarter: null, selectedInfrastructure: null, selectedPlot: null, plotsOpen: false });
            lastSelectionTrigger?.focus({ preventScroll: true });
        }
    });

    root.addEventListener('keydown', (event) => {
        const quarter = event.target.closest('[data-quarter-trigger]');
        const point = event.target.closest('[data-point-trigger]');
        const tab = event.target.closest('[data-genplan-view]');

        if ((quarter || point) && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault();
            if (quarter) selectQuarter(quarter);
            if (point) selectPoint(point);
            return;
        }

        if (event.key === 'Escape' && state.selectedPlot) {
            event.preventDefault();
            commit({ ...state, selectedPlot: null });
            return;
        }

        if (event.key === 'Escape' && (state.selectedQuarter || state.selectedInfrastructure)) {
            event.preventDefault();
            commit({ ...state, selectedQuarter: null, selectedInfrastructure: null, selectedPlot: null, plotsOpen: false });
            lastSelectionTrigger?.focus({ preventScroll: true });
            return;
        }

        if (!tab) return;
        const index = stage.tabs.indexOf(tab);
        let next = index;
        if (event.key === 'ArrowRight') next = (index + 1) % stage.tabs.length;
        if (event.key === 'ArrowLeft') next = (index - 1 + stage.tabs.length) % stage.tabs.length;
        if (event.key === 'Home') next = 0;
        if (event.key === 'End') next = stage.tabs.length - 1;
        if (next !== index) {
            event.preventDefault();
            stage.tabs[next].focus();
            commit({ ...state, activeTab: stage.tabs[next].dataset.genplanView, selectedQuarter: null, selectedInfrastructure: null, selectedPlot: null, plotsOpen: false });
        }
    });

    ['pointerover', 'focusin'].forEach((eventName) => root.addEventListener(eventName, (event) => {
        const trigger = event.target.closest('[data-quarter-trigger]');
        if (trigger) stage.highlightQuarter(trigger.dataset.quarterSlug, true);
    }));
    ['pointerout', 'focusout'].forEach((eventName) => root.addEventListener(eventName, (event) => {
        const trigger = event.target.closest('[data-quarter-trigger]');
        if (trigger) stage.highlightQuarter(trigger.dataset.quarterSlug, false);
    }));

    window.addEventListener('resize', () => stage.render(state), { passive: true });
    window.addEventListener('popstate', () => {
        commit({ ...stateFromUrl(), loading: false, error: null }, { history: false });
        if (state.selectedQuarter) plots.ensure(state);
    });
    stage.render(state);
    plots.render(state);
});
