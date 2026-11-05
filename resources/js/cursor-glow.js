const layer = document.querySelector('.cursor-glow');
const fine = window.matchMedia('(hover: hover) and (pointer: fine)');
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

// A trail is only worth drawing where there is a pointer to trail and a
// person who did not ask for less motion.
if (layer && fine.matches && !reduce.matches) {
    let x = 0;
    let y = 0;
    let box = null;
    let rect = null;
    let frame = 0;

    const paint = () => {
        frame = 0;

        // Read before writing: the ring is placed against a box whose position
        // the page may have scrolled away from, and a layout read after the
        // writes would force the browser to reflow in the same frame.
        if (box && !rect) {
            rect = box.getBoundingClientRect();
        }

        layer.classList.add('cursor-glow--on');
        layer.style.setProperty('--mx', `${x}px`);
        layer.style.setProperty('--my', `${y}px`);

        if (box) {
            box.style.setProperty('--gx', `${(x - rect.left).toFixed(1)}px`);
            box.style.setProperty('--gy', `${(y - rect.top).toFixed(1)}px`);
        }
    };

    const queue = (event) => {
        x = event.clientX;
        y = event.clientY;

        if (!frame) {
            frame = requestAnimationFrame(paint);
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
    document.addEventListener('pointerleave', () => layer.classList.remove('cursor-glow--on'));
}
