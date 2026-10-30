@extends('layouts.super-admin')

@section('page-title', 'Tenants')
@section('breadcrumb', 'All tenants registered on the platform')

@section('topbar-actions')
    <a href="{{ route('super-admin.tenants.create') }}" class="btn btn-primary">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Tenant
    </a>
@endsection

@section('content')

    <div class="stats-row">
        <div class="stat-card purple">
            <div class="stat-label">Total Tenants</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Active</div>
            <div class="stat-value">{{ $stats['active'] }}</div>
        </div>
        <div class="stat-card slate">
            <div class="stat-label">Inactive / Suspended</div>
            <div class="stat-value">{{ $stats['inactive'] }}</div>
        </div>
        <div class="stat-card slate">
            <div class="stat-label">Total Users</div>
            <div class="stat-value">{{ $stats['users'] }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">All Tenants</span>
        </div>
        <div class="table-wrap">
            @if($tenants->isEmpty())
                <div style="padding:2.5rem;text-align:center;color:var(--text-muted);">
                    No tenants yet.
                    <a href="{{ route('super-admin.tenants.create') }}" style="color:var(--primary);font-weight:500;">Create the first one</a>
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Tenant</th>
                            <th>Slug / Domain</th>
                            <th>Status</th>
                            <th>Users</th>
                            <th>Created By</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenants as $tenant)
                        <tr>
                            <td>
                                <div style="font-weight:600">{{ $tenant->name }}</div>
                            </td>
                            <td style="color:var(--text-muted);font-size:.82rem">
                                <div>{{ $tenant->slug }}.niralagam.com</div>
                                @if($tenant->domain)
                                    <div style="color:var(--primary)">{{ $tenant->domain }}</div>
                                @endif
                            </td>
                            <td><span class="badge badge-{{ $tenant->status }}">{{ $tenant->status }}</span></td>
                            <td>{{ $tenant->users_count }}</td>
                            <td style="color:var(--text-muted);font-size:.82rem">{{ $tenant->creator?->name ?? '—' }}</td>
                            <td style="color:var(--text-muted);font-size:.8rem">{{ $tenant->created_at->format('M d, Y') }}</td>
                            <td>
                                <div style="display:flex;gap:.375rem">
                                    <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="btn btn-ghost btn-sm">View</a>
                                    <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="btn btn-ghost btn-sm">Edit</a>
                                    <form method="POST" action="{{ route('super-admin.tenants.destroy', $tenant) }}" onsubmit="return confirm('Delete this tenant?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($tenants->hasPages())
                    <div style="padding:1rem 1.25rem;border-top:1px solid var(--border)">{{ $tenants->links() }}</div>
                @endif
            @endif
        </div>
    </div>

@endsection
