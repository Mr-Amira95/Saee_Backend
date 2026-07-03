@extends('admin.layouts.app')

@section('title', "Why Sa'ee Section")
@section('page-title', "Why Sa'ee Section")

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">Why Sa'ee Section</span>
@endsection

@section('content')
<div>
    <h1 style="font-size:1.2rem;font-weight:700;margin-bottom:20px;">Manage Why Sa'ee Section</h1>

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

    <form method="POST" action="{{ route('admin.cms.why-saee-page.update') }}">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">Badge</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Badge (English)</label>
                    <input type="text" name="badge[en]" class="form-input @error('badge.en') err @enderror"
                           value="{{ old('badge.en', $whySaee->badge['en'] ?? '') }}" placeholder="e.g. Why Choose Us">
                    @error('badge.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Badge (Arabic)</label>
                    <input type="text" name="badge[ar]" dir="rtl" class="form-input @error('badge.ar') err @enderror"
                           value="{{ old('badge.ar', $whySaee->badge['ar'] ?? '') }}" placeholder="مثال: لماذا تختارنا">
                    @error('badge.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Title</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Title (English) <span class="req">*</span></label>
                    <input type="text" name="title[en]" class="form-input @error('title.en') err @enderror"
                           value="{{ old('title.en', $whySaee->title['en'] ?? '') }}" required>
                    @error('title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Title (Arabic) <span class="req">*</span></label>
                    <input type="text" name="title[ar]" dir="rtl" class="form-input @error('title.ar') err @enderror"
                           value="{{ old('title.ar', $whySaee->title['ar'] ?? '') }}" required>
                    @error('title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Subtitle</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Subtitle (English)</label>
                    <textarea name="subtitle[en]" class="form-input @error('subtitle.en') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.en', $whySaee->subtitle['en'] ?? '') }}</textarea>
                    @error('subtitle.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitle (Arabic)</label>
                    <textarea name="subtitle[ar]" dir="rtl" class="form-input @error('subtitle.ar') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.ar', $whySaee->subtitle['ar'] ?? '') }}</textarea>
                    @error('subtitle.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <button type="submit" class="btn-primary">Save Why Sa'ee Section</button>
        </div>
    </form>

    <div style="margin-top:32px;display:flex;align-items:center;justify-content:space-between;">
        <h2 style="font-size:1.05rem;font-weight:700;margin:0;">Why Sa'ee Reasons</h2>
        <a href="{{ route('admin.cms.why-saee-reasons.index') }}" class="btn-secondary">Manage Reasons &rarr;</a>
    </div>
</div>
@endsection
