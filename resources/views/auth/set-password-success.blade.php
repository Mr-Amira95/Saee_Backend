<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Set — Sa'ee Logistics</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Inter', sans-serif; background: #080c1e; color: #f1f5f9; padding: 24px; }
        .card {
            max-width: 420px; width: 100%; background: rgba(12,18,48,.85);
            border: 1px solid rgba(255,255,255,.07); border-radius: 22px; padding: 44px 40px;
            backdrop-filter: blur(20px); text-align: center;
            animation: card-in .5s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes card-in { from{opacity:0;transform:translateY(24px) scale(.97);} to{opacity:1;transform:none;} }
        .check-wrap {
            width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 24px;
            background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.25);
            display: flex; align-items: center; justify-content: center;
            animation: pop .5s .2s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes pop { from{transform:scale(.5);opacity:0;} to{transform:scale(1);opacity:1;} }
        img.logo { height: 44px; object-fit: contain; margin-bottom: 28px; filter: drop-shadow(0 2px 10px rgba(0,0,0,.4)); }
        h1 { font-size: 1.5rem; font-weight: 800; letter-spacing: -.02em; margin-bottom: 10px; }
        p { font-size: .875rem; color: #94a3b8; line-height: 1.6; margin-bottom: 32px; }
    </style>
</head>
<body>
<div class="card">
    <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee Logistics" class="logo">
    <div class="check-wrap">
        <svg width="32" height="32" fill="none" stroke="#22c55e" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h1>You're all set!</h1>
    <p>Your password has been created and your Sa'ee Logistics account is now active. You can close this window.</p>
</div>
</body>
</html>
