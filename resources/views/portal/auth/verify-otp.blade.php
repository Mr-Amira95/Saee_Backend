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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Verify Code') }} — Sa'ee Portal</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --red: #dc2626; --red-dark: #991b1b; --red-deep: #7f1d1d; --red-light: #ef4444;
            --bg: #07091a; --card-bg: rgba(7,10,28,0.9); --text: #f1f5f9;
            --text-sub: #94a3b8; --text-dim: #475569;
            --in-bg: rgba(255,255,255,0.035); --in-bdr: rgba(255,255,255,0.07);
        }
        html, body { height: 100%; min-height: 100vh; font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); }
        #cvs { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .blob { position: fixed; border-radius: 50%; pointer-events: none; z-index: 0; filter: blur(80px); }
        .blob-1 { width: 600px; height: 600px; background: radial-gradient(circle, rgba(185,28,28,0.2) 0%, transparent 65%); bottom: -200px; left: -120px; animation: bd 11s ease-in-out infinite alternate; }
        .blob-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(220,38,38,0.1) 0%, transparent 65%); top: -80px; right: 8%; animation: bd 8s ease-in-out infinite alternate-reverse; }
        @keyframes bd { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(50px,-50px) scale(1.15); } }
        .page { position: relative; z-index: 1; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
        .card { width: 100%; max-width: 440px; background: var(--card-bg); border: 1px solid rgba(220,38,38,0.1); border-radius: 22px; padding: 48px 42px; backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); box-shadow: 0 0 0 1px rgba(255,255,255,0.025), 0 32px 80px rgba(0,0,0,0.65); opacity: 0; transform: translateY(22px); animation: card-in .8s .1s cubic-bezier(0.16,1,0.3,1) forwards; }
        @keyframes card-in { to { opacity: 1; transform: translateY(0); } }
        .logo { margin-bottom: 28px; }
        .icon-wrap { width: 56px; height: 56px; background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); border-radius: 14px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; animation: icon-glow 3s ease-in-out infinite; }
        @keyframes icon-glow { 0%,100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); } 50% { box-shadow: 0 0 0 6px rgba(34,197,94,0.1); } }
        h1 { font-size: 1.65rem; font-weight: 800; color: #fff; letter-spacing: -.03em; margin-bottom: 8px; }
        .desc { color: var(--text-sub); font-size: .88rem; line-height: 1.55; margin-bottom: 28px; }
        .alert { border-radius: 11px; padding: 12px 16px; font-size: .84rem; margin-bottom: 20px; }
        .alert-err { background: rgba(220,38,38,0.08); border: 1px solid rgba(220,38,38,0.18); color: #fca5a5; animation: shake .45s cubic-bezier(0.36,0.07,0.19,0.97); }
        @keyframes shake { 0%,100% { transform: translateX(0); } 15%,45%,75% { transform: translateX(-5px); } 30%,60%,90% { transform: translateX(5px); } }
        .field { margin-bottom: 20px; }
        .field-label { display: block; font-size: .72rem; font-weight: 600; color: rgba(255,255,255,.5); letter-spacing: .07em; text-transform: uppercase; margin-bottom: 8px; }
        .otp-wrap { display: flex; gap: 10px; justify-content: center; }
        .otp-wrap input { width: 52px; height: 60px; text-align: center; font-size: 1.5rem; font-weight: 700; background: var(--in-bg); border: 1px solid var(--in-bdr); border-radius: 11px; color: #fff; font-family: 'Inter', sans-serif; outline: none; transition: border-color .2s, box-shadow .2s, background .2s; caret-color: transparent; }
        .otp-wrap input:focus { border-color: rgba(220,38,38,.4); background: rgba(220,38,38,.025); box-shadow: 0 0 0 3px rgba(220,38,38,.07); }
        .otp-wrap input.filled { border-color: rgba(220,38,38,.3); }
        .btn { width: 100%; padding: 14px 24px; background: linear-gradient(135deg, var(--red-deep) 0%, var(--red) 55%, #f87171 100%); background-size: 200% 200%; color: #fff; border: none; border-radius: 11px; font-size: .9rem; font-weight: 700; font-family: 'Inter', sans-serif; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; position: relative; overflow: hidden; transition: background-position .35s, transform .12s, box-shadow .2s; box-shadow: 0 4px 20px rgba(220,38,38,.3); letter-spacing: .015em; margin-bottom: 20px; }
        .btn:hover { background-position: 100% 100%; transform: translateY(-2px); box-shadow: 0 10px 32px rgba(220,38,38,.48); }
        .btn:active { transform: translateY(0); }
        .btn:disabled { pointer-events: none; opacity: .5; }
        .btn.loading { pointer-events: none; opacity: .75; }
        .spinner { width: 18px; height: 18px; display: none; border: 2px solid rgba(255,255,255,.25); border-top-color: #fff; border-radius: 50%; animation: spin .65s linear infinite; }
        .btn.loading .spinner { display: block; }
        .btn.loading .btn-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .back { display: flex; align-items: center; justify-content: center; gap: 7px; text-decoration: none; color: var(--text-sub); font-size: .84rem; font-weight: 500; transition: color .2s; }
        .back:hover { color: #fff; }
        .back svg { transition: transform .2s; }
        .back:hover svg { transform: translateX(-4px); }
        .divider { border: none; border-top: 1px solid rgba(255,255,255,0.06); margin: 22px 0; }
        .footer { text-align: center; color: var(--text-dim); font-size: .73rem; margin-top: 24px; }
        .f1 { animation: fu .5s .3s both; } .f2 { animation: fu .5s .38s both; } .f3 { animation: fu .5s .46s both; } .f4 { animation: fu .5s .54s both; }
        @keyframes fu { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 480px) { .card { padding: 32px 22px; border-radius: 16px; } .otp-wrap input { width: 44px; height: 52px; font-size: 1.3rem; } }

        /* ─── RTL Directional Overrides ──────────────────── */
        html[dir="rtl"] .back:hover svg { transform: translateX(4px); }

        /* ─── Language & Theme Switches ───────────────────── */
        .auth-switches { position: fixed; top: 20px; right: 20px; z-index: 60; display: flex; align-items: center; gap: 8px; }
        html[dir="rtl"] .auth-switches { right: auto; left: 20px; }
        .auth-switches .icon-btn { width: 36px; height: 36px; border-radius: 9px; background: var(--in-bg); border: 1px solid var(--in-bdr); display: flex; align-items: center; justify-content: center; color: var(--text-sub); cursor: pointer; transition: background .15s, color .15s; text-decoration: none; font-weight: 700; font-size: .78rem; }
        .auth-switches .icon-btn:hover { background: rgba(220,38,38,.1); color: var(--text); }

        /* ─── Light Theme Overrides ────────────────────────── */
        html.light-theme {
            --bg:       #f8fafc;
            --card-bg:  rgba(255, 255, 255, 0.85);
            --text:     #0f172a;
            --text-sub: #475569;
            --text-dim: #64748b;
            --in-bg:    rgba(15, 23, 42, 0.035);
            --in-bdr:   rgba(15, 23, 42, 0.09);
        }
        html.light-theme .card { box-shadow: 0 0 0 1px rgba(15,23,42,0.05), 0 32px 80px rgba(15,23,42,0.12); }
        html.light-theme h1 { color: var(--text); }
        html.light-theme .field-label { color: #0f172a; }
        html.light-theme .otp-wrap input { color: var(--text); }
        html.light-theme .divider { border-top-color: rgba(15,23,42,.09); }
    </style>
</head>
<body>

@include('portal.partials.mobile-block')

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

<canvas id="cvs"></canvas>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="page">
    <div class="card">

        <div class="logo">
            <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee" width="42" height="42" style="object-fit:contain;border-radius:10px;">
        </div>

        <div class="icon-wrap f1">
            <svg width="26" height="26" fill="none" stroke="#22c55e" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </div>

        <h1 class="f1">{{ __('Enter verification code') }}</h1>
        <p class="desc f1">
            {{ ($channel ?? 'whatsapp') === 'email'
                ? __('We sent a 6-digit code to your email. Enter it below to continue.')
                : __('We sent a 6-digit code to your WhatsApp. Enter it below to continue.') }}
        </p>

        @if ($errors->any())
            <div class="alert alert-err">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('portal.forgot-password.verify-otp.submit') }}" id="otpForm" novalidate>
            @csrf

            <div class="field f2">
                <label class="field-label">{{ __('Verification Code') }}</label>
                <div class="otp-wrap" id="otpWrap">
                    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]" autofocus>
                    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]">
                    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]">
                    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]">
                    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]">
                    <input type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]">
                </div>
                <input type="hidden" name="code" id="codeHidden">
            </div>

            <button type="submit" class="btn f3" id="submitBtn" disabled>
                <div class="spinner"></div>
                <span class="btn-text">{{ __('Verify Code') }}</span>
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
        </form>

        <hr class="divider">

        <a href="{{ route('portal.forgot-password') }}" class="back f4">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('Back — request a new code') }}
        </a>

        <p class="footer">&copy; {{ date('Y') }} {{ __('Sa\'ee LogisticsServices. All rights reserved.') }}</p>
    </div>
