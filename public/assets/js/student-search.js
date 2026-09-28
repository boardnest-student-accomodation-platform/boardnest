(() => {
    const filterPanel = document.querySelector('[data-filter-panel]');
    const filterBackdrop = document.querySelector('.student-filter-backdrop');
    const mobileMenu = document.querySelector('[data-mobile-menu]');

    const setFiltersOpen = (open) => {
        if (!filterPanel || !filterBackdrop) return;
        filterPanel.classList.toggle('is-open', open);
        filterBackdrop.hidden = !open;
        document.body.classList.toggle('student-modal-open', open);
    };

    document.querySelector('[data-filter-open]')?.addEventListener('click', () => setFiltersOpen(true));
    document.querySelectorAll('[data-filter-close]').forEach((button) => {
        button.addEventListener('click', () => setFiltersOpen(false));
    });

    document.querySelector('[data-mobile-menu-button]')?.addEventListener('click', () => {
        if (!mobileMenu) return;
        mobileMenu.hidden = !mobileMenu.hidden;
    });

    document.querySelector('[data-auto-submit]')?.addEventListener('change', (event) => {
        event.currentTarget.form.submit();
    });

    document.querySelector('[data-university-search]')?.addEventListener('input', (event) => {
        const query = event.currentTarget.value.trim().toLowerCase();
        document.querySelectorAll('[data-university-option]').forEach((option) => {
            option.hidden = query !== '' && !option.textContent.toLowerCase().includes(query);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setFiltersOpen(false);
            if (mobileMenu) mobileMenu.hidden = true;
        }
    });
})();
