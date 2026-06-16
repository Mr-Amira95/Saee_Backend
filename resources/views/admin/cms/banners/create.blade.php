@extends('admin.layouts.app')

@section('title', 'Add Banner')
@section('page-title', 'Add Banner')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.banners.index') }}">Banners</a>
    <span class="sep">/</span>
    <span class="current">Add</span>
@endsection

@section('content')
<div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cms.banners.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Add Homepage Banner</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cms.banners.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-section">
            <div class="form-section-title">Banner Content</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Banner Title <span class="req">*</span></label>
                    <input type="text" name="title" class="form-input @error('title') err @enderror"
                           value="{{ old('title') }}" placeholder="e.g. Speed & Reliability" required>
                    @error('title')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Banner Subtitle</label>
                    <input type="text" name="subtitle" class="form-input"
                           value="{{ old('subtitle') }}" placeholder="e.g. Delivering across the country in record time.">
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Media Upload</div>
            <div style="background: rgba(255,255,255,.02); border: 1px solid var(--bdr); border-radius: 10px; padding: 20px; margin-bottom: 15px;">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Upload Image File</label>
                        <input type="file" name="image_file" class="form-input @error('image_file') err @enderror" 
                               accept="image/*" style="height: auto; padding: 8px;">
                        <span style="font-size: .75rem; color: var(--text-dim); margin-top: 4px;">Recommended dimensions: 1920x800. Max size: 4MB.</span>
                        @error('image_file')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Or External Image URL</label>
                        <input type="text" name="image_path" class="form-input @error('image_path') err @enderror"
                               value="{{ old('image_path') }}" placeholder="e.g. https://example.com/banner-bg.jpg">
                        <span style="font-size: .75rem; color: var(--text-dim); margin-top: 4px;">Used if no file is uploaded.</span>
                        @error('image_path')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Link Action & Ordering</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Link URL</label>
                    <input type="text" name="link_url" class="form-input"
                           value="{{ old('link_url') }}" placeholder="e.g. /orders/create or external https://">
                </div>

                <div class="form-group">
                    <label class="form-label">Link Text</label>
                    <input type="text" name="link_text" class="form-input"
                           value="{{ old('link_text') }}" placeholder="e.g. Ship Now">
                </div>

                <div class="form-group">
                    <label class="form-label">Sort Order <span class="req">*</span></label>
                    <input type="number" name="sort_order" class="form-input"
                           value="{{ old('sort_order', 0) }}" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input" style="background: var(--in-bg); color: var(--text);">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <a href="{{ route('admin.cms.banners.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Banner</button>
        </div>
    </form>
</div>
@endsection
