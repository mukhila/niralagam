@php
    $primary = $tenant->settings['theme']['primary_color'] ?? '#4f46e5';
    $accent  = $tenant->settings['theme']['accent_color'] ?? $primary;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">
                <tr>
                    <td style="background:{{ $primary }};padding:28px 36px;text-align:center;">
                        @if ($tenant->logo)
                            <img src="{{ $tenant->logo }}" alt="{{ $tenant->name }}" height="36" style="height:36px;max-width:200px;">
                        @else
                            <div style="color:#fff;font-size:22px;font-weight:800;letter-spacing:-.02em;">{{ $tenant->name }}</div>
                        @endif
                        <div style="color:rgba(255,255,255,.8);font-size:13px;margin-top:6px;">
                            {{ $posts->count() === 1 ? 'A new post for you' : $posts->count().' new posts for you' }}
                        </div>
                    </td>
                </tr>

                @foreach ($posts as $post)
                    @php
                        $link    = $siteUrl.'/blog/'.rawurlencode($post->slug).'?utm_source=newsletter&utm_medium=email';
                        $summary = $post->meta_description ?: $post->excerpt ?: \Illuminate\Support\Str::limit(trim(strip_tags($post->content)), 180);
                        $image   = $post->og_image ?: $post->featured_image;
                    @endphp
                    <tr>
                        <td style="padding:24px 36px 0;">
                            @if ($image && $loop->first)
                                <a href="{{ $link }}"><img src="{{ $image }}" alt="" width="488" style="width:100%;max-width:488px;height:auto;border-radius:10px;display:block;margin-bottom:14px;"></a>
                            @endif
                            <a href="{{ $link }}" style="font-size:{{ $loop->first ? '20px' : '17px' }};font-weight:800;color:#0f172a;text-decoration:none;line-height:1.3;">{{ $post->title }}</a>
                            <div style="font-size:12px;color:#94a3b8;margin:6px 0 8px;">
                                {{ $post->user?->name }}@if ($post->user) · @endif{{ $post->published_at->format('M j, Y') }}
                            </div>
                            <p style="font-size:14px;color:#475569;line-height:1.55;margin:0 0 10px;">{{ $summary }}</p>
                            <a href="{{ $link }}" style="font-size:14px;font-weight:700;color:{{ $accent }};text-decoration:none;">Read more →</a>
                            @unless ($loop->last)
                                <div style="border-top:1px solid #e2e8f0;margin-top:24px;"></div>
                            @endunless
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <td style="padding:28px 36px;text-align:center;">
                        <a href="{{ $siteUrl }}" style="display:inline-block;background:{{ $accent }};color:#fff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 28px;border-radius:10px;">Visit {{ $tenant->name }}</a>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 36px;text-align:center;">
                        <p style="font-size:12px;color:#94a3b8;margin:0 0 6px;">You're getting this because you subscribed to {{ $tenant->name }}.</p>
                        <a href="{{ $unsubscribeUrl }}" style="font-size:12px;color:#64748b;">Unsubscribe</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
