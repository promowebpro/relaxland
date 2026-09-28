import './bootstrap';
import './genplan/foundation';

const menuToggle = document.querySelector('[data-menu-toggle]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

if (menuToggle && mobileMenu) {
    const closeButtons = mobileMenu.querySelectorAll('[data-menu-close]');
    let previousFocus = null;
    let closeTimer = null;

    const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const finishClose = () => {
        mobileMenu.hidden = true;
        mobileMenu.classList.remove('is-closing');
        previousFocus?.focus();
    };

    const closeMenu = () => {
        if (mobileMenu.hidden || mobileMenu.classList.contains('is-closing')) return;

        window.clearTimeout(closeTimer);
        mobileMenu.classList.remove('is-open');
        mobileMenu.classList.add('is-closing');
        menuToggle.setAttribute('aria-expanded', 'false');
        document.body.style.removeProperty('overflow');

        if (reducedMotion()) {
            finishClose();
            return;
        }

        closeTimer = window.setTimeout(finishClose, 700);
    };

    const openMenu = () => {
        window.clearTimeout(closeTimer);
        previousFocus = document.activeElement;
        mobileMenu.hidden = false;
        mobileMenu.classList.remove('is-closing');
        menuToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';

        window.requestAnimationFrame(() => {
            window.requestAnimationFrame(() => {
                mobileMenu.classList.add('is-open');
                mobileMenu.querySelector('button[data-menu-close]')?.focus();
            });
        });
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

const homeIntro = document.querySelector('[data-home-intro-motion]');

if (homeIntro && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    homeIntro.classList.add('is-motion-pending');

    const introObserver = new IntersectionObserver((entries, observer) => {
        const introEntry = entries.find((entry) => entry.target === homeIntro);

        if (!introEntry?.isIntersecting) return;

        homeIntro.classList.add('is-motion-visible');
        observer.unobserve(homeIntro);
    }, {
        threshold: 0.16,
        rootMargin: '0px 0px -8% 0px',
    });

    introObserver.observe(homeIntro);
}

const homeMotionSections = [...document.querySelectorAll('[data-home-motion]')];

if (homeMotionSections.length && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    homeMotionSections.forEach((section) => section.classList.add('is-motion-pending'));

    const homeMotionObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;

            entry.target.classList.add('is-motion-visible');
            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.14,
        rootMargin: '0px 0px -8% 0px',
    });

    homeMotionSections.forEach((section) => homeMotionObserver.observe(section));
}

document.querySelectorAll('[data-home-rhythm]').forEach((section) => {
    const scroller = section.querySelector('[data-home-rhythm-scroll]');
    const previousButton = section?.querySelector('[data-home-rhythm-prev]');
    const nextButton = section?.querySelector('[data-home-rhythm-next]');
    const progress = section?.querySelector('[data-home-rhythm-progress]');
    const desktopQuery = window.matchMedia('(min-width: 48rem)');
    const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    let dragStartX = 0;
    let dragStartScroll = 0;
    let dragging = false;
    let updateFrame = null;
    let wheelUnlockTimer = null;
    let wheelLocked = false;
    let travel = 0;
    let sectionStart = 0;

    if (!scroller) return;

    const clamp = (value, minimum = 0, maximum = 1) => Math.min(maximum, Math.max(minimum, value));
    const fadeBetween = (value, from, to) => clamp((value - from) / (to - from));
    const usesScrollTimeline = () => desktopQuery.matches && !reducedMotionQuery.matches;

    const updatePhases = (value) => {
        const outgoingOpacity = 1 - fadeBetween(value, 0.7, 0.76);
        const openingOpacity = outgoingOpacity;
        const middleOpacity = fadeBetween(value, 0.2, 0.28) * outgoingOpacity;
        const closingOpacity = fadeBetween(value, 0.8, 0.88);
        const titleOpacity = 1;

        section.style.setProperty('--rhythm-opening-opacity', openingOpacity.toFixed(3));
        section.style.setProperty('--rhythm-middle-opacity', middleOpacity.toFixed(3));
        section.style.setProperty('--rhythm-closing-opacity', closingOpacity.toFixed(3));
        section.style.setProperty('--rhythm-title-opacity', titleOpacity.toFixed(3));
    };

    const updateControls = () => {
        const maximum = Math.max(0, scroller.scrollWidth - scroller.clientWidth);
        const endTolerance = 2;
        const visibleProgress = scroller.scrollWidth > 0
            ? Math.min(1, (scroller.scrollLeft + scroller.clientWidth) / scroller.scrollWidth)
            : 1;

        previousButton?.toggleAttribute('disabled', scroller.scrollLeft <= endTolerance);
        nextButton?.toggleAttribute('disabled', scroller.scrollLeft >= maximum - endTolerance);
        progress?.style.setProperty('--rhythm-progress', String(visibleProgress));
    };

    const syncFromPage = () => {
        if (updateFrame !== null) return;

        updateFrame = window.requestAnimationFrame(() => {
            updateFrame = null;

            if (usesScrollTimeline()) {
                const timelineProgress = travel > 0
                    ? clamp((window.scrollY - sectionStart) / travel)
                    : 0;

                scroller.scrollLeft = timelineProgress * travel;
                updatePhases(timelineProgress);
            }

            updateControls();
        });
    };

    const measure = () => {
        const scrollLinked = usesScrollTimeline();

        section.classList.toggle('is-rhythm-static', !scrollLinked);
        section.style.removeProperty('--rhythm-travel');
        travel = Math.max(0, scroller.scrollWidth - scroller.clientWidth);

        if (scrollLinked) {
            section.style.setProperty('--rhythm-travel', `${travel}px`);
            sectionStart = window.scrollY + section.getBoundingClientRect().top;
        } else {
            updatePhases(0);
        }

        syncFromPage();
    };

    const moveTo = (target, behavior = 'smooth') => {
        const maximum = Math.max(0, scroller.scrollWidth - scroller.clientWidth);
        const nextPosition = clamp(target, 0, maximum);

        if (usesScrollTimeline()) {
            window.scrollTo({ top: sectionStart + nextPosition, behavior });
        } else {
            scroller.scrollTo({ left: nextPosition, behavior });
        }
    };

    const moveByPage = (direction) => {
        moveTo(scroller.scrollLeft + (direction * Math.max(280, scroller.clientWidth * 0.72)));
    };

    previousButton?.addEventListener('click', () => moveByPage(-1));
    nextButton?.addEventListener('click', () => moveByPage(1));

    section.addEventListener('wheel', (event) => {
        if (!usesScrollTimeline()) return;

        if (Math.abs(event.deltaY) >= Math.abs(event.deltaX)) {
            const wheelDelta = event.deltaMode === WheelEvent.DOM_DELTA_LINE
                ? event.deltaY * 16
                : event.deltaMode === WheelEvent.DOM_DELTA_PAGE
                    ? event.deltaY * window.innerHeight
                    : event.deltaY;
            const timelinePosition = window.scrollY - sectionStart;
            const movesInsideTimeline = (wheelDelta > 0 && timelinePosition < travel - 1)
                || (wheelDelta < 0 && timelinePosition > 1);
            const timelineIsActive = timelinePosition >= -1 && timelinePosition <= travel + 1;

            if (timelineIsActive && movesInsideTimeline) {
                event.preventDefault();

                if (wheelLocked) return;

                const snapPoints = [0, travel * 0.68, travel];
                const tolerance = 16;
                const target = wheelDelta > 0
                    ? snapPoints.find((point) => point > timelinePosition + tolerance) ?? travel
                    : snapPoints.slice().reverse().find((point) => point < timelinePosition - tolerance) ?? 0;

                wheelLocked = true;
                window.clearTimeout(wheelUnlockTimer);
                window.scrollTo({ top: sectionStart + target, behavior: 'smooth' });
                wheelUnlockTimer = window.setTimeout(() => {
                    wheelLocked = false;
                }, 760);
            }

            return;
        }

        event.preventDefault();
        window.scrollBy({ top: event.deltaX, behavior: 'auto' });
    }, { passive: false });

    scroller.addEventListener('keydown', (event) => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;

        event.preventDefault();

        if (event.key === 'Home') {
            moveTo(0);
        } else if (event.key === 'End') {
            moveTo(scroller.scrollWidth);
        } else {
            moveByPage(event.key === 'ArrowRight' ? 1 : -1);
        }
    });

    scroller.addEventListener('pointerdown', (event) => {
        if (event.pointerType === 'touch' || (event.pointerType === 'mouse' && event.button !== 0)) return;

        dragging = true;
        dragStartX = event.clientX;
        dragStartScroll = scroller.scrollLeft;
        scroller.classList.add('is-dragging');
        scroller.setPointerCapture(event.pointerId);
    });

    scroller.addEventListener('pointermove', (event) => {
        if (!dragging) return;

        moveTo(dragStartScroll - (event.clientX - dragStartX), 'auto');
    });

    const stopDragging = (event) => {
        if (!dragging) return;

        dragging = false;
        scroller.classList.remove('is-dragging');

        if (scroller.hasPointerCapture(event.pointerId)) {
            scroller.releasePointerCapture(event.pointerId);
        }
    };

    scroller.addEventListener('pointerup', stopDragging);
    scroller.addEventListener('pointercancel', stopDragging);
    scroller.addEventListener('scroll', updateControls, { passive: true });
    window.addEventListener('scroll', syncFromPage, { passive: true });
    window.addEventListener('resize', measure, { passive: true });
    desktopQuery.addEventListener('change', measure);
    reducedMotionQuery.addEventListener('change', measure);
    window.requestAnimationFrame(measure);
});

