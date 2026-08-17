import './bootstrap';
import './genplan/foundation';

const menuToggle = document.querySelector('[data-menu-toggle]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

if (menuToggle && mobileMenu) {
    const closeButtons = mobileMenu.querySelectorAll('[data-menu-close]');
    let previousFocus = null;

    const closeMenu = () => {
        mobileMenu.hidden = true;
        menuToggle.setAttribute('aria-expanded', 'false');
        document.body.style.removeProperty('overflow');
        previousFocus?.focus();
    };

    const openMenu = () => {
        previousFocus = document.activeElement;
        mobileMenu.hidden = false;
        menuToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        mobileMenu.querySelector('button[data-menu-close]')?.focus();
    };

    menuToggle.addEventListener('click', () => {
        if (mobileMenu.hidden) {
            openMenu();
        } else {
            closeMenu();
        }
    });

    closeButtons.forEach((button) => button.addEventListener('click', closeMenu));
    mobileMenu.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !mobileMenu.hidden) {
            closeMenu();
        }

        if (event.key !== 'Tab' || mobileMenu.hidden) {
            return;
        }

        const focusable = [...mobileMenu.querySelectorAll('a[href], button:not([disabled])')];
        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    });
}

document.querySelectorAll('[data-season-tabs]').forEach((tabs) => {
    const buttons = [...tabs.querySelectorAll('[data-season-tab]')];
    const panels = [...tabs.querySelectorAll('[data-season-panel]')];

    const activate = (index, moveFocus = false) => {
        buttons.forEach((button, buttonIndex) => {
            const active = buttonIndex === index;
            button.setAttribute('aria-selected', active ? 'true' : 'false');
            button.tabIndex = active ? 0 : -1;

            if (active && moveFocus) {
                button.focus();
            }
        });

        panels.forEach((panel, panelIndex) => {
            panel.hidden = panelIndex !== index;
        });
    };

    buttons.forEach((button, index) => {
        button.addEventListener('click', () => activate(index));
        button.addEventListener('keydown', (event) => {
            let nextIndex = index;

            if (event.key === 'ArrowRight') nextIndex = (index + 1) % buttons.length;
            if (event.key === 'ArrowLeft') nextIndex = (index - 1 + buttons.length) % buttons.length;
            if (event.key === 'Home') nextIndex = 0;
            if (event.key === 'End') nextIndex = buttons.length - 1;

            if (nextIndex !== index) {
                event.preventDefault();
                activate(nextIndex, true);
            }
        });
    });
});

const leadModal = document.querySelector('[data-lead-modal]');

if (leadModal) {
    const dialog = leadModal.querySelector('[role="dialog"]');
    const sourceInput = leadModal.querySelector('[data-lead-source]');
    const formTypeInput = leadModal.querySelector('[data-lead-form-type]');
    const headingInput = leadModal.querySelector('[data-lead-form-heading]');
    const quarterInput = leadModal.querySelector('[data-lead-quarter]');
    const plotInput = leadModal.querySelector('[data-lead-plot]');
    const title = leadModal.querySelector('[data-lead-modal-title]');
    const nameInput = leadModal.querySelector('[data-lead-name]');
    let previousFocus = null;

    const focusableElements = () => [...dialog.querySelectorAll('a[href], button:not([disabled]), input:not([type="hidden"]):not([tabindex="-1"]), textarea')]
        .filter((element) => !element.hasAttribute('disabled'));

    const closeModal = () => {
        leadModal.classList.remove('is-open');
        document.body.classList.remove('has-open-modal');

        if (window.location.hash === '#lead-form') {
            window.history.replaceState(null, '', `${window.location.pathname}${window.location.search}`);
        }

        previousFocus?.focus();
    };

    const openModal = (trigger = null) => {
        previousFocus = trigger || document.activeElement;

        if (trigger) {
            sourceInput.value = trigger.dataset.leadSource || sourceInput.value;
            formTypeInput.value = trigger.dataset.leadFormType || formTypeInput.value;
            const heading = trigger.dataset.leadHeading || 'Давайте знакомиться';
            title.textContent = heading;
            headingInput.value = heading;
            quarterInput.value = trigger.dataset.leadQuarter || '';
            plotInput.value = trigger.dataset.leadPlot || '';
        }

        nameInput.required = ['visit', 'consultation'].includes(formTypeInput.value);
        nameInput.setAttribute('aria-required', nameInput.required ? 'true' : 'false');
        leadModal.classList.add('is-open');
        document.body.classList.add('has-open-modal');
        window.history.replaceState(null, '', `${window.location.pathname}${window.location.search}#lead-form`);
        window.requestAnimationFrame(() => {
            window.requestAnimationFrame(() => dialog.focus({ preventScroll: true }));
        });
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-lead-modal-trigger]');
        if (!trigger) return;
        event.preventDefault();
        openModal(trigger);
    });

    leadModal.querySelectorAll('[data-lead-modal-close]').forEach((control) => {
        control.addEventListener('click', (event) => {
            event.preventDefault();
            closeModal();
        });
    });

    document.addEventListener('keydown', (event) => {
        if (!leadModal.classList.contains('is-open')) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal();
            return;
        }

        if (event.key !== 'Tab') return;

        const focusable = focusableElements();
        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    });

    if (leadModal.hasAttribute('data-lead-open-on-load') || window.location.hash === '#lead-form') {
        openModal();
    }
}
