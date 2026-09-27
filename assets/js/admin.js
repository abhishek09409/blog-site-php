(function () {
    'use strict';
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const flash = document.querySelector('[data-flash]');
    if (flash?.dataset.message) {
        const toast = document.createElement('div');
        toast.className = 'toast' + (flash.dataset.type === 'error' ? ' is-error' : '');
        toast.textContent = flash.dataset.message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3800);
    }

    const sidebar = document.getElementById('sidebar');
    document.querySelector('[data-admin-nav]')?.addEventListener('click', () => {
        document.body.classList.toggle('sidebar-open');
    });
    sidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
        document.body.classList.remove('sidebar-open');
    }));

    const modal = document.getElementById('confirm-modal');
    const text = modal?.querySelector('[data-confirm-text]');
    let pendingForm = null;
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === '1') return;
            event.preventDefault();
            pendingForm = form;
            if (text) text.textContent = form.dataset.confirm || 'Continue?';
            if (modal) modal.hidden = false;
        });
    });
    modal?.querySelector('[data-confirm-cancel]')?.addEventListener('click', () => { modal.hidden = true; pendingForm = null; });
    modal?.querySelector('[data-confirm-ok]')?.addEventListener('click', () => {
        if (!pendingForm) return;
        pendingForm.dataset.confirmed = '1';
        modal.hidden = true;
        pendingForm.submit();
    });

    document.querySelectorAll('[data-file-preview]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files && input.files[0];
            const preview = document.querySelector('[data-featured-preview]');
            if (!file || !preview) return;
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
        });
    });

    window.MeridianCsrf = token;
})();
