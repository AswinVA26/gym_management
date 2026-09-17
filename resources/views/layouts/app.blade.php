<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GymHub') - {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    @include('partials.theme')

    @vite(['resources/css/app.css', 'resources/js/app.jsx'])

    <style>
        body { background: #f4f6f9; overflow-x: hidden; }
        .sidebar { width: 250px; height: 100vh; position: fixed; left: 0; top: 0; background: #212529; color: #fff; display: flex; flex-direction: column; z-index: 100; }
        .sidebar-header { padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar-nav { flex: 1; overflow-y: auto; }
        .sidebar a { color: #adb5bd; text-decoration: none; display: block; padding: 12px 20px; transition: .3s; font-size: .95rem; }
        .sidebar a:hover { background: #343a40; color: #fff; }
        .sidebar a.active { background: #0d6efd; color: #fff; }
        .sidebar .nav-section { padding: 12px 20px 4px; font-size: .72rem; text-transform: uppercase; letter-spacing: 1px; color: #6c757d; }
        .content { margin-left: 250px; padding: 20px; }
        .topbar { background: #fff; padding: 15px 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,.05); margin-bottom: 20px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 3px 15px rgba(0,0,0,.08); }
        .stat-icon { font-size: 40px; opacity: .2; }
        .badge-soft { font-size: .72rem; }
        .table thead th { font-size: .78rem; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h5 class="mb-0"><i class="fas fa-dumbbell"></i> {{ config('app.name') }}</h5>
        @if(auth()->user()->isSuperAdmin())
            <small style="color:#8b5cf6">Platform Admin</small>
        @endif
    </div>

    <nav class="sidebar-nav">
        @php $isOverride = auth()->user()->isSuperAdmin() && app(\App\Services\TenantManager::class)->ready(); @endphp

        @if(auth()->user()->isSuperAdmin())
            <div class="nav-section">Platform</div>
            <a href="{{ route('platform.tenants.index') }}" class="{{ request()->routeIs('platform.*') && !request()->routeIs('app.*') ? 'active' : '' }}">
                <i class="fas fa-building me-2"></i> Gyms
            </a>
            @if($isOverride)
                <div class="nav-section">{{ app(\App\Services\TenantManager::class)->get()->name }}</div>
                <a href="{{ route('app.dashboard') }}" class="{{ request()->routeIs('app.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
            @endif
        @endif

        @if($isOverride === false && !auth()->user()->isSuperAdmin())
            <div class="nav-section">{{ app(\App\Services\TenantManager::class)->ready() ? app(\App\Services\TenantManager::class)->get()->name : 'Workspace' }}</div>
            <a href="{{ route('app.dashboard') }}" class="{{ request()->routeIs('app.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line me-2"></i> Dashboard
            </a>
        @endif

        @if($isOverride === false && !auth()->user()->isSuperAdmin())
            <a href="{{ route('app.members.index') }}" class="{{ request()->routeIs('app.members.*') ? 'active' : '' }}">
                <i class="fas fa-users me-2"></i> Members
            </a>
            @if(auth()->user()->isGymAdmin())
                <a href="{{ route('app.trainers.index') }}" class="{{ request()->routeIs('app.trainers.*') ? 'active' : '' }}">
                    <i class="fas fa-user-tie me-2"></i> Trainers
                </a>
            @endif
            <a href="{{ route('app.plans.index') }}" class="{{ request()->routeIs('app.plans.*') ? 'active' : '' }}">
                <i class="fas fa-id-card-clip me-2"></i> Plans
            </a>
            <a href="{{ route('app.memberships.index') }}" class="{{ request()->routeIs('app.memberships.*') ? 'active' : '' }}">
                <i class="fas fa-id-card me-2"></i> Memberships
            </a>
            <a href="{{ route('app.attendance.index') }}" class="{{ request()->routeIs('app.attendance.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check me-2"></i> Attendance
            </a>
            <a href="{{ route('app.payments.index') }}" class="{{ request()->routeIs('app.payments.*') ? 'active' : '' }}">
                <i class="fas fa-money-bill-wave me-2"></i> Payments
            </a>
            <a href="{{ route('app.ai.index') }}" class="{{ request()->routeIs('app.ai.*') ? 'active' : '' }}">
                <i class="fas fa-robot me-2"></i> AI Assistant
            </a>
        @endif

        @if($isOverride)
            <div class="nav-section">Gym Sections</div>
            <a href="{{ route('app.members.index') }}" class="{{ request()->routeIs('app.members.*') ? 'active' : '' }}">
                <i class="fas fa-users me-2"></i> Members
            </a>
            <a href="{{ route('app.memberships.index') }}" class="{{ request()->routeIs('app.memberships.*') ? 'active' : '' }}">
                <i class="fas fa-id-card me-2"></i> Memberships
            </a>
            <a href="{{ route('app.ai.index') }}" class="{{ request()->routeIs('app.ai.*') ? 'active' : '' }}">
                <i class="fas fa-robot me-2"></i> AI Assistant
            </a>
        @endif

        <div class="nav-section">Account</div>
        @if(auth()->user()->isSuperAdmin() && !$isOverride)
            <a href="{{ route('platform.settings') }}" class="{{ request()->routeIs('platform.settings*') ? 'active' : '' }}">
                <i class="fas fa-gear me-2"></i> Platform
            </a>
        @else
            <a href="{{ route('app.settings') }}" class="{{ request()->routeIs('app.settings') ? 'active' : '' }}">
                <i class="fas fa-gear me-2"></i> {{ auth()->user()->isSuperAdmin() ? 'Gym Settings' : 'Settings' }}
            </a>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link w-100 text-start text-decoration-none" style="color:#adb5bd; padding:12px 20px; font-size:.95rem;">
                <i class="fas fa-right-from-bracket me-2"></i> Logout
            </button>
        </form>
    </nav>
</div>

<div class="content">
    <div class="topbar d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">@yield('title')</h4>
        <div class="d-flex align-items-center gap-3">
            @if($isOverride)
                <span class="badge text-bg-warning badge-soft">
                    <i class="fas fa-eye me-1"></i> Viewing {{ app(\App\Services\TenantManager::class)->get()->name }}
                </span>
                <form method="POST" action="{{ route('platform.tenants.leave') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary">Back to Gyms</button>
                </form>
            @endif
            <div class="dropdown">
                <button class="btn btn-outline-secondary theme-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Switch theme">
                    <i class="fas fa-moon"></i>
                    <i class="fas fa-sun"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item d-flex align-items-center gap-2" type="button" data-bs-theme-value="light"><i class="fas fa-sun fa-fw"></i> Light</button></li>
                    <li><button class="dropdown-item d-flex align-items-center gap-2" type="button" data-bs-theme-value="dark"><i class="fas fa-moon fa-fw"></i> Dark</button></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><button class="dropdown-item d-flex align-items-center gap-2" type="button" data-bs-theme-value="auto"><i class="fas fa-circle-half-stroke fa-fw"></i> Auto</button></li>
                </ul>
            </div>
            <div class="text-end">
                <strong>{{ auth()->user()->name }}</strong><br>
                <span class="badge text-bg-secondary badge-soft">{{ \App\Models\User::roleLabel(auth()->user()->role) }}</span>
            </div>
        </div>
    </div>

    @include('partials.flash')

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@stack('scripts')

</body>
</html>