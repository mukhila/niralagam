@php
    $tenant  = $post->tenant;
    $primary = $tenant->settings['theme']['primary_color'] ?? '#4f46e5';
    $accent  = $tenant->settings['theme']['accent_color'] ?? $primary;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->title }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">
                <tr>
                    <td style="background:{{ $primary }};padding:24px 36px;text-align:center;">
                        <div style="color:#fff;font-size:20px;font-weight:800;letter-spacing:-.02em;">{{ $tenant->name }}</div>
                        <div style="color:rgba(255,255,255,.8);font-size:13px;margin-top:4px;">
                            New post{{ $post->user ? ' from '.$post->user->name : '' }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px 36px;">
                        @if ($image)
                            <a href="{{ $url }}"><img src="{{ $image }}" alt="" width="448" style="width:100%;max-width:448px;height:auto;border-radius:10px;display:block;margin-bottom:16px;"></a>
                        @endif
                        <a href="{{ $url }}" style="font-size:20px;font-weight:800;color:#0f172a;text-decoration:none;line-height:1.3;">{{ $post->title }}</a>
                        <div style="font-size:12px;color:#94a3b8;margin:6px 0 10px;">
                            @if ($post->user && $authorUrl)<a href="{{ $authorUrl }}" style="color:#64748b;">{{ $post->user->name }}</a> · @endif{{ $post->published_at?->format('M j, Y') }}
                        </div>
                        <p style="font-size:14px;color:#475569;line-height:1.55;margin:0 0 20px;">{{ $summary }}</p>
                        <a href="{{ $url }}" style="display:inline-block;background:{{ $accent }};color:#fff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 28px;border-radius:10px;">Read the post</a>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:18px 36px;text-align:center;">
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 6px;">You follow {{ $tenant->name }}{{ $post->user ? ' or '.$post->user->name : '' }}, so we let you know about new posts.</p>
                        <a href="{{ $manageUrl }}" style="font-size:12px;color:#64748b;">Manage what you follow</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
