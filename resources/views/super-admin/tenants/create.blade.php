@extends('layouts.super-admin')

@section('page-title', 'Create Tenant')
@section('breadcrumb', 'Register a new tenant on the platform')

@section('topbar-actions')
    <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-ghost">← Back to Tenants</a>
@endsection

@section('content')

<div class="card" style="max-width:720px">
    <div class="card-header">
        <span class="card-title">New Tenant</span>
    </div>
    <div class="card-body">

        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:1.25rem">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('super-admin.tenants.store') }}">
            @csrf
            <div class="form-grid">

                <div class="section-divider">Tenant Details</div>

                <div class="form-group">
                    <label class="form-label" for="name">Tenant Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        placeholder="Acme Corp Blog" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                        required autofocus>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="slug">Subdomain Slug *</label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug') }}"
                        placeholder="acme-corp" class="form-control {{ $errors->has('slug') ? 'is-invalid' : '' }}">
                    <span class="form-hint">Auto-generated from name if left blank. e.g. <em>acme-corp</em>.niralagam.com</span>
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="domain">Custom Domain</label>
                    <input type="text" id="domain" name="domain" value="{{ old('domain') }}"
                        placeholder="https://blog.acmecorp.com" class="form-control {{ $errors->has('domain') ? 'is-invalid' : '' }}">
                    <span class="form-hint">Optional — leave blank to use subdomain only</span>
                    @error('domain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="section-divider">Tenant Admin Account</div>

                <div class="form-group">
                    <label class="form-label" for="admin_name">Admin Name *</label>
                    <input type="text" id="admin_name" name="admin_name" value="{{ old('admin_name') }}"
                        placeholder="John Doe" class="form-control {{ $errors->has('admin_name') ? 'is-invalid' : '' }}"
                        required>
                    @error('admin_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="admin_email">Admin Email *</label>
                    <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email') }}"
                        placeholder="admin@acmecorp.com" class="form-control {{ $errors->has('admin_email') ? 'is-invalid' : '' }}"
                        required>
                    <span class="form-hint">They'll receive a login OTP on first access</span>
                    @error('admin_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-actions full">
                    <button type="submit" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Create Tenant
                    </button>
                    <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-ghost">Cancel</a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Auto-generate slug from name
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    let slugEdited = false;
    slugInput.addEventListener('input', () => slugEdited = true);
    nameInput.addEventListener('input', () => {
        if (slugEdited) return;
        slugInput.value = nameInput.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    });
</script>
@endpush