document.querySelectorAll('[data-home-care]').forEach((carousel) => {
    const slides = [...carousel.querySelectorAll('[data-home-care-slide]')];
    const tabs = [...carousel.querySelectorAll('[data-home-care-tab]')];
    const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let activeIndex = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
    let hideTimer = null;
    let pointerStartX = null;

    const activate = (nextIndex, moveFocus = false) => {
        if (!slides.length) return;

        const normalizedIndex = (nextIndex + slides.length) % slides.length;
        const previousSlide = slides[activeIndex];
        const nextSlide = slides[normalizedIndex];

        if (normalizedIndex === activeIndex) {
            if (moveFocus) tabs[normalizedIndex]?.focus();
            return;
        }

        window.clearTimeout(hideTimer);
        slides.forEach((slide, index) => {
            if (index !== activeIndex && index !== normalizedIndex) slide.hidden = true;
        });

        nextSlide.hidden = false;
        nextSlide.classList.remove('is-active');
        previousSlide?.classList.remove('is-active');

        tabs.forEach((tab, index) => {
            const selected = index === normalizedIndex;
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.tabIndex = selected ? 0 : -1;
        });

        window.requestAnimationFrame(() => {
            window.requestAnimationFrame(() => nextSlide.classList.add('is-active'));
        });

        if (previousSlide) {
            if (reducedMotion()) {
                previousSlide.hidden = true;
            } else {
                hideTimer = window.setTimeout(() => {
                    previousSlide.hidden = true;
                }, 650);
            }
        }

        activeIndex = normalizedIndex;
        if (moveFocus) tabs[normalizedIndex]?.focus();
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activate(index));
        tab.addEventListener('keydown', (event) => {
            let nextIndex = index;

            if (event.key === 'ArrowRight') nextIndex = index + 1;
            if (event.key === 'ArrowLeft') nextIndex = index - 1;
            if (event.key === 'Home') nextIndex = 0;
            if (event.key === 'End') nextIndex = slides.length - 1;

            if (nextIndex === index) return;

            event.preventDefault();
            activate(nextIndex, true);
        });
    });

    carousel.addEventListener('pointerdown', (event) => {
        if (event.target.closest('button')) return;
        pointerStartX = event.clientX;
    });

    carousel.addEventListener('pointerup', (event) => {
        if (pointerStartX === null) return;

        const distance = event.clientX - pointerStartX;
        pointerStartX = null;

        if (Math.abs(distance) < 50) return;
        activate(activeIndex + (distance < 0 ? 1 : -1));
    });

    carousel.addEventListener('pointercancel', () => {
        pointerStartX = null;
    });
});

