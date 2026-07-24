@extends('admin.layouts.app')

@section('title', __('Edit Capability'))
@section('page-title', __('Edit Capability'))

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.showcase-capabilities.index') }}">{{ __('Capabilities') }}</a>
    <span class="sep">/</span>
    <span class="current">{{ __('Edit') }}</span>
@endsection

@section('content')
<div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cms.showcase-capabilities.index') }}" class="btn-secondary">{{ __('← Back') }}</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">{{ __('Edit Capability') }}</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cms.showcase-capabilities.update', $capability) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">{{ __('Capability Details') }}</div>

            <div class="form-group">
                <label class="form-label">{{ __('Icon (SVG file)') }}</label>
                @if($capability->icon_path)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ $capability->icon_path }}" alt="{{ __('Current icon') }}" style="width: 42px; height: 42px; object-fit: contain; border-radius: 8px; border: 1px solid var(--bdr); background: rgba(255,255,255,.05); padding: 6px;">
                    </div>
                @endif
                <input type="file" name="icon_file" class="form-input @error('icon_file') err @enderror"
                       accept="image/svg+xml" style="height: auto; padding: 8px;">
                <span style="font-size: .75rem; color: var(--text-dim); margin-top: 4px;">{{ __('Leave blank to keep current icon. Max size: 512KB.') }}</span>
                @error('icon_file')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-grid-2" style="margin-top:15px;">
                <div class="form-group">
                    <label class="form-label">{{ __('Title (English)') }} <span class="req">*</span></label>
                    <input type="text" name="title[en]" class="form-input @error('title.en') err @enderror"
                           value="{{ old('title.en', $capability->title['en'] ?? '') }}" required>
                    @error('title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Title (Arabic)') }} <span class="req">*</span></label>
                    <input type="text" name="title[ar]" dir="rtl" class="form-input @error('title.ar') err @enderror"
                           value="{{ old('title.ar', $capability->title['ar'] ?? '') }}" required>
                    @error('title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-grid-2" style="margin-top:15px;">
                <div class="form-group">
                    <label class="form-label">{{ __('Subtitle (English)') }}</label>
                    <textarea name="subtitle[en]" class="form-input @error('subtitle.en') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.en', $capability->subtitle['en'] ?? '') }}</textarea>
                    @error('subtitle.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Subtitle (Arabic)') }}</label>
                    <textarea name="subtitle[ar]" dir="rtl" class="form-input @error('subtitle.ar') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.ar', $capability->subtitle['ar'] ?? '') }}</textarea>
                    @error('subtitle.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">{{ __('Ordering & Visibility') }}</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('Sort Order') }} <span class="req">*</span></label>
                    <input type="number" name="sort_order" class="form-input"
                           value="{{ old('sort_order', $capability->sort_order) }}" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-input" style="background: var(--in-bg); color: var(--text);">
                        <option value="active" {{ old('status', $capability->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ old('status', $capability->status) === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <a href="{{ route('admin.cms.showcase-capabilities.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ __('Update Capability') }}</button>
        </div>
    </form>
</div>
@endsection
