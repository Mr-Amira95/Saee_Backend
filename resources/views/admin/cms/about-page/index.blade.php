@extends('admin.layouts.app')

@section('title', 'About Page')
@section('page-title', 'About Page')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">About Page</span>
@endsection

@section('content')
<div>
    <h1 style="font-size:1.2rem;font-weight:700;margin-bottom:20px;">Manage About Page</h1>

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

    <form method="POST" action="{{ route('admin.cms.about-page.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">Badge</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Badge (English)</label>
                    <input type="text" name="page_badge[en]" class="form-input @error('page_badge.en') err @enderror"
                           value="{{ old('page_badge.en', $page->page_badge['en'] ?? '') }}" placeholder="e.g. Who We Are">
                    @error('page_badge.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Badge (Arabic)</label>
                    <input type="text" name="page_badge[ar]" dir="rtl" class="form-input @error('page_badge.ar') err @enderror"
                           value="{{ old('page_badge.ar', $page->page_badge['ar'] ?? '') }}" placeholder="مثال: من نحن">
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

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">About Image</div>
            <div style="background: rgba(255,255,255,.02); border: 1px solid var(--bdr); border-radius: 10px; padding: 20px; margin-bottom: 15px;">
                @if($page->image_path)
                    <div style="margin-bottom: 15px;">
                        <span class="form-label" style="display:block; margin-bottom:8px;">Current Image Preview:</span>
                        <img src="{{ $page->image_path }}" alt="About image" style="max-width: 320px; height: 180px; object-fit: cover; border-radius: 8px; border: 1px solid var(--bdr);">
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
                               value="{{ old('image_path', $page->image_path) }}" placeholder="e.g. https://example.com/about.jpg">
                        @error('image_path')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Mission</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Mission (English) <span class="req">*</span></label>
                    <textarea name="mission[en]" class="form-input @error('mission.en') err @enderror" rows="3" style="height:auto;" required>{{ old('mission.en', $page->mission['en'] ?? '') }}</textarea>
                    @error('mission.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Mission (Arabic) <span class="req">*</span></label>
                    <textarea name="mission[ar]" dir="rtl" class="form-input @error('mission.ar') err @enderror" rows="3" style="height:auto;" required>{{ old('mission.ar', $page->mission['ar'] ?? '') }}</textarea>
                    @error('mission.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Vision</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Vision (English) <span class="req">*</span></label>
                    <textarea name="vision[en]" class="form-input @error('vision.en') err @enderror" rows="3" style="height:auto;" required>{{ old('vision.en', $page->vision['en'] ?? '') }}</textarea>
                    @error('vision.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Vision (Arabic) <span class="req">*</span></label>
                    <textarea name="vision[ar]" dir="rtl" class="form-input @error('vision.ar') err @enderror" rows="3" style="height:auto;" required>{{ old('vision.ar', $page->vision['ar'] ?? '') }}</textarea>
                    @error('vision.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <button type="submit" class="btn-primary">Save About Page</button>
        </div>
    </form>
</div>
@endsection