const cookieBanner = document.querySelector('[data-cookie-banner]');

if (cookieBanner) {
    const consentKey = 'relaxland-cookie-consent';
    const acceptButton = cookieBanner.querySelector('[data-cookie-accept]');
    let accepted = false;

    try {
        accepted = window.localStorage.getItem(consentKey) === 'accepted';
    } catch {
        accepted = false;
    }

    if (!accepted) {
        cookieBanner.hidden = false;
    }

    acceptButton?.addEventListener('click', () => {
        try {
            window.localStorage.setItem(consentKey, 'accepted');
        } catch {
            // The acknowledgement still applies to the current page session.
        }

        cookieBanner.hidden = true;
    });
}

document.querySelectorAll('[data-home-seasons]').forEach((section) => {
    const buttons = [...section.querySelectorAll('[data-season-tab]')];
    const cards = [...section.querySelectorAll('[data-season-card]')];
    const sticky = section.querySelector('.home-seasons__sticky');
    const scene = section.querySelector('[data-home-seasons-scene]');
    const desktopQuery = window.matchMedia('(min-width: 48rem)');
    const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    const coarsePointerQuery = window.matchMedia('(hover: none)');
    let frame = null;
    let pinned = false;

    const clamp = (value) => Math.min(1, Math.max(0, value));

    const setActiveTab = (index, moveFocus = false) => {
        buttons.forEach((button, buttonIndex) => {
            const active = buttonIndex === index;
            button.setAttribute('aria-selected', active ? 'true' : 'false');
            button.tabIndex = active ? 0 : -1;

            if (active && moveFocus) {
                button.focus();
            }
        });
    };

    const measureShift = () => {
        const canPin = desktopQuery.matches && !reducedMotionQuery.matches && sticky && scene;

        if (!canPin) {
            pinned = false;
            section.classList.remove('is-seasons-pinned');
            section.style.setProperty('--seasons-shift', '0px');
            section.style.setProperty('--seasons-progress', '0');
            return 0;
        }

        section.classList.add('is-seasons-pinned');
        const styles = window.getComputedStyle(sticky);
        const available = sticky.clientHeight - parseFloat(styles.paddingTop) - parseFloat(styles.paddingBottom);
        const shift = Math.max(0, scene.scrollHeight - available);

        if (shift < 48) {
            pinned = false;
            section.classList.remove('is-seasons-pinned');
            section.style.setProperty('--seasons-shift', '0px');
            section.style.setProperty('--seasons-progress', '0');
            return 0;
        }

        pinned = true;
        section.style.setProperty('--seasons-shift', `${Math.round(shift)}px`);
        return shift;
    };

    const updateProgress = () => {
        if (!pinned) {
            return;
        }

        const travel = Math.max(1, section.offsetHeight - window.innerHeight);
        const progress = clamp(-section.getBoundingClientRect().top / travel);
        section.style.setProperty('--seasons-progress', progress.toFixed(4));
        setActiveTab(progress > 0.55 ? 1 : 0);
    };

    const sync = () => {
        measureShift();
        updateProgress();
    };

    const requestUpdate = () => {
        if (frame) return;

        frame = window.requestAnimationFrame(() => {
            frame = null;
            updateProgress();
        });
    };

    cards.forEach((card, index) => {
        card.addEventListener('pointerenter', () => setActiveTab(index));
        card.addEventListener('focusin', () => setActiveTab(index));

        if (coarsePointerQuery.matches) {
            card.addEventListener('click', () => {
                const next = !card.classList.contains('is-season-hover');
                cards.forEach((item) => item.classList.toggle('is-season-hover', item === card && next));
            });
        }
    });

    buttons.forEach((button, index) => {
        button.addEventListener('click', () => {
            setActiveTab(index, true);
            cards[index]?.scrollIntoView({ block: 'center', behavior: reducedMotionQuery.matches ? 'auto' : 'smooth' });
        });

        button.addEventListener('keydown', (event) => {
            let nextIndex = index;

            if (event.key === 'ArrowRight') nextIndex = (index + 1) % buttons.length;
            if (event.key === 'ArrowLeft') nextIndex = (index - 1 + buttons.length) % buttons.length;
            if (event.key === 'Home') nextIndex = 0;
            if (event.key === 'End') nextIndex = buttons.length - 1;

            if (nextIndex !== index) {
                event.preventDefault();
                buttons[nextIndex]?.click();
            }
        });
    });

    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', sync);
    desktopQuery.addEventListener('change', sync);
    reducedMotionQuery.addEventListener('change', sync);

    if (typeof ResizeObserver === 'function' && scene) {
        new ResizeObserver(sync).observe(scene);
    }

    sync();
});

