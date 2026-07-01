<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Password Reset Code</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Segoe UI',Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:40px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;width:100%;">

                {{-- ── Header ─────────────────────────────── --}}
                <tr>
                    <td style="background:linear-gradient(135deg,#7f1d1d 0%,#991b1b 40%,#dc2626 100%);border-radius:18px 18px 0 0;padding:36px 40px;text-align:center;">
                        <img
                            src="{{ asset('saee_logo_dark.png') }}"
                            alt="Sa'ee Logistics"
                            width="160"
                            style="display:block;margin:0 auto;max-width:160px;height:auto;"
                        >
                        <p style="margin:18px 0 0;font-size:13px;color:rgba(255,255,255,0.55);letter-spacing:2px;text-transform:uppercase;font-weight:600;">
                            Logistics &amp; Delivery Platform
                        </p>
                    </td>
                </tr>

                {{-- ── Body ─────────────────────────────── --}}
                <tr>
                    <td style="background:#ffffff;padding:44px 40px 36px;">

                        <h1 style="margin:0 0 6px;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;">
                            Password Reset Code
                        </h1>
                        <p style="margin:0 0 28px;font-size:15px;color:#64748b;line-height:1.6;">
                            Hi {{ explode(' ', $user->name)[0] }}, use the code below to reset your
                            <strong style="color:#0f172a;">Sa'ee Logistics</strong> account password.
                        </p>

                        {{-- Code card --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:28px;">
                            <tr>
                                <td style="padding:28px 24px;text-align:center;">
                                    <p style="margin:0 0 10px;font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:1.5px;text-transform:uppercase;">Verification Code</p>
                                    <p style="margin:0;font-size:38px;font-weight:800;color:#dc2626;letter-spacing:10px;">{{ $code }}</p>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 28px;font-size:14px;color:#475569;line-height:1.6;">
                            This code expires in <strong style="color:#0f172a;">5 minutes</strong>. Enter it where you requested the password reset.
                        </p>

                        {{-- Security note --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px 18px;">
                                    <p style="margin:0;font-size:12.5px;color:#92400e;line-height:1.6;">
                                        <strong>🔒 Security Notice:</strong>
                                        If you did not request this code, please ignore this email — your password will remain unchanged.
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                {{-- ── Footer ───────────────────────────── --}}
                <tr>
                    <td style="background:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 18px 18px;padding:28px 40px;text-align:center;">
                        <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#0f172a;">Sa'ee LogisticsServices</p>
                        <p style="margin:0 0 16px;font-size:12px;color:#94a3b8;">
                            This email was sent to <strong>{{ $user->email }}</strong>
                        </p>
                        <p style="margin:0;font-size:11px;color:#cbd5e1;line-height:1.6;">
                            &copy; {{ date('Y') }} Sa'ee Logistics. All rights reserved.<br>
                            This is an automated message — please do not reply.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
