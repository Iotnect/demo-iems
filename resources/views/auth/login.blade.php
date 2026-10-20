<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#e8eef0">
    <meta name="description" content="Sign in to IEMS Environmental Monitoring.">
    <title>Sign in · IEMS Environmental Monitoring</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23008b79'/%3E%3Cpath d='M8 22V10h4v12m4 0V6h4v16m4 0V14h3v8' stroke='white' stroke-width='2'/%3E%3C/svg%3E">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
<div class="app-shell auth-shell">
    <header class="auth-header">
        <a class="brand" href="{{ route('login') }}" aria-label="IEMS sign in">
            <span class="brand-mark"><x-icon name="leaf" size="26"/></span>
            <span><b>IEMS<span class="brand-period">.</span></b><small>Environmental Monitoring</small></span>
        </a>
    </header>

    <main class="auth-main">
        <section class="auth-intro" aria-labelledby="auth-intro-title">
            <div class="auth-eyebrow"><span class="tiny-dot green"></span> INTEGRATED ENVIRONMENTAL MONITORING SYSTEM</div>
            <h1 id="auth-intro-title">Environmental clarity.<br><span>Informed decisions.</span></h1>
            <p>A connected view of environmental observations, historical context, and the insights that guide your next step.</p>

            <div class="auth-illustration" aria-hidden="true">
                <div class="auth-orbit auth-orbit-outer"></div>
                <div class="auth-orbit auth-orbit-inner"></div>
                <div class="auth-orbit-center"><x-icon name="leaf" size="52"/></div>
                <div class="auth-signal signal-satellite"><span class="metric-icon teal"><x-icon name="satellite" size="20"/></span><div><strong>Satellite observations</strong><small>See the bigger picture</small></div><span class="tiny-dot green"></span></div>
                <div class="auth-signal signal-baseline"><span class="metric-icon blue"><x-icon name="chart" size="20"/></span><div><strong>Historical context</strong><small>Understand what’s changed</small></div></div>
                <div class="auth-signal signal-insight"><span class="metric-icon cyan"><x-icon name="shield" size="20"/></span><div><strong>Actionable insights</strong><small>Review with confidence</small></div></div>
                <span class="orbit-dot orbit-dot-one"></span><span class="orbit-dot orbit-dot-two"></span>
            </div>

            <div class="auth-capabilities"><span><x-icon name="methane" size="16"/> Methane</span><span><x-icon name="wind" size="16"/> Air quality</span><span><x-icon name="droplet" size="16"/> Water quality</span></div>
        </section>

        <section class="auth-card panel" aria-labelledby="login-title">
            <div class="auth-lock"><x-icon name="lock" size="25"/></div>
            <div class="auth-card-eyebrow">YOUR MONITORING WORKSPACE</div>
            <h2 id="login-title">Welcome back.</h2>
            <p class="auth-card-subtitle">Sign in to continue to your dashboard.</p>

            @if(session('success'))
                <div class="auth-feedback auth-feedback-success" role="status"><x-icon name="check-circle" size="18"/><p>{{ session('success') }}</p></div>
            @endif
            @if($errors->any())
                <div class="auth-feedback auth-feedback-error" role="alert" id="login-error"><x-icon name="alert" size="18"/><p>{{ $errors->first() }}</p></div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="auth-form" data-login-form>
                @csrf
                <div class="auth-form-field">
                    <label for="username">Username</label>
                    <div class="auth-input"><x-icon name="user" size="18"/><input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="Enter your username" autocomplete="username" maxlength="80" required autofocus @if($errors->has('username')) aria-invalid="true" aria-describedby="login-error" @endif></div>
                </div>
                <div class="auth-form-field">
                    <label for="password">Password</label>
                    <div class="auth-input"><x-icon name="lock" size="18"/><input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" maxlength="128" required @if($errors->has('password')) aria-invalid="true" aria-describedby="login-error" @endif><button type="button" class="password-toggle" data-toggle-password aria-label="Show password" aria-pressed="false" aria-controls="password"><span data-password-show><x-icon name="eye" size="19"/></span><span data-password-hide hidden><x-icon name="eye-off" size="19"/></span></button></div>
                </div>
                <button type="submit" class="button button-primary auth-submit"><span data-login-label>Sign in to IEMS</span><x-icon name="arrow" size="18"/></button>
            </form>

            <div class="auth-workspace-note"><x-icon name="shield" size="18"/><div><strong>Phase 1 proof of concept</strong><p>A dedicated workspace for environmental technology assessment and stakeholder review.</p></div></div>
            <div class="auth-card-footer"><span class="tiny-dot green"></span> Observation. Understanding. Action.</div>
        </section>
    </main>

    <footer class="auth-footer"><span>IEMS · Environmental Technology Assessment</span><span>PoC · Illustrative demonstration data</span></footer>
</div>
</body>
</html>