document.querySelectorAll('[data-home-stories]').forEach((section) => {
    const sticky = section.querySelector('.home-stories__sticky');
    const viewport = section.querySelector('[data-home-stories-viewport]');
    const scene = section.querySelector('[data-home-stories-scene]');
    const desktopQuery = window.matchMedia('(min-width: 48rem)');
    const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    let frame = null;
    let pinned = false;

    const clamp = (value) => Math.min(1, Math.max(0, value));

    const measureShift = () => {
        const canPin = desktopQuery.matches && !reducedMotionQuery.matches && sticky && viewport && scene;

        if (!canPin) {
            pinned = false;
            section.classList.remove('is-stories-pinned');
            section.style.setProperty('--stories-shift', '0px');
            section.style.setProperty('--stories-progress', '0');
            return 0;
        }

        section.classList.add('is-stories-pinned');
        const shift = Math.max(0, scene.scrollHeight - viewport.clientHeight);

        if (shift < 64) {
            pinned = false;
            section.classList.remove('is-stories-pinned');
            section.style.setProperty('--stories-shift', '0px');
            section.style.setProperty('--stories-progress', '0');
            return 0;
        }

        pinned = true;
        section.style.setProperty('--stories-shift', `${Math.round(shift)}px`);
        return shift;
    };

    const updateProgress = () => {
        if (!pinned) {
            return;
        }

        const travel = Math.max(1, section.offsetHeight - window.innerHeight);
        const progress = clamp(-section.getBoundingClientRect().top / travel);
        section.style.setProperty('--stories-progress', progress.toFixed(4));
    };

    const sync = () => {
        measureShift();
        updateProgress();
    };

    const requestUpdate = () => {
        if (frame) return;

        frame = window.requestAnimationFrame(() => {
            frame = null;
            updateProgress();
        });
    };

    window.addEventListener('scroll', requestUpdate, { passive: true });
    window.addEventListener('resize', sync);
    desktopQuery.addEventListener('change', sync);
    reducedMotionQuery.addEventListener('change', sync);

    if (typeof ResizeObserver === 'function') {
        if (scene) new ResizeObserver(sync).observe(scene);
        if (viewport) new ResizeObserver(sync).observe(viewport);
    }

    sync();
});

