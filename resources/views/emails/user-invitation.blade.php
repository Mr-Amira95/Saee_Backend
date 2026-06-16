<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome to Sa'ee Logistics</title>
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

                {{-- ── Role badge strip ─────────────────── --}}
                <tr>
                    <td style="background:#dc2626;padding:10px 40px;text-align:center;">
                        <span style="display:inline-block;background:rgba(255,255,255,0.18);color:#fff;font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;padding:4px 16px;border-radius:100px;">
                            {{ $roleLabel }} Account
                        </span>
                    </td>
                </tr>

                {{-- ── Body ─────────────────────────────── --}}
                <tr>
                    <td style="background:#ffffff;padding:44px 40px 36px;">

                        {{-- Greeting --}}
                        <h1 style="margin:0 0 6px;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px;">
                            Welcome, {{ explode(' ', $user->name)[0] }}! 👋
                        </h1>
                        <p style="margin:0 0 28px;font-size:15px;color:#64748b;line-height:1.6;">
                            Your <strong style="color:#0f172a;">Sa'ee Logistics</strong> {{ strtolower($roleLabel) }} account has been created by our team.
                            You're one step away from getting started.
                        </p>

                        {{-- Divider --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                            <tr>
                                <td style="border-top:1px solid #f1f5f9;font-size:0;line-height:0;">&nbsp;</td>
                            </tr>
                        </table>

                        {{-- Account details card --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:32px;">
                            <tr>
                                <td style="padding:20px 24px;">
                                    <p style="margin:0 0 4px;font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:1.5px;text-transform:uppercase;">Account Details</p>
                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:12px;">
                                        <tr>
                                            <td style="padding:6px 0;font-size:13px;color:#64748b;width:90px;">Name</td>
                                            <td style="padding:6px 0;font-size:13px;font-weight:600;color:#0f172a;">{{ $user->name }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px 0;font-size:13px;color:#64748b;border-top:1px solid #e2e8f0;">Email</td>
                                            <td style="padding:6px 0;font-size:13px;font-weight:600;color:#0f172a;border-top:1px solid #e2e8f0;">{{ $user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px 0;font-size:13px;color:#64748b;border-top:1px solid #e2e8f0;">Role</td>
                                            <td style="padding:6px 0;border-top:1px solid #e2e8f0;">
                                                <span style="display:inline-block;background:#fef2f2;color:#dc2626;font-size:11px;font-weight:700;padding:2px 10px;border-radius:100px;letter-spacing:0.5px;">
                                                    {{ $roleLabel }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        {{-- CTA --}}
                        <p style="margin:0 0 16px;font-size:14px;color:#475569;line-height:1.6;">
                            Click the button below to set your password and activate your account.
                            This link is valid for <strong style="color:#0f172a;">24 hours</strong>.
                        </p>

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom:32px;">
                            <tr>
                                <td style="border-radius:12px;background:linear-gradient(135deg,#dc2626,#991b1b);box-shadow:0 4px 20px rgba(220,38,38,0.35);">
                                    <a
                                        href="{{ $setPasswordUrl }}"
                                        style="display:inline-block;padding:15px 36px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;letter-spacing:0.3px;border-radius:12px;"
                                    >
                                        Set My Password &rarr;
                                    </a>
                                </td>
                            </tr>
                        </table>

                        {{-- Fallback URL --}}
                        <p style="margin:0 0 4px;font-size:12px;color:#94a3b8;">
                            Button not working? Copy and paste this link into your browser:
                        </p>
                        <p style="margin:0 0 28px;font-size:11px;color:#64748b;word-break:break-all;line-height:1.6;">
                            <a href="{{ $setPasswordUrl }}" style="color:#dc2626;text-decoration:none;">{{ $setPasswordUrl }}</a>
                        </p>

                        {{-- Divider --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                            <tr>
                                <td style="border-top:1px solid #f1f5f9;font-size:0;line-height:0;">&nbsp;</td>
                            </tr>
                        </table>

                        {{-- Security note --}}
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px 18px;">
                                    <p style="margin:0;font-size:12.5px;color:#92400e;line-height:1.6;">
                                        <strong>🔒 Security Notice:</strong>
                                        If you did not expect this email, please ignore it or contact our support team. Your account will remain inactive until a password is set.
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                {{-- ── Footer ───────────────────────────── --}}
                <tr>
                    <td style="background:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 18px 18px;padding:28px 40px;text-align:center;">
                        <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#0f172a;">Sa'ee Logistic Services</p>
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
