@extends('layouts.admin')

@section('title', 'Tags')
@section('page-title', 'Tags')
@section('breadcrumb', 'Manage tags')

@section('content')

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:.75rem 1rem;border-radius:8px;margin-bottom:1.25rem;font-size:.875rem;">
        {{ session('success') }}
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">

    {{-- Tags grid --}}
    <div class="card" style="padding:1.25rem;">
        @if($tags->isEmpty())
            <div style="color:var(--text-muted);font-size:.875rem;">No tags yet.</div>
        @else
            <div style="display:flex;flex-wrap:wrap;gap:.625rem;">
                @foreach($tags as $tag)
                <div style="display:flex;align-items:center;gap:.375rem;background:#f1f5f9;border:1px solid var(--border);border-radius:20px;padding:.3rem .5rem .3rem .875rem;font-size:.85rem;">
                    <span style="font-weight:500">{{ $tag->name }}</span>
                    <span style="color:var(--text-muted);font-size:.75rem;">({{ $tag->posts_count }})</span>
                    <button onclick="openEdit({{ $tag->id }}, '{{ addslashes($tag->name) }}')"
                        style="background:none;border:none;cursor:pointer;color:var(--primary);padding:.1rem .25rem;font-size:.75rem;line-height:1;">✎</button>
                    <form method="POST" action="{{ route('admin.tags.destroy', $tag) }}" style="display:inline" onsubmit="return confirm('Delete tag?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--danger);padding:.1rem .25rem;font-size:.75rem;line-height:1;">✕</button>
                    </form>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Create / Edit form --}}
    <div class="card" style="padding:1.25rem;">
        <div style="font-size:.9rem;font-weight:600;margin-bottom:1rem;" id="form-heading">New Tag</div>

        @if($errors->any())
            <div style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;padding:.6rem .875rem;border-radius:8px;margin-bottom:1rem;font-size:.82rem;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" id="tag-form" action="{{ route('admin.tags.store') }}">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">

            <div style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:1rem;">
                <label style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#374151;">Tag Name</label>
                <input type="text" name="name" id="tag-name"
                    style="padding:.65rem .875rem;border:1.5px solid var(--border);border-radius:8px;font-size:.9rem;outline:none;width:100%;"
                    placeholder="e.g. Technology" required>
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
    input:focus { border-color: var(--primary) !important; box-shadow: 0 0 0 3px rgba(79,70,229,.1); background:#fff; }
</style>
<script>
function openEdit(id, name) {
    document.getElementById('form-heading').textContent = 'Edit Tag';
    document.getElementById('tag-name').value = name;
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('tag-form').action = '/admin/tags/' + id;
    document.getElementById('submit-btn').textContent = 'Update';
    document.getElementById('cancel-btn').style.display = '';
    document.getElementById('tag-name').focus();
}
function resetForm() {
    document.getElementById('form-heading').textContent = 'New Tag';
    document.getElementById('tag-form').reset();
    document.getElementById('form-method').value = 'POST';
    document.getElementById('tag-form').action = '{{ route('admin.tags.store') }}';
    document.getElementById('submit-btn').textContent = 'Create';
    document.getElementById('cancel-btn').style.display = 'none';
}
</script>
@endpush

@endsection
