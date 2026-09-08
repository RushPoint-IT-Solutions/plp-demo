(function () {
    'use strict';

    var config = window.PLP_PWA_CONFIG || {};

    if ('serviceWorker' in navigator && config.serviceWorkerUrl) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register(config.serviceWorkerUrl).catch(function (error) {
                if (window.console && console.warn) {
                    console.warn('PLP PWA service worker registration failed.', error);
                }
            });
        });
    }
})();
