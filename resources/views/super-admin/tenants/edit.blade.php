@extends('layouts.super-admin')

@section('page-title', 'Edit Tenant')
@section('breadcrumb', 'Update tenant details')

@section('topbar-actions')
    <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-ghost">← Back</a>
@endsection

@section('content')

<div class="card" style="max-width:720px">
    <div class="card-header">
        <span class="card-title">{{ $tenant->name }}</span>
        <span class="badge badge-{{ $tenant->status }}">{{ $tenant->status }}</span>
    </div>
    <div class="card-body">

        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:1.25rem">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            </div>
        @endif

        <form method="POST" action="{{ route('super-admin.tenants.update', $tenant) }}">
            @csrf @method('PUT')
            <div class="form-grid">

                <div class="form-group">
                    <label class="form-label" for="name">Tenant Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $tenant->name) }}"
                        class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="slug">Subdomain Slug *</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $tenant->slug) }}"
                        class="form-control {{ $errors->has('slug') ? 'is-invalid' : '' }}" required>
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="domain">Custom Domain</label>
                    <input type="text" id="domain" name="domain" value="{{ old('domain', $tenant->domain) }}"
                        placeholder="https://blog.example.com" class="form-control {{ $errors->has('domain') ? 'is-invalid' : '' }}">
                    @error('domain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        @foreach(['active','inactive','suspended'] as $s)
                            <option value="{{ $s }}" {{ old('status', $tenant->status) === $s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-actions full">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-ghost">Cancel</a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection
