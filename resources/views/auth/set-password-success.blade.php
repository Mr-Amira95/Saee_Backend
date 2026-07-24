<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <script>
        (function() {
            if (localStorage.getItem('theme') === 'light') {
                document.documentElement.classList.add('light-theme');
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Password Set') }} — Sa'ee Logistics</title>
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

        /* ─── Language & Theme Switches ───────────────────── */
        .auth-switches { position: fixed; top: 20px; right: 20px; z-index: 60; display: flex; align-items: center; gap: 8px; }
        html[dir="rtl"] .auth-switches { right: auto; left: 20px; }
        .auth-switches .icon-btn { width: 36px; height: 36px; border-radius: 9px; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.08); display: flex; align-items: center; justify-content: center; color: #94a3b8; cursor: pointer; transition: background .15s, color .15s; text-decoration: none; font-weight: 700; font-size: .78rem; }
        .auth-switches .icon-btn:hover { background: rgba(220,38,38,.1); color: #f1f5f9; }

        /* ─── Light Theme Overrides ────────────────────────── */
        html.light-theme body { background: #f8fafc; color: #0f172a; }
        html.light-theme .card { background: rgba(255,255,255,.85); border-color: rgba(15,23,42,.08); box-shadow: 0 32px 80px rgba(15,23,42,.12); }
        html.light-theme p { color: #475569; }
        html.light-theme .auth-switches .icon-btn { background: rgba(15,23,42,.035); border-color: rgba(15,23,42,.09); color: #475569; }
        html.light-theme .auth-switches .icon-btn:hover { color: #0f172a; }
    </style>
</head>
<body>

<div class="auth-switches">
    @if(app()->getLocale() === 'en')
        <a href="{{ route('lang.switch', 'ar') }}" class="icon-btn" title="تغيير اللغة إلى العربية">عربي</a>
    @else
        <a href="{{ route('lang.switch', 'en') }}" class="icon-btn" title="Switch to English">EN</a>
    @endif
    <button type="button" class="icon-btn" id="themeToggler" onclick="toggleTheme()" title="{{ __('Toggle Theme') }}">
        <svg id="themeMoon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
        <svg id="themeSun" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21m8.942-8.942h-2.25M4.313 12H2.063m15.122-6.938l-1.591 1.591M6.818 17.182l-1.591 1.591m12.94 0l-1.591-1.591M6.818 6.818L5.227 5.227M12 9a3 3 0 100 6 3 3 0 000-6z"/></svg>
    </button>
</div>

<div class="card">
    <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee Logistics" class="logo">
    <div class="check-wrap">
        <svg width="32" height="32" fill="none" stroke="#22c55e" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h1>{{ __('You\'re all set!') }}</h1>
    <p>{{ __('Your password has been created and your Sa\'ee Logistics account is now active. You can close this window.') }}</p>
</div>
<script>
function toggleTheme() {
    const isLight = document.documentElement.classList.toggle('light-theme');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    updateThemeIcons();
}
function updateThemeIcons() {
    const isLight = document.documentElement.classList.contains('light-theme');
    const sun  = document.getElementById('themeSun');
    const moon = document.getElementById('themeMoon');
    if (sun && moon) {
        sun.style.display  = isLight ? 'none'  : 'block';
        moon.style.display = isLight ? 'block' : 'none';
    }
}
document.addEventListener('DOMContentLoaded', updateThemeIcons);
</script>
</body>
</html>
