@extends('admin.layouts.app')

@section('title', __('Edit –') . ' ' . ($driver->user->name ?? __('Driver')))

@section('page-title', __('Edit Driver'))

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
    <span>/</span>
    <a href="{{ route('admin.drivers.index') }}">{{ __('Drivers') }}</a>
    <span>/</span>
    <a href="{{ route('admin.drivers.show', $driver) }}">{{ $driver->user->name ?? '—' }}</a>
    <span>/</span>
    <span>{{ __('Edit') }}</span>
@endsection

@section('head')
<style>
.phone-wrap { display: flex; gap: 0; }
.phone-ext-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 0 10px; min-width: 110px; height: 42px;
    background: var(--in-bg); border: 1px solid var(--bdr);
    border-right: none; border-radius: 8px 0 0 8px;
    color: var(--text); cursor: pointer; user-select: none;
    white-space: nowrap; font-size: .9rem; transition: border-color .2s;
}
.phone-ext-btn:hover { border-color: var(--red); }
.phone-ext-btn .flag { font-size: 1.1rem; }
.phone-ext-btn .code { font-weight: 600; color: var(--red); }
.phone-ext-btn .arrow { margin-left: auto; font-size: .65rem; color: var(--text-sub); }
.phone-input-field {
    flex: 1; padding: 0 12px; height: 42px;
    background: var(--in-bg); border: 1px solid var(--bdr);
    border-radius: 0 8px 8px 0; color: var(--text); font-size: .9rem;
    transition: border-color .2s;
}
.phone-input-field:focus { outline: none; border-color: var(--red); }
.phone-dropdown {
    position: absolute; z-index: 500; top: calc(100% + 4px); left: 0;
    width: 300px; background: var(--bg-2); border: 1px solid var(--bdr);
    border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.35);
    display: none; overflow: hidden;
}
.phone-dropdown.open { display: block; }
.phone-dd-search {
    width: 100%; padding: 10px 12px; background: var(--bg);
    border: none; border-bottom: 1px solid var(--bdr);
    color: var(--text); font-size: .85rem; box-sizing: border-box;
}
.phone-dd-search:focus { outline: none; }
.phone-dd-list { max-height: 220px; overflow-y: auto; }
.phone-dd-item {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 12px; cursor: pointer; font-size: .88rem; transition: background .15s;
}
.phone-dd-item:hover { background: rgba(220,38,38,.12); }
.phone-dd-item .dd-flag { font-size: 1.1rem; }
.phone-dd-item .dd-name { flex: 1; color: var(--text); }
.phone-dd-item .dd-code { color: var(--red); font-weight: 600; font-size: .8rem; }

