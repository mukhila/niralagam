<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm your subscription</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">
                <tr>
                    <td style="background:{{ $tenant->settings['theme']['primary_color'] ?? '#4f46e5' }};padding:32px 40px;text-align:center;">
                        <div style="color:#fff;font-size:22px;font-weight:800;letter-spacing:-.02em;">{{ $tenant->name }}</div>
                        <div style="color:rgba(255,255,255,.75);font-size:12px;margin-top:4px;">Newsletter</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 40px;text-align:center;">
                        <p style="font-size:17px;color:#0f172a;font-weight:700;margin:0 0 12px;">One click to confirm</p>
                        <p style="font-size:15px;color:#475569;margin:0 0 28px;line-height:1.5;">
                            Please confirm that you want the {{ $tenant->name }} newsletter at this address.
                            You'll get new posts by email, about once a week.
                        </p>
                        <a href="{{ $url }}" style="display:inline-block;background:{{ $tenant->settings['theme']['accent_color'] ?? '#4f46e5' }};color:#ffffff;text-decoration:none;font-weight:700;font-size:15px;padding:14px 32px;border-radius:10px;">
                            Yes, subscribe me
                        </a>
                        <p style="font-size:13px;color:#94a3b8;margin:28px 0 0;">If you didn't sign up, ignore this email and you won't hear from us.</p>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 40px;text-align:center;">
                        <p style="font-size:12px;color:#94a3b8;margin:0;">Sent by {{ $tenant->name }} on Niralagam</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
