@extends('layouts.admin')

@section('title', 'Categories')
@section('page-title', 'Categories')
@section('breadcrumb', 'Manage categories')

@section('content')

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:.75rem 1rem;border-radius:8px;margin-bottom:1.25rem;font-size:.875rem;">
        {{ session('success') }}
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;">

    {{-- List --}}
    <div class="card">
        <div class="table-wrapper">
            @if($categories->isEmpty())
                <div style="padding:2rem 1.25rem;color:var(--text-muted);font-size:.875rem;">No categories yet.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Posts</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $cat)
                        <tr>
                            <td>
                                <div style="font-weight:500">{{ $cat->name }}</div>
                                @if($cat->description)
                                    <div style="font-size:.78rem;color:var(--text-muted);margin-top:.1rem;">{{ Str::limit($cat->description, 60) }}</div>
                                @endif
                            </td>
                            <td style="color:var(--text-muted)">{{ $cat->posts_count }}</td>
                            <td style="white-space:nowrap">
                                <button onclick="openEdit({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description ?? '') }}')" class="btn btn-ghost btn-sm">Edit</button>
                                <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" style="display:inline" onsubmit="return confirm('Delete {{ $cat->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Create / Edit form --}}
    <div class="card" style="padding:1.25rem;">
        <div style="font-size:.9rem;font-weight:600;margin-bottom:1rem;" id="form-heading">New Category</div>

        @if($errors->any())
            <div style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;padding:.6rem .875rem;border-radius:8px;margin-bottom:1rem;font-size:.82rem;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" id="cat-form" action="{{ route('admin.categories.store') }}">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <input type="hidden" name="_id" id="form-id" value="">

            <div style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:1rem;">
                <label style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#374151;">Name</label>
                <input type="text" name="name" id="cat-name" class="form-control" placeholder="Category name" required
                    style="padding:.65rem .875rem;border:1.5px solid var(--border);border-radius:8px;font-size:.9rem;outline:none;width:100%;">
            </div>
            <div style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:1.25rem;">
                <label style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#374151;">Description</label>
                <textarea name="description" id="cat-desc" placeholder="Optional description…"
                    style="padding:.65rem .875rem;border:1.5px solid var(--border);border-radius:8px;font-size:.9rem;outline:none;width:100%;resize:vertical;min-height:80px;font-family:inherit;"></textarea>
            </div>

            <div style="display:flex;gap:.5rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;" id="submit-btn">Create</button>
                <button type="button" onclick="resetForm()" class="btn btn-ghost" id="cancel-btn" style="display:none;">Cancel</button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<style>
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,.1); background:#fff; }
</style>
<script>
function openEdit(id, name, desc) {
    document.getElementById('form-heading').textContent = 'Edit Category';
    document.getElementById('cat-name').value = name;
    document.getElementById('cat-desc').value = desc;
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('cat-form').action = '/admin/categories/' + id;
    document.getElementById('submit-btn').textContent = 'Update';
    document.getElementById('cancel-btn').style.display = '';
    document.getElementById('cat-name').focus();
}
function resetForm() {
    document.getElementById('form-heading').textContent = 'New Category';
    document.getElementById('cat-form').reset();
    document.getElementById('form-method').value = 'POST';
    document.getElementById('cat-form').action = '{{ route('admin.categories.store') }}';
    document.getElementById('submit-btn').textContent = 'Create';
    document.getElementById('cancel-btn').style.display = 'none';
}
</script>
@endpush

@endsection