/* ── Field tooltip ── */
.field-tip {
    position: relative; display: inline-flex; align-items: center; justify-content: center;
    width: 16px; height: 16px; border-radius: 50%;
    background: rgba(220,38,38,.12); color: var(--red);
    font-size: .68rem; font-weight: 700; cursor: help;
    margin-left: 6px; vertical-align: middle;
}
.field-tip .field-tip-bubble {
    position: absolute; bottom: calc(100% + 8px); left: 50%;
    transform: translateX(-50%) translateY(4px);
    background: var(--bg-2); border: 1px solid var(--bdr); color: var(--text);
    padding: 8px 10px; border-radius: 8px; font-size: .72rem; font-weight: 400;
    line-height: 1.4; white-space: normal; width: max-content; max-width: 220px;
    opacity: 0; pointer-events: none; box-shadow: 0 8px 24px rgba(0,0,0,.4);
    transition: opacity .18s ease, transform .18s ease; z-index: 50;
}
.field-tip:hover .field-tip-bubble { opacity: 1; transform: translateX(-50%) translateY(0); }
</style>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.drivers.update', $driver) }}" enctype="multipart/form-data" novalidate>
    @csrf
    @method('PUT')

    {{-- Account --}}
    {{-- Account --}}
    <div class="form-section" style="position:relative;z-index:2;">
        <div class="form-section-title">{{ __('Account Credentials') }}</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="name">{{ __('Full Name') }} <span class="req">*</span></label>
                <input class="form-input @error('name') is-error @enderror" id="name" type="text" name="name" value="{{ old('name', $driver->user->name) }}" required>
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="username">{{ __('Username') }} <span class="req">*</span></label>
                <input class="form-input @error('username') is-error @enderror" id="username" type="text" name="username" value="{{ old('username', $driver->user->username) }}" required>
                @error('username')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="email">{{ __('Email') }} <span class="opt">({{ __('required for email channel') }})</span></label>
                <input class="form-input @error('email') is-error @enderror" id="email" type="email" name="email" value="{{ old('email', $driver->user->email) }}">
                @error('email')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group" style="position:relative; grid-column:span 2;">
                <label class="form-label">{{ __('Phone') }} <span class="opt">({{ __('required for WhatsApp') }})</span></label>
                <div class="phone-wrap">
                    <button type="button" class="phone-ext-btn" id="phoneExtBtn">
                        <span class="flag" id="phoneExtFlag">🇯🇴</span>
                        <span class="code" id="phoneExtCode">+962</span>
                        <span class="arrow">▼</span>
                    </button>
                    <input type="hidden" name="phone_country_code" id="phoneExtVal"
                           value="{{ old('phone_country_code', $driver->user->phone_country_code ?? '+962') }}">
                    <input type="text" name="phone" class="phone-input-field"
                           value="{{ old('phone', $driver->user->phone) }}" placeholder="7X XXX XXXX">
                </div>
                <div class="phone-dropdown" id="phoneExtDropdown">
                    <input type="text" class="phone-dd-search" placeholder="Search country or code…">
                    <div class="phone-dd-list" id="phoneExtList"></div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('OTP / Notification Channel') }}</label>
                @php $currentOtpChannel = old('otp_channel', $driver->user->otp_channel ?? 'whatsapp'); @endphp
                <div style="position:relative;display:inline-flex;align-items:center;background:var(--in-bg);border:1px solid var(--bdr);border-radius:8px;padding:3px;gap:2px;" id="otpChannelWrap">
                    <input type="hidden" name="otp_channel" id="otpChannelInput" value="{{ $currentOtpChannel }}">
                    <button type="button" id="btnOtpWhatsapp"
                        onclick="setOtpChannel('whatsapp')"
                        style="display:flex;align-items:center;gap:6px;padding:5px 12px;border:none;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;transition:background .2s,color .2s;{{ $currentOtpChannel === 'whatsapp' ? 'background:#25D366;color:#fff;' : 'background:transparent;color:var(--text-sub);' }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        {{ __('WhatsApp') }}
                    </button>
                    <button type="button" id="btnOtpEmail"
                        onclick="setOtpChannel('email')"
                        style="display:flex;align-items:center;gap:6px;padding:5px 12px;border:none;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;transition:background .2s,color .2s;{{ $currentOtpChannel === 'email' ? 'background:var(--red);color:#fff;' : 'background:transparent;color:var(--text-sub);' }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ __('Email') }}
                    </button>
                </div>
                <span style="font-size:.75rem;color:var(--text-dim);display:block;margin-top:6px;">{{ __('Used for password-reset OTPs and future invitations.') }}</span>
            </div>
        </div>
    </div>

    {{-- Identity & License --}}
    <div class="form-section">
        <div class="form-section-title">{{ __('Identity & License') }}</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="national_id">{{ __('National ID') }} <span class="req">*</span></label>
                <input class="form-input @error('national_id') is-error @enderror" id="national_id" type="text" name="national_id" value="{{ old('national_id', $driver->national_id) }}" placeholder="{{ __('10 digit number') }}" maxlength="10" inputmode="numeric" required>
                @error('national_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="national_id_attachment">{{ __('National ID Attachment') }} <span class="opt">({{ __('optional') }})</span>
                    <span class="field-tip">ⓘ<span class="field-tip-bubble">{{ __('Max file size: 10 MB. Supported formats: JPG, PNG, PDF.') }}</span></span>
                </label>
                @if($driver->national_id_attachment)
                <div style="margin-bottom:6px;font-size:.82rem;color:var(--text-sub);">
                    {{ __('Current:') }} <a href="{{ Storage::disk('public')->url($driver->national_id_attachment) }}" target="_blank" style="color:var(--red);">↗ {{ __('View file') }}</a>
                </div>
                @endif
                <input class="form-input @error('national_id_attachment') is-error @enderror" id="national_id_attachment" type="file" name="national_id_attachment" accept="image/*,.pdf" style="padding:6px 8px;">
                @error('national_id_attachment')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="license_number">{{ __('License Number') }} <span class="req">*</span></label>
                <input class="form-input @error('license_number') is-error @enderror" id="license_number" type="text" name="license_number" value="{{ old('license_number', $driver->license_number) }}" placeholder="{{ __('Letters and numbers only') }}" required>
                @error('license_number')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="license_expiry_date">{{ __('License Expiry') }} <span class="req">*</span></label>
                <input class="form-input @error('license_expiry_date') is-error @enderror" id="license_expiry_date" type="text" name="license_expiry_date" value="{{ old('license_expiry_date', $driver->license_expiry_date?->format('d-m-Y')) }}" placeholder="DD-MM-YYYY" maxlength="10" autocomplete="off" required>
                @error('license_expiry_date')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="license_attachment">{{ __('License Attachment') }} <span class="opt">({{ __('optional') }})</span>
                    <span class="field-tip">ⓘ<span class="field-tip-bubble">{{ __('Max file size: 10 MB. Supported formats: JPG, PNG, PDF.') }}</span></span>
                </label>
                @if($driver->license_attachment)
                <div style="margin-bottom:6px;font-size:.82rem;color:var(--text-sub);">
                    {{ __('Current:') }} <a href="{{ Storage::disk('public')->url($driver->license_attachment) }}" target="_blank" style="color:var(--red);">↗ {{ __('View file') }}</a>
                </div>
                @endif
                <input class="form-input" id="license_attachment" type="file" name="license_attachment" accept="image/*,.pdf" style="padding:6px 8px;">
                @error('license_attachment')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    {{-- Vehicle --}}
    <div class="form-section">
        <div class="form-section-title">{{ __('Vehicle Information') }}</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="vehicle_type">{{ __('Vehicle Type') }}</label>
                <input class="form-input" id="vehicle_type" type="text" name="vehicle_type" value="{{ old('vehicle_type', $driver->vehicle_type) }}">
            </div>
            <div class="form-group">
                <label class="form-label" for="vehicle_plate">{{ __('Plate Number') }}</label>
                <input class="form-input @error('vehicle_plate') is-error @enderror" id="vehicle_plate" type="text" name="vehicle_plate" value="{{ old('vehicle_plate', $driver->vehicle_plate) }}" placeholder="e.g. 12-345">
                @error('vehicle_plate')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="car_license_expiry">{{ __('Car License Expiry') }} <span class="opt">({{ __('optional') }})</span></label>
                <input class="form-input @error('car_license_expiry') is-error @enderror" id="car_license_expiry" type="text" name="car_license_expiry" value="{{ old('car_license_expiry', $driver->car_license_expiry?->format('d-m-Y')) }}" placeholder="DD-MM-YYYY" maxlength="10" autocomplete="off">
                @error('car_license_expiry')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="car_license_attachment">{{ __('Car License Attachment') }} <span class="opt">({{ __('optional') }})</span>
                    <span class="field-tip">ⓘ<span class="field-tip-bubble">{{ __('Max file size: 10 MB. Supported formats: JPG, PNG, PDF.') }}</span></span>
                </label>
                @if($driver->car_license_attachment)
                <div style="margin-bottom:6px;font-size:.82rem;color:var(--text-sub);">
                    {{ __('Current:') }} <a href="{{ Storage::disk('public')->url($driver->car_license_attachment) }}" target="_blank" style="color:var(--red);">↗ {{ __('View file') }}</a>
                </div>
                @endif
                <input class="form-input" id="car_license_attachment" type="file" name="car_license_attachment" accept="image/*,.pdf" style="padding:6px 8px;">
                @error('car_license_attachment')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    {{-- Bank Details --}}
    <div class="form-section">
        <div class="form-section-title">{{ __('Bank Details') }}
            <span class="opt" style="text-transform:none;font-size:.72rem;font-weight:400;">{{ __('optional') }}</span>
        </div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="bank_name">{{ __('Bank Name') }}</label>
                <input class="form-input @error('bank_name') is-error @enderror" id="bank_name" type="text" name="bank_name" value="{{ old('bank_name', $driver->bankDetail?->bank_name) }}" placeholder="e.g. Arab Bank">
                @error('bank_name')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="account_name">{{ __('Account Name') }}</label>
                <input class="form-input @error('account_name') is-error @enderror" id="account_name" type="text" name="account_name" value="{{ old('account_name', $driver->bankDetail?->account_name) }}">
                @error('account_name')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="account_number">{{ __('Account Number') }}</label>
                <input class="form-input @error('account_number') is-error @enderror" id="account_number" type="text" name="account_number" value="{{ old('account_number', $driver->bankDetail?->account_number) }}" style="font-family:monospace;">
                @error('account_number')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="iban">{{ __('IBAN') }}</label>
                <input class="form-input @error('iban') is-error @enderror" id="iban" type="text" name="iban" value="{{ old('iban', $driver->bankDetail?->iban) }}" style="font-family:monospace;" placeholder="JO…">
                @error('iban')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="swift_code">{{ __('SWIFT / BIC Code') }}</label>
                <input class="form-input @error('swift_code') is-error @enderror" id="swift_code" type="text" name="swift_code" value="{{ old('swift_code', $driver->bankDetail?->swift_code) }}" style="font-family:monospace;">
                @error('swift_code')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="cliq_id">{{ __('CliQ ID') }}</label>
                <div style="display:flex;gap:8px;">
                    <select class="form-input @error('cliq_alias_type') is-error @enderror" id="cliq_alias_type" name="cliq_alias_type" style="width:130px;flex-shrink:0;">
                        <option value="">{{ __('— Type —') }}</option>
                        <option value="alias" {{ old('cliq_alias_type', $driver->bankDetail?->cliq_alias_type) === 'alias' ? 'selected' : '' }}>{{ __('Alias') }}</option>
                        <option value="phone" {{ old('cliq_alias_type', $driver->bankDetail?->cliq_alias_type) === 'phone' ? 'selected' : '' }}>{{ __('Phone') }}</option>
                    </select>
                    <input class="form-input @error('cliq_id') is-error @enderror" id="cliq_id" type="text" name="cliq_id" value="{{ old('cliq_id', $driver->bankDetail?->cliq_id) }}" placeholder="{{ __('Phone number or Alias') }}" style="flex:1;">
                </div>
                @error('cliq_id')<span class="form-error">{{ $message }}</span>@enderror
                @error('cliq_alias_type')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group" style="grid-column:span 2;">
                <label class="form-label" for="bank_notes">{{ __('Notes') }}</label>
                <textarea class="form-input @error('bank_notes') is-error @enderror" id="bank_notes" name="bank_notes" rows="2" style="resize:vertical;">{{ old('bank_notes', $driver->bankDetail?->notes) }}</textarea>
                @error('bank_notes')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    {{-- Salary Settings --}}
    <div class="form-section">
        <div class="form-section-title">{{ __('Salary Settings') }}</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="basic_salary">{{ __('Basic Salary') }}</label>
                <input class="form-input @error('basic_salary') is-error @enderror" id="basic_salary" type="number"
                       name="basic_salary" value="{{ old('basic_salary', $driver->basic_salary) }}"
                       step="0.01" min="0" placeholder="0.00">
                @error('basic_salary')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="car_allowance">{{ __('Car & Gasoline Allowance') }}</label>
                <input class="form-input @error('car_allowance') is-error @enderror" id="car_allowance" type="number"
                       name="car_allowance" value="{{ old('car_allowance', $driver->car_allowance) }}"
                       step="0.01" min="0" placeholder="0.00">
                @error('car_allowance')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="daily_order_threshold">{{ __('Daily Order Threshold') }}</label>
                <input class="form-input @error('daily_order_threshold') is-error @enderror" id="daily_order_threshold" type="number"
                       name="daily_order_threshold" value="{{ old('daily_order_threshold', $driver->daily_order_threshold) }}"
                       min="0" placeholder="0">
                <span style="font-size:.75rem;color:var(--text-dim);margin-top:4px;display:block;">{{ __('Orders per day above this count earn a bonus') }}</span>
                @error('daily_order_threshold')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="bonus_per_extra_order">{{ __('Bonus Per Extra Order') }}</label>
                <input class="form-input @error('bonus_per_extra_order') is-error @enderror" id="bonus_per_extra_order" type="number"
                       name="bonus_per_extra_order" value="{{ old('bonus_per_extra_order', $driver->bonus_per_extra_order) }}"
                       step="0.01" min="0" placeholder="0.00">
                @error('bonus_per_extra_order')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.drivers.show', $driver) }}" class="btn-secondary">{{ __('Cancel') }}</a>
        <button type="submit" class="btn-primary">{{ __('Save Changes') }}</button>
    </div>
</form>
@endsection

@section('scripts')
<script>
const i18nDriverEdit = {
    fileSizeExceeded: @json(__(i18nDriverEdit.fileSizeExceeded)),
    cliqPhoneInvalid: @json(__(i18nDriverEdit.cliqPhoneInvalid)),
    cliqAliasInvalid: @json(__(i18nDriverEdit.cliqAliasInvalid)),
    nationalIdInvalid: @json(__(i18nDriverEdit.nationalIdInvalid)),
    plateNumberInvalid: @json(__(i18nDriverEdit.plateNumberInvalid)),
    validDateFormat: @json(__(i18nDriverEdit.validDateFormat)),
    fullNameInvalid: @json(__(i18nDriverEdit.fullNameInvalid)),
    usernameInvalid: @json(__(i18nDriverEdit.usernameInvalid)),
    emailInvalid: @json(__(i18nDriverEdit.emailInvalid)),
    phoneInvalid: @json(__(i18nDriverEdit.phoneInvalid)),
    bankNameInvalid: @json(__(i18nDriverEdit.bankNameInvalid)),
    accountNameInvalid: @json(__(i18nDriverEdit.accountNameInvalid)),
    ibanInvalid: @json(__(i18nDriverEdit.ibanInvalid)),
    swiftInvalid: @json(__(i18nDriverEdit.swiftInvalid)),
    accountNumberInvalid: @json(__(i18nDriverEdit.accountNumberInvalid)),
    fullNameRequired: @json(__(i18nDriverEdit.fullNameRequired)),
    usernameRequired: @json(__(i18nDriverEdit.usernameRequired)),
    nationalIdRequired: @json(__(i18nDriverEdit.nationalIdRequired)),
    licenseNumberRequired: @json(__(i18nDriverEdit.licenseNumberRequired)),
    licenseExpiryRequired: @json(__(i18nDriverEdit.licenseExpiryRequired)),
    licenseNumberInvalid: @json(__(i18nDriverEdit.licenseNumberInvalid)),
};

const COUNTRIES = [
    { flag:'🇯🇴', name:'Jordan',               code:'+962' },
    { flag:'🇸🇦', name:'Saudi Arabia',          code:'+966' },
    { flag:'🇦🇪', name:'UAE',                   code:'+971' },
    { flag:'🇰🇼', name:'Kuwait',                code:'+965' },
    { flag:'🇧🇭', name:'Bahrain',               code:'+973' },
    { flag:'🇶🇦', name:'Qatar',                 code:'+974' },
    { flag:'🇴🇲', name:'Oman',                  code:'+968' },
    { flag:'🇮🇶', name:'Iraq',                  code:'+964' },
    { flag:'🇸🇾', name:'Syria',                 code:'+963' },
    { flag:'🇱🇧', name:'Lebanon',               code:'+961' },
    { flag:'🇵🇸', name:'Palestine',             code:'+970' },
    { flag:'🇪🇬', name:'Egypt',                 code:'+20'  },
    { flag:'🇱🇾', name:'Libya',                 code:'+218' },
    { flag:'🇹🇳', name:'Tunisia',               code:'+216' },
    { flag:'🇩🇿', name:'Algeria',               code:'+213' },
    { flag:'🇲🇦', name:'Morocco',               code:'+212' },
    { flag:'🇸🇩', name:'Sudan',                 code:'+249' },
    { flag:'🇾🇪', name:'Yemen',                 code:'+967' },
    { flag:'🇹🇷', name:'Turkey',                code:'+90'  },
    { flag:'🇮🇳', name:'India',                 code:'+91'  },
    { flag:'🇵🇰', name:'Pakistan',              code:'+92'  },
    { flag:'🇬🇧', name:'United Kingdom',        code:'+44'  },
    { flag:'🇺🇸', name:'United States',         code:'+1'   },
    { flag:'🇩🇪', name:'Germany',               code:'+49'  },
    { flag:'🇫🇷', name:'France',                code:'+33'  },
    { flag:'🇦🇺', name:'Australia',             code:'+61'  },
    { flag:'🇸🇬', name:'Singapore',             code:'+65'  },
    { flag:'🇯🇵', name:'Japan',                 code:'+81'  },
    { flag:'🇨🇳', name:'China',                 code:'+86'  },
    { flag:'🇷🇺', name:'Russia',                code:'+7'   },
    { flag:'🇿🇦', name:'South Africa',          code:'+27'  },
    { flag:'🇧🇷', name:'Brazil',                code:'+55'  },
];

function initPhoneDropdown(btnId, flagId, codeId, valId, ddId, listId) {
    var btn      = document.getElementById(btnId);
    var flagEl   = document.getElementById(flagId);
    var codeEl   = document.getElementById(codeId);
    var valEl    = document.getElementById(valId);
    var dd       = document.getElementById(ddId);
    var listEl   = document.getElementById(listId);
    var searchEl = dd.querySelector('.phone-dd-search');
    var initial  = valEl.value || '+962';

    var match = COUNTRIES.find(function(c) { return c.code === initial; });
    if (match) { flagEl.textContent = match.flag; codeEl.textContent = match.code; }

    function renderList(q) {
        q = (q || '').toLowerCase();
        listEl.innerHTML = '';
        COUNTRIES.filter(function(c) { return !q || c.name.toLowerCase().includes(q) || c.code.includes(q); })
            .forEach(function(c) {
                var item = document.createElement('div');
                item.className = 'phone-dd-item';
                item.innerHTML = '<span class="dd-flag">' + c.flag + '</span><span class="dd-name">' + c.name + '</span><span class="dd-code">' + c.code + '</span>';
                item.addEventListener('click', function() {
                    flagEl.textContent = c.flag; codeEl.textContent = c.code; valEl.value = c.code;
                    dd.classList.remove('open');
                });
                listEl.appendChild(item);
            });
    }
    renderList('');
    btn.addEventListener('click', function(e) { e.stopPropagation(); dd.classList.toggle('open'); if (dd.classList.contains('open')) searchEl.focus(); });
    searchEl.addEventListener('input', function() { renderList(this.value); });
    document.addEventListener('click', function(e) { if (!dd.contains(e.target) && e.target !== btn) dd.classList.remove('open'); });
}

document.addEventListener('DOMContentLoaded', function() {
    initPhoneDropdown('phoneExtBtn','phoneExtFlag','phoneExtCode','phoneExtVal','phoneExtDropdown','phoneExtList');
});

function setOtpChannel(ch) {
    document.getElementById('otpChannelInput').value = ch;
    const wa = document.getElementById('btnOtpWhatsapp');
    const em = document.getElementById('btnOtpEmail');
    if (ch === 'whatsapp') {
        wa.style.background = '#25D366'; wa.style.color = '#fff';
        em.style.background = 'transparent'; em.style.color = 'var(--text-sub)';
    } else {
        em.style.background = 'var(--red)'; em.style.color = '#fff';
        wa.style.background = 'transparent'; wa.style.color = 'var(--text-sub)';
    }
}

/* ── Driver Form Validation ── */
(function() {
    var form = document.querySelector('form[novalidate]');
    if (!form) return;

    function getField(n) {
        return document.getElementById(n) || form.querySelector('[name="' + n + '"]');
    }

    function showFieldError(el, msg) {
        var container = el.closest ? (el.closest('.form-group') || el.parentElement) : el.parentElement;
        el.classList.add('is-error', 'js-marked');
        if (container.querySelector('.js-err')) return;
        var s = document.createElement('span');
        s.className = 'form-error js-err';
        s.textContent = msg;
        container.appendChild(s);
    }

    function clearErrors() {
        form.querySelectorAll('.js-err').forEach(function(e) { e.remove(); });
        form.querySelectorAll('.js-marked').forEach(function(e) { e.classList.remove('is-error', 'js-marked'); });
    }

    function isEmail(v) { return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(v.trim()); }
    function isValidName(v) { return /^[\p{L}\s]+$/u.test(v.trim()); }
    function isValidUsername(v) { return /^(?=.*[a-zA-Z])[a-zA-Z0-9]([a-zA-Z0-9_.-]*[a-zA-Z0-9])?$/.test(v.trim()); }
    function isValidPhone(v) { return /^[0-9]{6,15}$/.test(v.trim()); }
    function isValidEnglishName(v) { return /^[A-Za-z\s'.-]+$/.test(v.trim()); }
    function isValidIban(v) { return /^[A-Za-z]{2}[0-9]{2}[A-Za-z0-9]{1,30}$/.test(v.trim()); }
    function isValidSwift(v) { return /^[A-Za-z0-9]{8,11}$/.test(v.trim()); }
    function isValidAccountNumber(v) { return /^[0-9]+$/.test(v.trim()); }
    function isValidCliqPhone(v) { return /^7[789][0-9]{7}$/.test(v.trim()); }
    function isValidCliqAlias(v) { return /^[A-Za-z0-9]+$/.test(v.trim()); }
    function isValidNationalId(v) { return /^[0-9]{10}$/.test(v.trim()); }
    function isValidLicenseNumber(v) { return /^[A-Za-z0-9]+$/.test(v.trim()); }
    function isValidPlateNumber(v) { return /^\d{1,2}-\d{1,5}$/.test(v.trim()); }
    function isValidExpiryDate(v) {
        var m = v.trim().match(/^(\d{2})-(\d{2})-(\d{4})$/);
        if (!m) return false;
        var day = parseInt(m[1], 10), month = parseInt(m[2], 10), year = parseInt(m[3], 10);
        var d = new Date(year, month - 1, day);
        return d.getFullYear() === year && d.getMonth() === month - 1 && d.getDate() === day;
    }

    function clearFieldError(el) {
        var container = el.closest ? (el.closest('.form-group') || el.parentElement) : el.parentElement;
        el.classList.remove('is-error', 'js-marked');
        var err = container.querySelector('.js-err');
        if (err) err.remove();
    }

    /* ── File size pre-submit checks (max 10 MB) ── */
    ['national_id_attachment', 'license_attachment', 'car_license_attachment'].forEach(function(name) {
        var el = getField(name);
        if (!el) return;
        el.addEventListener('change', function() {
            clearFieldError(el);
            if (el.files[0] && el.files[0].size > 10 * 1024 * 1024) {
                showFieldError(el, i18nDriverEdit.fileSizeExceeded);
                el.value = '';
            }
        });
    });

    /* ── CliQ ID validation depends on selected type ── */
    var cliqTypeEl = getField('cliq_alias_type');
    var cliqIdEl   = getField('cliq_id');
    function validateCliq() {
        if (!cliqIdEl) return;
        clearFieldError(cliqIdEl);
        var type = cliqTypeEl ? cliqTypeEl.value : '';
        var val  = cliqIdEl.value.trim();
        if (!val) return;
        if (type === 'phone' && !isValidCliqPhone(val)) {
            showFieldError(cliqIdEl, i18nDriverEdit.cliqPhoneInvalid);
        } else if (type === 'alias' && !isValidCliqAlias(val)) {
            showFieldError(cliqIdEl, i18nDriverEdit.cliqAliasInvalid);
        }
    }
    if (cliqIdEl) cliqIdEl.addEventListener('input', validateCliq);
    if (cliqTypeEl) cliqTypeEl.addEventListener('change', validateCliq);

    /* ── National ID: digits only, exactly 10 ── */
    var nationalIdEl = getField('national_id');
    if (nationalIdEl) {
        nationalIdEl.addEventListener('input', function() {
            nationalIdEl.value = nationalIdEl.value.replace(/[^0-9]/g, '').slice(0, 10);
            clearFieldError(nationalIdEl);
        });
        nationalIdEl.addEventListener('blur', function() {
            if (nationalIdEl.value.trim() && !isValidNationalId(nationalIdEl.value)) {
                showFieldError(nationalIdEl, i18nDriverEdit.nationalIdInvalid);
            }
        });
    }

    /* ── License Number: alphanumeric only ── */
    var licenseNumberEl = getField('license_number');
    if (licenseNumberEl) {
        licenseNumberEl.addEventListener('input', function() {
            var filtered = licenseNumberEl.value.replace(/[^A-Za-z0-9]/g, '');
            if (filtered !== licenseNumberEl.value) licenseNumberEl.value = filtered;
            clearFieldError(licenseNumberEl);
        });
    }

    /* ── Plate Number: {1-2 digits}-{1-5 digits} ── */
    var platePlateEl = getField('vehicle_plate');
    if (platePlateEl) {
        platePlateEl.addEventListener('input', function() {
            var filtered = platePlateEl.value.replace(/[^0-9-]/g, '');
            if (filtered !== platePlateEl.value) platePlateEl.value = filtered;
            clearFieldError(platePlateEl);
        });
        platePlateEl.addEventListener('blur', function() {
            if (platePlateEl.value.trim() && !isValidPlateNumber(platePlateEl.value)) {
                showFieldError(platePlateEl, i18nDriverEdit.plateNumberInvalid);
            }
        });
    }

    /* ── Expiry date masks (DD-MM-YYYY) ── */
    function attachDateMask(name, msg) {
        var el = getField(name);
        if (!el) return;
        el.addEventListener('input', function() {
            var digits = el.value.replace(/[^0-9]/g, '').slice(0, 8);
            var out = digits.slice(0, 2);
            if (digits.length > 2) out += '-' + digits.slice(2, 4);
            if (digits.length > 4) out += '-' + digits.slice(4, 8);
            el.value = out;
            clearFieldError(el);
            if (out.length === 10 && !isValidExpiryDate(out)) {
                showFieldError(el, msg);
            }
        });
    }
    attachDateMask('license_expiry_date', i18nDriverEdit.validDateFormat);
    attachDateMask('car_license_expiry', i18nDriverEdit.validDateFormat);

    function wireLiveValidation(name, validator, msg) {
        var el = getField(name);
        if (!el) return;
        el.addEventListener('input', function() {
            clearFieldError(el);
            if (el.value.trim() && !validator(el.value)) showFieldError(el, msg);
        });
    }

    wireLiveValidation('name', isValidName, i18nDriverEdit.fullNameInvalid);
    wireLiveValidation('username', isValidUsername, i18nDriverEdit.usernameInvalid);
    wireLiveValidation('email', isEmail, i18nDriverEdit.emailInvalid);
    wireLiveValidation('phone', isValidPhone, i18nDriverEdit.phoneInvalid);
    wireLiveValidation('bank_name', isValidEnglishName, i18nDriverEdit.bankNameInvalid);
    wireLiveValidation('account_name', isValidEnglishName, i18nDriverEdit.accountNameInvalid);
    wireLiveValidation('iban', isValidIban, i18nDriverEdit.ibanInvalid);
    wireLiveValidation('swift_code', isValidSwift, i18nDriverEdit.swiftInvalid);
    wireLiveValidation('account_number', isValidAccountNumber, i18nDriverEdit.accountNumberInvalid);

    form.addEventListener('submit', function(e) {
        clearErrors();
        var first = null;

        function req(name, msg) {
            var el = getField(name);
            if (!el || el.value.trim()) return;
            showFieldError(el, msg);
            if (!first) first = el;
        }

        req('name',                i18nDriverEdit.fullNameRequired);
        req('username',            i18nDriverEdit.usernameRequired);
        req('national_id',         i18nDriverEdit.nationalIdRequired);
        req('license_number',      i18nDriverEdit.licenseNumberRequired);
        req('license_expiry_date', i18nDriverEdit.licenseExpiryRequired);

        var niEl = getField('national_id');
        if (niEl && niEl.value.trim() && !isValidNationalId(niEl.value)) {
            showFieldError(niEl, i18nDriverEdit.nationalIdInvalid);
            if (!first) first = niEl;
        }

        var lnEl = getField('license_number');
        if (lnEl && lnEl.value.trim() && !isValidLicenseNumber(lnEl.value)) {
            showFieldError(lnEl, i18nDriverEdit.licenseNumberInvalid);
            if (!first) first = lnEl;
        }

        var vpEl = getField('vehicle_plate');
        if (vpEl && vpEl.value.trim() && !isValidPlateNumber(vpEl.value)) {
            showFieldError(vpEl, i18nDriverEdit.plateNumberInvalid);
            if (!first) first = vpEl;
        }

        var leEl = getField('license_expiry_date');
        if (leEl && leEl.value.trim() && !isValidExpiryDate(leEl.value)) {
            showFieldError(leEl, i18nDriverEdit.validDateFormat);
            if (!first) first = leEl;
        }

        var cleEl = getField('car_license_expiry');
        if (cleEl && cleEl.value.trim() && !isValidExpiryDate(cleEl.value)) {
            showFieldError(cleEl, i18nDriverEdit.validDateFormat);
            if (!first) first = cleEl;
        }

        var nEl = getField('name');
        if (nEl && nEl.value.trim() && !isValidName(nEl.value)) {
            showFieldError(nEl, i18nDriverEdit.fullNameInvalid);
            if (!first) first = nEl;
        }

        var uEl = getField('username');
        if (uEl && uEl.value.trim() && !isValidUsername(uEl.value)) {
            showFieldError(uEl, i18nDriverEdit.usernameInvalid);
            if (!first) first = uEl;
        }

        var eEl = getField('email');
        if (eEl && eEl.value.trim() && !isEmail(eEl.value)) {
            showFieldError(eEl, i18nDriverEdit.emailInvalid);
            if (!first) first = eEl;
        }

        var phEl = getField('phone');
        if (phEl && phEl.value.trim() && !isValidPhone(phEl.value)) {
            showFieldError(phEl, i18nDriverEdit.phoneInvalid);
            if (!first) first = phEl;
        }

        var bnEl = getField('bank_name');
        if (bnEl && bnEl.value.trim() && !isValidEnglishName(bnEl.value)) {
            showFieldError(bnEl, i18nDriverEdit.bankNameInvalid);
            if (!first) first = bnEl;
        }

        var anEl = getField('account_name');
        if (anEl && anEl.value.trim() && !isValidEnglishName(anEl.value)) {
            showFieldError(anEl, i18nDriverEdit.accountNameInvalid);
            if (!first) first = anEl;
        }

        var ibEl = getField('iban');
        if (ibEl && ibEl.value.trim() && !isValidIban(ibEl.value)) {
            showFieldError(ibEl, i18nDriverEdit.ibanInvalid);
            if (!first) first = ibEl;
        }

        var swEl = getField('swift_code');
        if (swEl && swEl.value.trim() && !isValidSwift(swEl.value)) {
            showFieldError(swEl, i18nDriverEdit.swiftInvalid);
            if (!first) first = swEl;
        }

        var acEl = getField('account_number');
        if (acEl && acEl.value.trim() && !isValidAccountNumber(acEl.value)) {
            showFieldError(acEl, i18nDriverEdit.accountNumberInvalid);
            if (!first) first = acEl;
        }

        if (cliqIdEl && cliqIdEl.value.trim()) {
            var cType = cliqTypeEl ? cliqTypeEl.value : '';
            var cVal  = cliqIdEl.value.trim();
            if (cType === 'phone' && !isValidCliqPhone(cVal)) {
                showFieldError(cliqIdEl, i18nDriverEdit.cliqPhoneInvalid);
                if (!first) first = cliqIdEl;
            } else if (cType === 'alias' && !isValidCliqAlias(cVal)) {
                showFieldError(cliqIdEl, i18nDriverEdit.cliqAliasInvalid);
                if (!first) first = cliqIdEl;
            }
        }

        if (first) {
            e.preventDefault();
            first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(function() { try { first.focus(); } catch(x) {} }, 350);
        }
    });
})();
</script>
@endsection
