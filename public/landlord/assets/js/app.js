"use strict";

/* VIVA: Handles the responsive landlord navigation drawer. CSS controls visibility; JS manages opening, focus, Escape and backdrop closing for accessibility. */

(() => {
    const menu = document.getElementById('menu-toggle');
    const closeButton = document.getElementById('menu-close');
    const overlay = document.getElementById('sidebar-overlay');
    const sidebar = document.getElementById('sidebar');
    let previousFocus = null;

    /** Close the mobile drawer, restore scrolling and return keyboard focus. */
    function closeMenu() {
        if (!menu || !sidebar || !overlay) return;
        document.body.classList.remove('nav-open');
        document.body.style.overflow = '';
        overlay.hidden = true;
        menu.setAttribute('aria-expanded', 'false');
        menu.setAttribute('aria-label', 'Open navigation');
        sidebar.removeAttribute('aria-modal');
        sidebar.removeAttribute('role');
        if (previousFocus && window.matchMedia('(max-width: 949px)').matches) {
            previousFocus.focus();
        }
    }

    /** Open the mobile drawer and keep interaction focused inside it. */
    function openMenu() {
        if (!menu || !sidebar || !overlay) return;
        previousFocus = document.activeElement;
        document.body.classList.add('nav-open');
        document.body.style.overflow = 'hidden';
        overlay.hidden = false;
        menu.setAttribute('aria-expanded', 'true');
        menu.setAttribute('aria-label', 'Close navigation');
        sidebar.setAttribute('role', 'dialog');
        sidebar.setAttribute('aria-modal', 'true');
        closeButton?.focus();
    }

    if (menu && sidebar && overlay) {
        menu.addEventListener('click', () => {
            document.body.classList.contains('nav-open') ? closeMenu() : openMenu();
        });
        closeButton?.addEventListener('click', closeMenu);
        overlay.addEventListener('click', closeMenu);
        sidebar.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 950) closeMenu();
        });
        document.addEventListener('focusin', event => {
            if (document.body.classList.contains('nav-open') && !sidebar.contains(event.target)) {
                closeButton?.focus();
            }
        });
        document.addEventListener('keydown', event => {
            if (!document.body.classList.contains('nav-open')) return;
            if (event.key === 'Escape') closeMenu();
            if (event.key === 'Tab') {
                // Keep keyboard navigation within the mobile drawer.
                const focusable = Array.from(sidebar.querySelectorAll('button, a[href]'));
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });
    }

    // One help button opens only the active section's topic in a native dialog.
    // Native <dialog> handles Escape, focus containment and screen-reader modality.
    const helpDialog = document.getElementById('landlord-help-dialog');
    const helpOpen = document.getElementById('context-help-open');
    const helpClose = document.getElementById('context-help-close');
    const helpDone = document.getElementById('context-help-done');
    if (helpDialog && helpOpen) {
        helpOpen.addEventListener('click', () => {
            if (typeof helpDialog.showModal === 'function') helpDialog.showModal();
            else helpDialog.setAttribute('open', '');
        });
        const closeHelp = () => {
            if (typeof helpDialog.close === 'function') helpDialog.close();
            else helpDialog.removeAttribute('open');
            helpOpen.focus();
        };
        helpClose?.addEventListener('click', closeHelp);
        helpDone?.addEventListener('click', closeHelp);
        helpDialog.addEventListener('click', event => {
            if (event.target === helpDialog) closeHelp();
        });
    }

    // Confirmation prompts apply only to forms explicitly marked data-confirm.
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', event => {
            if (!window.confirm(form.dataset.confirm || 'Continue?')) event.preventDefault();
        });
    });
})();