</div>

<script>
(function () {
    const cvs = document.getElementById('cvs');
    const ctx = cvs.getContext('2d');
    let W, H, pts = [];
    const N = 55, LINK = 115;
    function resize() { W = cvs.width = window.innerWidth; H = cvs.height = window.innerHeight; }
    resize(); window.addEventListener('resize', resize);
    class Pt {
        constructor() { this.x=Math.random()*W;this.y=Math.random()*H;this.vx=(Math.random()-.5)*.32;this.vy=(Math.random()-.5)*.32;this.r=Math.random()*1.1+.4;this.a=Math.random()*.3+.07; }
        step() { this.x+=this.vx;this.y+=this.vy;if(this.x<0||this.x>W)this.vx*=-1;if(this.y<0||this.y>H)this.vy*=-1; }
        draw() { const isLight = document.documentElement.classList.contains('light-theme'); ctx.beginPath();ctx.arc(this.x,this.y,this.r,0,Math.PI*2);ctx.fillStyle = isLight ? `rgba(15,23,42,${this.a})` : `rgba(255,255,255,${this.a})`;ctx.fill(); }
    }
    for(let i=0;i<N;i++) pts.push(new Pt());
    function loop() {
        ctx.clearRect(0,0,W,H);
        pts.forEach(p=>{p.step();p.draw();});
        for(let i=0;i<N;i++) for(let j=i+1;j<N;j++){
            const dx=pts[i].x-pts[j].x,dy=pts[i].y-pts[j].y,d=Math.sqrt(dx*dx+dy*dy);
            if(d<LINK){ctx.beginPath();ctx.moveTo(pts[i].x,pts[i].y);ctx.lineTo(pts[j].x,pts[j].y);ctx.strokeStyle=`rgba(220,38,38,${.065*(1-d/LINK)})`;ctx.lineWidth=.55;ctx.stroke();}
        }
        requestAnimationFrame(loop);
    }
    loop();

    /* ── OTP input behaviour ── */
    const inputs = Array.from(document.querySelectorAll('#otpWrap input'));
    const hidden = document.getElementById('codeHidden');
    const btn    = document.getElementById('submitBtn');

    function syncCode() {
        const val = inputs.map(i => i.value).join('');
        hidden.value = val;
        btn.disabled = val.length < 6;
    }

    inputs.forEach((inp, idx) => {
        inp.addEventListener('input', () => {
            inp.value = inp.value.replace(/\D/g, '').slice(-1);
            inp.classList.toggle('filled', inp.value !== '');
            if (inp.value && idx < inputs.length - 1) inputs[idx + 1].focus();
            syncCode();
        });
        inp.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !inp.value && idx > 0) {
                inputs[idx - 1].value = '';
                inputs[idx - 1].classList.remove('filled');
                inputs[idx - 1].focus();
                syncCode();
            }
        });
        inp.addEventListener('paste', e => {
            e.preventDefault();
            const digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
            digits.split('').forEach((d, i) => {
                if (inputs[i]) { inputs[i].value = d; inputs[i].classList.add('filled'); }
            });
            if (inputs[Math.min(digits.length, 5)]) inputs[Math.min(digits.length, 5)].focus();
            syncCode();
        });
    });

    document.getElementById('otpForm').addEventListener('submit', () => {
        syncCode();
        btn.classList.add('loading');
    });
})();

/* ── Theme toggle ─────────────────────────────── */
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
