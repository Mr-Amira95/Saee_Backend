@extends('admin.layouts.app')

@section('title', 'Login Page Content')
@section('page-title', 'Login Page Content')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">Login Page</span>
@endsection

@section('head')
<style>
    .login-page-wrap { display: grid; grid-template-columns: 1fr 420px; gap: 24px; align-items: start; }
    @media (max-width: 1100px) { .login-page-wrap { grid-template-columns: 1fr; } }

    .preview-panel {
        position: sticky; top: 24px;
        background: #07091a;
        border: 1px solid rgba(220,38,38,.15);
        border-radius: 16px;
        padding: 36px 28px;
        display: flex; flex-direction: column; align-items: center; gap: 18px;
        text-align: center;
        font-family: 'Inter', sans-serif;
    }
    .preview-panel img { width: 70px; height: auto; filter: drop-shadow(0 4px 18px rgba(220,38,38,.4)); }
    .preview-headline { font-size: 1.55rem; font-weight: 800; color: #fff; letter-spacing: -.02em; line-height: 1.25; word-break: break-word; }
    .preview-subtitle { font-size: .78rem; color: rgba(255,255,255,.35); letter-spacing: .1em; text-transform: uppercase; margin-top: 4px; }
    .preview-pills { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; margin-top: 4px; }
    .preview-pill {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08);
        border-radius: 100px; padding: 6px 13px;
        font-size: .76rem; color: rgba(255,255,255,.55);
    }
    .preview-pill-dot { width: 6px; height: 6px; border-radius: 50%; background: #dc2626; box-shadow: 0 0 6px #dc2626; flex-shrink: 0; }
    .preview-label { font-size: .7rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: rgba(255,255,255,.2); margin-bottom: -8px; }
    .preview-divider { width: 100%; border: none; border-top: 1px solid rgba(255,255,255,.06); margin: 4px 0; }

    .point-row { display: flex; align-items: center; gap: 10px; animation: fade-in .2s ease-out both; }
    .remove-btn {
        background: rgba(220,38,38,.1); border: 1px solid rgba(220,38,38,.25);
        color: #fca5a5; border-radius: 8px; padding: 9px 13px;
        cursor: pointer; font-size: .8rem; white-space: nowrap; transition: all .2s;
        flex-shrink: 0;
    }
    .remove-btn:hover { background: rgba(220,38,38,.2); }
    .add-point-btn {
        margin-top: 10px; background: rgba(255,255,255,.04);
        border: 1px dashed rgba(255,255,255,.15); color: var(--text-sub);
        border-radius: 8px; padding: 9px 18px; cursor: pointer;
        font-size: .83rem; font-weight: 600; transition: all .2s;
    }
    .add-point-btn:hover { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.3); }
    @keyframes fade-in { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
</style>
@endsection

@section('content')

@if(session('success'))
<div style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);border-radius:10px;padding:12px 18px;margin-bottom:20px;color:#86efac;font-size:.88rem;">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
    <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('admin.cms.login-page.update') }}">
    @csrf
    @method('PUT')

    <div class="login-page-wrap">

        {{-- ── Fields column ── --}}
        <div>
            <div class="form-section">
                <div class="form-section-title">Brand Panel Content</div>
                <p style="color:var(--text-sub);font-size:.83rem;margin-bottom:20px;">
                    Customise the content shown on the <strong style="color:var(--text);">left side</strong> of the client/portal login page.
                </p>

                {{-- Headline --}}
                <div class="form-group" style="margin-bottom:20px;">
                    <label class="form-label">Headline</label>
                    <input type="text" id="inp-headline" name="login_brand_headline" class="form-input"
                           value="{{ old('login_brand_headline', $settings['login_brand_headline']) }}"
                           placeholder="e.g. Your Business, Delivered.">
                    <span style="color:var(--text-dim);font-size:.75rem;display:block;margin-top:5px;">
                        The large bold title shown on the login hero panel.
                    </span>
                </div>

                {{-- Subtitle --}}
                <div class="form-group" style="margin-bottom:28px;">
                    <label class="form-label">Sub-headline / Tagline</label>
                    <input type="text" id="inp-subtitle" name="login_brand_subtitle" class="form-input"
                           value="{{ old('login_brand_subtitle', $settings['login_brand_subtitle']) }}"
                           placeholder="e.g. SA'EE LOGISTICS PORTAL">
                    <span style="color:var(--text-dim);font-size:.75rem;display:block;margin-top:5px;">
                        Smaller uppercase label shown directly below the headline.
                    </span>
                </div>

                {{-- Points Repeater --}}
                <div class="form-group">
                    <label class="form-label" style="margin-bottom:12px;">Feature Points (Pills)</label>
                    <div id="pointsList" style="display:flex;flex-direction:column;gap:10px;">
                        @foreach($settings['login_brand_points'] as $i => $point)
                        <div class="point-row">
                            <input type="text" name="login_brand_points[]" class="form-input point-input" style="flex:1;"
                                   value="{{ old('login_brand_points.'.$i, $point) }}"
                                   placeholder="e.g. Track Orders">
                            <button type="button" class="remove-btn" onclick="removePoint(this)">✕ Remove</button>
                        </div>
                        @endforeach
                    </div>

                    <button type="button" class="add-point-btn" onclick="addPoint()">+ Add Point</button>
                    <p style="color:var(--text-dim);font-size:.75rem;margin-top:8px;">
                        These appear as animated pill badges. Maximum recommended: 5.
                    </p>
                </div>
            </div>

            <div class="form-actions" style="margin-top:20px;">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ url()->previous() }}" class="btn-secondary" style="margin-left:10px;">Cancel</a>
            </div>
        </div>

        {{-- ── Live Preview column ── --}}
        <div class="preview-panel">
            <span class="preview-label">Live Preview</span>
            <hr class="preview-divider">
            <img src="{{ asset('saee_logo_dark.png') }}" alt="logo">
            <div>
                <p class="preview-headline" id="prev-headline">{{ $settings['login_brand_headline'] }}</p>
                <p class="preview-subtitle" id="prev-subtitle">{{ $settings['login_brand_subtitle'] }}</p>
            </div>
            <div class="preview-pills" id="prev-pills">
                @foreach($settings['login_brand_points'] as $point)
                <span class="preview-pill"><span class="preview-pill-dot"></span>{{ $point }}</span>
                @endforeach
            </div>
            <hr class="preview-divider" style="margin-top:4px;">
            <span style="font-size:.7rem;color:rgba(255,255,255,.18);">As seen on the login page</span>
        </div>

    </div>
