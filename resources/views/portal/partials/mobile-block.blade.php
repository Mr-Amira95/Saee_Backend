{{-- Blocks access to Portal auth pages on small/mobile screens, prompting the user to switch to a desktop/laptop. --}}
<div id="mobileBlock" class="mobile-block">
    <div class="mobile-block-card">
        <svg width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <h1>{{ __('Best Viewed on Desktop') }}</h1>
        <p>{{ __('The Sa\'ee Logistics Portal is optimized for larger screens. Please switch to a laptop or desktop for the best experience.') }}</p>
        <button type="button" id="mobileBlockBack">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ __('Go Back') }}
        </button>
    </div>
</div>

<style>
    .mobile-block {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: #07091a;
        align-items: center;
        justify-content: center;
        padding: 32px 24px;
        text-align: center;
    }
    .mobile-block-card { display: flex; flex-direction: column; align-items: center; max-width: 340px; }
    .mobile-block-card svg { color: #dc2626; margin-bottom: 20px; }
    .mobile-block-card h1 { font-family: 'Inter', system-ui, sans-serif; font-size: 1.3rem; font-weight: 800; color: #f1f5f9; margin: 0 0 10px; letter-spacing: -.02em; }
    .mobile-block-card p { font-family: 'Inter', system-ui, sans-serif; font-size: .9rem; color: #94a3b8; line-height: 1.55; margin: 0 0 26px; }
    .mobile-block-card button {
        display: flex; align-items: center; gap: 8px;
        padding: 12px 24px; background: #dc2626; color: #fff; border: none; border-radius: 10px;
        font-family: 'Inter', system-ui, sans-serif; font-size: .88rem; font-weight: 700; cursor: pointer;
    }
    html[dir="rtl"] .mobile-block-card button svg { transform: scaleX(-1); }

    @media (max-width: 767px) {
        .mobile-block { display: flex; }
    }
</style>

<script>
    document.getElementById('mobileBlockBack').addEventListener('click', function () {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = '/';
        }
    });
</script>
