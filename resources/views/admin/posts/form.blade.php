@extends('layouts.admin')

@section('title', isset($post) ? 'Edit Post' : 'New Post')
@section('page-title', isset($post) ? 'Edit Post' : 'New Post')
@section('breadcrumb', isset($post) ? $post->title : 'Create a new post')

@section('topbar-actions')
    <a href="{{ route('admin.posts.index') }}" class="btn btn-ghost btn-sm">← Back to Posts</a>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
    .form-grid { display: grid; grid-template-columns: 1fr 300px; gap: 1.5rem; align-items: start; }
    .form-group { display: flex; flex-direction: column; gap: .4rem; margin-bottom: 1.125rem; }
    .form-label { font-size: .75rem; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: .06em; }
    .form-control {
        width: 100%; padding: .65rem .875rem;
        border: 1.5px solid var(--border); border-radius: 8px;
        font-size: .9rem; color: var(--text); background: #f8fafc;
        outline: none; font-family: inherit;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79,70,229,.1); background: #fff; }
    .form-error { font-size: .78rem; color: var(--danger); margin-top: .25rem; }

    /* Quill editor */
    #quill-editor {
        min-height: 380px;
        font-size: .92rem;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #fff;
    }
    .ql-container { border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; border-color: var(--border) !important; }
    .ql-toolbar { border-top-left-radius: 8px; border-top-right-radius: 8px; border-color: var(--border) !important; background: #f8fafc; }
    .ql-toolbar.ql-snow + .ql-container.ql-snow { border-top: 0; }

    /* Featured image */
    .img-drop-zone {
        border: 2px dashed var(--border);
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: border-color .15s, background .15s;
        background: #f8fafc;
        position: relative;
    }
    .img-drop-zone:hover, .img-drop-zone.drag-over { border-color: var(--primary); background: rgba(79,70,229,.04); }
    .img-drop-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .img-drop-zone svg { color: #94a3b8; margin-bottom: .5rem; }
    .img-drop-zone p { font-size: .82rem; color: var(--text-muted); margin: 0; }
    .img-drop-zone strong { color: var(--primary); }
    .img-preview {
        width: 100%; aspect-ratio: 16/9; border-radius: 8px;
        object-fit: cover; display: block; margin-bottom: .75rem;
        border: 1px solid var(--border);
    }
    .img-uploading {
        display: none; align-items: center; justify-content: center;
        gap: .5rem; font-size: .82rem; color: var(--text-muted);
        padding: .5rem 0;
    }
    .spinner-sm {
        width: 14px; height: 14px; border: 2px solid #e2e8f0;
        border-top-color: var(--primary); border-radius: 50%;
        animation: spin .65s linear infinite; flex-shrink: 0;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .btn-remove-img {
        width: 100%; background: none; border: 1px solid #fecdd3;
        color: var(--danger); border-radius: 7px; padding: .4rem;
        font-size: .8rem; cursor: pointer; transition: all .15s;
    }
    .btn-remove-img:hover { background: #fff1f2; }

    /* Tag checkboxes */
    .tag-checkboxes { display: flex; flex-wrap: wrap; gap: .5rem; }
    .tag-cb label {
        display: flex; align-items: center; gap: .375rem;
        font-size: .82rem; cursor: pointer;
        background: var(--body-bg); border: 1px solid var(--border);
        border-radius: 20px; padding: .25rem .75rem;
        transition: all .15s; user-select: none;
    }
    .tag-cb input:checked + label { background: var(--primary); color: #fff; border-color: var(--primary); }
    .tag-cb input { display: none; }

    @media (max-width: 900px) { .form-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

@if($errors->any())
    <div style="background:#fff1f2;border:1px solid #fecdd3;color:#be123c;padding:.75rem 1rem;border-radius:8px;margin-bottom:1.25rem;font-size:.875rem;">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST" id="post-form"
    action="{{ isset($post) ? route('admin.posts.update', $post) : route('admin.posts.store') }}">
    @csrf
    @if(isset($post)) @method('PUT') @endif

    {{-- Hidden fields populated by JS --}}
    <input type="hidden" name="content" id="content-input" value="{{ old('content', $post->content ?? '') }}">
    <input type="hidden" name="featured_image" id="featured-image-input" value="{{ old('featured_image', $post->featured_image ?? '') }}">

    <div class="form-grid">

        {{-- ── Main column ── --}}
        <div>
            {{-- Title --}}
            <div class="card" style="padding:1.5rem 1.5rem 1rem;margin-bottom:1.25rem;">
                <div class="form-group" style="margin-bottom:0">
                    <input type="text" name="title" id="title-input"
                        style="width:100%;border:none;border-bottom:1.5px solid var(--border);border-radius:0;padding:.4rem 0;font-size:1.5rem;font-weight:700;color:var(--text);outline:none;background:transparent;font-family:inherit;"
                        value="{{ old('title', $post->title ?? '') }}"
                        placeholder="Post title…" required
                        onfocus="this.style.borderBottomColor='var(--primary)'"
                        onblur="this.style.borderBottomColor='var(--border)'">
                    @error('title')<div class="form-error" style="margin-top:.35rem">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Excerpt --}}
            <div class="card" style="padding:1.25rem 1.5rem;margin-bottom:1.25rem;">
                <div class="form-group" style="margin-bottom:0">
                    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:.4rem;">
                        <label class="form-label" style="margin-bottom:0">Excerpt</label>
                        <span id="excerpt-count" style="font-size:.72rem;color:var(--text-muted);">0 / 500</span>
                    </div>
                    <textarea name="excerpt" id="excerpt-field" class="form-control" style="min-height:72px;resize:vertical"
                        maxlength="500"
                        placeholder="Short summary shown in post listings…">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                </div>
            </div>

            {{-- Rich text editor --}}
            <div class="card" style="padding:1.25rem 1.5rem 1.5rem;margin-bottom:1.25rem;">
                <label class="form-label" style="display:block;margin-bottom:.75rem;">Content</label>
                <div id="quill-editor">{!! old('content', $post->content ?? '') !!}</div>
            </div>
        </div>

        {{-- ── Sidebar column ── --}}
        <div>

            {{-- Publish settings --}}
            <div class="card" style="padding:1.25rem;margin-bottom:1.25rem;">
                <div class="form-label" style="margin-bottom:.875rem;">Publish</div>

                <div class="form-group">
                    <label class="form-label" style="font-size:.7rem;">Status</label>
                    <select name="status" class="form-control">
                        @foreach(['draft' => 'Draft', 'pending' => 'Pending Review', 'published' => 'Published'] as $val => $label)
                            <option value="{{ $val }}" {{ old('status', $post->status ?? 'draft') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" style="font-size:.7rem;">Category</label>
                    <select name="category_id" class="form-control">
                        <option value="">— None —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $post->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Featured image --}}
            <div class="card" style="padding:1.25rem;margin-bottom:1.25rem;">
                <div class="form-label" style="margin-bottom:.875rem;">Featured Image</div>

                <div id="img-uploading" class="img-uploading">
                    <div class="spinner-sm"></div> Uploading…
                </div>

                <div id="img-preview-wrap" style="{{ isset($post) && $post->featured_image ? '' : 'display:none' }}">
                    <img id="img-preview" src="{{ $post->featured_image ?? '' }}" alt="" class="img-preview">
                    <button type="button" class="btn-remove-img" onclick="removeImage()">Remove image</button>
                </div>

                <div id="img-drop-zone" class="img-drop-zone" style="{{ isset($post) && $post->featured_image ? 'display:none' : '' }}">
                    <input type="file" id="img-file-input" accept="image/jpeg,image/png,image/webp,image/gif"
                        onchange="handleImageFile(this.files[0])">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto .5rem">
                        <rect x="3" y="3" width="18" height="18" rx="3"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21,15 16,10 5,21"/>
                    </svg>
                    <p><strong>Click to upload</strong> or drag &amp; drop</p>
                    <p style="margin-top:.25rem;font-size:.75rem;">JPG, PNG, WebP, GIF · max 8 MB</p>
                </div>
            </div>

            {{-- Tags --}}
            @if($tags->isNotEmpty())
            <div class="card" style="padding:1.25rem;margin-bottom:1.25rem;">
                <div class="form-label" style="margin-bottom:.75rem;">Tags</div>
                <div class="tag-checkboxes">
                    @foreach($tags as $tag)
                        <div class="tag-cb">
                            <input type="checkbox" id="tag_{{ $tag->id }}" name="tags[]" value="{{ $tag->id }}"
                                {{ in_array($tag->id, old('tags', isset($post) ? $post->tags->pluck('id')->toArray() : [])) ? 'checked' : '' }}>
                            <label for="tag_{{ $tag->id }}">{{ $tag->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:.8rem;font-size:.95rem;">
                {{ isset($post) ? 'Update Post' : 'Publish Post' }}
            </button>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
// ── Excerpt counter ──────────────────────────────────────────
const excerptField = document.getElementById('excerpt-field');
const excerptCount = document.getElementById('excerpt-count');
function updateCount() {
    const len = excerptField.value.length;
    excerptCount.textContent = len + ' / 500';
    excerptCount.style.color = len >= 480 ? '#dc2626' : len >= 400 ? '#d97706' : 'var(--text-muted)';
}
excerptField.addEventListener('input', updateCount);
updateCount();

// ── Quill setup ──────────────────────────────────────────────
const quill = new Quill('#quill-editor', {
    theme: 'snow',
    placeholder: 'Write your post content here…',
    modules: {
        toolbar: {
            container: [
                [{ header: [2, 3, 4, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'code-block'],
                ['link', 'image'],
                [{ align: [] }],
                ['clean'],
            ],
            handlers: { image: imageHandler },
        },
    },
});

// Sync quill HTML → hidden input before submit
document.getElementById('post-form').addEventListener('submit', () => {
    document.getElementById('content-input').value = quill.getSemanticHTML();
});

// ── Quill image handler — uploads to /admin/upload-image ─────
function imageHandler() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/jpeg,image/png,image/webp,image/gif';
    input.onchange = () => { if (input.files[0]) uploadAndInsert(input.files[0]); };
    input.click();
}

async function uploadAndInsert(file) {
    const url = await uploadImage(file);
    if (url) {
        const range = quill.getSelection(true);
        quill.insertEmbed(range.index, 'image', url);
        quill.setSelection(range.index + 1);
    }
}

// ── Featured image ───────────────────────────────────────────
const dropZone = document.getElementById('img-drop-zone');

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) handleImageFile(file);
});

async function handleImageFile(file) {
    dropZone.style.display = 'none';
    document.getElementById('img-uploading').style.display = 'flex';

    const url = await uploadImage(file);

    document.getElementById('img-uploading').style.display = 'none';

    if (url) {
        document.getElementById('featured-image-input').value = url;
        document.getElementById('img-preview').src = url;
        document.getElementById('img-preview-wrap').style.display = '';
    } else {
        dropZone.style.display = '';
        alert('Upload failed. Please try again.');
    }
}

function removeImage() {
    document.getElementById('featured-image-input').value = '';
    document.getElementById('img-preview').src = '';
    document.getElementById('img-preview-wrap').style.display = 'none';
    document.getElementById('img-drop-zone').style.display = '';
    document.getElementById('img-file-input').value = '';
}

// ── Shared upload helper ─────────────────────────────────────
async function uploadImage(file) {
    const fd = new FormData();
    fd.append('image', file);
    fd.append('_token', '{{ csrf_token() }}');
    try {
        const res = await fetch('{{ route('admin.upload.image') }}', { method: 'POST', body: fd });
        if (!res.ok) return null;
        const data = await res.json();
        return data.url ?? null;
    } catch {
        return null;
    }
}
</script>
@endpush
