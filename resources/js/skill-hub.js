const DRAG_THRESHOLD = 10;

const readSpin = (node) => {
    const dir = (node.closest('[dir]')?.getAttribute('dir') || 'ltr').toLowerCase();
    return dir === 'rtl' ? -1 : 1;
};

const initHub = (hub) => {
    const ring = hub.querySelector('[data-skill-ring]');
    const track = hub.querySelector('[data-skill-track]');
    const cards = [...hub.querySelectorAll('[data-skill-card]')];
    const panels = cards.map((card) => document.getElementById(card.dataset.skillPanel));
    const previous = hub.querySelector('[data-skill-prev]');
    const next = hub.querySelector('[data-skill-next]');

    if (!ring || !track || cards.length < 2 || panels.some((panel) => !panel)) {
        return;
    }

    const count = cards.length;
    const stepDegrees = 360 / count;

    let active = Number.parseInt(hub.dataset.skillActive ?? '0', 10);
    if (!Number.isInteger(active) || active < 0 || active >= count) {
        active = 0;
    }

    let spin = readSpin(hub);
    let dragPointerId = null;
    let dragStartX = 0;
    let dragTravel = 0;
    let dragDegrees = 0;
    let dragPixelsPerStep = 48;
    let suppressClick = false;
    let pendingFrame = 0;

    // The track owns the tabs directly, so it carries the tablist role.
    track.setAttribute('role', 'tablist');
    if (hub.dataset.skillHubLabel) {
        track.setAttribute('aria-label', hub.dataset.skillHubLabel);
    }

    // The panels hold no focusable content, so they are tabbable themselves.
    panels.forEach((panel) => {
        panel.tabIndex = 0;
    });

    const setActive = (index, { focus = false } = {}) => {
        active = ((index % count) + count) % count;
        ring.style.setProperty('--hub-active', String(active));

        cards.forEach((card, i) => {
            card.setAttribute('role', 'tab');
            card.setAttribute('aria-selected', String(i === active));
            card.tabIndex = i === active ? 0 : -1;
        });

        panels.forEach((panel, i) => {
            panel.setAttribute('role', 'tabpanel');
            panel.hidden = i !== active;
        });

        if (focus) {
            cards[active].focus();
        }
    };

    setActive(active);

    const degreesFromPixels = (pixels) => (-pixels / dragPixelsPerStep) * stepDegrees * spin;

    const clearDrag = () => {
        if (pendingFrame) {
            cancelAnimationFrame(pendingFrame);
            pendingFrame = 0;
        }
        dragDegrees = 0;
        ring.style.removeProperty('--hub-drag');
        ring.classList.remove('is-dragging');
    };

    track.addEventListener('pointerdown', (event) => {
        const card = event.target.closest('[data-skill-card]');
        if (!card || (event.pointerType === 'mouse' && event.button !== 0)) {
            return;
        }
        spin = readSpin(hub);
        // Measured once per gesture so pointermove never reads layout.
        dragPixelsPerStep = Math.max(ring.getBoundingClientRect().width * 0.3, 48);
        suppressClick = false;
        dragPointerId = event.pointerId;
        dragStartX = event.clientX;
        dragTravel = 0;
        card.setPointerCapture(event.pointerId);
        ring.classList.add('is-dragging');
    });

    track.addEventListener('pointermove', (event) => {
        if (dragPointerId !== event.pointerId) {
            return;
        }
        dragTravel = event.clientX - dragStartX;
        if (Math.abs(dragTravel) < DRAG_THRESHOLD) {
            return;
        }
        dragDegrees = degreesFromPixels(dragTravel);
        if (pendingFrame) {
            return;
        }
        pendingFrame = requestAnimationFrame(() => {
            pendingFrame = 0;
            ring.style.setProperty('--hub-drag', `${dragDegrees.toFixed(2)}deg`);
        });
    });

    const endDrag = (event) => {
        if (dragPointerId !== event.pointerId) {
            return;
        }
        const travelled = Math.abs(dragTravel) >= DRAG_THRESHOLD;
        const steps = travelled ? Math.round(dragDegrees / stepDegrees) : 0;
        dragPointerId = null;

        clearDrag();

        // Only a drag that actually moved the ring replaces the click that follows it.
        if (steps !== 0) {
            suppressClick = true;
            setActive(active + steps);
        }
    };

    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointercancel', endDrag);

    track.addEventListener('click', (event) => {
        const card = event.target.closest('[data-skill-card]');
        if (!card) {
            return;
        }
        // detail === 0 marks a keyboard-activated click, which is never a drag leftover.
        if (suppressClick && event.detail !== 0) {
            suppressClick = false;
            return;
        }
        setActive(cards.indexOf(card), { focus: true });
    });

    ring.addEventListener('keydown', (event) => {
        const forwards = spin === -1 ? 'ArrowLeft' : 'ArrowRight';
        const backwards = spin === -1 ? 'ArrowRight' : 'ArrowLeft';

        if (event.key === forwards) {
            event.preventDefault();
            setActive(active + 1, { focus: true });
        } else if (event.key === backwards) {
            event.preventDefault();
            setActive(active - 1, { focus: true });
        }
    });

    previous?.addEventListener('click', () => setActive(active - 1, { focus: true }));
    next?.addEventListener('click', () => setActive(active + 1, { focus: true }));
};

document.querySelectorAll('[data-skill-hub]').forEach(initHub);