const formatStoryTime = (seconds) => {
    const safe = Number.isFinite(seconds) && seconds > 0 ? Math.round(seconds) : 0;
    return `${Math.floor(safe / 60)}:${String(safe % 60).padStart(2, '0')}`;
};

document.querySelectorAll('[data-story-audio]').forEach((player) => {
    const audio = player.querySelector('[data-story-audio-element]');
    const toggle = player.querySelector('[data-story-audio-toggle]');
    const seek = player.querySelector('[data-story-audio-seek]');
    const time = player.querySelector('[data-story-audio-time]');
    const bars = [...player.querySelectorAll('.story-audio__wave span')];

    if (!audio || !toggle) return;

    const syncBars = () => {
        if (!bars.length || !audio.duration) return;
        const progress = audio.currentTime / audio.duration;
        bars.forEach((bar, index) => {
            bar.classList.toggle('is-played', index / bars.length <= progress);
        });
    };

    const syncTime = () => {
        if (!time) return;
        const total = Number(audio.dataset.duration) || audio.duration || 0;
        time.textContent = formatStoryTime(total);
    };

    toggle.addEventListener('click', async () => {
        document.querySelectorAll('[data-story-audio]').forEach((other) => {
            if (other === player) return;
            const otherAudio = other.querySelector('[data-story-audio-element]');
            otherAudio?.pause();
            other.classList.remove('is-playing');
        });

        if (audio.paused) {
            try {
                await audio.play();
                player.classList.add('is-playing');
            } catch {
                player.classList.remove('is-playing');
            }
        } else {
            audio.pause();
            player.classList.remove('is-playing');
        }
    });

    seek?.addEventListener('click', (event) => {
        if (!audio.duration) return;
        const bounds = seek.getBoundingClientRect();
        const ratio = Math.min(1, Math.max(0, (event.clientX - bounds.left) / bounds.width));
        audio.currentTime = ratio * audio.duration;
        syncBars();
    });

    audio.addEventListener('loadedmetadata', syncTime);
    audio.addEventListener('timeupdate', syncBars);
    audio.addEventListener('ended', () => {
        player.classList.remove('is-playing');
        syncBars();
    });

    syncTime();
});

