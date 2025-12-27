<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PFinances | Premium Assets & Portfolio Manager</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #465fff 0%, #6366f1 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        h1,
        h2,
        h3,
        .outfit {
            font-family: 'Outfit', sans-serif;
        }

        .hero-section {
            padding: 100px 0;
            background: radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.05) 0%, rgba(255, 255, 255, 0) 100%);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 0 !important;
            border-radius: 20px !important;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
        }

        .icon-box {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-white">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light pt-4">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#">
                <i class="fa-solid fa-gauge-high text-primary fs-3"></i>
                <span class="outfit fs-4">PFinances</span>
            </a>
            <div class="ms-auto">
                @if (Route::has('login'))
                    <div class="d-flex gap-2">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-primary px-4 fw-bold rounded-pill">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-link text-dark fw-bold text-decoration-none px-4">Log
                                in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-primary px-4 fw-bold rounded-pill">Getting
                                    Started</a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-3 fw-bold">Now
                        with Bootstrap 5</span>
                    <h1 class="display-3 fw-bold mb-4 outfit">Take Control of Your <span class="text-primary">Financial
                            Future</span>.</h1>
                    <p class="lead text-muted mb-5">PFinances provides elite-level asset tracking and portfolio
                        management. Experience the most powerful way to monitor your wealth with our all-new premium
                        interface.</p>
                    <div class="d-flex gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}"
                                class="btn btn-primary btn-lg px-5 py-3 fw-bold rounded-pill">Go to My Dashboard</a>
                        @else
                            <a href="{{ route('register') }}"
                                class="btn btn-primary btn-lg px-5 py-3 fw-bold rounded-pill">Start for Free</a>
                            <a href="#features" class="btn btn-light btn-lg px-5 py-3 fw-bold border rounded-pill">Learn
                                More</a>
                        @endauth
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="position-relative">
                        <div class="bg-primary rounded-pill opacity-10 position-absolute"
                            style="width: 500px; height: 500px; top: -100px; right: -100px; filter: blur(80px);"></div>
                        <div class="card border-0 shadow-lg rounded-4 overflow-hidden position-relative">
                            <div class="bg-light p-4 border-bottom d-flex align-items-center gap-2">
                                <div class="bg-danger rounded-circle" style="width: 12px; height: 12px;"></div>
                                <div class="bg-warning rounded-circle" style="width: 12px; height: 12px;"></div>
                                <div class="bg-success rounded-circle" style="width: 12px; height: 12px;"></div>
                            </div>
                            <div class="card-body p-0">
                                <div class="p-5 bg-white text-center">
                                    <i class="fa-solid fa-chart-line text-primary display-1 mb-4"></i>
                                    <h4 class="fw-bold">Intelligent Dashboard</h4>
                                    <p class="text-muted small">Real-time charts, category breakdowns, and growth
                                        indicators designed for clarity.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold outfit">Complete Financial Ecosystem</h2>
                <p class="text-muted">Everything you need to manage your personal or business finances.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4 shadow-sm">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="fa-solid fa-receipt fs-3"></i>
                        </div>
                        <h5 class="fw-bold">Smart Transactions</h5>
                        <p class="text-muted small">Automated import and categorization. Keep track of every dollar with
                            precision.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4 shadow-sm">
                        <div class="icon-box bg-success bg-opacity-10 text-success">
                            <i class="fa-solid fa-building-columns fs-3"></i>
                        </div>
                        <h5 class="fw-bold">Account Management</h5>
                        <p class="text-muted small">Manage multiple accounts, currencies, and institutions in one
                            centralized hub.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4 shadow-sm">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning">
                            <i class="fa-solid fa-piggy-bank fs-3"></i>
                        </div>
                        <h5 class="fw-bold">Budgeting Goals</h5>
                        <p class="text-muted small">Set custom benchmarks and track your progress with visual
                            indicators.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-5 bg-light mt-5">
        <div class="container text-center">
            <p class="text-muted small mb-0">&copy; {{ date('Y') }} PFinances Bootstrap. Built with Laravel 12 &
                Bootstrap 5.</p>
        </div>
    </footer>
</body>

</html>