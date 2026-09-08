<button type="button" id="pwaInstallButton" class="pwa-install-button" hidden aria-label="Install PLP Portal on this device">
    <span aria-hidden="true">&#8595;</span>
    <span>Install PLP Portal</span>
</button>
<script>
    window.PLP_PWA_CONFIG = {
        serviceWorkerUrl: @json(asset('service-worker.js'))
    };
</script>
<script src="{{ asset('js/pwa.js') }}" defer></script>
