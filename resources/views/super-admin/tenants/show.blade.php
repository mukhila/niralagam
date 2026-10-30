@extends('layouts.super-admin')

@section('page-title', $tenant->name)
@section('breadcrumb', 'Tenant details and users')

@section('topbar-actions')
    <div style="display:flex;gap:.5rem">
        <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="btn btn-primary btn-sm">Edit Tenant</a>
        <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem">

    <div class="card">
        <div class="card-header"><span class="card-title">Tenant Info</span></div>
        <div class="card-body">
            <dl style="display:grid;grid-template-columns:auto 1fr;gap:.5rem 1rem;font-size:.875rem">
                <dt style="color:var(--text-muted);font-weight:600">Name</dt>
                <dd>{{ $tenant->name }}</dd>
                <dt style="color:var(--text-muted);font-weight:600">Slug</dt>
                <dd style="font-family:monospace">{{ $tenant->slug }}.niralagam.com</dd>
                @if($tenant->domain)
                <dt style="color:var(--text-muted);font-weight:600">Domain</dt>
                <dd><a href="{{ $tenant->domain }}" style="color:var(--primary)">{{ $tenant->domain }}</a></dd>
                @endif
                <dt style="color:var(--text-muted);font-weight:600">Status</dt>
                <dd><span class="badge badge-{{ $tenant->status }}">{{ $tenant->status }}</span></dd>
                <dt style="color:var(--text-muted);font-weight:600">Created by</dt>
                <dd>{{ $tenant->creator?->name ?? '—' }}</dd>
                <dt style="color:var(--text-muted);font-weight:600">Created at</dt>
                <dd>{{ $tenant->created_at->format('M d, Y H:i') }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Users ({{ $tenant->users->count() }})</span>
        </div>
        @if($tenant->users->isEmpty())
            <div style="padding:1.5rem;color:var(--text-muted);font-size:.875rem">No users yet.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th></tr></thead>
                    <tbody>
                        @foreach($tenant->users as $user)
                        <tr>
                            <td style="font-weight:500">{{ $user->name }}</td>
                            <td style="color:var(--text-muted);font-size:.82rem">{{ $user->email }}</td>
                            <td><span class="badge badge-active">{{ $user->pivot->role }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

@endsection
