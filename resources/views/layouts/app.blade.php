<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
    <meta name="msapplication-tap-highlight" content="no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Techcare')</title>
    <script>
    document.documentElement.setAttribute('data-bs-theme', localStorage.getItem('theme') || 'light');
    </script>

    <link rel="icon" href="{{ asset('architectui/favicon.ico') }}">
    <link href="{{ asset('architectui/assets/styles/main.css') }}" rel="stylesheet">
    @stack('styles')
    <style>
    [data-bs-theme="dark"] .app-main__outer { background: #14171a; }
    [data-bs-theme="dark"] .app-footer { background: #1b1f23 !important; color: #adb5bd; }
    [data-bs-theme="dark"] .card { background-color: #212529; }
    [data-bs-theme="dark"] .card-header { background-color: #2b3035 !important; color: #dee2e6; }
    [data-bs-theme="dark"] .app-page-title { background: transparent; }
    [data-bs-theme="dark"] .app-page-title,
    [data-bs-theme="dark"] .page-title-heading,
    [data-bs-theme="dark"] .widget-heading { color: #dee2e6; }
    [data-bs-theme="dark"] .table { --bs-table-color: #dee2e6; }
    </style>

    <script defer src="{{ asset('architectui/assets/scripts/main.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/demo.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/toastr.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/scrollbar.js') }}"></script>
    <script defer src="{{ asset('architectui/assets/scripts/chart_js.js') }}"></script>
    @stack('scripts')
</head>

<body>
<div class="app-container app-theme-white body-tabs-shadow fixed-sidebar fixed-header">

    {{-- ========== HEADER ========== --}}
    <div class="app-header header-shadow">
        <div class="app-header__logo">
            <div class="logo-src"></div>
            <div class="header__pane ms-auto">
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box"><span class="hamburger-inner"></span></span>
                </button>
            </div>
        </div>

        <div class="app-header__mobile-menu">
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box"><span class="hamburger-inner"></span></span>
            </button>
        </div>

        <div class="app-header__menu">
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper"><i class="fa fa-ellipsis-v fa-w-6"></i></span>
            </button>
        </div>

        <div class="app-header__content">
            <div class="app-header-left">
                <div class="search-wrapper">
                    <div class="input-holder">
                        <input class="search-input" placeholder="Type to search">
                        <button class="search-icon"><span></span></button>
                    </div>
                    <button class="btn-close"></button>
                </div>
            </div>

            <div class="app-header-right">
                 <button type="button" id="theme-toggle" class="btn btn-sm btn-outline-secondary me-3" title="Ganti tema">
                    <i class="fa fa-moon" id="theme-icon"></i>
                </button>
                <div class="header-btn-lg pe-0">
                    <div class="widget-content p-0">
                        <div class="widget-content-wrapper">
                            <div class="widget-content-left">
                                <div class="btn-group">
                                    <a data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="p-0 btn">
                                        <img width="42" class="rounded-circle"
                                             src="{{ asset('architectui/assets/images/avatars/1.jpg') }}" alt="">
                                        <i class="fa fa-angle-down ms-2 opacity-8"></i>
                                    </a>
                                    <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                        <button type="button" class="dropdown-item">Profil</button>
                                        <div tabindex="-1" class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item">Keluar</button>
                                    </div>
                                </div>
                            </div>
                            <div class="widget-content-left ms-3 header-user-info">
                                <div class="widget-heading">Admin Techcare</div>
                                <div class="widget-subheading">Administrator</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== BODY ========== --}}
    <div class="app-main">

        {{-- Sidebar --}}
        <div class="app-sidebar sidebar-shadow">
            <div class="app-header__logo">
                <div class="logo-src"></div>
                <div class="header__pane ms-auto">
                    <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                        <span class="hamburger-box"><span class="hamburger-inner"></span></span>
                    </button>
                </div>
            </div>

            <div class="scrollbar-sidebar">
                <div class="app-sidebar__inner">
                    <ul class="vertical-nav-menu">
                        <li class="app-sidebar__heading">Menu</li>
                        <li>
                            <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'mm-active' : '' }}">
                                <i class="metismenu-icon pe-7s-rocket"></i> Dashboard
                            </a>
                        </li>

                        {{-- Tambahkan menu Techcare di sini --}}
                    </ul>
                </div>
            </div>
        </div>

        {{-- Konten halaman --}}
        <div class="app-main__outer">
            <div class="app-main__inner">
                @yield('content')
            </div>

            <div class="app-wrapper-footer">
                <div class="app-footer">
                    <div class="app-footer__inner">
                        <div class="app-footer-left">
                            <span class="nav-link">&copy; {{ date('Y') }} Techcare</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const html = document.documentElement;
        const header = document.querySelector('.app-header');
        const sidebar = document.querySelector('.app-sidebar');
        const icon = document.getElementById('theme-icon');

        function applyTheme(theme) {
            const dark = theme === 'dark';
            html.setAttribute('data-bs-theme', theme);
            header.classList.toggle('bg-dark', dark);
            header.classList.toggle('header-text-light', dark);
            sidebar.classList.toggle('bg-dark', dark);
            sidebar.classList.toggle('sidebar-text-light', dark);
            icon.className = dark ? 'fa fa-sun' : 'fa fa-moon';
            localStorage.setItem('theme', theme);
        }

        applyTheme(localStorage.getItem('theme') || 'light');

        document.getElementById('theme-toggle').addEventListener('click', function () {
            applyTheme(html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
        });
    });
</script>
</html>