</form>

@endsection

@section('scripts')
<script>
    function updatePreview() {
        const headline = document.getElementById('inp-headline')?.value || '';
        const subtitle = document.getElementById('inp-subtitle')?.value || '';
        const points   = [...document.querySelectorAll('.point-input')].map(i => i.value.trim()).filter(Boolean);

        document.getElementById('prev-headline').textContent = headline;
        document.getElementById('prev-subtitle').textContent  = subtitle;

        document.getElementById('prev-pills').innerHTML = points.map(p => `
            <span class="preview-pill">
                <span class="preview-pill-dot"></span>${p}
            </span>`).join('');
    }

    function addPoint() {
        const list = document.getElementById('pointsList');
        const row  = document.createElement('div');
        row.className = 'point-row';
        row.innerHTML = `
            <input type="text" name="login_brand_points[]" class="form-input point-input" style="flex:1;" placeholder="e.g. Fast Delivery">
            <button type="button" class="remove-btn" onclick="removePoint(this)">✕ Remove</button>`;
        list.appendChild(row);
        row.querySelector('input').addEventListener('input', updatePreview);
        row.querySelector('input').focus();
        updatePreview();
    }

    function removePoint(btn) {
        btn.closest('.point-row').remove();
        updatePreview();
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('inp-headline').addEventListener('input', updatePreview);
        document.getElementById('inp-subtitle').addEventListener('input', updatePreview);
        document.querySelectorAll('.point-input').forEach(i => i.addEventListener('input', updatePreview));
    });
</script>
@endsection
