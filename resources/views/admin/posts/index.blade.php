@extends('layouts.admin')

@section('title', 'Posts')
@section('page-title', 'Posts')
@section('breadcrumb', 'All posts')

@section('topbar-actions')
    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Post
    </a>
@endsection

@section('content')

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:.75rem 1rem;border-radius:8px;margin-bottom:1.25rem;font-size:.875rem;">
        {{ session('success') }}
    </div>
@endif

<div class="card">
    <div class="table-wrapper">
        @if($posts->isEmpty())
            <div style="padding:2rem 1.25rem;color:var(--text-muted);font-size:.875rem;">No posts yet. <a href="{{ route('admin.posts.create') }}" style="color:var(--primary);">Create one →</a></div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posts as $post)
                    <tr>
                        <td style="font-weight:500;max-width:320px;">{{ $post->title }}</td>
                        <td style="color:var(--text-muted);font-size:.82rem;">{{ $post->user?->name ?? '—' }}</td>
                        <td style="color:var(--text-muted)">{{ $post->category?->name ?? '—' }}</td>
                        <td><span class="badge badge-{{ $post->status }}">{{ $post->status }}</span></td>
                        <td style="color:var(--text-muted);font-size:.8rem;white-space:nowrap">{{ $post->created_at->format('M d, Y') }}</td>
                        <td style="white-space:nowrap">
                            <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-ghost btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" style="display:inline" onsubmit="return confirm('Delete this post?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($posts->hasPages())
                <div style="padding:1rem 1.25rem;">{{ $posts->links() }}</div>
            @endif
        @endif
    </div>
</div>

@endsection
