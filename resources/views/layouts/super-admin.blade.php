<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Admin') — Niralagam</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #7c3aed;
            --primary-dark: #6d28d9;
            --sidebar-bg: #0f0a1e;
            --sidebar-text: #c4b5fd;
            --sidebar-active: #7c3aed;
            --body-bg: #f1f5f9;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --text-muted: #64748b;
            --success: #059669;
            --danger: #dc2626;
            --radius: 10px;
            --shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--body-bg); color: var(--text); min-height: 100vh; display: flex; }

        .sidebar { width: 240px; background: var(--sidebar-bg); display: flex; flex-direction: column; flex-shrink: 0; min-height: 100vh; }
        .sidebar-brand { padding: 1.5rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,.07); }
        .sidebar-brand a { color: #fff; font-size: 1.1rem; font-weight: 800; text-decoration: none; }
        .sidebar-brand .super-badge { display: inline-block; background: var(--primary); color: #fff; font-size: .6rem; font-weight: 700; padding: .15rem .4rem; border-radius: 4px; margin-left: .375rem; text-transform: uppercase; letter-spacing: .05em; vertical-align: middle; }
        .sidebar-brand small { display: block; color: var(--sidebar-text); font-size: .7rem; opacity: .6; margin-top: .2rem; }

        .sidebar-nav { padding: 1rem 0; flex: 1; }
        .nav-section { padding: .25rem .75rem; font-size: .65rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: rgba(196,181,253,.4); margin-top: .75rem; }
        .sidebar-nav a { display: flex; align-items: center; gap: .625rem; padding: .6rem 1.25rem; color: var(--sidebar-text); text-decoration: none; font-size: .875rem; transition: all .15s; border-radius: 6px; margin: .1rem .5rem; }
        .sidebar-nav a:hover { background: rgba(255,255,255,.06); color: #fff; }
        .sidebar-nav a.active { background: var(--sidebar-active); color: #fff; }

        .sidebar-footer { padding: 1rem; border-top: 1px solid rgba(255,255,255,.07); }
        .user-info { display: flex; align-items: center; gap: .625rem; margin-bottom: .75rem; }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--sidebar-active); display: flex; align-items: center; justify-content: center; font-size: .85rem; font-weight: 700; color: #fff; flex-shrink: 0; }
        .user-name { font-size: .82rem; color: #fff; font-weight: 600; }
        .user-role { font-size: .68rem; color: var(--sidebar-text); opacity: .65; }
        .btn-logout { width: 100%; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); color: var(--sidebar-text); padding: .5rem; border-radius: 6px; cursor: pointer; font-size: .8rem; transition: all .15s; }
        .btn-logout:hover { background: rgba(220,38,38,.2); color: #fca5a5; border-color: rgba(220,38,38,.3); }

        .main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .topbar { background: var(--card-bg); border-bottom: 1px solid var(--border); padding: 1rem 1.75rem; display: flex; align-items: center; justify-content: space-between; }
        .topbar-left h1 { font-size: 1.1rem; font-weight: 600; }
        .topbar-left p { font-size: .78rem; color: var(--text-muted); margin-top: .1rem; }
        .content { padding: 1.75rem; flex: 1; }

        /* Alert */
        .alert { padding: .875rem 1.125rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1.25rem; display: flex; align-items: flex-start; gap: .625rem; }
        .alert-success { background: #d1fae5; border: 1px solid #a7f3d0; color: #065f46; }
        .alert-error   { background: #fee2e2; border: 1px solid #fecaca; color: var(--danger); }

        /* Stats */
        .stats-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); padding: 1.25rem; box-shadow: var(--shadow); }
        .stat-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-bottom: .375rem; }
        .stat-value { font-size: 1.9rem; font-weight: 800; line-height: 1; }
        .stat-card.purple .stat-value { color: var(--primary); }
        .stat-card.green .stat-value  { color: var(--success); }
        .stat-card.slate .stat-value  { color: var(--text-muted); }

        /* Card */
        .card { background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); }
        .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .card-title { font-size: .95rem; font-weight: 600; }
        .card-body { padding: 1.5rem; }

        /* Table */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        thead th { padding: .625rem 1rem; text-align: left; font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); background: #f8fafc; border-bottom: 1px solid var(--border); }
        tbody td { padding: .75rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: #f8fafc; }

        /* Badges */
        .badge { display: inline-flex; align-items: center; padding: .2rem .625rem; border-radius: 20px; font-size: .72rem; font-weight: 600; }
        .badge-active    { background: #d1fae5; color: #065f46; }
        .badge-inactive  { background: #f1f5f9; color: var(--text-muted); }
        .badge-suspended { background: #fee2e2; color: var(--danger); }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .5rem 1rem; border-radius: 7px; font-size: .85rem; font-weight: 500; cursor: pointer; text-decoration: none; border: none; transition: all .15s; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid var(--border); }
        .btn-ghost:hover { background: var(--body-bg); color: var(--text); }
        .btn-danger { background: #fee2e2; color: var(--danger); border: 1px solid #fecaca; }
        .btn-danger:hover { background: var(--danger); color: #fff; }
        .btn-sm { padding: .3rem .7rem; font-size: .78rem; }

        /* Form */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
        .form-group { display: flex; flex-direction: column; gap: .375rem; }
        .form-group.full { grid-column: 1 / -1; }
        .form-label { font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--text); }
        .form-hint { font-size: .72rem; color: var(--text-muted); }
        .form-control { padding: .65rem .875rem; border: 1.5px solid var(--border); border-radius: 8px; font-size: .9rem; color: var(--text); background: #f8fafc; outline: none; transition: border-color .15s, box-shadow .15s; font-family: inherit; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(124,58,237,.1); background: #fff; }
        .form-control.is-invalid { border-color: var(--danger); }
        .invalid-feedback { font-size: .75rem; color: var(--danger); margin-top: .2rem; }
        .form-actions { padding-top: .5rem; display: flex; gap: .75rem; align-items: center; }

        /* Divider */
        .section-divider { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); padding: .875rem 0 .5rem; border-bottom: 1px solid var(--border); margin-bottom: 1.25rem; grid-column: 1/-1; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
    @stack('styles')
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('super-admin.tenants.index') }}">Niralagam<span class="super-badge">SA</span><br><small>Super Admin</small></a>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Management</div>
        <a href="{{ route('super-admin.tenants.index') }}" class="{{ request()->routeIs('super-admin.tenants*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
            Tenants
        </a>
        <a href="{{ route('admin.dashboard') }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Blog Dashboard
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">Super Administrator</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Sign out</button>
        </form>
    </div>
</aside>

<div class="main">
    <div class="topbar">
        <div class="topbar-left">
            <h1>@yield('page-title', 'Super Admin')</h1>
            <p>@yield('breadcrumb')</p>
        </div>
        <div>@yield('topbar-actions')</div>
    </div>
    <div class="content">
        @if(session('success'))
            <div class="alert alert-success">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </div>
</div>

@stack('scripts')
</body>
</html>
