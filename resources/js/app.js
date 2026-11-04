import './skill-hub';

const toggle = document.getElementById('nav-toggle');
const nav = document.getElementById('primary-navigation');

if (toggle && nav) {
    const setOpen = (open) => {
        nav.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
    };

    toggle.addEventListener('click', () => setOpen(nav.hidden));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !nav.hidden) {
            setOpen(false);
            toggle.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (nav.hidden || nav.contains(event.target) || toggle.contains(event.target)) {
            return;
        }
        setOpen(false);
    });

    nav.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));

    const desktop = window.matchMedia('(min-width: 768px)');
    const collapse = () => desktop.matches && setOpen(false);
    desktop.addEventListener('change', collapse);
    collapse();
}
