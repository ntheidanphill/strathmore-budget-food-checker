{{--
    MASTER LAYOUT - every page extends this file with @extends('layouts.app').
    It provides: the navigation bar, flash messages, validation errors and footer.
    Pages fill the @section('content') area.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    {{-- viewport tag makes the site work on a phone browser (requirement R6) --}}
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Budget Food Checker') | Strathmore</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

{{-- ===== TOP NAVIGATION: links change depending on who is logged in ===== --}}
<header class="topbar">
    <a class="brand" href="{{ route('search') }}">
        <span class="brand-icon">🍛</span>
        <span>Strathmore <strong>Budget Food Checker</strong></span>
    </a>

    <nav class="nav">
        <a href="{{ route('search') }}" class="{{ request()->routeIs('search') ? 'active' : '' }}">🔍 Find Food</a>

        @auth
            @if (auth()->user()->isVendorManager())
                <a href="{{ route('vendor.dashboard') }}" class="{{ request()->routeIs('vendor.*') ? 'active' : '' }}">📊 Dashboard</a>
                <a href="{{ route('items.index') }}" class="{{ request()->routeIs('items.*') ? 'active' : '' }}">🍽️ My Menu</a>
            @endif

            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">⚙️ Admin</a>
                <a href="{{ route('items.index') }}" class="{{ request()->routeIs('items.*') ? 'active' : '' }}">🍽️ All Menus</a>
            @endif

            {{-- Logout MUST be a POST form with @csrf (security) --}}
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="link-btn">Logout ({{ auth()->user()->username }})</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">🔐 Staff Login</a>
        @endauth
    </nav>
</header>

<main class="container">
    {{-- Success message set with ->with('success', '...') in controllers --}}
    @if (session('success'))
        <div class="alert alert-success" data-autohide>✅ {{ session('success') }}</div>
    @endif

    {{-- Server-side validation errors (API brief 5.4) --}}
    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Please fix the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="footer">
    <p>🍴 Built by Group 8 for Strathmore University students · Eat well, spend less.</p>
    <p class="muted">Ole Sangale Road, Madaraka, Nairobi</p>
</footer>

<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
