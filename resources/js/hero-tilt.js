const MAX_LEAN = 7;
const MAX_TILT = 5;

const pointer = window.matchMedia('(hover: hover) and (pointer: fine)');
const calm = window.matchMedia('(prefers-reduced-motion: reduce)');

const clampHalf = (value) => Math.min(Math.max(value, -0.5), 0.5);

document.querySelectorAll('[data-hero-tilt]').forEach((stage) => {
    let frame = 0;
    let clientX = 0;
    let clientY = 0;

    const release = () => {
        stage.style.removeProperty('--hero-tilt-x');
        stage.style.removeProperty('--hero-tilt-z');
    };

    stage.addEventListener('pointermove', (event) => {
        if (event.pointerType !== 'mouse' || !pointer.matches || calm.matches) {
            return;
        }

        clientX = event.clientX;
        clientY = event.clientY;

        if (frame) {
            return;
        }

        // One layout read per frame, inside the same frame as the write.
        frame = requestAnimationFrame(() => {
            frame = 0;

            const box = stage.getBoundingClientRect();
            const lean = clampHalf((clientX - box.left) / box.width - 0.5) * MAX_LEAN * 2;
            const tilt = clampHalf((clientY - box.top) / box.height - 0.5) * -MAX_TILT * 2;

            stage.style.setProperty('--hero-tilt-z', `${lean.toFixed(2)}deg`);
            stage.style.setProperty('--hero-tilt-x', `${tilt.toFixed(2)}deg`);
        });
    });

    stage.addEventListener('pointerleave', () => {
        if (frame) {
            cancelAnimationFrame(frame);
            frame = 0;
        }
        release();
    });
});
