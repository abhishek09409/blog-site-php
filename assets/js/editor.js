(function () {
    'use strict';
    const canvas = document.querySelector('[data-editor]');
    const field = document.querySelector('[data-content]');
    const form = document.getElementById('post-form');
    if (!canvas || !field || !form) return;

    function sync() {
        field.value = canvas.innerHTML.trim();
    }

    function wrap(tag) {
        document.execCommand('formatBlock', false, tag);
        sync();
    }

    document.querySelector('[data-editor-toolbar]')?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-cmd]');
        if (!button) return;
        event.preventDefault();
        canvas.focus();
        const cmd = button.dataset.cmd;
        if (cmd === 'bold') document.execCommand('bold');
        if (cmd === 'italic') document.execCommand('italic');
        if (cmd === 'h2') wrap('h2');
        if (cmd === 'h3') wrap('h3');
        if (cmd === 'ul') document.execCommand('insertUnorderedList');
        if (cmd === 'ol') document.execCommand('insertOrderedList');
        if (cmd === 'quote') wrap('blockquote');
        if (cmd === 'link') {
            const url = prompt('Link URL');
            if (url && /^(https?:\/\/|\/|mailto:)/i.test(url)) document.execCommand('createLink', false, url);
        }
        if (cmd === 'code') {
            const text = window.getSelection()?.toString() || 'code';
            document.execCommand('insertHTML', false, '<pre><code class="language-text">' + text.replace(/[&<>]/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c])) + '</code></pre>');
        }
        if (cmd === 'image') {
            const url = prompt('Image URL (uploads or https)');
            if (url && /^(\/uploads\/|\/assets\/|https:\/\/)/.test(url)) {
                const alt = prompt('Alt text') || '';
                document.execCommand('insertHTML', false, '<figure><img src="' + url.replace(/"/g, '') + '" alt="' + alt.replace(/"/g, '') + '"></figure>');
            }
        }
        if (cmd === 'table') {
            document.execCommand('insertHTML', false, '<table><thead><tr><th>Column</th><th>Column</th></tr></thead><tbody><tr><td>Value</td><td>Value</td></tr></tbody></table>');
        }
        if (cmd === 'youtube') {
            const url = prompt('YouTube URL');
            const match = (url || '').match(/(?:v=|youtu\.be\/|embed\/)([A-Za-z0-9_-]{6,20})/);
            if (match) {
                document.execCommand('insertHTML', false, '<div class="embed"><iframe src="https://www.youtube-nocookie.com/embed/' + match[1] + '" title="YouTube video" width="560" height="315" allowfullscreen></iframe></div>');
            }
        }
        sync();
    });

    canvas.addEventListener('input', sync);
    form.addEventListener('submit', sync);

    const title = document.querySelector('[data-title]');
    const slug = document.querySelector('[data-slug]');
    title?.addEventListener('blur', () => {
        if (slug && slug.value.trim() === '') {
            slug.value = title.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 180);
        }
    });

    const mediaModal = document.getElementById('media-modal');
    document.querySelector('[data-open-media]')?.addEventListener('click', () => { if (mediaModal) mediaModal.hidden = false; });
    document.querySelector('[data-close-media]')?.addEventListener('click', () => { if (mediaModal) mediaModal.hidden = true; });
    document.querySelectorAll('.media-pick').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.querySelector('[data-featured]');
            const preview = document.querySelector('[data-featured-preview]');
            const alt = form.querySelector('[name="featured_image_alt"]');
            if (input) input.value = button.dataset.src || '';
            if (preview) {
                preview.src = button.dataset.src || '';
                preview.hidden = false;
            }
            if (alt && !alt.value) alt.value = button.dataset.alt || '';
            if (mediaModal) mediaModal.hidden = true;
        });
    });
})();