document.querySelectorAll('[data-story-quote-audio]').forEach((button) => {
    const audio = button.querySelector('[data-story-audio-element]');
    if (!audio) return;

    button.addEventListener('click', async () => {
        document.querySelectorAll('[data-story-audio]').forEach((player) => {
            player.querySelector('[data-story-audio-element]')?.pause();
            player.classList.remove('is-playing');
        });
        document.querySelectorAll('[data-story-quote-audio]').forEach((other) => {
            if (other === button) return;
            other.querySelector('[data-story-audio-element]')?.pause();
            other.classList.remove('is-playing');
        });

        if (audio.paused) {
            try {
                await audio.play();
                button.classList.add('is-playing');
            } catch {
                button.classList.remove('is-playing');
            }
        } else {
            audio.pause();
            button.classList.remove('is-playing');
        }
    });

    audio.addEventListener('ended', () => button.classList.remove('is-playing'));
});

document.querySelectorAll('[data-story-video-toggle]').forEach((button) => {
    const media = button.parentElement?.querySelector('[data-story-video-element]');
    if (!media) return;

    button.addEventListener('click', async () => {
        if (media.paused) {
            try {
                await media.play();
                button.hidden = true;
            } catch {
                button.hidden = false;
            }
        } else {
            media.pause();
            button.hidden = false;
        }
    });

    media.addEventListener('ended', () => {
        button.hidden = false;
    });
});

