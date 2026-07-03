@extends('admin.layouts.app')

@section('title', 'Contact Information')
@section('page-title', 'Contact Information')

@section('breadcrumb')
    <span class="sep">/</span>
    <span class="current">Website CMS</span>
    <span class="sep">/</span>
    <span class="current">Contact Information</span>
@endsection

@section('content')
<div>
    <h1 style="font-size:1.2rem;font-weight:700;margin-bottom:20px;">Manage Contact Information</h1>

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

    <form method="POST" action="{{ route('admin.cms.contact-information.update') }}">
        @csrf
        @method('PUT')

        <div class="form-section">
            <div class="form-section-title">Contact Details</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="text" name="email" class="form-input @error('email') err @enderror"
                           value="{{ old('email', $contact->email) }}" placeholder="e.g. info@saee.com">
                    @error('email')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-input @error('phone') err @enderror"
                           value="{{ old('phone', $contact->phone) }}" placeholder="e.g. +966 12 345 6789">
                    @error('phone')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Address Link</label>
                    <input type="text" name="address_link" class="form-input @error('address_link') err @enderror"
                           value="{{ old('address_link', $contact->address_link) }}" placeholder="e.g. https://maps.google.com/?q=...">
                    @error('address_link')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Address Text</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Address (English)</label>
                    <textarea name="address_text[en]" class="form-input @error('address_text.en') err @enderror" rows="3" style="height:auto;">{{ old('address_text.en', $contact->address_text['en'] ?? '') }}</textarea>
                    @error('address_text.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Address (Arabic)</label>
                    <textarea name="address_text[ar]" dir="rtl" class="form-input @error('address_text.ar') err @enderror" rows="3" style="height:auto;">{{ old('address_text.ar', $contact->address_text['ar'] ?? '') }}</textarea>
                    @error('address_text.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-top:20px;">
            <div class="form-section-title">Working Hours</div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Working Hours (English)</label>
                    <input type="text" name="working_hours_text[en]" class="form-input @error('working_hours_text.en') err @enderror"
                           value="{{ old('working_hours_text.en', $contact->working_hours_text['en'] ?? '') }}" placeholder="e.g. Sun - Thu: 9AM - 6PM">
                    @error('working_hours_text.en')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Working Hours (Arabic)</label>
                    <input type="text" name="working_hours_text[ar]" dir="rtl" class="form-input @error('working_hours_text.ar') err @enderror"
                           value="{{ old('working_hours_text.ar', $contact->working_hours_text['ar'] ?? '') }}" placeholder="مثال: الأحد - الخميس: 9 صباحًا - 6 مساءً">
                    @error('working_hours_text.ar')<span class="form-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:20px;">
            <button type="submit" class="btn-primary">Save Contact Information</button>
        </div>
    </form>
</div>
@endsection
