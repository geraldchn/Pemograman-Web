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
    <style>
        .auth-box { max-width: 460px; width: 100%; }
        .auth-brand { text-align: center; color: #fff; margin-bottom: 1.25rem; }
        .auth-bg { background: radial-gradient(circle at 50% 0%, rgba(37,99,235,.35), transparent 55%), #0a0a0a; }
        .auth-logo { height: 52px; width: auto; margin-bottom: .75rem; }
        .auth-brand p { opacity: .85; margin: 0; }
        [data-bs-theme="dark"] .auth-card { background-color: #212529; }
    </style>
</head>
<body>
<div class="app-container app-theme-white">
    <div class="h-100 auth-bg" style="min-height:100vh">
        <div class="d-flex justify-content-center align-items-center p-3" style="min-height:100vh">
            <div class="auth-box">
                <div class="auth-brand">
                    <img src="{{ asset('images/logo/icon-text-on-dark.png') }}" alt="Techcare" class="auth-logo">
                    <p>Laptop service &amp; maintenance, made easy.</p>
                </div>

                <div class="card auth-card shadow">
                    <div class="card-body p-4">
                        @yield('content')
                    </div>
                </div>

                <p class="text-center text-white-50 mt-3 mb-0">&copy; {{ date('Y') }} Techcare</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
