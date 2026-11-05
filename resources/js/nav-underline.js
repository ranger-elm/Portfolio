const bar = document.querySelector('[data-nav-bar]');
const rule = document.querySelector('[data-nav-rule]');

if (bar && rule) {
    document.documentElement.classList.add('js');

    const links = [...bar.querySelectorAll('[data-nav-link]')];
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
    const current = () => links.find((link) => link.getAttribute('aria-current') === 'page') ?? links[0];

    // The rule sits under the letters, so it measures the text box rather than
    // the link box, which carries its own inline padding.
    const textRect = (link) => {
        const range = document.createRange();
        range.selectNodeContents(link);

        return range.getBoundingClientRect();
    };

    const move = (link, { instant = false } = {}) => {
        const barBox = bar.getBoundingClientRect();
        const box = textRect(link);
        const rtl = getComputedStyle(bar).direction === 'rtl';

        if (instant) {
            rule.style.transition = 'none';
        }

        rule.style.setProperty('--rule-x', `${(rtl ? barBox.right - box.right : box.left - barBox.left).toFixed(2)}px`);
        rule.style.setProperty('--rule-w', `${box.width.toFixed(2)}px`);

        if (instant) {
            rule.getBoundingClientRect();
            rule.style.transition = '';
        }
    };

    const settle = () => move(current(), { instant: true });

    settle();
    window.addEventListener('resize', settle);

    // Estedad and Space Grotesk settle at different widths, and the rule is
    // measured in text pixels, so it has to be placed again once they land.
    document.fonts?.ready.then(settle);

    let timer = 0;

    bar.addEventListener('click', (event) => {
        const link = event.target.closest('[data-nav-link]');

        if (!link || event.defaultPrevented || link === current() || reduce.matches) {
            return;
        }

        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target) {
            return;
        }

        event.preventDefault();
        move(link);

        // The rule has to arrive before the page it marks does.
        clearTimeout(timer);
        timer = setTimeout(() => window.location.assign(link.href), 260);
    });
}
