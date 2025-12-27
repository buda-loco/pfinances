<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | PFinances</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            min-height: 100vh;
        }

        .content-area {
            padding: 2rem 0;
        }

        .navbar-brand {
            padding: 0;
        }
    </style>

</head>

<body x-data="{ commandBarOpen: false, theme: 'light' }" @keydown.window.k.prevent.meta="commandBarOpen = true"
    @keydown.window.k.prevent.ctrl="commandBarOpen = true"
    x-init="theme = document.documentElement.getAttribute('data-bs-theme'); $watch('theme', val => document.documentElement.setAttribute('data-bs-theme', val))">

    <x-command-bar />
    <x-toast-container />
    <x-confirm-dialog />

    <!-- Skip Navigation Link -->
    <a href="#main-content" class="skip-link visually-hidden-focusable">
        Skip to main content
    </a>

    <header class="sticky-top">
        <nav class="navbar navbar-expand-xl" role="navigation" aria-label="Main navigation">
            <div class="container-fluid px-4">
                <a class="navbar-brand me-4" href="{{ route('dashboard') }}">
                    <x-application-logo />
                </a>

                <button class="navbar-toggler border-0 shadow-none"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#mainNavbar"
                    aria-controls="mainNavbar"
                    aria-expanded="false"
                    aria-label="Toggle navigation">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>

                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('dashboard*') || request()->is('/') ? 'active' : '' }}"
                                href="{{ route('dashboard') }}">
                                <i class="fa-solid fa-house"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('transactions*') ? 'active' : '' }}"
                                href="{{ route('transactions.index') }}">
                                <i class="fa-solid fa-receipt"></i> Transactions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('accounts*') ? 'active' : '' }}"
                                href="{{ route('accounts.index') }}">
                                <i class="fa-solid fa-wallet"></i> Accounts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('income*') ? 'active' : '' }}"
                                href="{{ route('income.index') }}">
                                <i class="fa-solid fa-arrow-trend-up"></i> Income
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('expenses*') ? 'active' : '' }}"
                                href="{{ route('expenses.index') }}">
                                <i class="fa-solid fa-arrow-trend-down"></i> Expenses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('budgets*') ? 'active' : '' }}"
                                href="{{ route('budgets.index') }}">
                                <i class="fa-solid fa-bullseye"></i> Budgets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('categories*') ? 'active' : '' }}"
                                href="{{ route('categories.index') }}">
                                <i class="fa-solid fa-tags"></i> Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('projects*') ? 'active' : '' }}"
                                href="{{ route('projects.index') }}">
                                <i class="fa-solid fa-rocket"></i> Projects
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('portfolio*') ? 'active' : '' }}"
                                href="{{ route('portfolio.index') }}">
                                <i class="fa-solid fa-layer-group"></i> Portfolio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('import*') ? 'active' : '' }}"
                                href="{{ route('import.index') }}">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Import
                            </a>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center gap-3 ms-xl-4">
                        <div class="position-relative d-none d-xxl-block" style="width: 240px;">
                            <i
                                class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-3 text-muted opacity-50 small"></i>
                            <input type="text"
                                class="form-control bg-light border-0 rounded-pill ps-5 py-2 extra-small fw-bold"
                                placeholder="SEARCH (⌘K)" @click="commandBarOpen = true" readonly
                                style="cursor: pointer;">
                        </div>

                        <button
                            class="btn btn-light rounded-circle shadow-none border-0 p-0 d-flex align-items-center justify-content-center"
                            style="width: 44px; height: 44px;"
                            @click="theme = (theme === 'dark' ? 'light' : 'dark')"
                            aria-label="Toggle theme"
                            :aria-pressed="theme === 'dark' ? 'true' : 'false'">
                            <i class="fa-solid"
                                :class="theme === 'dark' ? 'fa-sun text-warning' : 'fa-moon text-primary'"></i>
                        </button>

                        <div class="vr mx-1 opacity-10 d-none d-lg-block"></div>

                        <div class="dropdown">
                            <button
                                class="btn btn-link p-0 text-decoration-none d-flex align-items-center gap-2 border-0"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                aria-label="User menu">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white"
                                    style="width: 38px; height: 38px;">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <div class="text-start d-none d-sm-block">
                                    <div class="fw-bold small text-primary-adaptive outfit">{{ Auth::user()->name }}</div>
                                    <div class="extra-small text-muted fw-bold text-uppercase tracking-wider">Premium
                                    </div>
                                </div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2 mt-2">
                                <li>
                                    <div class="px-3 py-2 d-sm-none">
                                        <div class="fw-bold small text-primary-adaptive outfit">{{ Auth::user()->name }}</div>
                                        <div class="extra-small text-muted fw-bold">Premium Plan</div>
                                    </div>
                                </li>
                                <li><a class="dropdown-item rounded-2 py-2" href="{{ route('profile.edit') }}"><i
                                            class="fa-solid fa-user-gear me-2 opacity-50"></i> Settings</a></li>
                                <li><a class="dropdown-item rounded-2 py-2" href="{{ route('import.index') }}"><i
                                            class="fa-solid fa-cloud-arrow-up me-2 opacity-50"></i> Import Data</a></li>
                                <li>
                                    <hr class="dropdown-divider opacity-10">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item rounded-2 py-2 text-danger">
                                            <i class="fa-solid fa-power-off me-2 opacity-50"></i> Sign Out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main id="main-content" class="container-fluid px-4 content-area" role="main">
        @yield('content')
    </main>

    <!-- Search Detail Modal -->
    <x-search-detail-modal />

    <script>
        // Initialize global search detail modal instance
        document.addEventListener('DOMContentLoaded', function() {
            window.searchDetailModalInstance = Alpine.$data(document.querySelector('#search-detail-modal'));
        });
    </script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scroll Position Preservation -->
    <script>
        (function () {
            // Restore scroll position after page load
            const scrollPos = sessionStorage.getItem('scrollPosition');
            if (scrollPos) {
                window.addEventListener('load', function () {
                    requestAnimationFrame(function () {
                        window.scrollTo({
                            top: parseInt(scrollPos),
                            behavior: 'instant'
                        });
                        sessionStorage.removeItem('scrollPosition');
                    });
                });
            }

            // Save scroll position before navigation for same-domain links
            document.addEventListener('click', function (e) {
                const link = e.target.closest('a');
                if (link && link.href && !link.getAttribute('data-no-scroll-save')) {
                    // Only save for same-domain links that aren't anchors
                    const url = new URL(link.href, window.location.origin);
                    if (url.origin === window.location.origin && !link.href.includes('#')) {
                        sessionStorage.setItem('scrollPosition', window.scrollY.toString());
                    }
                }
            });

            // Also handle form submissions (for pagination forms if any)
            document.addEventListener('submit', function (e) {
                if (!e.target.getAttribute('data-no-scroll-save')) {
                    sessionStorage.setItem('scrollPosition', window.scrollY.toString());
                }
            });
        })();
    </script>

    @stack('scripts')
</body>

</html>