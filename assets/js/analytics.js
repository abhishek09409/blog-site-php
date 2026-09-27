(function () {
    'use strict';
    var meta = document.querySelector('meta[name="ga-id"]');
    if (!meta || !meta.content) return;
    var id = meta.content;
    var script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id);
    document.head.appendChild(script);
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', id);
})();
