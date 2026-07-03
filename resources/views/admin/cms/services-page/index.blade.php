@extends('admin.layouts.app')

@section('title', 'Services Page')
@section('page-title', 'Services Page')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">Services Page</span>
@endsection

@section('content')
<div>
    <h1 style="font-size:1.2rem;font-weight:700;margin-bottom:20px;">Manage Services Page</h1>

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

    <form method="POST" action="{{ route('admin.cms.services-page.update') }}">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">Page Badge</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Page Badge (English)</label>
                    <input type="text" name="page_badge[en]" class="form-input @error('page_badge.en') err @enderror"
                           value="{{ old('page_badge.en', $servicesPage->page_badge['en'] ?? '') }}" placeholder="e.g. Our Services">
                    @error('page_badge.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Page Badge (Arabic)</label>
                    <input type="text" name="page_badge[ar]" dir="rtl" class="form-input @error('page_badge.ar') err @enderror"
                           value="{{ old('page_badge.ar', $servicesPage->page_badge['ar'] ?? '') }}" placeholder="مثال: خدماتنا">
                    @error('page_badge.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Page Title</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Page Title (English)</label>
                    <input type="text" name="page_title[en]" class="form-input @error('page_title.en') err @enderror"
                           value="{{ old('page_title.en', $servicesPage->page_title['en'] ?? '') }}">
                    @error('page_title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Page Title (Arabic)</label>
                    <input type="text" name="page_title[ar]" dir="rtl" class="form-input @error('page_title.ar') err @enderror"
                           value="{{ old('page_title.ar', $servicesPage->page_title['ar'] ?? '') }}">
                    @error('page_title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Page Subtitle</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Page Subtitle (English)</label>
                    <textarea name="page_subtitle[en]" class="form-input @error('page_subtitle.en') err @enderror" rows="3" style="height:auto;">{{ old('page_subtitle.en', $servicesPage->page_subtitle['en'] ?? '') }}</textarea>
                    @error('page_subtitle.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Page Subtitle (Arabic)</label>
                    <textarea name="page_subtitle[ar]" dir="rtl" class="form-input @error('page_subtitle.ar') err @enderror" rows="3" style="height:auto;">{{ old('page_subtitle.ar', $servicesPage->page_subtitle['ar'] ?? '') }}</textarea>
                    @error('page_subtitle.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Section Badge</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Section Badge (English)</label>
                    <input type="text" name="section_badge[en]" class="form-input @error('section_badge.en') err @enderror"
                           value="{{ old('section_badge.en', $servicesPage->section_badge['en'] ?? '') }}">
                    @error('section_badge.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Section Badge (Arabic)</label>
                    <input type="text" name="section_badge[ar]" dir="rtl" class="form-input @error('section_badge.ar') err @enderror"
                           value="{{ old('section_badge.ar', $servicesPage->section_badge['ar'] ?? '') }}">
                    @error('section_badge.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Section Title</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Section Title (English)</label>
                    <input type="text" name="section_title[en]" class="form-input @error('section_title.en') err @enderror"
                           value="{{ old('section_title.en', $servicesPage->section_title['en'] ?? '') }}">
                    @error('section_title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Section Title (Arabic)</label>
                    <input type="text" name="section_title[ar]" dir="rtl" class="form-input @error('section_title.ar') err @enderror"
                           value="{{ old('section_title.ar', $servicesPage->section_title['ar'] ?? '') }}">
                    @error('section_title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Section Subtitle</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Section Subtitle (English)</label>
                    <textarea name="section_subtitle[en]" class="form-input @error('section_subtitle.en') err @enderror" rows="3" style="height:auto;">{{ old('section_subtitle.en', $servicesPage->section_subtitle['en'] ?? '') }}</textarea>
                    @error('section_subtitle.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Section Subtitle (Arabic)</label>
                    <textarea name="section_subtitle[ar]" dir="rtl" class="form-input @error('section_subtitle.ar') err @enderror" rows="3" style="height:auto;">{{ old('section_subtitle.ar', $servicesPage->section_subtitle['ar'] ?? '') }}</textarea>
                    @error('section_subtitle.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <button type="submit" class="btn-primary">Save Services Page</button>
        </div>
    </form>

    <div style="margin-top:32px;display:flex;align-items:center;justify-content:space-between;">
        <h2 style="font-size:1.05rem;font-weight:700;margin:0;">Services List</h2>
        <a href="{{ route('admin.cms.services.index') }}" class="btn-secondary">Manage Services List &rarr;</a>
    </div>
</div>
@endsection
