@extends('admin.layouts.app')

@section('title', 'Add Flow Step')
@section('page-title', 'Add Flow Step')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.flow-steps.index') }}">Flow Steps</a>
    <span class="sep">/</span>
    <span class="current">Add</span>
@endsection

@section('content')
<div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cms.flow-steps.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Add Flow Step</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cms.flow-steps.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-section">
            <div class="form-section-title">Title</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Title (English) <span class="req">*</span></label>
                    <input type="text" name="title[en]" class="form-input @error('title.en') err @enderror"
                           value="{{ old('title.en') }}" placeholder="e.g. Place Your Order" required>
                    @error('title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Title (Arabic) <span class="req">*</span></label>
                    <input type="text" name="title[ar]" dir="rtl" class="form-input @error('title.ar') err @enderror"
                           value="{{ old('title.ar') }}" required>
                    @error('title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Subtitle</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Subtitle (English)</label>
                    <textarea name="subtitle[en]" class="form-input @error('subtitle.en') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.en') }}</textarea>
                    @error('subtitle.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitle (Arabic)</label>
                    <textarea name="subtitle[ar]" dir="rtl" class="form-input @error('subtitle.ar') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.ar') }}</textarea>
                    @error('subtitle.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Step Image</div>
            <div style="background: rgba(255,255,255,.02); border: 1px solid var(--bdr); border-radius: 10px; padding: 20px; margin-bottom: 15px;">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Upload Image File</label>
                        <input type="file" name="image_file" class="form-input @error('image_file') err @enderror"
                               accept="image/*" style="height: auto; padding: 8px;">
                        <span style="font-size: .75rem; color: var(--text-dim); margin-top: 4px;">Max size: 4MB.</span>
                        @error('image_file')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Or External Image URL</label>
                        <input type="text" name="image_path" class="form-input @error('image_path') err @enderror"
                               value="{{ old('image_path') }}" placeholder="e.g. https://example.com/step.jpg">
                        @error('image_path')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Ordering & Status</div>
            <div class="form-grid-2">
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
            <a href="{{ route('admin.cms.flow-steps.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Step</button>
        </div>
    </form>
</div>
@endsection
