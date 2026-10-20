<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#008b79"><meta name="description" content="IEMS environmental monitoring — Phase 1 proof of concept with illustrative demonstration data.">
    <title>{{ $titles[$screen] }} · IEMS</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23008b79'/%3E%3Cpath d='M8 22V10h4v12m4 0V6h4v16m4 0V14h3v8' stroke='white' stroke-width='2'/%3E%3C/svg%3E">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dashboard-app">
<a href="#main-content" class="skip-link">Skip to dashboard content</a>
<div class="app-shell">
    <aside class="sidebar" id="sidebar"><button type="button" class="icon-button sidebar-close" data-close-sidebar aria-label="Close navigation"><x-icon name="close" size="18"/></button>
        <a class="brand" href="{{ route('iems.overview', $query) }}"><span class="brand-mark"><x-icon name="leaf" size="26"/></span><span><b>IEMS<span class="brand-period">.</span></b><small>Environmental Monitoring</small></span></a>
        <div class="workspace-label">PETRONAS <span>PHASE 1 PoC</span></div>
        <div class="nav-section-label">WORKSPACE</div>
        <nav class="main-nav" aria-label="Main navigation">
        @foreach($navigation as $key => [$label, $icon])
            @if($key === 'administration')<div class="nav-divider"></div><div class="nav-section-label">MANAGEMENT</div>@endif
            <a href="{{ route('iems.screen', ['screen' => $key] + $query) }}" @class(['nav-link', 'active' => $screen === $key]) @if($screen === $key) aria-current="page" @endif><x-icon :name="$icon" size="19"/><span>{{ $label }}</span>@if($key === 'alerts' && $openAnomalies)<span class="nav-count">{{ $openAnomalies }}</span>@endif</a>
        @endforeach
        </nav>
        <div class="sidebar-bottom"><div class="poc-card"><span class="poc-icon"><x-icon name="satellite" size="20"/></span><strong>From observation to insight.</strong><p>Building a clearer picture of our environmental footprint.</p><a href="{{ route('iems.screen', ['screen' => 'poc-evaluation'] + $query) }}">Explore the PoC <x-icon name="arrow" size="14"/></a><div class="poc-orbit"></div></div><button type="button" class="help-button" data-open-dialog="help-dialog"><x-icon name="help" size="18"/> Help & documentation <x-icon name="arrow" size="14"/></button><div class="sidebar-footer"><span class="tiny-dot green"></span> System operational <span>v1.0 PoC</span></div></div>
    </aside>
    <div class="workspace">
        <header class="topbar"><div class="breadcrumb"><button type="button" class="icon-button mobile-nav" aria-label="Toggle navigation" aria-controls="sidebar" aria-expanded="false" data-toggle-sidebar><x-icon name="menu"/></button><span>Workspace</span><x-icon name="chevron" size="13"/><strong>{{ $navigation[$screen][0] }}</strong></div><div class="topbar-right"><span class="demo-badge"><span></span> PoC · Demonstration Data</span><span class="topbar-divider"></span><div class="notification-wrap"><button type="button" class="icon-button notification-button" data-toggle-notifications aria-label="Notifications" aria-expanded="false"><x-icon name="bell"/><i></i></button><div class="notification-popover" hidden><strong>Observation updates</strong><p>{{ $latestAcquisition }}</p><a href="{{ route('iems.screen', ['screen' => 'alerts'] + $query) }}">{{ $openAnomalies }} anomalies awaiting review <x-icon name="arrow" size="14"/></a><p class="muted">Demonstration notifications · not a live feed</p></div></div><span class="topbar-divider"></span><button type="button" class="profile-button" data-open-dialog="profile-dialog"><span class="avatar">AD</span><span><b>Administrator</b><small>Administrator</small></span><x-icon name="down" size="13"/></button><form class="logout-form" method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="icon-button" aria-label="Sign out" title="Sign out"><x-icon name="logout" size="18"/></button></form></div></header>
        <main id="main-content">
            <div class="page-heading"><div><div class="page-eyebrow">INTEGRATED ENVIRONMENTAL MONITORING SYSTEM</div><h1>{{ $titles[$screen] }}</h1>@if($screen !== 'overview')<p>@yield('description', 'A clearer view of environmental conditions, from observation to action.')</p>@endif</div><div class="heading-actions"><span class="last-updated"><x-icon name="clock" size="13"/> {{ $hasData ? 'Latest data: '.$latestAcquisition : 'No acquisition in selected period' }}</span>@yield('heading-action')@if(!View::hasSection('heading-action'))<a class="button button-primary" href="{{ route('iems.screen', ['screen' => 'reports'] + $query) }}"><x-icon name="file" size="16"/> Generate report</a>@endif</div></div>
            <form class="filterbar" method="GET" action="{{ route('iems.screen', ['screen' => $screen]) }}" data-global-filters>
                <input type="hidden" name="reference" value="{{ $reference }}">
                @if($screen === 'reports')<input type="hidden" name="type" value="{{ $reportType }}">@endif
                <div class="filter-field aoi-filter"><x-icon name="pin" size="17"/><div><label for="aoi">AREA OF INTEREST</label><select name="aoi" id="aoi" data-auto-submit>@foreach($aois as $key => $area)<option value="{{ $key }}" @selected($aoiKey === $key)>{{ $area['name'] }}</option>@endforeach</select></div></div>
                <div class="filter-field period-filter"><x-icon name="calendar" size="17"/><div><span class="filter-label">OBSERVATION PERIOD</span><x-period-picker :period="$period" :start-date="$startDate" :end-date="$endDate" :period-label="$periodLabel"/></div></div>
                <div class="filter-field"><x-icon name="filter" size="17"/><div><label for="parameter">PARAMETERS</label><select name="parameter" id="parameter" data-auto-submit><option value="all" @selected($parameter === 'all')>All parameters</option>@foreach($metrics as $key => $metric)<option value="{{ $key }}" @selected($parameter === $key)>{{ $metric['label'] }}</option>@endforeach</select></div></div>
                <a href="{{ route('iems.screen', ['screen' => $screen]) }}" class="filter-reset" title="Reset all filters"><x-icon name="history" size="15"/> Reset</a><noscript><button type="submit" class="button">Apply filters</button></noscript>
            </form>
            @if(session('success'))<div class="notice notice-success" role="status"><x-icon name="check-circle" size="18"/>{{ session('success') }}<button type="button" data-dismiss aria-label="Dismiss"><x-icon name="close" size="16"/></button></div>@endif
            @if($errors->any())<div class="notice notice-warning" role="alert"><x-icon name="alert" size="18"/><div><strong>Please check your entries.</strong>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div></div>@endif
            @unless($hasData)<div class="notice notice-neutral"><x-icon name="satellite" size="20"/><div><strong>No observations available for {{ $periodLabel }}.</strong><p>This demonstration dataset covers October. Missing data does not indicate that no anomalies occurred.</p></div><a href="{{ route('iems.screen', ['screen' => $screen, 'aoi' => $aoiKey, 'parameter' => $parameter] + $query) }}">View October <x-icon name="arrow" size="15"/></a></div>@endunless
            @yield('content')
            <footer class="main-footer"><span><span class="tiny-dot teal"></span> IEMS · Phase 1 Environmental Technology Assessment</span><span>All observations, boundaries, thresholds and findings are illustrative.</span></footer>
        </main>
    </div>
