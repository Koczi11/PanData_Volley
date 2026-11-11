const navbar = document.querySelector('[data-admin-navbar]');
const toggleButton = navbar?.querySelector('.admin-navbar__hamburger') ?? null;
const navMenu = navbar?.querySelector('#admin-navbar-menu') ?? null;
const navLinks = navbar?.querySelectorAll('.admin-navbar__link') ?? [];
const desktopMediaQuery = window.matchMedia('(min-width: 64.0625rem)');

function setMenuOpen(isOpen) {
    if (!navbar || !toggleButton) {
        return;
    }

    navbar.classList.toggle('is-open', isOpen);
    toggleButton.setAttribute('aria-expanded', String(isOpen));
}

if (navbar && toggleButton && navMenu) {
    toggleButton.addEventListener('click', () => {
        const isOpen = navbar.classList.contains('is-open');
        setMenuOpen(!isOpen);
    });

    navLinks.forEach((link) => {
        link.addEventListener('click', () => {
            setMenuOpen(false);
        });
    });

    document.addEventListener('click', (event) => {
        if (!navbar.classList.contains('is-open')) {
            return;
        }

        if (event.target instanceof Node && !navbar.contains(event.target)) {
            setMenuOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && navbar.classList.contains('is-open')) {
            setMenuOpen(false);
            toggleButton.focus();
        }
    });

    desktopMediaQuery.addEventListener('change', (event) => {
        if (event.matches) {
            setMenuOpen(false);
        }
    });
}
