@extends('admin.layouts.app')

@section('title', 'General Site Settings')
@section('page-title', 'General Site Settings')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Settings</span>
    <span class="sep">/</span>
    <span class="current">General Settings</span>
@endsection

@section('head')
<style>
    .tabs-nav {
        display: flex; gap: 10px; margin-bottom: 24px; border-bottom: 1px solid var(--bdr); padding-bottom: 12px;
    }
    .tab-btn {
        background: rgba(255,255,255,.03); border: 1px solid var(--bdr); border-radius: 8px;
        padding: 10px 20px; font-size: .84rem; font-weight: 600; color: var(--text-sub);
        cursor: pointer; transition: all .15s; outline: none;
    }
    .tab-btn:hover { background: rgba(255,255,255,.07); color: var(--text); }
    .tab-btn.active { background: rgba(220,38,38,.12); color: #fca5a5; border-color: rgba(220,38,38,.3); }
    .tab-pane { display: none; }
    .tab-pane.active { display: block; animation: fade-in .25s ease-out both; }
    @keyframes fade-in { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection

@section('content')
<div>
    <h1 style="font-size:1.2rem;font-weight:700;margin-bottom:20px;">Manage Website General Settings</h1>

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

    <form method="POST" action="{{ route('admin.settings.site.update') }}">
        @csrf

        {{-- Tabs Navigation --}}
        <div class="tabs-nav">
            <button type="button" class="tab-btn active" onclick="switchTab('seo')">SEO &amp; Marketing</button>
            <button type="button" class="tab-btn" onclick="switchTab('contact')">Contact Info</button>
            <button type="button" class="tab-btn" onclick="switchTab('social')">Social Channels</button>
        </div>

        {{-- Tab 1: SEO & Marketing --}}
        <div class="tab-pane active" id="tab-seo">
            <div class="form-section">
                <div class="form-section-title">SEO &amp; Marketing Meta tags</div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Website Name</label>
                        <input type="text" name="site_name" class="form-input"
                               value="{{ old('site_name', $settings['site_name']) }}" placeholder="e.g. SAEE Logistics">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Default Meta Title</label>
                        <input type="text" name="meta_title" class="form-input"
                               value="{{ old('meta_title', $settings['meta_title']) }}" placeholder="Site page header title">
                    </div>
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-input" rows="4" 
                              placeholder="Describe your logistics platform for search engines..." style="height: auto;">{{ old('meta_description', $settings['meta_description']) }}</textarea>
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" name="meta_keywords" class="form-input"
                           value="{{ old('meta_keywords', $settings['meta_keywords']) }}" placeholder="e.g. delivery, shipping, jordan (comma-separated)">
                </div>
            </div>
        </div>

        {{-- Tab 2: Contact Info --}}
        <div class="tab-pane" id="tab-contact">
            <div class="form-section">
                <div class="form-section-title">Corporate Contact Info</div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Support Email Address</label>
                        <input type="email" name="site_email" class="form-input"
                               value="{{ old('site_email', $settings['site_email']) }}" placeholder="e.g. info@saee.com.jo">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Support Phone Number</label>
                        <input type="text" name="site_phone" class="form-input"
                               value="{{ old('site_phone', $settings['site_phone']) }}" placeholder="e.g. +962 6 123 4567">
                    </div>
                </div>

                <div class="form-group" style="margin-top:15px;">
                    <label class="form-label">Office Physical Address</label>
                    <textarea name="site_address" class="form-input" rows="3" 
                              placeholder="Physical headquarters address detail..." style="height: auto;">{{ old('site_address', $settings['site_address']) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Tab 3: Social Channels --}}
        <div class="tab-pane" id="tab-social">
            <div class="form-section">
                <div class="form-section-title">Social Media Connections</div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Facebook Profile URL</label>
                        <input type="url" name="social_facebook" class="form-input"
                               value="{{ old('social_facebook', $settings['social_facebook']) }}" placeholder="https://facebook.com/page">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Twitter / X Profile URL</label>
                        <input type="url" name="social_twitter" class="form-input"
                               value="{{ old('social_twitter', $settings['social_twitter']) }}" placeholder="https://twitter.com/profile">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Instagram Profile URL</label>
                        <input type="url" name="social_instagram" class="form-input"
                               value="{{ old('social_instagram', $settings['social_instagram']) }}" placeholder="https://instagram.com/profile">
                    </div>

                    <div class="form-group">
                        <label class="form-label">LinkedIn Page URL</label>
                        <input type="url" name="social_linkedin" class="form-input"
                               value="{{ old('social_linkedin', $settings['social_linkedin']) }}" placeholder="https://linkedin.com/company">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:24px;">
            <button type="submit" class="btn-primary">Save All Settings</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        const activeBtn = Array.from(document.querySelectorAll('.tab-btn')).find(btn => btn.getAttribute('onclick').includes(tabName));
        if (activeBtn) activeBtn.classList.add('active');
        const activePane = document.getElementById('tab-' + tabName);
        if (activePane) activePane.classList.add('active');
    }
</script>
@endsection
