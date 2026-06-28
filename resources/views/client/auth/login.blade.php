@extends('client.layouts.auth')
@section('title', 'Sign In')

@section('form')
<div class="card">
    <div class="card-logo">
        <img src="{{ asset('saee_logo_dark.png') }}" alt="Sa'ee" width="44" height="44" style="object-fit:contain;border-radius:10px;">
    </div>

    <span class="badge-portal"><span class="badge-dot"></span>{{ __('Client Portal') }}</span>
    <h1>{{ __('Welcome back') }}</h1>
    <p class="sub">{{ __('Sign in to manage your shipments') }}</p>

    @if($errors->any())
        <div class="alert alert-err">{{ $errors->first() }}</div>
    @endif
    @if(session('status'))
        <div class="alert alert-ok">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('client.login') }}" id="loginForm" novalidate>
        @csrf

        <div class="field f1">
            <label class="field-label" for="phoneLocal">{{ __('Phone Number') }}</label>
            <div class="field-wrap phone-wrap{{ $errors->has('phone') ? ' has-error' : '' }}">
                <button type="button" class="country-btn" id="countryBtn" aria-label="{{ __('Select country code') }}">
                    <span class="country-flag" id="countryFlag">🇯🇴</span>
                    <span class="country-dial" id="countryDial">+962</span>
                    <svg class="country-chevron" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="country-dropdown" id="countryDropdown">
                    <div class="country-option active" data-dial="+962" data-flag="🇯🇴"><span class="opt-flag">🇯🇴</span><span class="opt-name">Jordan</span><span class="opt-dial">+962</span></div>
                    <div class="country-option" data-dial="+966" data-flag="🇸🇦"><span class="opt-flag">🇸🇦</span><span class="opt-name">Saudi Arabia</span><span class="opt-dial">+966</span></div>
                    <div class="country-option" data-dial="+971" data-flag="🇦🇪"><span class="opt-flag">🇦🇪</span><span class="opt-name">UAE</span><span class="opt-dial">+971</span></div>
                    <div class="country-option" data-dial="+965" data-flag="🇰🇼"><span class="opt-flag">🇰🇼</span><span class="opt-name">Kuwait</span><span class="opt-dial">+965</span></div>
                    <div class="country-option" data-dial="+974" data-flag="🇶🇦"><span class="opt-flag">🇶🇦</span><span class="opt-name">Qatar</span><span class="opt-dial">+974</span></div>
                    <div class="country-option" data-dial="+973" data-flag="🇧🇭"><span class="opt-flag">🇧🇭</span><span class="opt-name">Bahrain</span><span class="opt-dial">+973</span></div>
                    <div class="country-option" data-dial="+968" data-flag="🇴🇲"><span class="opt-flag">🇴🇲</span><span class="opt-name">Oman</span><span class="opt-dial">+968</span></div>
                    <div class="country-option" data-dial="+20" data-flag="🇪🇬"><span class="opt-flag">🇪🇬</span><span class="opt-name">Egypt</span><span class="opt-dial">+20</span></div>
                    <div class="country-option" data-dial="+970" data-flag="🇵🇸"><span class="opt-flag">🇵🇸</span><span class="opt-name">Palestine</span><span class="opt-dial">+970</span></div>
                    <div class="country-option" data-dial="+961" data-flag="🇱🇧"><span class="opt-flag">🇱🇧</span><span class="opt-name">Lebanon</span><span class="opt-dial">+961</span></div>
                    <div class="country-option" data-dial="+964" data-flag="🇮🇶"><span class="opt-flag">🇮🇶</span><span class="opt-name">Iraq</span><span class="opt-dial">+964</span></div>
                    <div class="country-option" data-dial="+963" data-flag="🇸🇾"><span class="opt-flag">🇸🇾</span><span class="opt-name">Syria</span><span class="opt-dial">+963</span></div>
                </div>
                <input id="phoneLocal" type="tel" placeholder="{{ __('7xxxxxxxx') }}" autocomplete="tel" autofocus>
                <input type="hidden" name="phone" id="phoneHidden">
            </div>
            @error('phone') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field f2">
            <label class="field-label" for="password">{{ __('Password') }}</label>
            <div class="field-wrap">
                <svg class="field-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <input id="password" name="password" type="password" placeholder="{{ __('••••••••••') }}" autocomplete="current-password">
                <button type="button" class="pwd-btn" id="pwdToggle" aria-label="{{ __('Toggle password') }}">
                    <svg id="eyeIcon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="extras f3">
            <span></span>
            <a href="{{ route('client.forgot-password') }}" class="forgot">{{ __('Forgot password?') }}</a>
        </div>

        <button type="submit" class="btn f4" id="submitBtn">
            <div class="spinner"></div>
            <span class="btn-text">{{ __('Sign In') }}</span>
            <svg class="btn-arrow" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>
    </form>

    <p class="card-footer f5">&copy; {{ date('Y') }} Sa'ee LogisticsServices. {{ __('All rights reserved.') }}</p>
</div>
@endsection

@push('scripts')
<script>
const pwdInput  = document.getElementById('password');
const pwdToggle = document.getElementById('pwdToggle');
const eyeIcon   = document.getElementById('eyeIcon');
const eyeOpen   = `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
const eyeClosed = `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
pwdToggle.addEventListener('click', () => {
    const isText = pwdInput.type === 'text';
    pwdInput.type = isText ? 'password' : 'text';
    eyeIcon.innerHTML = isText ? eyeOpen : eyeClosed;
});

// Country code dropdown
(function () {
    const btn      = document.getElementById('countryBtn');
    const dropdown = document.getElementById('countryDropdown');
    const flagEl   = document.getElementById('countryFlag');
    const dialEl   = document.getElementById('countryDial');

    btn.addEventListener('click', e => {
        e.stopPropagation();
        btn.classList.toggle('open');
        dropdown.classList.toggle('open');
    });
    document.addEventListener('click', () => {
        btn.classList.remove('open');
        dropdown.classList.remove('open');
    });
    dropdown.querySelectorAll('.country-option').forEach(opt => {
        opt.addEventListener('click', () => {
            dropdown.querySelectorAll('.country-option').forEach(o => o.classList.remove('active'));
            opt.classList.add('active');
            flagEl.textContent = opt.dataset.flag;
            dialEl.textContent = opt.dataset.dial;
            btn.classList.remove('open');
            dropdown.classList.remove('open');
        });
    });

    document.getElementById('loginForm').addEventListener('submit', () => {
        let local = document.getElementById('phoneLocal').value.trim();
        if (local.startsWith('0')) local = local.slice(1);
        document.getElementById('phoneHidden').value = dialEl.textContent + local;
        document.getElementById('submitBtn').classList.add('loading');
    });
})();
</script>
@endpush
