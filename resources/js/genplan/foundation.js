import { initializeQuarterSelection, initializeStage } from './core/stage';

document.querySelectorAll('[data-genplan-foundation]').forEach((root) => {
    const tabs = [...root.querySelectorAll('[data-genplan-view]')];
    const panels = [...root.querySelectorAll('[data-genplan-panel]')];

    const activateTab = (index, moveFocus = false) => {
        tabs.forEach((tab, tabIndex) => {
            const active = tabIndex === index;
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.tabIndex = active ? 0 : -1;
            if (active && moveFocus) tab.focus();
        });
        panels.forEach((panel) => { panel.hidden = panel.dataset.genplanPanel !== tabs[index].dataset.genplanView; });
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activateTab(index));
        tab.addEventListener('keydown', (event) => {
            let next = index;
            if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
            if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
            if (event.key === 'Home') next = 0;
            if (event.key === 'End') next = tabs.length - 1;
            if (next !== index) {
                event.preventDefault();
                activateTab(next, true);
            }
        });
    });

    initializeStage(root);
    initializeQuarterSelection(root);
});
