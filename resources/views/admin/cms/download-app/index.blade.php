@extends('admin.layouts.app')

@section('title', __('Download Application'))
@section('page-title', __('Download Application'))

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">{{ __('Website CMS') }}</span>
    <span class="sep">/</span>
    <span class="current">{{ __('Download Application') }}</span>
@endsection

@section('content')
<div>
    <h1 style="font-size:1.2rem;font-weight:700;margin-bottom:20px;">{{ __('Manage Download Application Page') }}</h1>

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

    <form method="POST" action="{{ route('admin.cms.download-app-page.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">{{ __('App Store Links') }}</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('Android URL') }}</label>
                    <input type="text" name="android_url" class="form-input @error('android_url') err @enderror"
                           value="{{ old('android_url', $downloadApp->android_url) }}" placeholder="{{ __('e.g. https://play.google.com/store/apps/details?id=...') }}">
                    @error('android_url')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('iOS URL') }}</label>
                    <input type="text" name="ios_url" class="form-input @error('ios_url') err @enderror"
                           value="{{ old('ios_url', $downloadApp->ios_url) }}" placeholder="{{ __('e.g. https://apps.apple.com/app/id...') }}">
                    @error('ios_url')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">{{ __('Badge') }}</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('Badge (English)') }}</label>
                    <input type="text" name="badge[en]" class="form-input @error('badge.en') err @enderror"
                           value="{{ old('badge.en', $downloadApp->badge['en'] ?? '') }}" placeholder="e.g. Available Now">
                    @error('badge.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Badge (Arabic)') }}</label>
                    <input type="text" name="badge[ar]" dir="rtl" class="form-input @error('badge.ar') err @enderror"
                           value="{{ old('badge.ar', $downloadApp->badge['ar'] ?? '') }}" placeholder="مثال: متوفر الآن">
                    @error('badge.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">{{ __('Section Title') }}</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('Title (English)') }} <span class="req">*</span></label>
                    <input type="text" name="title[en]" class="form-input @error('title.en') err @enderror"
                           value="{{ old('title.en', $downloadApp->title['en'] ?? '') }}" required>
                    @error('title.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Title (Arabic)') }} <span class="req">*</span></label>
                    <input type="text" name="title[ar]" dir="rtl" class="form-input @error('title.ar') err @enderror"
                           value="{{ old('title.ar', $downloadApp->title['ar'] ?? '') }}" required>
                    @error('title.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">{{ __('Section Subtitle') }}</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('Subtitle (English)') }}</label>
                    <textarea name="subtitle[en]" class="form-input @error('subtitle.en') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.en', $downloadApp->subtitle['en'] ?? '') }}</textarea>
                    @error('subtitle.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('Subtitle (Arabic)') }}</label>
                    <textarea name="subtitle[ar]" dir="rtl" class="form-input @error('subtitle.ar') err @enderror" rows="3" style="height:auto;">{{ old('subtitle.ar', $downloadApp->subtitle['ar'] ?? '') }}</textarea>
                    @error('subtitle.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">{{ __('Mockup Screenshot') }}</div>
            <div style="background: rgba(255,255,255,.02); border: 1px solid var(--bdr); border-radius: 10px; padding: 20px; margin-bottom: 15px;">
                @if($downloadApp->image_path)
                    <div style="margin-bottom: 15px;">
                        <span class="form-label" style="display:block; margin-bottom:8px;">{{ __('Current Image Preview:') }}</span>
                        <img src="{{ $downloadApp->image_path }}" alt="{{ __('Mockup screenshot') }}" style="max-width: 220px; height: auto; border-radius: 8px; border: 1px solid var(--bdr);">
                    </div>
                @endif
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">{{ __('Upload New Image File') }}</label>
                        <input type="file" name="image_file" class="form-input @error('image_file') err @enderror"
                               accept="image/*" style="height: auto; padding: 8px;">
                        <span style="font-size: .75rem; color: var(--text-dim); margin-top: 4px;">{{ __('Leave blank to keep current image. Max size: 4MB.') }}</span>
                        @error('image_file')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('Or External Image URL') }}</label>
                        <input type="text" name="image_path" class="form-input @error('image_path') err @enderror"
                               value="{{ old('image_path', $downloadApp->image_path) }}" placeholder="{{ __('e.g. https://example.com/mockup.png') }}">
                        @error('image_path')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <button type="submit" class="btn-primary">{{ __('Save Download Application Page') }}</button>
        </div>
    </form>
</div>
@endsection
