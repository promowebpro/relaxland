const responsiveImage = (stage, mode) => {
    const isMobile = window.matchMedia('(max-width: 47.99rem)').matches;
    const mobileSource = stage.getAttribute(`data-mobile-image-${mode}`);

    return {
        source: isMobile && mobileSource ? mobileSource : stage.getAttribute(`data-image-${mode}`),
        mobile: isMobile && Boolean(mobileSource),
    };
};

export const createStage = (root) => {
    const stage = root.querySelector('[data-genplan-stage]');
    const quarterTriggers = [...root.querySelectorAll('[data-quarter-trigger]')];
    const pointTriggers = [...root.querySelectorAll('[data-point-trigger]')];
    const modeControls = [...root.querySelectorAll('[data-genplan-mode]')];
    const panels = [...root.querySelectorAll('[data-genplan-panel]')];
    const tabs = [...root.querySelectorAll('[data-genplan-view]')];
    const layers = stage ? [...stage.querySelectorAll('[data-geometry-mode]')] : [];
    const emptyStates = stage ? [...stage.querySelectorAll('[data-genplan-geometry-empty]')] : [];
    const cards = [...root.querySelectorAll('[data-quarter-card], [data-point-card]')];
    const selectionEmpty = root.querySelector('[data-selection-empty]');
    const image = stage?.querySelector('[data-genplan-image]');

    const hasQuarter = (slug, mode) => quarterTriggers.some((trigger) => trigger.dataset.quarterSlug === slug
        && (trigger.dataset.triggerMode === mode || trigger.dataset.quarterModes?.split(',').includes(mode)));
    const hasPoint = (slug, mode) => pointTriggers.some((trigger) => trigger.dataset.pointSlug === slug && trigger.dataset.triggerMode === mode);
    const hasAnyGeometry = (mode) => emptyStates.find((item) => item.dataset.genplanGeometryEmpty === mode)?.dataset.hasGeometry === 'true';

    const render = (state) => {
        root.dataset.loading = state.loading ? 'true' : 'false';
        root.dataset.incomplete = state.incomplete ? 'true' : 'false';

        tabs.forEach((tab) => {
            const active = tab.dataset.genplanView === state.activeTab;
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.tabIndex = active ? 0 : -1;
        });
        panels.forEach((panel) => panel.toggleAttribute('hidden', panel.dataset.genplanPanel !== state.activeTab));
        modeControls.forEach((control) => control.setAttribute('aria-pressed', control.dataset.genplanMode === state.mode ? 'true' : 'false'));

        if (stage && image) {
            const nextImage = responsiveImage(stage, state.mode);
            image.src = nextImage.source;
            image.alt = `${root.querySelector('.genplan-toolbar .eyebrow')?.textContent?.trim() || 'Генплан'}, вид ${state.mode.toUpperCase()}`;
            stage.classList.toggle('has-mobile-background', nextImage.mobile);
        }

        layers.forEach((layer) => layer.toggleAttribute('hidden', layer.dataset.geometryMode !== state.mode));
        emptyStates.forEach((empty) => empty.toggleAttribute('hidden', empty.dataset.genplanGeometryEmpty !== state.mode || empty.dataset.hasGeometry === 'true'));

        quarterTriggers.forEach((trigger) => {
            const selected = trigger.dataset.quarterSlug === state.selectedQuarter;
            const available = !trigger.dataset.quarterModes || trigger.dataset.quarterModes.split(',').includes(state.mode);
            trigger.classList.toggle('is-selected', selected);
            trigger.setAttribute('aria-pressed', selected ? 'true' : 'false');
            if (trigger.dataset.quarterModes) trigger.setAttribute('aria-disabled', available ? 'false' : 'true');
        });
        pointTriggers.forEach((trigger) => {
            const selected = trigger.dataset.pointSlug === state.selectedInfrastructure;
            trigger.classList.toggle('is-selected', selected);
            trigger.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });
        cards.forEach((card) => {
            const visible = card.dataset.quarterCard === state.selectedQuarter || card.dataset.pointCard === state.selectedInfrastructure;
            card.toggleAttribute('hidden', !visible);
        });
        selectionEmpty?.toggleAttribute('hidden', Boolean(state.selectedQuarter || state.selectedInfrastructure));
    };

    const highlightQuarter = (slug, active) => {
        quarterTriggers.filter((trigger) => trigger.dataset.quarterSlug === slug)
            .forEach((trigger) => trigger.classList.toggle('is-hovered', active));
    };

    return { render, hasQuarter, hasPoint, hasAnyGeometry, highlightQuarter, tabs };
};
