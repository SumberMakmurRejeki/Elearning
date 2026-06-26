import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

const modalState = {
    selector: null,
    opener: null,
};

const modalRootFor = (selector) => {
    const id = selector?.startsWith('#') ? selector.slice(1) : selector;

    return id ? document.querySelector(`[data-modal-id="${id}"]`) : null;
};

const modalFocusable = (root) => {
    if (!root) {
        return [];
    }

    return Array.from(root.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'))
        .filter((element) => element.offsetParent !== null);
};

const openModal = (selector) => {
    modalState.selector = selector;
    modalState.opener = document.activeElement;
    document.body.classList.add('overflow-hidden');

    window.requestAnimationFrame(() => {
        modalFocusable(modalRootFor(selector))[0]?.focus();
    });
};

const closeModal = (selector) => {
    if (modalState.selector && (!selector || modalState.selector === selector)) {
        document.body.classList.remove('overflow-hidden');
        modalState.opener?.focus?.();
        modalState.selector = null;
        modalState.opener = null;
    }
};

document.addEventListener('keydown', (event) => {
    if (!modalState.selector) {
        return;
    }

    if (event.key === 'Escape') {
        closeModal(modalState.selector);
        window.dispatchEvent(new CustomEvent('modal:close', { detail: modalState.selector }));
        return;
    }

    if (event.key !== 'Tab') {
        return;
    }

    const focusables = modalFocusable(modalRootFor(modalState.selector));

    if (focusables.length < 2) {
        return;
    }

    const first = focusables[0];
    const last = focusables[focusables.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    }

    if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
});

const syncSidebar = (selector, open) => {
    const sidebar = document.querySelector(selector);
    const overlay = document.querySelector(`[data-sidebar-overlay="${selector}"]`);

    if (sidebar) {
        sidebar.classList.toggle('-translate-x-full', !open);
    }

    if (overlay) {
        overlay.classList.toggle('hidden', !open);
    }
};

document.addEventListener('click', (event) => {
    const sidebarToggle = event.target.closest('[data-toggle-sidebar]');

    if (sidebarToggle) {
        const selector = sidebarToggle.dataset.toggleSidebar;
        const target = document.querySelector(selector);

        if (target) {
            const open = target.classList.contains('-translate-x-full');
            syncSidebar(selector, open);
        }

        return;
    }

    const modalOpen = event.target.closest('[data-modal-open]');

    if (modalOpen) {
        openModal(modalOpen.dataset.modalOpen);
        window.dispatchEvent(new CustomEvent('modal:open', { detail: modalOpen.dataset.modalOpen }));

        return;
    }

    const modalClose = event.target.closest('[data-modal-close]');

    if (modalClose) {
        const selector = modalClose.dataset.modalClose || modalClose.closest('[data-modal-id]')?.dataset.modalId;
        closeModal(selector);
        window.dispatchEvent(new CustomEvent('modal:close', { detail: selector }));
    }
});
