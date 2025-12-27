<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .outfit {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container min-vh-100 d-flex flex-column justify-content-center align-items-center py-5">
        <div class="mb-4">
            <a href="/" class="text-decoration-none">
                <div class="bg-primary bg-opacity-10 p-3 rounded-4 shadow-sm">
                    <i class="fa-solid fa-gauge-high text-primary fs-1"></i>
                </div>
            </a>
        </div>

        <div class="w-100" style="max-width: 450px;">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    {{ $slot }}
                </div>
            </div>

            @if(Route::has('register') || Route::has('login'))
                <div class="mt-4 text-center">
                    <p class="text-muted small">
                        PFinances &mdash; Premium Portfolio & Finance Manager
                    </p>
                </div>
            @endif
        </div>
    </div>
</body>

</html>