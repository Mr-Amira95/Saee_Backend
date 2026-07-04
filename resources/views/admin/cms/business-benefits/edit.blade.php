@extends('admin.layouts.app')

@section('title', 'Edit Benefit')
@section('page-title', 'Edit Benefit')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.business-benefits.index') }}">Business Benefits</a>
    <span class="sep">/</span>
    <span class="current">Edit</span>
@endsection

@section('content')
<div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cms.business-benefits.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Edit Benefit</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cms.business-benefits.update', $benefit) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">Title</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Title (English) <span class="req">*</span></label>
                    <input type="text" name="title[en]" class="form-input @error('title.en') err @enderror"
                           value="{{ old('title.en', $benefit->title['en'] ?? '') }}" required>
                    @error('title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Title (Arabic) <span class="req">*</span></label>
                    <input type="text" name="title[ar]" dir="rtl" class="form-input @error('title.ar') err @enderror"
                           value="{{ old('title.ar', $benefit->title['ar'] ?? '') }}" required>
                    @error('title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Subtitle</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Subtitle (English)</label>
                    <textarea name="subtitle[en]" class="form-input @error('subtitle.en') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.en', $benefit->subtitle['en'] ?? '') }}</textarea>
                    @error('subtitle.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitle (Arabic)</label>
                    <textarea name="subtitle[ar]" dir="rtl" class="form-input @error('subtitle.ar') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.ar', $benefit->subtitle['ar'] ?? '') }}</textarea>
                    @error('subtitle.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Icon & Ordering</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Icon (SVG file)</label>
                    @if($benefit->icon_path)
                        <div style="margin-bottom: 10px;">
                            <img src="{{ $benefit->icon_path }}" alt="Current icon" style="width: 42px; height: 42px; object-fit: contain; border-radius: 8px; border: 1px solid var(--bdr); background: rgba(255,255,255,.05); padding: 6px;">
                        </div>
                    @endif
                    <input type="file" name="icon_file" class="form-input @error('icon_file') err @enderror"
                           accept="image/svg+xml" style="height: auto; padding: 8px;">
                    <span style="font-size: .75rem; color: var(--text-dim); margin-top: 4px;">Leave blank to keep current icon. Max size: 512KB.</span>
                    @error('icon_file')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Sort Order <span class="req">*</span></label>
                    <input type="number" name="sort_order" class="form-input"
                           value="{{ old('sort_order', $benefit->sort_order) }}" min="0" required>
                </div>
            </div>
            <div class="form-group" style="margin-top:15px;">
                <label class="form-label">Status</label>
                <select name="status" class="form-input" style="background: var(--in-bg); color: var(--text);">
                    <option value="active" {{ old('status', $benefit->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $benefit->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <a href="{{ route('admin.cms.business-benefits.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Update Benefit</button>
        </div>
    </form>
</div>
@endsection
