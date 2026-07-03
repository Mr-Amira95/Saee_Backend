@extends('admin.layouts.app')

@section('title', 'Hero Section')
@section('page-title', 'Hero Section')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">Hero Section</span>
@endsection

@section('content')
<div>
    <h1 style="font-size:1.2rem;font-weight:700;margin-bottom:20px;">Manage Hero Section</h1>

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

    <form method="POST" action="{{ route('admin.cms.hero.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">Badge</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Badge (English)</label>
                    <input type="text" name="badge[en]" class="form-input @error('badge.en') err @enderror"
                           value="{{ old('badge.en', $hero->badge['en'] ?? '') }}" placeholder="e.g. Trusted Logistics Partner">
                    @error('badge.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Badge (Arabic)</label>
                    <input type="text" name="badge[ar]" dir="rtl" class="form-input @error('badge.ar') err @enderror"
                           value="{{ old('badge.ar', $hero->badge['ar'] ?? '') }}" placeholder="مثال: شريك لوجستي موثوق">
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
                           value="{{ old('title.en', $hero->title['en'] ?? '') }}" required>
                    @error('title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Title (Arabic) <span class="req">*</span></label>
                    <input type="text" name="title[ar]" dir="rtl" class="form-input @error('title.ar') err @enderror"
                           value="{{ old('title.ar', $hero->title['ar'] ?? '') }}" required>
                    @error('title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Subtitle</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Subtitle (English)</label>
                    <textarea name="subtitle[en]" class="form-input @error('subtitle.en') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.en', $hero->subtitle['en'] ?? '') }}</textarea>
                    @error('subtitle.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitle (Arabic)</label>
                    <textarea name="subtitle[ar]" dir="rtl" class="form-input @error('subtitle.ar') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.ar', $hero->subtitle['ar'] ?? '') }}</textarea>
                    @error('subtitle.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Hero Image</div>
            <div style="background: rgba(255,255,255,.02); border: 1px solid var(--bdr); border-radius: 10px; padding: 20px; margin-bottom: 15px;">
                @if($hero->image_path)
                    <div style="margin-bottom: 15px;">
                        <span class="form-label" style="display:block; margin-bottom:8px;">Current Image Preview:</span>
                        <img src="{{ $hero->image_path }}" alt="Hero image" style="max-width: 320px; height: 180px; object-fit: cover; border-radius: 8px; border: 1px solid var(--bdr);">
                    </div>
                @endif
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Upload New Image File</label>
                        <input type="file" name="image_file" class="form-input @error('image_file') err @enderror"
                               accept="image/*" style="height: auto; padding: 8px;">
                        <span style="font-size: .75rem; color: var(--text-dim); margin-top: 4px;">Leave blank to keep current image. Max size: 4MB.</span>
                        @error('image_file')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Or External Image URL</label>
                        <input type="text" name="image_path" class="form-input @error('image_path') err @enderror"
                               value="{{ old('image_path', $hero->image_path) }}" placeholder="e.g. https://example.com/hero.jpg">
                        @error('image_path')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <button type="submit" class="btn-primary">Save Hero Section</button>
        </div>
    </form>

    <div style="margin-top:32px;display:flex;align-items:center;justify-content:space-between;">
        <h2 style="font-size:1.05rem;font-weight:700;margin:0;">Hero Stats</h2>
        <a href="{{ route('admin.cms.hero-stats.index') }}" class="btn-secondary">Manage Stats &rarr;</a>
    </div>
</div>
@endsection
