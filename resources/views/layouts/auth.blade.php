<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Techcare')</title>
    <script>
        document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
    </script>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon.png') }}">
    <link href="{{ asset('architectui/assets/styles/main.css') }}" rel="stylesheet">
    <link href="{{ asset('css/techcare-tokens.css') }}" rel="stylesheet">
    <link href="{{ asset('css/techcare-theme.css') }}" rel="stylesheet">
    <link href="{{ asset('css/techcare-laptop3d.css') }}" rel="stylesheet">
    <script defer src="{{ asset('js/techcare-laptop3d.js') }}"></script>
    <style>
        .auth-bg { background: radial-gradient(circle at 20% 10%, rgba(37,99,235,.38), transparent 50%), radial-gradient(circle at 85% 90%, rgba(37,99,235,.16), transparent 45%), #0a0a0a; }
        .auth-shell { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem; align-items: center; width: 100%;
                      max-width: 1120px; margin: 0 auto; padding: 1.5rem; min-height: 100vh; }
        @media (min-width: 992px) { .auth-shell { grid-template-columns: minmax(0, 1.15fr) minmax(0, 440px); gap: 3rem; } }

        .auth-hero { display: none; color: #fff; }
        @media (min-width: 992px) { .auth-hero { display: block; } .auth-mobile-logo { display: none !important; } }
        .auth-hero .auth-logo { height: 46px; width: auto; margin-bottom: 1.5rem; }
        .auth-hero h1 { font-size: 2.6rem; font-weight: 800; letter-spacing: -.03em; line-height: 1.1; margin: 0 0 .75rem; color: #fff; }
        .auth-hero h1 span { color: #60a5fa; }
        .auth-hero p.lead { color: rgba(255,255,255,.7); max-width: 30rem; margin-bottom: 0; }
        .auth-hero .l3d { margin: .5rem 0 .25rem; }
        .auth-hero .hint { font-size: .75rem; color: rgba(255,255,255,.45); }
        .auth-points { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: 1rem; padding: 0; list-style: none; }
        .auth-points li { padding: .35rem .8rem; border-radius: 99px; font-size: .8rem; color: rgba(255,255,255,.85);
                          background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14); }

        .auth-mobile-logo { text-align: center; margin-bottom: 1.25rem; }
        .auth-mobile-logo img { height: 42px; width: auto; }
        .auth-mobile-logo p { color: rgba(255,255,255,.8); margin: .5rem 0 0; }
        .auth-panel { width: 100%; max-width: 460px; margin: 0 auto; }
        [data-bs-theme="dark"] .auth-card { background-color: #212529; }
    </style>
</head>
<body>
<div class="app-container app-theme-white">
    <div class="auth-bg" style="min-height:100vh">
        <div class="auth-shell">

            {{-- Left: brand + 3D laptop (desktop only, keeps phones light) --}}
            <section class="auth-hero" aria-hidden="false">
                <img src="{{ asset('images/logo/icon-text-on-dark.png') }}" alt="Techcare" class="auth-logo">
                <h1>Laptop service,<br><span>made easy.</span></h1>
                <p class="lead">Register your laptop, book a repair, and follow every step of the work in real time.</p>

                <x-laptop3d title="Techcare" subtitle="Laptop service & maintenance" badge="Repair in Progress"
                            color="accent" :step="3" :height="380" :scale="1.12" :ry="-26" />
                <div class="hint">Drag the laptop to rotate it.</div>

                <ul class="auth-points">
                    <li>Live repair timeline</li>
                    <li>Notes from the workshop</li>
                    <li>All your laptops in one place</li>
                </ul>
            </section>

            {{-- Right: form --}}
            <section class="auth-panel">
                <div class="auth-mobile-logo">
                    <img src="{{ asset('images/logo/icon-text-on-dark.png') }}" alt="Techcare">
                    <p>Laptop service &amp; maintenance, made easy.</p>
                </div>

                <div class="card auth-card shadow">
                    <div class="card-body p-4">
                        @yield('content')
                    </div>
                </div>

                <p class="text-center text-white-50 mt-3 mb-0">&copy; {{ date('Y') }} Techcare</p>
            </section>
        </div>
    </div>
</div>
</body>
</html>
