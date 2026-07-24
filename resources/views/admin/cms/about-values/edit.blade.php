@extends('admin.layouts.app')

@section('title', __('Edit Value'))
@section('page-title', __('Edit Value'))

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.about-values.index') }}">{{ __('About Values') }}</a>
    <span class="sep">/</span>
    <span class="current">{{ __('Edit') }}</span>
@endsection

@section('content')
<div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cms.about-values.index') }}" class="btn-secondary">{{ __('← Back') }}</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">{{ __('Edit Value') }}</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cms.about-values.update', $value) }}">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">{{ __('Value Text') }}</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('Text (English)') }} <span class="req">*</span></label>
                    <input type="text" name="text[en]" class="form-input @error('text.en') err @enderror"
                           value="{{ old('text.en', $value->text['en'] ?? '') }}" required>
                    @error('text.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Text (Arabic)') }} <span class="req">*</span></label>
                    <input type="text" name="text[ar]" dir="rtl" class="form-input @error('text.ar') err @enderror"
                           value="{{ old('text.ar', $value->text['ar'] ?? '') }}" required>
                    @error('text.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">{{ __('Ordering & Visibility') }}</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('Sort Order') }} <span class="req">*</span></label>
                    <input type="number" name="sort_order" class="form-input"
                           value="{{ old('sort_order', $value->sort_order) }}" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-input" style="background: var(--in-bg); color: var(--text);">
                        <option value="active" {{ old('status', $value->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ old('status', $value->status) === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <a href="{{ route('admin.cms.about-values.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ __('Update Value') }}</button>
        </div>
    </form>
</div>
@endsection
