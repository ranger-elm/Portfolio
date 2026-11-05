const layer = document.querySelector('.cursor-glow');
const fine = window.matchMedia('(hover: hover) and (pointer: fine)');
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

// A trail is only worth drawing where there is a pointer to trail and a
// person who did not ask for less motion.
if (layer && fine.matches && !reduce.matches) {
    // The pointer holds the target; the two drawn positions chase it with
    // different time constants. That gap is the effect — the core is the hand,
    // the field is the room it moves through.
    let tx = 0;
    let ty = 0;
    let ax = 0;
    let ay = 0;
    let cx = 0;
    let cy = 0;
    let box = null;
    let rect = null;
    let frame = 0;
    let since = 0;
    let rest = 0;
    let lit = false;
    let seeded = false;

    const settle = () => {
        rest = 0;
        lit = false;
        layer.classList.remove('cursor-glow--on');
    };

    const step = (now) => {
        frame = 0;

        // Read before writing: the ring is placed against a box whose position
        // the page may have scrolled away from, and a layout read after the
        // writes would force the browser to reflow in the same frame.
        if (box && !rect) {
            rect = box.getBoundingClientRect();
        }

        // Clamped, because the first frame after a pause carries the whole
        // pause as elapsed time and would snap both layers to the pointer.
        const dt = Math.min((now - since) / 1000, 0.05);
        since = now;

        // 1 - e^(-dt / tau) rather than a fixed factor, so 0.16s means 0.16s on
        // a 60, 120 or 144 Hz display instead of twice the lag on the slow one.
        const ka = 1 - Math.exp(-dt / 0.16);
        const kc = 1 - Math.exp(-dt / 0.045);

        ax += (tx - ax) * ka;
        ay += (ty - ay) * ka;
        cx += (tx - cx) * kc;
        cy += (ty - cy) * kc;

        layer.style.setProperty('--ax', `${ax.toFixed(1)}px`);
        layer.style.setProperty('--ay', `${ay.toFixed(1)}px`);
        layer.style.setProperty('--cx', `${cx.toFixed(1)}px`);
        layer.style.setProperty('--cy', `${cy.toFixed(1)}px`);

        if (box) {
            // The ring and the light inside the glass answer the eased core, not
            // the raw pointer, so the box agrees with the field it sits in.
            box.style.setProperty('--gx', `${(cx - rect.left).toFixed(1)}px`);
            box.style.setProperty('--gy', `${(cy - rect.top).toFixed(1)}px`);
        }

        if (Math.abs(tx - ax) + Math.abs(ty - ay) + Math.abs(tx - cx) + Math.abs(ty - cy) > 0.4) {
            frame = requestAnimationFrame(step);
        } else {
            // Both layers have arrived, so nothing is left to draw until the
            // next move. The field dims once the hand has stayed off it.
            rest = setTimeout(settle, 2600);
        }
    };

    const queue = (event) => {
        tx = event.clientX;
        ty = event.clientY;

        if (!seeded) {
            // The first position is also the starting position, or the field
            // would fly in from the top-left corner of the document.
            seeded = true;
            ax = cx = tx;
            ay = cy = ty;
            since = event.timeStamp;
        }

        if (rest) {
            clearTimeout(rest);
            rest = 0;
        }

        if (!lit) {
            lit = true;
            layer.classList.add('cursor-glow--on');
        }

        if (!frame) {
            frame = requestAnimationFrame(step);
        }
    };

    const drop = () => {
        rect = null;
    };

    document.addEventListener('pointermove', queue, { passive: true });

    document.addEventListener('pointerover', (event) => {
        const target = event.target.closest?.('.glow');

        if (target !== box) {
            // Leaving a box clears its coordinates, so a pane lit only by
            // keyboard focus reflects the middle of itself, not wherever the
            // pointer happened to be a minute ago.
            if (box) {
                box.style.removeProperty('--gx');
                box.style.removeProperty('--gy');
            }

            box = target;
            drop();
        }
    });

    window.addEventListener('scroll', drop, { passive: true, capture: true });
    window.addEventListener('resize', drop);

    document.addEventListener('pointerleave', () => {
        if (rest) {
            clearTimeout(rest);
            rest = 0;
        }

        settle();
    });
}
