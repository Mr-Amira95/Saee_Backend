@extends('admin.layouts.app')

@section('title', 'Legal Content')
@section('page-title', 'Legal Content')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">Legal Content</span>
@endsection

@section('head')
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<style>
    .legal-tabs-nav {
        display: flex; gap: 10px; margin-bottom: 24px; border-bottom: 1px solid var(--bdr); padding-bottom: 12px;
    }
    .legal-tab-btn {
        background: rgba(255,255,255,.03); border: 1px solid var(--bdr); border-radius: 8px;
        padding: 10px 20px; font-size: .84rem; font-weight: 600; color: var(--text-sub);
        cursor: pointer; transition: all .15s; outline: none;
    }
    .legal-tab-btn:hover { background: rgba(255,255,255,.07); color: var(--text); }
    .legal-tab-btn.active { background: rgba(220,38,38,.12); color: #fca5a5; border-color: rgba(220,38,38,.3); }
    .legal-tab-pane { display: none; }
    .legal-tab-pane.active { display: block; animation: fade-in .25s ease-out both; }
    @keyframes fade-in { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
    .editor-note {
        font-size: .8rem; color: var(--text-sub); margin-bottom: 10px;
        padding: 8px 12px; background: rgba(255,255,255,.03); border-radius: 6px; border: 1px solid var(--bdr);
    }
    .cke { border-radius: 8px; overflow: hidden; }
    .lang-block { margin-bottom: 24px; }
    .lang-block-title {
        font-size: .82rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase;
        letter-spacing: .04em; margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div>
    <h1 style="font-size:1.2rem;font-weight:700;margin-bottom:20px;">Manage Legal Content</h1>

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

    <form method="POST" action="{{ route('admin.settings.legal.update') }}" id="legal-form">
        @csrf

        <div class="legal-tabs-nav">
            <button type="button" class="legal-tab-btn active" onclick="switchLegalTab('terms')">Terms &amp; Conditions</button>
            <button type="button" class="legal-tab-btn" onclick="switchLegalTab('privacy')">Privacy Policy</button>
        </div>

        {{-- Terms & Conditions --}}
        <div class="legal-tab-pane active" id="legal-tab-terms">
            <div class="form-section">
                <div class="form-section-title">Terms &amp; Conditions</div>
                <p class="editor-note">
                    Write the full Terms &amp; Conditions content in both languages below. The content supports rich formatting (headings, bold, lists, links, etc.)
                    and is served via the public API at <code>/api/legal/terms</code> and <code>/api/public/terms-and-conditions</code>.
                </p>

                <div class="lang-block">
                    <div class="lang-block-title">English</div>
                    <div class="form-group">
                        <textarea name="terms_and_conditions[en]" id="editor-terms-en" rows="16" style="width:100%;">{{ old('terms_and_conditions.en', $settings['terms_and_conditions']['en']) }}</textarea>
                    </div>
                </div>

                <div class="lang-block">
                    <div class="lang-block-title">Arabic</div>
                    <div class="form-group">
                        <textarea name="terms_and_conditions[ar]" id="editor-terms-ar" dir="rtl" rows="16" style="width:100%;">{{ old('terms_and_conditions.ar', $settings['terms_and_conditions']['ar']) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Privacy Policy --}}
        <div class="legal-tab-pane" id="legal-tab-privacy">
            <div class="form-section">
                <div class="form-section-title">Privacy Policy</div>
                <p class="editor-note">
                    Write the full Privacy Policy content in both languages below. The content supports rich formatting (headings, bold, lists, links, etc.)
                    and is served via the public API at <code>/api/legal/privacy</code> and <code>/api/public/privacy-policy</code>.
                </p>

                <div class="lang-block">
                    <div class="lang-block-title">English</div>
                    <div class="form-group">
                        <textarea name="privacy_policy[en]" id="editor-privacy-en" rows="16" style="width:100%;">{{ old('privacy_policy.en', $settings['privacy_policy']['en']) }}</textarea>
                    </div>
                </div>

                <div class="lang-block">
                    <div class="lang-block-title">Arabic</div>
                    <div class="form-group">
                        <textarea name="privacy_policy[ar]" id="editor-privacy-ar" dir="rtl" rows="16" style="width:100%;">{{ old('privacy_policy.ar', $settings['privacy_policy']['ar']) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:24px;">
            <button type="submit" class="btn-primary">Save Legal Content</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    var legalEditorIds = ['editor-terms-en', 'editor-terms-ar', 'editor-privacy-en', 'editor-privacy-ar'];

    legalEditorIds.forEach(function (id) {
        CKEDITOR.replace(id, {
            height: 380,
            versionCheck: false,
            contentsLangDirection: id.endsWith('-ar') ? 'rtl' : 'ltr',
            toolbar: [
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'] },
                { name: 'paragraph',   items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] },
                { name: 'styles',      items: ['Format'] },
                { name: 'links',       items: ['Link', 'Unlink'] },
                { name: 'tools',       items: ['Maximize'] },
                { name: 'document',    items: ['Source'] },
            ],
        });
    });

    // Sync CKEditor content back into the textareas before form submit
    document.getElementById('legal-form').addEventListener('submit', function () {
        for (var name in CKEDITOR.instances) {
            CKEDITOR.instances[name].updateElement();
        }
    });

    function switchLegalTab(tabName) {
        document.querySelectorAll('.legal-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.legal-tab-pane').forEach(pane => pane.classList.remove('active'));

        const activeBtn = Array.from(document.querySelectorAll('.legal-tab-btn'))
            .find(btn => btn.getAttribute('onclick').includes(tabName));
        if (activeBtn) activeBtn.classList.add('active');

        const activePane = document.getElementById('legal-tab-' + tabName);
        if (activePane) activePane.classList.add('active');
    }
</script>
@endsection
