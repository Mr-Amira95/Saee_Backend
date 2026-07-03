@extends('admin.layouts.app')

@section('title', 'Add Capability')
@section('page-title', 'Add Capability')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.showcase-capabilities.index') }}">Capabilities</a>
    <span class="sep">/</span>
    <span class="current">Add</span>
@endsection

@section('content')
<div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cms.showcase-capabilities.index') }}" class="btn-secondary">&#8592; Back</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">Add Capability</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cms.showcase-capabilities.store') }}">
        @csrf

        <div class="form-section">
            <div class="form-section-title">Capability Details</div>

            <div class="form-group">
                <label class="form-label">Icon (Emoji or CSS class)</label>
                <input type="text" name="icon" class="form-input"
                       value="{{ old('icon', '⭐') }}" placeholder="e.g. ⚡, ✈️, 📦, truck">
            </div>

            <div class="form-grid-2" style="margin-top:15px;">
                <div class="form-group">
                    <label class="form-label">Title (English) <span class="req">*</span></label>
                    <input type="text" name="title[en]" class="form-input @error('title.en') err @enderror"
                           value="{{ old('title.en') }}" required>
                    @error('title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Title (Arabic) <span class="req">*</span></label>
                    <input type="text" name="title[ar]" dir="rtl" class="form-input @error('title.ar') err @enderror"
                           value="{{ old('title.ar') }}" required>
                    @error('title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-grid-2" style="margin-top:15px;">
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
            <div class="form-section-title">Ordering & Visibility</div>
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
            <a href="{{ route('admin.cms.showcase-capabilities.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save Capability</button>
        </div>
    </form>
</div>
@endsection
