@extends('admin.layouts.app')

@section('title', 'Showcases')
@section('page-title', 'Showcases')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">Showcases</span>
@endsection

@section('content')
<div>
    <h1 style="font-size:1.2rem;font-weight:700;margin-bottom:20px;">Manage Showcases</h1>

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

    <form method="POST" action="{{ route('admin.cms.showcase-page.update') }}">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">Page Header</div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Page Badge (English)</label>
                    <input type="text" name="page_badge[en]" class="form-input @error('page_badge.en') err @enderror"
                           value="{{ old('page_badge.en', $showcase->page_badge['en'] ?? '') }}" placeholder="e.g. Our Showcases">
                    @error('page_badge.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Page Badge (Arabic)</label>
                    <input type="text" name="page_badge[ar]" dir="rtl" class="form-input @error('page_badge.ar') err @enderror"
                           value="{{ old('page_badge.ar', $showcase->page_badge['ar'] ?? '') }}" placeholder="مثال: أعمالنا">
                    @error('page_badge.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-grid-2" style="margin-top:15px;">
                <div class="form-group">
                    <label class="form-label">Page Title (English) <span class="req">*</span></label>
                    <input type="text" name="page_title[en]" class="form-input @error('page_title.en') err @enderror"
                           value="{{ old('page_title.en', $showcase->page_title['en'] ?? '') }}" required>
                    @error('page_title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Page Title (Arabic) <span class="req">*</span></label>
                    <input type="text" name="page_title[ar]" dir="rtl" class="form-input @error('page_title.ar') err @enderror"
                           value="{{ old('page_title.ar', $showcase->page_title['ar'] ?? '') }}" required>
                    @error('page_title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-grid-2" style="margin-top:15px;">
                <div class="form-group">
                    <label class="form-label">Page Subtitle (English)</label>
                    <textarea name="page_subtitle[en]" class="form-input @error('page_subtitle.en') err @enderror" rows="3" style="height:auto;">{{ old('page_subtitle.en', $showcase->page_subtitle['en'] ?? '') }}</textarea>
                    @error('page_subtitle.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Page Subtitle (Arabic)</label>
                    <textarea name="page_subtitle[ar]" dir="rtl" class="form-input @error('page_subtitle.ar') err @enderror" rows="3" style="height:auto;">{{ old('page_subtitle.ar', $showcase->page_subtitle['ar'] ?? '') }}</textarea>
                    @error('page_subtitle.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Section Header</div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Section Badge (English)</label>
                    <input type="text" name="section_badge[en]" class="form-input @error('section_badge.en') err @enderror"
                           value="{{ old('section_badge.en', $showcase->section_badge['en'] ?? '') }}" placeholder="e.g. What We Offer">
                    @error('section_badge.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Section Badge (Arabic)</label>
                    <input type="text" name="section_badge[ar]" dir="rtl" class="form-input @error('section_badge.ar') err @enderror"
                           value="{{ old('section_badge.ar', $showcase->section_badge['ar'] ?? '') }}" placeholder="مثال: ما نقدمه">
                    @error('section_badge.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-grid-2" style="margin-top:15px;">
                <div class="form-group">
                    <label class="form-label">Section Title (English) <span class="req">*</span></label>
                    <input type="text" name="section_title[en]" class="form-input @error('section_title.en') err @enderror"
                           value="{{ old('section_title.en', $showcase->section_title['en'] ?? '') }}" required>
                    @error('section_title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Section Title (Arabic) <span class="req">*</span></label>
                    <input type="text" name="section_title[ar]" dir="rtl" class="form-input @error('section_title.ar') err @enderror"
                           value="{{ old('section_title.ar', $showcase->section_title['ar'] ?? '') }}" required>
                    @error('section_title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-grid-2" style="margin-top:15px;">
                <div class="form-group">
                    <label class="form-label">Section Subtitle (English)</label>
                    <textarea name="section_subtitle[en]" class="form-input @error('section_subtitle.en') err @enderror" rows="3" style="height:auto;">{{ old('section_subtitle.en', $showcase->section_subtitle['en'] ?? '') }}</textarea>
                    @error('section_subtitle.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Section Subtitle (Arabic)</label>
                    <textarea name="section_subtitle[ar]" dir="rtl" class="form-input @error('section_subtitle.ar') err @enderror" rows="3" style="height:auto;">{{ old('section_subtitle.ar', $showcase->section_subtitle['ar'] ?? '') }}</textarea>
                    @error('section_subtitle.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <button type="submit" class="btn-primary">Save Showcases</button>
        </div>
    </form>

    <div style="margin-top:32px;display:flex;align-items:center;justify-content:space-between;">
        <h2 style="font-size:1.05rem;font-weight:700;margin:0;">Capabilities</h2>
        <a href="{{ route('admin.cms.showcase-capabilities.index') }}" class="btn-secondary">Manage Capabilities &rarr;</a>
    </div>

    <div style="margin-top:16px;display:flex;align-items:center;justify-content:space-between;">
        <h2 style="font-size:1.05rem;font-weight:700;margin:0;">How It Works</h2>
        <a href="{{ route('admin.cms.showcase-how-it-works.index') }}" class="btn-secondary">Manage How It Works &rarr;</a>
    </div>

    <div style="margin-top:16px;display:flex;align-items:center;justify-content:space-between;">
        <h2 style="font-size:1.05rem;font-weight:700;margin:0;">Metrics</h2>
        <a href="{{ route('admin.cms.showcase-metrics.index') }}" class="btn-secondary">Manage Metrics &rarr;</a>
    </div>

    <div style="margin-top:16px;display:flex;align-items:center;justify-content:space-between;">
        <h2 style="font-size:1.05rem;font-weight:700;margin:0;">Screenshots</h2>
        <a href="{{ route('admin.cms.showcase-screenshots.index') }}" class="btn-secondary">Manage Screenshots &rarr;</a>
    </div>
</div>
@endsection
