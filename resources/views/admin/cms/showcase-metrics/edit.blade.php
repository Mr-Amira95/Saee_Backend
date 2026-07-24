@extends('admin.layouts.app')

@section('title', __('Edit Metric'))
@section('page-title', __('Edit Metric'))

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.showcase-metrics.index') }}">{{ __('Showcase Metrics') }}</a>
    <span class="sep">/</span>
    <span class="current">{{ __('Edit') }}</span>
@endsection

@section('content')
<div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cms.showcase-metrics.index') }}" class="btn-secondary">{{ __('← Back') }}</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">{{ __('Edit Showcase Metric') }}</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cms.showcase-metrics.update', $metric) }}">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">{{ __('Metric Value') }}</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('Value (English)') }} <span class="req">*</span></label>
                    <input type="text" name="value[en]" class="form-input @error('value.en') err @enderror"
                           value="{{ old('value.en', $metric->value['en'] ?? '') }}" required>
                    @error('value.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Value (Arabic)') }} <span class="req">*</span></label>
                    <input type="text" name="value[ar]" dir="rtl" class="form-input @error('value.ar') err @enderror"
                           value="{{ old('value.ar', $metric->value['ar'] ?? '') }}" required>
                    @error('value.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">{{ __('Metric Label') }}</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('Label (English)') }} <span class="req">*</span></label>
                    <input type="text" name="key[en]" class="form-input @error('key.en') err @enderror"
                           value="{{ old('key.en', $metric->key['en'] ?? '') }}" required>
                    @error('key.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Label (Arabic)') }} <span class="req">*</span></label>
                    <input type="text" name="key[ar]" dir="rtl" class="form-input @error('key.ar') err @enderror"
                           value="{{ old('key.ar', $metric->key['ar'] ?? '') }}" required>
                    @error('key.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">{{ __('Ordering') }}</div>
            <div class="form-group">
                <label class="form-label">{{ __('Sort Order') }} <span class="req">*</span></label>
                <input type="number" name="sort_order" class="form-input"
                       value="{{ old('sort_order', $metric->sort_order) }}" min="0" required>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <a href="{{ route('admin.cms.showcase-metrics.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ __('Update Metric') }}</button>
        </div>
    </form>
</div>
@endsection
