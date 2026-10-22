<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Page unavailable | PLP Portal</title>
    <link rel="icon" href="{{ asset('img/logobg.png') }}" type="image/png">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #f5f7f6; color: #243a30; font-family: 'Segoe UI', Arial, sans-serif; }
        .brand { display: flex; align-items: center; gap: 14px; padding: 24px clamp(24px, 6vw, 88px); border-top: 6px solid #006837; background: #fff; }
        .brand img { width: 52px; height: 52px; object-fit: contain; }
        .brand strong { display: block; font-size: 19px; }
        .brand span { display: block; margin-top: 4px; color: #52665b; font-size: 13px; }
        main { max-width: 1040px; margin: auto; padding: clamp(48px, 10vh, 112px) 24px 48px; }
        .panel { display: grid; grid-template-columns: 12px 1fr; background: #fff; border: 1px solid #dce5df; border-radius: 8px; overflow: hidden; }
        .stripe { background: #006837; }
        .content { padding: clamp(28px, 6vw, 72px); }
        .status { display: inline-block; margin: 0 0 24px; padding: 7px 12px; background: #fff4bd; color: #594600; font-size: 14px; font-weight: 600; border-radius: 4px; }
        h1 { max-width: 620px; margin: 0 0 20px; color: #005028; font-size: clamp(32px, 5vw, 48px); line-height: 1.15; letter-spacing: -1px; }
        .message { max-width: 560px; margin: 0; color: #52665b; font-size: 18px; line-height: 1.7; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; margin: 32px 0; }
        .button { display: inline-flex; justify-content: center; align-items: center; min-height: 48px; padding: 12px 22px; border: 1px solid #006837; border-radius: 5px; color: #006837; text-decoration: none; font-weight: 600; }
        .primary { background: #006837; color: #fff; }
        .button:hover { background: #edf5ef; }
        .primary:hover { background: #005028; }
        a:focus-visible { outline: 3px solid #a16c00; outline-offset: 4px; }
        .help { max-width: 580px; padding-top: 24px; border-top: 1px solid #dce5df; font-size: 14px; line-height: 1.7; color: #52665b; }
        footer { margin-top: 24px; color: #52665b; font-size: 13px; text-align: center; }
        @media (max-width: 480px) { .brand { padding: 20px; } .brand span { max-width: 220px; } .panel { grid-template-columns: 6px 1fr; } .actions { flex-direction: column; } }
    </style>
</head>
<body>
    <header class="brand">
        <img src="{{ asset('img/logobg.png') }}" alt="PLP seal" width="52" height="52">
        <div><strong>PLP Portal</strong><span>Pamantasan ng Lungsod ng Pasig</span></div>
    </header>
    <main>
        <section class="panel" aria-labelledby="error-title">
            <div class="stripe" aria-hidden="true"></div>
            <div class="content">
                <p class="status">500 · Server error</p>
                <h1 id="error-title">We couldn’t load this page.</h1>
                <p class="message">The portal encountered a problem while processing your request. Please wait a moment, then open the page again.</p>
                <div class="actions">
                    <a class="button primary" href="{{ url('/') }}">Return to portal</a>
                </div>
                <p class="help">If you were submitting a form, check whether your changes were saved before submitting again. If the problem continues, contact your school’s portal administrator and mention the page you were opening and the time the error occurred.</p>
            </div>
        </section>
        <footer>PLP · University portal</footer>
    </main>
</body>
</html>
