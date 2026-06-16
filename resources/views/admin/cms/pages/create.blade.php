@extends('admin.layouts.app')

@section('title', 'Add Page')
@section('page-title', 'Add Page')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.pages.index') }}">Pages</a>
    <span class="sep">/</span>
    <span class="current">Add</span>
@endsection

@section('content')
<div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cms.pages.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Add Custom Page</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cms.pages.store') }}">
        @csrf

        <div class="form-section">
            <div class="form-section-title">Page Details</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Page Title <span class="req">*</span></label>
                    <input type="text" name="title" class="form-input @error('title') err @enderror"
                           value="{{ old('title') }}" placeholder="e.g. Privacy Policy" required>
                    @error('title')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Slug / Route URL <span class="req">(Auto-generated if empty)</span></label>
                    <input type="text" name="slug" class="form-input @error('slug') err @enderror"
                           value="{{ old('slug') }}" placeholder="e.g. privacy-policy">
                    @error('slug')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label class="form-label">Page Content (HTML/Markdown allowed) <span class="req">*</span></label>
                <textarea name="content" class="form-input @error('content') err @enderror" rows="12" 
                          placeholder="Write page content in HTML or plain text here..." required style="height: auto; font-family: monospace; font-size: 0.9rem; line-height: 1.5;">{{ old('content') }}</textarea>
                @error('content')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-section" style="margin-top: 20px;">
            <div class="form-section-title">SEO & Metadata (Optional)</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-input"
                           value="{{ old('meta_title') }}" placeholder="Default matches page title">
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input" style="background: var(--in-bg); color: var(--text);">
                        <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top:15px;">
                <label class="form-label">Meta Description</label>
                <textarea name="meta_description" class="form-input" rows="3" 
                          placeholder="Brief description for search engines..." style="height: auto;">{{ old('meta_description') }}</textarea>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <a href="{{ route('admin.cms.pages.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Page</button>
        </div>
    </form>
</div>
@endsection
