<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<meta name="theme-color" content="#006837">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="PLP Portal">
<meta name="application-name" content="PLP Portal">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('pwa/apple-touch-icon.png') }}">
<style>
    html { -webkit-text-size-adjust: 100%; }
    a, button, input, select, textarea { touch-action: manipulation; }
    .pwa-install-button {
        align-items: center;
        background: #006837;
        border: 1px solid rgba(255, 255, 255, .5);
        border-radius: 999px;
        bottom: max(18px, env(safe-area-inset-bottom));
        box-shadow: 0 8px 24px rgba(0, 68, 36, .28);
        color: #fff;
        cursor: pointer;
        display: inline-flex;
        font-family: inherit;
        font-size: .82rem;
        font-weight: 700;
        gap: 8px;
        min-height: 44px;
        padding: 10px 16px;
        position: fixed;
        right: max(18px, env(safe-area-inset-right));
        z-index: 12000;
    }
    .pwa-install-button[hidden] { display: none !important; }
    .pwa-install-button:focus-visible { outline: 3px solid rgba(0, 104, 55, .3); outline-offset: 3px; }
</style>
