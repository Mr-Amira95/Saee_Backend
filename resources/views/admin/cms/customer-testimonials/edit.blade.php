@extends('admin.layouts.app')

@section('title', __('Edit Testimonial'))
@section('page-title', __('Edit Testimonial'))

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.cms.customer-testimonials.index') }}">{{ __('Testimonials') }}</a>
    <span class="sep">/</span>
    <span class="current">{{ __('Edit') }}</span>
@endsection

@section('content')
<div>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.cms.customer-testimonials.index') }}" class="btn-secondary">{{ __('← Back') }}</a>
        <h1 style="font-size:1.2rem;font-weight:700;margin:0;">{{ __('Edit Testimonial') }}</h1>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.cms.customer-testimonials.update', $testimonial) }}">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">{{ __('Client') }}</div>
            <div class="form-group">
                <label class="form-label">{{ __('Client Name') }} <span class="req">*</span></label>
                <input type="text" name="client" class="form-input @error('client') err @enderror"
                       value="{{ old('client', $testimonial->client) }}" required>
                @error('client')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">{{ __('Feedback') }}</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('Feedback (English)') }} <span class="req">*</span></label>
                    <textarea name="feedback[en]" class="form-input @error('feedback.en') err @enderror" rows="4" style="height:auto;" required>{{ old('feedback.en', $testimonial->feedback['en'] ?? '') }}</textarea>
                    @error('feedback.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Feedback (Arabic)') }} <span class="req">*</span></label>
                    <textarea name="feedback[ar]" dir="rtl" class="form-input @error('feedback.ar') err @enderror" rows="4" style="height:auto;" required>{{ old('feedback.ar', $testimonial->feedback['ar'] ?? '') }}</textarea>
                    @error('feedback.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">{{ __('Ordering & Visibility') }}</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('Sort Order') }} <span class="req">*</span></label>
                    <input type="number" name="sort_order" class="form-input"
                           value="{{ old('sort_order', $testimonial->sort_order) }}" min="0" required>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-input" style="background: var(--in-bg); color: var(--text);">
                        <option value="active" {{ old('status', $testimonial->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ old('status', $testimonial->status) === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <a href="{{ route('admin.cms.customer-testimonials.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ __('Update Testimonial') }}</button>
        </div>
    </form>
</div>
@endsection