</div>
<dialog id="help-dialog" class="app-dialog" aria-labelledby="help-title"><button type="button" class="dialog-close icon-button" data-close-dialog aria-label="Close"><x-icon name="close"/></button><span class="large-dialog-icon"><x-icon name="help" size="28"/></span><h2 id="help-title">Your monitoring workspace</h2><p>Explore the illustrative Malaysian AOIs using the filters above. October contains sample satellite observations; September demonstrates an empty dataset.</p><div class="help-steps"><p><b>01 · Explore</b> Toggle map layers and select the highlighted anomaly.</p><p><b>02 · Interpret</b> Compare observations with seasonal reference ranges.</p><p><b>03 · Review</b> Record an analyst review in Alerts & Anomalies.</p><p><b>04 · Report</b> Prepare a report and use Print / Save PDF.</p></div><div class="notice notice-neutral">Demo reviews and settings are saved for this browser session. This PoC has no live ingestion or production access control.</div><button class="button button-primary" data-close-dialog>Got it</button></dialog>
<dialog id="profile-dialog" class="app-dialog small-dialog" aria-labelledby="profile-title">
    <button class="dialog-close icon-button" data-close-dialog aria-label="Close"><x-icon name="close"/></button>
    <span class="avatar avatar-large">AD</span><h2 id="profile-title">Administrator</h2>
    <p>admin &middot; IEMS demonstration workspace</p>
    <div class="notice notice-neutral">You are signed in with the Phase 1 administrator account.</div>
    <form method="POST" action="{{ route('logout') }}" class="profile-signout">@csrf<button class="button button-primary full-width" type="submit"><x-icon name="logout" size="17"/> Sign out</button></form>
</dialog>
<div class="loading-overlay" hidden role="status"><div class="loading-spinner"></div><span>Preparing observations…</span></div>
</body>
</html>
