import './bootstrap';

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
