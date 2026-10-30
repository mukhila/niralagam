<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Niralagam</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --sidebar-bg: #1e1b4b;
            --sidebar-text: #c7d2fe;
            --sidebar-active: #4f46e5;
            --body-bg: #f1f5f9;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --text-muted: #64748b;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --radius: 10px;
            --shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
        }

        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--body-bg); color: var(--text); min-height: 100vh; display: flex; }

        /* Sidebar */
        .sidebar { width: 240px; background: var(--sidebar-bg); display: flex; flex-direction: column; flex-shrink: 0; min-height: 100vh; }
        .sidebar-brand { padding: 1.5rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,.08); }
        .sidebar-brand a { color: #fff; font-size: 1.2rem; font-weight: 700; text-decoration: none; letter-spacing: -.01em; }
        .sidebar-brand small { display: block; color: var(--sidebar-text); font-size: .7rem; font-weight: 400; margin-top: .1rem; opacity: .7; }
        .sidebar-nav { padding: 1rem 0; flex: 1; }
        .nav-section { padding: .25rem .75rem; font-size: .65rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: rgba(199,210,254,.45); margin-top: .75rem; }
        .sidebar-nav a { display: flex; align-items: center; gap: .625rem; padding: .6rem 1.25rem; color: var(--sidebar-text); text-decoration: none; font-size: .875rem; transition: all .15s; border-radius: 6px; margin: .1rem .5rem; }
        .sidebar-nav a:hover { background: rgba(255,255,255,.07); color: #fff; }
        .sidebar-nav a.active { background: var(--sidebar-active); color: #fff; }
        .sidebar-nav a svg { flex-shrink: 0; }
        .sidebar-footer { padding: 1rem; border-top: 1px solid rgba(255,255,255,.08); }
        .user-info { display: flex; align-items: center; gap: .625rem; margin-bottom: .75rem; }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--sidebar-active); display: flex; align-items: center; justify-content: center; font-size: .85rem; font-weight: 600; color: #fff; flex-shrink: 0; }
        .user-name { font-size: .825rem; color: #fff; font-weight: 500; }
        .user-role { font-size: .7rem; color: var(--sidebar-text); opacity: .7; }
        .btn-logout { width: 100%; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1); color: var(--sidebar-text); padding: .5rem; border-radius: 6px; cursor: pointer; font-size: .8rem; transition: all .15s; }
        .btn-logout:hover { background: rgba(220,38,38,.2); border-color: rgba(220,38,38,.4); color: #fca5a5; }

        /* Main content */
        .main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        .topbar { background: var(--card-bg); border-bottom: 1px solid var(--border); padding: 1rem 1.75rem; display: flex; align-items: center; justify-content: space-between; }
        .topbar-title { font-size: 1.1rem; font-weight: 600; }
        .topbar-breadcrumb { font-size: .8rem; color: var(--text-muted); margin-top: .15rem; }
        .content { padding: 1.75rem; flex: 1; }

        /* Cards */
        .card { background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow); }
        .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .card-title { font-size: .95rem; font-weight: 600; }
        .card-body { padding: 1.25rem; }

        /* Stat cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: var(--card-bg); border-radius: var(--radius); border: 1px solid var(--border); padding: 1.25rem; box-shadow: var(--shadow); }
        .stat-label { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); margin-bottom: .375rem; }
        .stat-value { font-size: 2rem; font-weight: 700; line-height: 1; }
        .stat-card.indigo .stat-value { color: #4f46e5; }
        .stat-card.green .stat-value  { color: var(--success); }
        .stat-card.amber .stat-value  { color: var(--warning); }
        .stat-card.slate .stat-value  { color: var(--text-muted); }

        /* Table */
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        thead th { padding: .625rem 1rem; text-align: left; font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); background: #f8fafc; border-bottom: 1px solid var(--border); }
        tbody td { padding: .75rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: #f8fafc; }

        /* Badges */
        .badge { display: inline-flex; align-items: center; padding: .2rem .625rem; border-radius: 20px; font-size: .72rem; font-weight: 600; text-transform: capitalize; }
        .badge-published { background: #d1fae5; color: #065f46; }
        .badge-draft     { background: #fef3c7; color: #92400e; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: .375rem; padding: .5rem 1rem; border-radius: 7px; font-size: .85rem; font-weight: 500; cursor: pointer; text-decoration: none; border: none; transition: all .15s; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-sm { padding: .3rem .7rem; font-size: .78rem; }
        .btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid var(--border); }
        .btn-ghost:hover { background: var(--body-bg); color: var(--text); }

        @media (max-width: 768px) {
            .sidebar { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
<aside class="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}" style="display:flex;align-items:center;gap:.625rem;">
            <img src="{{ asset('niralagam_logo.png') }}" alt="Niralagam" style="height:32px;width:auto;border-radius:6px;">
            <span>Niralagam<br><small>Admin Panel</small></span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Content</div>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="{{ route('admin.posts.index') }}" class="{{ request()->routeIs('admin.posts*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
            Posts
        </a>
        <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            Categories
        </a>
        <a href="{{ route('admin.tags.index') }}" class="{{ request()->routeIs('admin.tags*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            Tags
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Sign out</button>
        </form>
    </div>
</aside>

{{-- Main --}}
<div class="main">
    <div class="topbar">
        <div>
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
            <div class="topbar-breadcrumb">@yield('breadcrumb', 'Home')</div>
        </div>
        <div>@yield('topbar-actions')</div>
    </div>

    <div class="content">
        @yield('content')
    </div>
</div>

@stack('scripts')
</body>
</html>
