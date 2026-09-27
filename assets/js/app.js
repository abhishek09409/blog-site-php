(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function toast(message, type) {
        if (!message) return;
        let stack = document.querySelector('.toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'toast-stack';
            document.body.appendChild(stack);
        }
        const item = document.createElement('div');
        item.className = 'toast' + (type === 'error' ? ' is-error' : '');
        item.textContent = message;
        stack.appendChild(item);
        setTimeout(() => item.remove(), 4200);
    }

    const flash = document.querySelector('[data-flash]');
    if (flash) toast(flash.dataset.message, flash.dataset.type);

    const drawer = document.getElementById('mobile-nav');
    const toggle = document.querySelector('[data-nav-toggle]');
    function setDrawer(open) {
        if (!drawer || !toggle) return;
        drawer.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.style.overflow = open ? 'hidden' : '';
    }
    toggle?.addEventListener('click', () => setDrawer(drawer.hidden));
    document.querySelector('[data-nav-close]')?.addEventListener('click', () => setDrawer(false));

    const modal = document.getElementById('search-modal');
    const searchInput = modal?.querySelector('[data-live-search]');
    const suggest = modal?.querySelector('[data-search-suggest]');
    function setSearch(open) {
        if (!modal) return;
        modal.hidden = !open;
        if (open) searchInput?.focus();
    }
    document.querySelector('[data-search-open]')?.addEventListener('click', () => setSearch(true));
    modal?.querySelector('[data-search-close]')?.addEventListener('click', () => setSearch(false));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setSearch(false);
            setDrawer(false);
        }
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            setSearch(true);
        }
    });

    let timer;
    searchInput?.addEventListener('input', () => {
        clearTimeout(timer);
        const q = searchInput.value.trim();
        timer = setTimeout(async () => {
            if (!suggest || q.length < 2) {
                if (suggest) suggest.innerHTML = '';
                return;
            }
            const response = await fetch('/api/search.php?q=' + encodeURIComponent(q));
            const data = await response.json();
            suggest.innerHTML = '';
            (data.results || []).forEach((item) => {
                const link = document.createElement('a');
                link.href = item.url;
                const title = document.createElement('span');
                title.textContent = item.title;
                const meta = document.createElement('small');
                meta.textContent = item.category;
                link.append(title, meta);
                suggest.appendChild(link);
            });
        }, 180);
    });

    document.querySelectorAll('[data-newsletter]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const note = form.querySelector('[data-newsletter-note]');
            const body = new FormData(form);
            try {
                const response = await fetch('/api/newsletter.php', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body
                });
                const data = await response.json();
                if (note) note.textContent = data.message || '';
                toast(data.message, data.ok ? 'success' : 'error');
                if (data.ok) form.reset();
            } catch (error) {
                form.submit();
            }
        });
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.parentElement.querySelector('[data-password]');
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.textContent = show ? 'Hide' : 'Show';
        });
    });

    document.querySelectorAll('[data-reply]').forEach((button) => {
        button.addEventListener('click', () => {
            const parent = document.querySelector('[data-parent]');
            const title = document.querySelector('[data-comment-title]');
            if (parent) parent.value = button.dataset.reply || '';
            if (title) title.textContent = 'Reply to ' + (button.dataset.replyName || 'comment');
            document.getElementById('comment-form')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            document.querySelector('#comment-form textarea')?.focus();
        });
    });

    document.querySelectorAll('[data-copy]').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copy || '');
                toast('Link copied.');
            } catch (error) {
                toast('Could not copy the link.', 'error');
            }
        });
    });

    const article = document.querySelector('[data-article]');
    const bar = document.querySelector('[data-progress]');
    if (article && bar) {
        const onScroll = () => {
            const rect = article.getBoundingClientRect();
            const total = article.offsetHeight - window.innerHeight;
            const seen = Math.min(Math.max(-rect.top, 0), Math.max(total, 1));
            bar.style.width = (seen / Math.max(total, 1)) * 100 + '%';
        };
        document.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
        const id = article.dataset.postId;
        if (id) {
            fetch('/api/views.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ post_id: Number(id) })
            }).then((response) => response.json()).then((data) => {
                const node = document.querySelector('[data-view-count]');
                if (node && data.views) node.textContent = Number(data.views).toLocaleString() + ' views';
            }).catch(() => {});
        }
    }

    document.querySelector('[data-auto-submit]')?.addEventListener('change', (event) => {
        event.target.form?.submit();
    });
})();
