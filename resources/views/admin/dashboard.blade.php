@extends('layouts.admin')

@section('page-title', 'Dashboard')
@section('breadcrumb', 'Overview of your blog')

@section('topbar-actions')
    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Post
    </a>
@endsection

@section('content')

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card indigo">
            <div class="stat-label">Total Posts</div>
            <div class="stat-value">{{ $stats['posts'] }}</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Published</div>
            <div class="stat-value">{{ $stats['published'] }}</div>
        </div>
        <div class="stat-card amber">
            <div class="stat-label">Drafts</div>
            <div class="stat-value">{{ $stats['drafts'] }}</div>
        </div>
        <div class="stat-card slate">
            <div class="stat-label">Categories</div>
            <div class="stat-value">{{ $stats['categories'] }}</div>
        </div>
        <div class="stat-card slate">
            <div class="stat-label">Tags</div>
            <div class="stat-value">{{ $stats['tags'] }}</div>
        </div>
    </div>

    {{-- Recent posts table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Posts</span>
            <a href="{{ route('admin.posts.index') }}" class="btn btn-ghost btn-sm">View all</a>
        </div>
        <div class="table-wrapper">
            @if($recentPosts->isEmpty())
                <div style="padding:2rem 1.25rem; color:var(--text-muted); font-size:.875rem;">No posts yet.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentPosts as $post)
                        <tr>
                            <td style="font-weight:500">{{ $post->title }}</td>
                            <td style="color:var(--text-muted)">{{ $post->category?->name ?? '—' }}</td>
                            <td><span class="badge badge-{{ $post->status }}">{{ $post->status }}</span></td>
                            <td style="color:var(--text-muted);font-size:.8rem">
                                {{ $post->created_at->format('M d, Y') }}
                            </td>
                            <td>
                                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-ghost btn-sm">Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

@endsection
