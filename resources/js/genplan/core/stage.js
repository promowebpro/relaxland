const selectResponsiveImage = (stage, mode) => {
    const mobile = window.matchMedia('(max-width: 47.99rem)').matches;
    const mobileSource = stage.getAttribute(`data-mobile-image-${mode}`);

    return {
        source: mobile && mobileSource ? mobileSource : stage.getAttribute(`data-image-${mode}`),
        usesMobileBackground: mobile && Boolean(mobileSource),
    };
};

export const initializeStage = (root) => {
    const stage = root.querySelector('[data-genplan-stage]');

    if (!stage) return;

    const image = stage.querySelector('[data-genplan-image]');
    const modeButtons = [...root.querySelectorAll('[data-genplan-mode]')];
    const geometryLayers = [...stage.querySelectorAll('[data-geometry-mode]')];
    const emptyStates = [...stage.querySelectorAll('[data-genplan-geometry-empty]')];
    let mode = modeButtons.find((button) => button.getAttribute('aria-pressed') === 'true')?.dataset.genplanMode || '3d';

    const renderMode = (nextMode) => {
        mode = nextMode;
        const responsiveImage = selectResponsiveImage(stage, mode);
        image.src = responsiveImage.source;
        image.alt = `${root.querySelector('.genplan-toolbar .eyebrow')?.textContent?.trim() || 'Генплан'}, вид ${mode.toUpperCase()}`;
        stage.classList.toggle('has-mobile-background', responsiveImage.usesMobileBackground);
        modeButtons.forEach((button) => button.setAttribute('aria-pressed', button.dataset.genplanMode === mode ? 'true' : 'false'));
        geometryLayers.forEach((layer) => layer.toggleAttribute('hidden', layer.dataset.geometryMode !== mode));
        emptyStates.forEach((state) => state.toggleAttribute('hidden', state.dataset.genplanGeometryEmpty !== mode || state.dataset.hasGeometry === 'true'));
    };

    modeButtons.forEach((button) => button.addEventListener('click', () => renderMode(button.dataset.genplanMode)));
    window.addEventListener('resize', () => renderMode(mode), { passive: true });
    renderMode(mode);
};

export const initializeQuarterSelection = (root) => {
    const controls = [...root.querySelectorAll('[data-quarter-target]')];
    const cards = [...root.querySelectorAll('[data-quarter-card]')];

    const select = (target) => {
        controls.forEach((control) => {
            const selected = control.dataset.quarterTarget === target;
            control.classList.toggle('is-selected', selected);
            control.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });
        cards.forEach((card) => { card.hidden = card.id !== target; });
    };

    controls.forEach((control) => {
        control.addEventListener('click', () => select(control.dataset.quarterTarget));
        control.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                select(control.dataset.quarterTarget);
            }
        });
    });
};
