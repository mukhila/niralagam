<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Login Code</title>
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
                        <p style="font-size:15px;color:#475569;margin:0 0 24px;">Your one-time login code is:</p>

                        <div style="display:inline-block;background:#f1f5f9;border:2px dashed #c7d2fe;border-radius:12px;padding:18px 36px;margin-bottom:28px;">
                            <span style="font-size:38px;font-weight:800;letter-spacing:10px;color:#4f46e5;font-variant-numeric:tabular-nums;">{{ $otp }}</span>
                        </div>

                        <p style="font-size:13px;color:#94a3b8;margin:0 0 8px;">This code expires in <strong style="color:#475569;">10 minutes</strong>.</p>
                        <p style="font-size:13px;color:#94a3b8;margin:0;">If you didn't request this, you can safely ignore this email.</p>
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
