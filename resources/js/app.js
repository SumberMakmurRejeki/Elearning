const toggleClass = (element, className) => {
    if (!element) return;

    element.classList.toggle(className);
};

document.addEventListener('click', (event) => {
    const sidebarToggle = event.target.closest('[data-toggle-sidebar]');

    if (sidebarToggle) {
        const target = document.querySelector(sidebarToggle.dataset.toggleSidebar);
        toggleClass(target, '-translate-x-full');
        return;
    }

    const modalOpen = event.target.closest('[data-modal-open]');

    if (modalOpen) {
        const dialog = document.querySelector(modalOpen.dataset.modalOpen);

        if (dialog?.showModal) {
            dialog.showModal();
        }

        return;
    }

    const modalClose = event.target.closest('[data-modal-close]');

    if (modalClose) {
        modalClose.closest('dialog')?.close();
    }
});

document.addEventListener('click', (event) => {
    const dialog = event.target;

    if (dialog instanceof HTMLDialogElement) {
        const rect = dialog.getBoundingClientRect();
        const inside = rect.top <= event.clientY
            && event.clientY <= rect.top + rect.height
            && rect.left <= event.clientX
            && event.clientX <= rect.left + rect.width;

        if (!inside) {
            dialog.close();
        }
    }
});
