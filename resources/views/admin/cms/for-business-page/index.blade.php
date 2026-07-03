@extends('admin.layouts.app')

@section('title', 'For Businesses Page')
@section('page-title', 'For Businesses Page')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">For Businesses Page</span>
@endsection

@section('content')
<div>
    <h1 style="font-size:1.2rem;font-weight:700;margin-bottom:20px;">Manage For Businesses Page</h1>

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

    <form method="POST" action="{{ route('admin.cms.for-business-page.update') }}">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">Badge</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Badge (English)</label>
                    <input type="text" name="page_badge[en]" class="form-input @error('page_badge.en') err @enderror"
                           value="{{ old('page_badge.en', $page->page_badge['en'] ?? '') }}" placeholder="e.g. Built for Businesses">
                    @error('page_badge.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Badge (Arabic)</label>
                    <input type="text" name="page_badge[ar]" dir="rtl" class="form-input @error('page_badge.ar') err @enderror"
                           value="{{ old('page_badge.ar', $page->page_badge['ar'] ?? '') }}" placeholder="مثال: مصمم للشركات">
                    @error('page_badge.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Title</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Title (English) <span class="req">*</span></label>
                    <input type="text" name="page_title[en]" class="form-input @error('page_title.en') err @enderror"
                           value="{{ old('page_title.en', $page->page_title['en'] ?? '') }}" required>
                    @error('page_title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Title (Arabic) <span class="req">*</span></label>
                    <input type="text" name="page_title[ar]" dir="rtl" class="form-input @error('page_title.ar') err @enderror"
                           value="{{ old('page_title.ar', $page->page_title['ar'] ?? '') }}" required>
                    @error('page_title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Subtitle</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Subtitle (English)</label>
                    <textarea name="page_subtitle[en]" class="form-input @error('page_subtitle.en') err @enderror" rows="3" style="height:auto;">{{ old('page_subtitle.en', $page->page_subtitle['en'] ?? '') }}</textarea>
                    @error('page_subtitle.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitle (Arabic)</label>
                    <textarea name="page_subtitle[ar]" dir="rtl" class="form-input @error('page_subtitle.ar') err @enderror" rows="3" style="height:auto;">{{ old('page_subtitle.ar', $page->page_subtitle['ar'] ?? '') }}</textarea>
                    @error('page_subtitle.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <button type="submit" class="btn-primary">Save For Businesses Page</button>
        </div>
    </form>
</div>
@endsection
