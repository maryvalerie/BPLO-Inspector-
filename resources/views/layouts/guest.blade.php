<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('permit.municipality').' — Business Permit System')</title>
    <meta name="theme-color" content="#536978">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
</head>
<body class="inspector-portal inspector-guest">
    <header class="inspector-guest-header">
        <a href="{{ url('/login') }}" class="inspector-guest-brand">
            <span class="sidebar-brand-mark">QR</span>
            <span>{{ config('permit.municipality') }}<small>Municipal Permit Inspection</small></span>
        </a>
        <span class="inspector-secure-label"><span aria-hidden="true"></span> Official inspector access</span>
    </header>

    <main class="page">
        <div class="container inspector-guest-content">
            <x-flash-messages />
            @yield('content')
        </div>
    </main>

    <footer class="inspector-guest-footer">
        &copy; {{ date('Y') }} {{ config('permit.municipality') }}, {{ config('permit.province') }}
        <span>Permit verification and monitoring</span>
    </footer>
</body>
</html>