document.querySelectorAll('[data-home-map]').forEach((map) => {
    const image = map.querySelector('[data-home-map-image]');
    const zoomIn = map.querySelector('[data-home-map-zoom-in]');
    const zoomOut = map.querySelector('[data-home-map-zoom-out]');
    const reset = map.querySelector('[data-home-map-reset]');
    const status = map.parentElement?.querySelector('[data-home-map-status]');
    const minimumScale = 1.08;
    const maximumScale = 2.4;
    let scale = minimumScale;
    let translateX = 0;
    let translateY = 0;
    let activePointer = null;
    let pointerOrigin = null;

    if (!image) return;

    const clampPosition = () => {
        const bounds = map.getBoundingClientRect();
        const maximumX = bounds.width * (scale - 1) / 2;
        const maximumY = bounds.height * (scale - 1) / 2;
        translateX = Math.max(-maximumX, Math.min(maximumX, translateX));
        translateY = Math.max(-maximumY, Math.min(maximumY, translateY));
    };

    const render = (announce = false) => {
        clampPosition();
        image.style.transform = `translate3d(${translateX}px, ${translateY}px, 0) scale(${scale})`;
        map.dataset.mapScale = scale.toFixed(2);
        map.dataset.mapX = Math.round(translateX).toString();
        map.dataset.mapY = Math.round(translateY).toString();

        if (announce && status) {
            status.textContent = `Масштаб карты ${Math.round(scale / minimumScale * 100)}%`;
        }
    };

    const changeScale = (difference) => {
        scale = Math.max(minimumScale, Math.min(maximumScale, scale + difference));
        render(true);
    };

    const resetMap = () => {
        scale = minimumScale;
        translateX = 0;
        translateY = 0;
        render(true);
    };

    zoomIn?.addEventListener('click', () => changeScale(0.2));
    zoomOut?.addEventListener('click', () => changeScale(-0.2));
    reset?.addEventListener('click', resetMap);

    map.addEventListener('wheel', (event) => {
        event.preventDefault();
        changeScale(event.deltaY < 0 ? 0.12 : -0.12);
    }, { passive: false });

    map.addEventListener('dblclick', (event) => {
        if (event.target.closest('button')) return;
        event.preventDefault();
        changeScale(0.2);
    });

    map.addEventListener('pointerdown', (event) => {
        if (event.target.closest('button')) return;
        activePointer = event.pointerId;
        pointerOrigin = { x: event.clientX - translateX, y: event.clientY - translateY };
        map.classList.add('is-dragging');
        map.setPointerCapture(event.pointerId);
    });

    map.addEventListener('pointermove', (event) => {
        if (event.pointerId !== activePointer || !pointerOrigin) return;
        translateX = event.clientX - pointerOrigin.x;
        translateY = event.clientY - pointerOrigin.y;
        render();
    });

    const finishDrag = (event) => {
        if (event.pointerId !== activePointer) return;
        activePointer = null;
        pointerOrigin = null;
        map.classList.remove('is-dragging');
    };

    map.addEventListener('pointerup', finishDrag);
    map.addEventListener('pointercancel', finishDrag);

    map.addEventListener('keydown', (event) => {
        const movement = 32;
        const actions = {
            ArrowLeft: () => { translateX += movement; },
            ArrowRight: () => { translateX -= movement; },
            ArrowUp: () => { translateY += movement; },
            ArrowDown: () => { translateY -= movement; },
            '+': () => changeScale(0.2),
            '=': () => changeScale(0.2),
            '-': () => changeScale(-0.2),
            '0': resetMap,
        };

        if (!actions[event.key]) return;
        event.preventDefault();
        actions[event.key]();

        if (event.key.startsWith('Arrow')) render();
    });

    render();
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

document.querySelectorAll('[data-about-map]').forEach((element) => {
    const observer = new IntersectionObserver(async (entries) => {
        if (!entries.some((entry) => entry.isIntersecting)) return;
        observer.disconnect();
        try {
            const { mountAboutMap } = await import('./about-map');
            mountAboutMap(element);
        } catch {
            const status = element.parentElement.querySelector('[data-about-map-status]');
            if (status) status.hidden = false;
        }
    }, { rootMargin: '200px' });
    observer.observe(element);
});
