(function () {
    'use strict';

    var config = window.PLP_PWA_CONFIG || {};
    var deferredInstallPrompt = null;
    var installButton = document.getElementById('pwaInstallButton');

    function hideInstallButton() {
        if (installButton) {
            installButton.hidden = true;
        }
    }

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredInstallPrompt = event;

        if (installButton) {
            installButton.hidden = false;
        }
    });

    if (installButton) {
        installButton.addEventListener('click', function () {
            if (!deferredInstallPrompt) {
                return;
            }

            deferredInstallPrompt.prompt();
            deferredInstallPrompt.userChoice.finally(function () {
                deferredInstallPrompt = null;
                hideInstallButton();
            });
        });
    }

    window.addEventListener('appinstalled', function () {
        deferredInstallPrompt = null;
        hideInstallButton();
    });

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
