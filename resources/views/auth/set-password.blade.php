<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Set Your Password') }} — Sa'ee Logistics</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --red: #dc2626; --red-dark: #991b1b; --red-deep: #7f1d1d;
            --red-lt: #ef4444; --red-glow: rgba(220,38,38,.4);
            --bg: #080c1e; --bg-2: #0c1230;
            --card: rgba(12,18,48,.85);
            --bdr: rgba(255,255,255,.07);
            --text: #f1f5f9; --text-sub: #94a3b8; --text-dim: #475569;
            --in-bg: rgba(255,255,255,.04); --in-bdr: rgba(255,255,255,.09);
        }
        html, body { height: 100%; font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); overflow-x: hidden; }

        /* ── Background ─────────────────────── */
        .bg-wrap { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
        .bg-wrap canvas { position: absolute; inset: 0; }
        .glow-blob {
            position: absolute; border-radius: 50%; filter: blur(90px); pointer-events: none;
            animation: blob-float 8s ease-in-out infinite;
        }
        .glow-blob-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(127,29,29,.5), transparent 70%); top: -100px; left: -100px; }
        .glow-blob-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(220,38,38,.2), transparent 70%); bottom: -80px; right: -80px; animation-delay: -4s; }
        @keyframes blob-float { 0%,100%{transform:translate(0,0);} 50%{transform:translate(30px,20px);} }

        /* ── Grid overlay ───────────────────── */
        .grid-overlay {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image: linear-gradient(rgba(220,38,38,.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(220,38,38,.04) 1px, transparent 1px);
            background-size: 48px 48px;
            animation: grid-drift 25s linear infinite;
        }
        @keyframes grid-drift { from { background-position: 0 0; } to { background-position: 48px 48px; } }

        /* ── Page layout ────────────────────── */
        .page {
            position: relative; z-index: 1; min-height: 100vh;
            display: flex; align-items: center; justify-content: center; padding: 32px 16px;
        }

        /* ── Card ───────────────────────────── */
        .card {
            width: 100%; max-width: 460px;
            background: var(--card); border: 1px solid var(--bdr);
            border-radius: 22px; padding: 40px 40px 36px;
            backdrop-filter: blur(24px);
            box-shadow: 0 32px 80px rgba(0,0,0,.55), 0 0 0 1px rgba(255,255,255,.04), inset 0 1px 0 rgba(255,255,255,.07);
            animation: card-in .5s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes card-in { from { opacity:0; transform: translateY(28px) scale(.97); } to { opacity:1; transform: none; } }

        /* ── Logo ───────────────────────────── */
        .logo-wrap { text-align: center; margin-bottom: 28px; }
        .logo-wrap img { height: 54px; object-fit: contain; filter: drop-shadow(0 2px 12px rgba(0,0,0,.4)); }

        /* ── Badge ──────────────────────────── */
        .badge {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(220,38,38,.12); border: 1px solid rgba(220,38,38,.22);
            border-radius: 100px; padding: 5px 14px; font-size: .75rem; font-weight: 600;
            color: #fca5a5; margin-bottom: 16px;
        }
        .badge-dot { width: 7px; height: 7px; border-radius: 50%; background: #ef4444; animation: pulse-dot 2s ease infinite; }
        @keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:.6;transform:scale(.75);} }

        /* ── Heading ────────────────────────── */
        h1 { font-size: 1.6rem; font-weight: 800; letter-spacing: -.03em; margin-bottom: 6px; }
        .sub { font-size: .855rem; color: var(--text-sub); margin-bottom: 30px; line-height: 1.55; }

        /* ── Form ───────────────────────────── */
        .field { margin-bottom: 18px; }
        .field-label { display: block; font-size: .7rem; font-weight: 700; color: rgba(255,255,255,.45); letter-spacing: .09em; text-transform: uppercase; margin-bottom: 7px; }
        .field-wrap { position: relative; }
        .field-input {
            width: 100%; padding: 12px 44px 12px 44px; background: var(--in-bg);
            border: 1px solid var(--in-bdr); border-radius: 11px; color: var(--text);
            font-size: .9rem; font-family: inherit; outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .field-input:focus { border-color: rgba(220,38,38,.4); box-shadow: 0 0 0 3px rgba(220,38,38,.08); }
        .field-icon { position: absolute; left: 14px; top: 50%; translate: 0 -50%; color: rgba(255,255,255,.22); pointer-events: none; }
        .pwd-btn { position: absolute; right: 12px; top: 50%; translate: 0 -50%; background: none; border: none; color: rgba(255,255,255,.3); cursor: pointer; padding: 4px; transition: color .15s; }
        .pwd-btn:hover { color: rgba(255,255,255,.7); }

        /* Error */
        .error-msg { font-size: .76rem; color: #f87171; margin-top: 5px; display: flex; align-items: center; gap: 5px; }
        .field-input.has-error { border-color: rgba(239,68,68,.45); }
        .alert-err {
            background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.2);
            border-radius: 11px; padding: 12px 16px; margin-bottom: 20px;
            font-size: .83rem; color: #fca5a5; display: flex; align-items: flex-start; gap: 9px;
        }

        /* ── Strength bar ───────────────────── */
        .strength-wrap { margin-top: 8px; }
        .strength-bar { height: 3px; border-radius: 2px; background: var(--in-bdr); overflow: hidden; }
        .strength-fill { height: 100%; width: 0; border-radius: 2px; transition: width .3s, background .3s; }
        .strength-label { font-size: .7rem; color: var(--text-dim); margin-top: 4px; }

        /* ── Submit button ──────────────────── */
        .btn {
            width: 100%; padding: 13px; margin-top: 8px;
            background: linear-gradient(135deg, var(--red-dark), var(--red));
            border: none; border-radius: 11px; color: white;
            font-size: .9rem; font-weight: 700; font-family: inherit; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: opacity .15s, box-shadow .15s;
            box-shadow: 0 4px 20px rgba(220,38,38,.3);
            position: relative; overflow: hidden;
        }
        .btn::before { content:''; position:absolute; inset:0; background: linear-gradient(135deg,rgba(255,255,255,.1),transparent); }
        .btn:hover { opacity: .92; box-shadow: 0 6px 28px rgba(220,38,38,.4); }
        .btn:active { opacity: .85; }

        /* ── Requirements ───────────────────── */
        .reqs { margin-top: 20px; padding: 14px 16px; background: rgba(255,255,255,.02); border: 1px solid var(--bdr); border-radius: 10px; }
        .reqs-title { font-size: .7rem; font-weight: 700; color: var(--text-dim); letter-spacing: .08em; text-transform: uppercase; margin-bottom: 10px; }
        .req-item { display: flex; align-items: center; gap: 8px; font-size: .77rem; color: var(--text-dim); margin-bottom: 5px; transition: color .2s; }
        .req-item.met { color: #4ade80; }
        .req-icon { width: 14px; height: 14px; border-radius: 50%; border: 1.5px solid currentColor; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 9px; transition: background .2s; }
        .req-item.met .req-icon { background: #4ade80; border-color: #4ade80; color: #052e16; }

        /* ─── RTL Directional Overrides ──────────────────── */
        html[dir="rtl"] .field-icon { right: 14px; left: auto; }
        html[dir="rtl"] .pwd-btn { left: 12px; right: auto; }
        html[dir="rtl"] .field-input { padding: 12px 44px 12px 44px; }
    </style>
</head>
<body>

<div class="bg-wrap">
    <canvas id="particles"></canvas>
    <div class="glow-blob glow-blob-1"></div>
    <div class="glow-blob glow-blob-2"></div>
</div>
<div class="grid-overlay"></div>

<div class="page">
    <div class="card">

        {{-- Logo --}}
        <div class="logo-wrap">
            <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee Logistics">
        </div>

        {{-- Badge --}}
        <div style="text-align:center; margin-bottom:20px;">
            <span class="badge"><span class="badge-dot"></span>{{ __('Account Setup') }}</span>
        </div>

        <h1 style="text-align:center;">{{ __('Set Your Password') }}</h1>
        <p class="sub" style="text-align:center;">{{ __('Create a strong password to activate your Sa\'ee Logistics account.') }}</p>

        {{-- Errors --}}
        @if($errors->any())
        <div class="alert-err">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('set-password.store') }}" id="spForm">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            {{-- Email (display only) --}}
            <div class="field">
                <label class="field-label">{{ __('Email Address') }}</label>
                <div class="field-wrap">
                    <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <input class="field-input" type="email" value="{{ $email }}" readonly style="cursor:default;opacity:.6;padding-right:14px;">
                </div>
            </div>

            {{-- Password --}}
            <div class="field">
                <label class="field-label" for="password">{{ __('New Password') }}</label>
                <div class="field-wrap">
                    <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <input class="field-input @error('password') has-error @enderror" type="password" id="password" name="password" placeholder="{{ __('Minimum 8 characters') }}" autocomplete="new-password" oninput="checkStrength(this.value)">
                    <button type="button" class="pwd-btn" onclick="togglePwd('password','eye1')">
                        <svg id="eye1" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <div class="strength-wrap">
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <div class="strength-label" id="strengthLabel">{{ __('Enter a password') }}</div>
                </div>
            </div>

            {{-- Confirm password --}}
            <div class="field">
                <label class="field-label" for="password_confirmation">{{ __('Confirm Password') }}</label>
                <div class="field-wrap">
                    <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <input class="field-input" type="password" id="password_confirmation" name="password_confirmation" placeholder="{{ __('Repeat your password') }}" autocomplete="new-password">
                    <button type="button" class="pwd-btn" onclick="togglePwd('password_confirmation','eye2')">
                        <svg id="eye2" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            {{-- Requirements --}}
            <div class="reqs">
                <div class="reqs-title">{{ __('Password Requirements') }}</div>
                <div class="req-item" id="req-len"><span class="req-icon">✓</span> {{ __('At least 8 characters') }}</div>
                <div class="req-item" id="req-upper"><span class="req-icon">✓</span> {{ __('At least one uppercase letter') }}</div>
                <div class="req-item" id="req-num"><span class="req-icon">✓</span> {{ __('At least one number') }}</div>
                <div class="req-item" id="req-special"><span class="req-icon">✓</span> {{ __('At least one special character') }}</div>
            </div>

            <button class="btn" type="submit" style="margin-top:24px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                {{ __('Activate Account') }}
            </button>
        </form>

    </div>
</div>

<script>
function togglePwd(inputId, iconId) {
    const input = document.getElementById(inputId);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    document.getElementById(iconId).style.opacity = isText ? '1' : '0.4';
}

function setReq(id, met) {
    const el = document.getElementById(id);
    if (met) el.classList.add('met'); else el.classList.remove('met');
}

function checkStrength(val) {
    const len     = val.length >= 8;
    const upper   = /[A-Z]/.test(val);
    const num     = /\d/.test(val);
    const special = /[^A-Za-z0-9]/.test(val);

    setReq('req-len',     len);
    setReq('req-upper',   upper);
    setReq('req-num',     num);
    setReq('req-special', special);

    const score = [len, upper, num, special].filter(Boolean).length;
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');

    const _trans = {
        'Enter a password': '{{ __("Enter a password") }}',
        'Weak': '{{ __("Weak") }}',
        'Fair': '{{ __("Fair") }}',
        'Good': '{{ __("Good") }}',
        'Strong': '{{ __("Strong") }}'
    };

    const map = {
        0: { w: '0%',   bg: 'transparent',  txt: _trans['Enter a password'] },
        1: { w: '25%',  bg: '#ef4444',       txt: _trans['Weak'] },
        2: { w: '50%',  bg: '#f59e0b',       txt: _trans['Fair'] },
        3: { w: '75%',  bg: '#3b82f6',       txt: _trans['Good'] },
        4: { w: '100%', bg: '#22c55e',       txt: _trans['Strong'] },
    };
    fill.style.width      = map[score].w;
    fill.style.background = map[score].bg;
    label.textContent     = map[score].txt;
    label.style.color     = score >= 3 ? map[score].bg : 'var(--text-dim)';
}

// Particles
(function(){
    const canvas = document.getElementById('particles');
    const ctx    = canvas.getContext('2d');
    let W, H, dots = [];

    function resize() {
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    for (let i = 0; i < 55; i++) {
        dots.push({ x: Math.random()*1e4, y: Math.random()*1e4, vx: (Math.random()-.5)*.25, vy: (Math.random()-.5)*.25, r: Math.random()*1.4+.4, a: Math.random()*.35+.05 });
    }

    function draw() {
        ctx.clearRect(0,0,W,H);
        dots.forEach(d => {
            d.x += d.vx; d.y += d.vy;
            if (d.x < 0) d.x = W; if (d.x > W) d.x = 0;
            if (d.y < 0) d.y = H; if (d.y > H) d.y = 0;
            ctx.beginPath(); ctx.arc(d.x,d.y,d.r,0,Math.PI*2);
            ctx.fillStyle = `rgba(220,38,38,${d.a})`; ctx.fill();
        });
        dots.forEach((a,i) => dots.slice(i+1).forEach(b => {
            const dx = a.x-b.x, dy = a.y-b.y, dist = Math.sqrt(dx*dx+dy*dy);
            if (dist < 110) { ctx.beginPath(); ctx.moveTo(a.x,a.y); ctx.lineTo(b.x,b.y); ctx.strokeStyle=`rgba(220,38,38,${.06*(1-dist/110)})`; ctx.lineWidth=.5; ctx.stroke(); }
        }));
        requestAnimationFrame(draw);
    }
    draw();
})();
</script>
</body>
</html>
