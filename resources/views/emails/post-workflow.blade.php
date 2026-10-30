@php
    $copy = [
        'submitted' => ['heading' => 'A post is waiting for review',  'button' => 'Review post',
                        'line' => "{$actor->name} submitted this post for review on {$brand}."],
        'published' => ['heading' => 'Your post is live',             'button' => 'View post',
                        'line' => "{$actor->name} approved and published your post on {$brand}."],
        'scheduled' => ['heading' => 'Your post is scheduled',        'button' => 'Open dashboard',
                        'line' => "{$actor->name} approved your post. It goes live on ".$post->published_at?->format('M j, Y \a\t g:i A').'.'],
        'rejected'  => ['heading' => 'Changes requested on your post', 'button' => 'Open dashboard',
                        'line' => "{$actor->name} sent your post back with a note. Edit it and submit it again when ready."],
    ][$event];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $copy['heading'] }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">

                {{-- Header --}}
                <tr>
                    <td style="background:linear-gradient(135deg,#312e81,#4f46e5);padding:32px 40px;text-align:center;">
                        <div style="color:#fff;font-size:22px;font-weight:800;letter-spacing:-.02em;">{{ $brand }}</div>
                        <div style="color:rgba(255,255,255,.65);font-size:12px;margin-top:4px;">on Niralagam</div>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="padding:36px 40px;">
                        <p style="font-size:17px;color:#0f172a;font-weight:700;margin:0 0 12px;">{{ $copy['heading'] }}</p>
                        <p style="font-size:15px;color:#475569;margin:0 0 20px;line-height:1.5;">{{ $copy['line'] }}</p>

                        <div style="border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;margin:0 0 20px;">
                            <div style="font-size:15px;font-weight:700;color:#0f172a;">{{ $post->title }}</div>
                            @if ($post->excerpt)
                                <div style="font-size:13px;color:#64748b;margin-top:6px;line-height:1.4;">{{ \Illuminate\Support\Str::limit($post->excerpt, 160) }}</div>
                            @endif
                        </div>

                        @if ($note)
                            <div style="background:#fff7ed;border-left:3px solid #f97316;border-radius:6px;padding:12px 14px;margin:0 0 24px;">
                                <div style="font-size:12px;font-weight:700;color:#9a3412;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;">Note from {{ $actor->name }}</div>
                                <div style="font-size:14px;color:#431407;line-height:1.5;white-space:pre-line;">{{ $note }}</div>
                            </div>
                        @endif

                        <div style="text-align:center;">
                            <a href="{{ $url }}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 32px;border-radius:10px;">
                                {{ $copy['button'] }}
                            </a>
                        </div>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 40px;text-align:center;">
                        <p style="font-size:12px;color:#94a3b8;margin:0;">© {{ date('Y') }} Niralagam &mdash; Multi-tenant Blog Platform</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
