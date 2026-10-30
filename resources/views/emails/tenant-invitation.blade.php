<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're invited to {{ $invitation->tenant->name }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">

                {{-- Header --}}
                <tr>
                    <td style="background:linear-gradient(135deg,#312e81,#4f46e5);padding:32px 40px;text-align:center;">
                        <div style="color:#fff;font-size:22px;font-weight:800;letter-spacing:-.02em;">Niralagam</div>
                        <div style="color:rgba(255,255,255,.65);font-size:12px;margin-top:4px;">Blog Platform</div>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="padding:36px 40px;text-align:center;">
                        <p style="font-size:17px;color:#0f172a;font-weight:700;margin:0 0 12px;">
                            Join {{ $invitation->tenant->name }}
                        </p>
                        <p style="font-size:15px;color:#475569;margin:0 0 28px;line-height:1.5;">
                            {{ $invitation->inviter?->name ?? 'A brand admin' }} invited you to join
                            <strong style="color:#0f172a;">{{ $invitation->tenant->name }}</strong>
                            as {{ in_array($invitation->role, ['admin', 'editor', 'author']) ? 'an' : 'a' }}
                            <strong style="color:#0f172a;">{{ $invitation->role }}</strong>.
                        </p>

                        <a href="{{ $url }}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 32px;border-radius:10px;">
                            Accept invitation
                        </a>

                        <p style="font-size:13px;color:#94a3b8;margin:28px 0 8px;">
                            This invitation expires on <strong style="color:#475569;">{{ $invitation->expires_at->format('M j, Y') }}</strong>
                            and only works for {{ $invitation->email }}.
                        </p>
                        <p style="font-size:13px;color:#94a3b8;margin:0;">If you weren't expecting this, you can safely ignore this email.</p>
                        <p style="font-size:12px;color:#cbd5e1;margin:20px 0 0;word-break:break-all;">{{ $url }}</p>
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
