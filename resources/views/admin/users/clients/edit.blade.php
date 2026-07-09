@extends('admin.layouts.app')

@section('title', 'Edit Client – ' . $client->company_name)

@section('page-title', 'Edit Client')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <span>/</span>
    <a href="{{ route('admin.clients.index') }}">Clients</a>
    <span>/</span>
    <a href="{{ route('admin.clients.show', $client) }}">{{ $client->company_name }}</a>
    <span>/</span>
    <span>Edit</span>
@endsection

@section('head')
<style>
/* ── Phone extension dropdown ── */
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
    border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.6);
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

/* ── Searchable select ── */
.searchable-select { position: relative; }
.searchable-select-btn {
    display: flex; align-items: center; justify-content: space-between;
    width: 100%; padding: 0 12px; height: 42px;
    background: var(--in-bg); border: 1px solid var(--bdr); border-radius: 8px;
    color: var(--text); cursor: pointer; font-size: .9rem; text-align: left;
    transition: border-color .2s; box-sizing: border-box;
}
.searchable-select-btn:hover,
.searchable-select-btn.is-open { border-color: var(--red); }
.searchable-select-btn .ss-text { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.searchable-select-btn .ss-arrow {
    font-size: .65rem; color: var(--text-sub); margin-left: 8px; flex-shrink: 0;
    transition: transform .2s;
}
.searchable-select-btn.is-open .ss-arrow { transform: rotate(180deg); }
.searchable-select-dd {
    position: absolute; z-index: 600; top: calc(100% + 4px); left: 0; right: 0;
    background: var(--bg-2); border: 1px solid var(--bdr); border-radius: 10px;
    box-shadow: 0 8px 30px rgba(0,0,0,.6); display: none; overflow: hidden;
}
.searchable-select-dd.is-open { display: block; }
.ss-search {
    display: block; width: 100%; padding: 10px 12px; background: var(--bg);
    border: none; border-bottom: 1px solid var(--bdr);
    color: var(--text); font-size: .85rem; box-sizing: border-box;
}
.ss-search:focus { outline: none; }
.ss-list { max-height: 200px; overflow-y: auto; }
.ss-item {
    padding: 9px 12px; cursor: pointer; font-size: .88rem; color: var(--text);
    transition: background .15s; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.ss-item:hover,
.ss-item.ss-selected { background: rgba(220,38,38,.12); }
.ss-item.ss-placeholder { color: var(--text-sub); }
.ss-item.ss-placeholder:hover { background: rgba(255,255,255,.04); }
.ss-empty { padding: 12px; text-align: center; color: var(--text-sub); font-size: .85rem; }

/* ── Logo upload ── */
.logo-upload-zone {
    border: 2px dashed var(--bdr); border-radius: 12px;
    padding: 24px; text-align: center; cursor: pointer;
    transition: border-color .2s, background .2s; position: relative;
}
.logo-upload-zone:hover { border-color: var(--red); background: rgba(220,38,38,.04); }
.logo-upload-zone input[type=file] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.logo-preview-img {
    max-width: 120px; max-height: 80px; object-fit: contain;
    border-radius: 8px; margin: 0 auto; display: block;
}
.logo-upload-icon { font-size: 2rem; color: var(--text-sub); margin-bottom: 6px; }
.logo-upload-hint { font-size: .8rem; color: var(--text-sub); }

/* ── Attachments ── */
.attach-row {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 10px; align-items: center;
    padding: 10px; background: var(--in-bg);
    border-radius: 8px; border: 1px solid var(--bdr); margin-bottom: 8px;
}
.btn-remove-att {
    background: rgba(220,38,38,.12); border: 1px solid rgba(220,38,38,.25);
    color: #dc2626; border-radius: 6px; padding: 6px 10px;
    cursor: pointer; font-size: .85rem; transition: background .2s;
}
.btn-remove-att:hover { background: rgba(220,38,38,.25); }
.btn-add-att {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; background: rgba(220,38,38,.1);
    border: 1px dashed rgba(220,38,38,.4); color: var(--red);
    border-radius: 8px; cursor: pointer; font-size: .88rem;
    transition: background .2s; margin-top: 4px;
}
.btn-add-att:hover { background: rgba(220,38,38,.2); }

/* ── Existing attachment item ── */
.exist-att {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 12px; background: var(--in-bg);
    border: 1px solid var(--bdr); border-radius: 8px; margin-bottom: 6px;
}
.exist-att-name { flex: 1; font-size: .88rem; color: var(--text); }
.exist-att-del { display: flex; align-items: center; gap: 6px; }
.exist-att-del label { font-size: .78rem; color: #f87171; cursor: pointer; }
</style>
@endsection

@section('content')

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

@php
    // Parse company phone and extensions
    $companyCode = $client->company_phone_country_code;
    $companyNumber = $client->company_phone ?? '';
    if (!$companyCode) {
        $companyPhoneRaw = $client->company_phone ?? '';
        if (str_starts_with($companyPhoneRaw, '+')) {
            $companyPhoneParts = explode(' ', $companyPhoneRaw, 2);
            $companyCode = $companyPhoneParts[0] ?? '+962';
            $companyNumber = $companyPhoneParts[1] ?? '';
            if (count($companyPhoneParts) < 2) {
                if (str_starts_with($companyPhoneRaw, '+962')) {
                    $companyCode = '+962';
                    $companyNumber = substr($companyPhoneRaw, 4);
                } elseif (str_starts_with($companyPhoneRaw, '+966')) {
                    $companyCode = '+966';
                    $companyNumber = substr($companyPhoneRaw, 4);
                }
            }
        } else {
            $companyCode = '+962';
            $companyNumber = $companyPhoneRaw;
        }
    }
@endphp

<form method="POST" action="{{ route('admin.clients.update', $client) }}" enctype="multipart/form-data" novalidate>
    @csrf
    @method('PUT')

    {{-- Account Owner --}}
    <div class="form-section" style="position:relative;z-index:3;">
        <div class="form-section-title">Account Owner (Master User)</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="name">Full Name <span class="req">*</span></label>
                <input class="form-input @error('name') is-error @enderror" id="name" type="text" name="name" value="{{ old('name', $client->masterUser->name) }}" placeholder="e.g. Ahmad Al-Hassan" required>
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="username">Username <span class="req">*</span></label>
                <input class="form-input @error('username') is-error @enderror" id="username" type="text" name="username" value="{{ old('username', $client->masterUser->username) }}" required>
                @error('username')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email Address <span class="opt">(required for email channel)</span></label>
                <input class="form-input @error('email') is-error @enderror" id="email" type="email" name="email" value="{{ old('email', $client->masterUser->email) }}" placeholder="owner@company.com">
                @error('email')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">Personal Phone <span class="opt">(required for WhatsApp)</span></label>
                <div style="position:relative;">
                    <div class="phone-wrap">
                        <button type="button" class="phone-ext-btn" id="personalExtBtn">
                            <span class="flag" id="personalExtFlag">🇯🇴</span>
                            <span class="code" id="personalExtCode">{{ old('phone_country_code', $client->masterUser->phone_country_code ?? '+962') }}</span>
                            <span class="arrow">▼</span>
                        </button>
                        <input type="hidden" name="phone_country_code" id="personalExtVal" value="{{ old('phone_country_code', $client->masterUser->phone_country_code ?? '+962') }}">
                        <input type="text" name="phone" id="phone" class="phone-input-field @error('phone') is-error @enderror"
                               value="{{ old('phone', $client->masterUser->phone) }}" placeholder="7X XXX XXXX">
                    </div>
                    <div class="phone-dropdown" id="personalExtDropdown">
                        <input type="text" class="phone-dd-search" placeholder="Search country or code…">
                        <div class="phone-dd-list" id="personalExtList"></div>
                    </div>
                </div>
                @error('phone')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">OTP / Notification Channel</label>
                @php $currentOtpChannel = old('otp_channel', $client->masterUser->otp_channel ?? 'whatsapp'); @endphp
                <div style="position:relative;display:inline-flex;align-items:center;background:var(--in-bg);border:1px solid var(--bdr);border-radius:8px;padding:3px;gap:2px;" id="otpChannelWrap">
                    <input type="hidden" name="otp_channel" id="otpChannelInput" value="{{ $currentOtpChannel }}">
                    <button type="button" id="btnOtpWhatsapp"
                        onclick="setOtpChannel('whatsapp')"
                        style="display:flex;align-items:center;gap:6px;padding:5px 12px;border:none;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;transition:background .2s,color .2s;{{ $currentOtpChannel === 'whatsapp' ? 'background:#25D366;color:#fff;' : 'background:transparent;color:var(--text-sub);' }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </button>
                    <button type="button" id="btnOtpEmail"
                        onclick="setOtpChannel('email')"
                        style="display:flex;align-items:center;gap:6px;padding:5px 12px;border:none;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;transition:background .2s,color .2s;{{ $currentOtpChannel === 'email' ? 'background:var(--red);color:#fff;' : 'background:transparent;color:var(--text-sub);' }}">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Email
                    </button>
                </div>
                <span style="font-size:.75rem;color:var(--text-dim);display:block;margin-top:6px;">Used for password-reset OTPs and future invitations.</span>
            </div>

        </div>
    </div>

    {{-- Company Information --}}
    <div class="form-section" style="position:relative;z-index:2;">
        <div class="form-section-title">Company Information</div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label" for="company_name">Company Name (EN) <span class="req">*</span></label>
                <input class="form-input @error('company_name') is-error @enderror" id="company_name" type="text" name="company_name" value="{{ old('company_name', $client->company_name) }}" placeholder="ACME Logistics Ltd." required>
                @error('company_name')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="company_name_ar">Company Name (AR) <span class="opt">(optional)</span></label>
                <input class="form-input" id="company_name_ar" type="text" name="company_name_ar" dir="rtl" value="{{ old('company_name_ar', $client->company_name_ar) }}" placeholder="اسم الشركة بالعربية">
            </div>
            <div class="form-group">
                <label class="form-label" for="commercial_register_number">Commercial Register No. <span class="opt">(optional)</span></label>
                <input class="form-input" id="commercial_register_number" type="text" name="commercial_register_number" value="{{ old('commercial_register_number', $client->commercial_register_number) }}" placeholder="CR-XXXXXXX">
            </div>
            <div class="form-group">
                <label class="form-label" for="vat_number">VAT Number <span class="opt">(optional)</span></label>
                <input class="form-input" id="vat_number" type="text" name="vat_number" value="{{ old('vat_number', $client->vat_number) }}" placeholder="VAT-XXXXXXX">
            </div>
            <div class="form-group">
                <label class="form-label" for="company_email">Company Email <span class="opt">(optional)</span></label>
                <input class="form-input @error('company_email') is-error @enderror" id="company_email" type="email" name="company_email" value="{{ old('company_email', $client->email) }}" placeholder="info@company.com">
                @error('company_email')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="company_phone">Company Phone <span class="opt">(optional)</span></label>
                <div style="position:relative;">
                    <div class="phone-wrap">
                        <button type="button" class="phone-ext-btn" id="companyExtBtn">
                            <span class="flag" id="companyExtFlag">🇯🇴</span>
                            <span class="code" id="companyExtCode">{{ old('company_phone_country_code', $companyCode) }}</span>
                            <span class="arrow">▼</span>
                        </button>
                        <input type="hidden" name="company_phone_country_code" id="companyExtVal" value="{{ old('company_phone_country_code', $companyCode) }}">
                        <input type="text" name="company_phone" id="company_phone" class="phone-input-field" value="{{ old('company_phone', $companyNumber) }}" placeholder="6 XXXX XXXX">
                    </div>
                    <div class="phone-dropdown" id="companyExtDropdown">
                        <input type="text" class="phone-dd-search" placeholder="Search country or code…">
                        <div class="phone-dd-list" id="companyExtList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Address --}}
    <div class="form-section" style="position:relative;z-index:1;">
        <div class="form-section-title">Address</div>
        <div class="form-grid-2">
            <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label" for="address_line1">Address Line <span class="opt">(optional)</span></label>
                <input class="form-input" id="address_line1" type="text" name="address_line1" value="{{ old('address_line1', $client->address_line1) }}" placeholder="Street, Building, Floor…">
            </div>

            <div class="form-group">
                <label class="form-label">Governorate <span class="opt">(optional)</span></label>
                <div class="searchable-select">
                    <button type="button" class="searchable-select-btn" id="citySelectBtn">
                        <span class="ss-text" style="color:var(--text-sub);">— Select Governorate —</span>
                        <span class="ss-arrow">▼</span>
                    </button>
                    <input type="hidden" name="city_id" id="citySelectVal" value="{{ old('city_id', $client->city_id) }}">
                    <div class="searchable-select-dd" id="citySelectDd">
                        <input type="text" class="ss-search" placeholder="Search governorate…">
                        <div class="ss-list" id="citySelectList">
                            <div class="ss-item ss-placeholder" data-value="">— Select Governorate —</div>
                            @foreach($cities as $city)
                            <div class="ss-item{{ old('city_id', $client->city_id) == $city->id ? ' ss-selected' : '' }}" data-value="{{ $city->id }}">{{ $city->name }}{{ $city->name_ar ? ' ('.$city->name_ar.')' : '' }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @error('city_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Area / District <span class="opt">(optional)</span></label>
                <div class="searchable-select">
                    <button type="button" class="searchable-select-btn" id="areaSelectBtn"
                            style="opacity:.6;cursor:not-allowed;pointer-events:none;">
                        <span class="ss-text" style="color:var(--text-sub);">— Select Governorate First —</span>
                        <span class="ss-arrow">▼</span>
                    </button>
                    <input type="hidden" name="area_id" id="areaSelectVal" value="{{ old('area_id', $client->area_id) }}">
                    <div class="searchable-select-dd" id="areaSelectDd">
                        <input type="text" class="ss-search" placeholder="Search area…">
                        <div class="ss-list" id="areaSelectList">
                            <div class="ss-item ss-placeholder" data-value="">— Select Governorate First —</div>
                        </div>
                    </div>
                </div>
                @error('area_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    {{-- Financial & Account Settings --}}
    <div class="form-section">
        <div class="form-section-title">Financial &amp; Account Settings</div>
        <div class="form-grid-2">
            <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label">Order Settings</label>
                <label style="display:inline-flex;align-items:center;gap:10px;cursor:pointer;user-select:none;padding:10px 14px;background:var(--in-bg);border:1px solid var(--bdr);border-radius:8px;">
                    <input type="hidden" name="require_national_id" value="0">
                    <input type="checkbox" name="require_national_id" id="require_national_id" value="1"
                           {{ old('require_national_id', $client->require_national_id) ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:var(--red);cursor:pointer;">
                    <span style="font-size:.9rem;color:var(--text);">Require national ID attachment on delivery</span>
                    <span style="font-size:.78rem;color:var(--text-sub);">(driver must upload a copy of the receiver's national ID when marking the order as delivered)</span>
                </label>
            </div>
            <div class="form-group">
                <label class="form-label" for="credit_limit">Credit Limit <span class="opt">(optional)</span></label>
                <div id="creditLimitWrap"
                     style="display:flex;align-items:stretch;border:1px solid var(--bdr);border-radius:8px;overflow:hidden;background:var(--in-bg);transition:border-color .2s;">
                    <input type="number" name="credit_limit" id="credit_limit"
                           class="@error('credit_limit') is-error @enderror"
                           value="{{ old('credit_limit', $client->credit_limit) }}" min="0" step="0.01"
                           style="flex:1;border:none;background:transparent;padding:0 12px;height:42px;color:var(--text);font-size:.9rem;outline:none;min-width:0;"
                           onfocus="document.getElementById('creditLimitWrap').style.borderColor='var(--red)'"
                           onblur="document.getElementById('creditLimitWrap').style.borderColor='var(--bdr)'">
                    <span style="display:flex;align-items:center;padding:0 14px;font-size:.82rem;font-weight:700;color:var(--red);background:rgba(220,38,38,.06);border-left:1px solid var(--bdr);white-space:nowrap;flex-shrink:0;">JD</span>
                </div>
                @error('credit_limit')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="expiry_date">Account Expiry Date <span class="opt">(optional)</span></label>
                <input class="form-input @error('expiry_date') is-error @enderror" id="expiry_date" type="date" name="expiry_date"
                       value="{{ old('expiry_date', $client->expiry_date ? $client->expiry_date->format('Y-m-d') : '') }}">
                @error('expiry_date')<span class="form-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    {{-- Banking Details --}}
    <div class="form-section">
        <div class="form-section-title">Banking Details
            <span class="opt" style="text-transform:none;font-size:.72rem;font-weight:400;">optional</span>
        </div>
        <div class="form-grid-2">
            @php $bd = $client->bankDetail; @endphp
            <div class="form-group">
                <label class="form-label" for="bank_name">Bank Name</label>
                <input class="form-input" id="bank_name" type="text" name="bank_name"
                       value="{{ old('bank_name', $bd->bank_name ?? '') }}" placeholder="e.g. Arab Bank, Cairo Amman Bank">
            </div>
            <div class="form-group">
                <label class="form-label" for="account_name">Account Holder Name</label>
                <input class="form-input" id="account_name" type="text" name="account_name"
                       value="{{ old('account_name', $bd->account_name ?? '') }}" placeholder="As it appears on the bank account">
            </div>
            <div class="form-group">
                <label class="form-label" for="iban">IBAN</label>
                <input class="form-input @error('iban') is-error @enderror" id="iban" type="text"
                       name="iban" value="{{ old('iban', $bd->iban ?? '') }}"
                       placeholder="JO94CBJO0010000000000131000302"
                       style="font-family:monospace;letter-spacing:.03em;"
                       oninput="this.value=this.value.toUpperCase().replace(/\s/g,'')">
                @error('iban')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="swift_code">SWIFT / BIC Code</label>
                <input class="form-input @error('swift_code') is-error @enderror" id="swift_code" type="text"
                       name="swift_code" value="{{ old('swift_code', $bd->swift_code ?? '') }}"
                       placeholder="e.g. ARABJOAX"
                       style="font-family:monospace;text-transform:uppercase;"
                       oninput="this.value=this.value.toUpperCase()">
                @error('swift_code')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="account_number">Account Number</label>
                <input class="form-input" id="account_number" type="text" name="account_number"
                       value="{{ old('account_number', $bd->account_number ?? '') }}" placeholder="Bank account number"
                       style="font-family:monospace;">
            </div>
            <div class="form-group">
                <label class="form-label">CliQ ID</label>
                <div style="display:flex;gap:8px;">
                    <select name="cliq_alias_type" class="form-select" style="width:150px;flex-shrink:0;">
                        <option value="">— Type —</option>
                        <option value="alias" {{ old('cliq_alias_type', $bd->cliq_alias_type ?? '') === 'alias' ? 'selected' : '' }}>Alias</option>
                        <option value="phone" {{ old('cliq_alias_type', $bd->cliq_alias_type ?? '') === 'phone' ? 'selected' : '' }}>Phone</option>
                    </select>
                    <input class="form-input @error('cliq_id') is-error @enderror" type="text"
                           name="cliq_id" value="{{ old('cliq_id', $bd->cliq_id ?? '') }}"
                           placeholder="Phone number or National ID" style="flex:1;">
                </div>
                @error('cliq_id')<span class="form-error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group" style="grid-column:1/-1;">
                <label class="form-label" for="bank_notes">Bank Notes</label>
                <textarea class="form-input" id="bank_notes" name="bank_notes" rows="2"
                          style="resize:vertical;height:auto;"
                          placeholder="Any additional payment or transfer instructions…">{{ old('bank_notes', $bd->notes ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Delivery Prices --}}
    <div class="form-section" style="padding:0;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--bdr);">
            <div class="form-section-title" style="margin:0;padding:0;border:none;">
                Delivery Prices per Governorate
                <span style="font-size:.72rem;font-weight:400;color:var(--text-dim);text-transform:none;margin-left:8px;">
                    Leave blank to use city default
                </span>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Governorate</th>
                        <th style="width:160px;">Default Price</th>
                        <th style="width:200px;">Custom Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cities as $city)
                    @php
                        $customPrice = $existingPrices->get($city->id);
                    @endphp
                    <tr>
                        <td>
                            <div class="cell-main">{{ $city->name }}</div>
                            @if($city->name_ar)
                            <div style="font-size:.8rem;color:var(--text-sub);" dir="rtl">{{ $city->name_ar }}</div>
                            @endif
                            @if(!$city->is_active)
                            <span class="badge-suspended" style="font-size:.7rem;">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-weight:600;color:var(--text-sub);">{{ number_format($city->delivery_price, 2) }} JD</span>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <input type="number"
                                       name="delivery_prices[{{ $city->id }}]"
                                       value="{{ old('delivery_prices.'.$city->id, $customPrice) }}"
                                       min="0" step="0.01"
                                       placeholder="{{ number_format($city->delivery_price, 2) }}"
                                       style="width:110px;height:36px;padding:0 10px;background:var(--in-bg);border:1px solid var(--bdr);border-radius:6px;color:var(--text);font-size:.88rem;outline:none;">
                                <span style="font-size:.82rem;font-weight:700;color:var(--red);">JD</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Company Logo --}}
    <div class="form-section">
        <div class="form-section-title">Company Logo
            <span class="opt" style="text-transform:none;font-size:.72rem;font-weight:400;">optional · max 2 MB · JPG/PNG/WEBP/SVG</span>
        </div>
        <div class="logo-upload-zone" id="logoZone">
            <input type="file" name="logo" id="logoInput" accept="image/*">
            @if($client->logo_path)
            <div id="logoPlaceholder" style="display:none;">
                <div class="logo-upload-icon">&#128444;</div>
                <div style="font-size:.9rem;color:var(--text);margin-bottom:4px;">Click to upload logo</div>
                <div class="logo-upload-hint">PNG, JPG, WEBP, SVG — up to 2 MB</div>
            </div>
            <div id="logoPreviewWrap" style="pointer-events:none;">
                <img id="logoPreviewImg" class="logo-preview-img" src="{{ Storage::disk('public')->url($client->logo_path) }}" alt="Logo preview">
                <div style="font-size:.78rem;color:var(--text-sub);margin-top:6px;" id="logoFileName">Current Logo</div>
            </div>
            @else
            <div id="logoPlaceholder">
                <div class="logo-upload-icon">&#128444;</div>
                <div style="font-size:.9rem;color:var(--text);margin-bottom:4px;">Click to upload logo</div>
                <div class="logo-upload-hint">PNG, JPG, WEBP, SVG — up to 2 MB</div>
            </div>
            <div id="logoPreviewWrap" style="display:none;pointer-events:none;">
                <img id="logoPreviewImg" class="logo-preview-img" src="" alt="Logo preview">
                <div style="font-size:.78rem;color:var(--text-sub);margin-top:6px;" id="logoFileName"></div>
            </div>
            @endif
        </div>
        <button type="button" id="clearLogoBtn" onclick="clearLogo()"
                style="display:{{ $client->logo_path ? 'inline' : 'none' }};margin-top:6px;font-size:.78rem;color:#dc2626;background:none;border:none;cursor:pointer;padding:0;">
            &#10005; Remove logo
        </button>
        @error('logo')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    {{-- Attachments --}}
    <div class="form-section">
        <div class="form-section-title">Attachments
            <span class="opt" style="text-transform:none;font-size:.72rem;font-weight:400;">optional · up to 10 MB each</span>
        </div>
        
        {{-- Existing files check --}}
        @if($client->attachments->count())
        <div style="margin-bottom: 16px;">
            <div style="font-size: .8rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; margin-bottom: 8px;">Existing Attachments</div>
            @foreach($client->attachments as $att)
            <div class="exist-att" id="exist-att-{{ $att->id }}">
                <span class="exist-att-name">{{ $att->label }} ({{ $att->original_filename }})</span>
                <div class="exist-att-del">
                    <input type="checkbox" name="delete_attachment_ids[]" value="{{ $att->id }}" id="del-att-{{ $att->id }}">
                    <label for="del-att-{{ $att->id }}">Delete</label>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div id="attachContainer"></div>
        <button type="button" class="btn-add-att" onclick="addAttachRow()">
            <span style="font-size:1.1rem;line-height:1;">+</span> Add Attachment
        </button>
        @error('attachment_files.*')<span class="form-error">{{ $message }}</span>@enderror
        @error('attachment_labels.*')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-actions">
        <a href="{{ route('admin.clients.show', $client) }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary">Save Changes</button>
    </div>

</form>

{{-- Warning Toast --}}
<div id="validationToast" style="display: none; position: fixed; bottom: 24px; {{ app()->getLocale() === 'ar' ? 'left: 24px;' : 'right: 24px;' }} z-index: 9999; align-items: center; gap: 12px; background: rgba(12, 18, 48, 0.95); border: 1px solid #f59e0b; border-radius: 12px; padding: 14px 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); backdrop-filter: blur(8px); transform: translateY(40px); opacity: 0; transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease-out; max-width: 380px;">
    <div style="width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: bold; flex-shrink: 0; border: 1px solid; color: #fbbf24; background: rgba(245,158,11,.1); border-color: rgba(245,158,11,.2);">!</div>
    <div style="flex: 1;">
        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text);">Warning</div>
        <div id="validationToastMessage" style="font-size: 0.78rem; color: var(--text-sub); margin-top: 2px; line-height: 1.3;"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
/* ── Country phone data ── */
const COUNTRIES = [
    { flag:'🇯🇴', name:'Jordan',         code:'+962' },
    { flag:'🇸🇦', name:'Saudi Arabia',    code:'+966' },
    { flag:'🇦🇪', name:'UAE',             code:'+971' },
    { flag:'🇰🇼', name:'Kuwait',          code:'+965' },
    { flag:'🇧🇭', name:'Bahrain',         code:'+973' },
    { flag:'🇶🇦', name:'Qatar',           code:'+974' },
    { flag:'🇴🇲', name:'Oman',            code:'+968' },
    { flag:'🇮🇶', name:'Iraq',            code:'+964' },
    { flag:'🇸🇾', name:'Syria',           code:'+963' },
    { flag:'🇱🇧', name:'Lebanon',         code:'+961' },
    { flag:'🇵🇸', name:'Palestine',       code:'+970' },
    { flag:'🇪🇬', name:'Egypt',           code:'+20'  },
    { flag:'🇱🇾', name:'Libya',           code:'+218' },
    { flag:'🇹🇳', name:'Tunisia',         code:'+216' },
    { flag:'🇩🇿', name:'Algeria',         code:'+213' },
    { flag:'🇲🇦', name:'Morocco',         code:'+212' },
    { flag:'🇸🇩', name:'Sudan',           code:'+249' },
    { flag:'🇾🇪', name:'Yemen',           code:'+967' },
    { flag:'🇹🇷', name:'Turkey',          code:'+90'  },
    { flag:'🇮🇳', name:'India',           code:'+91'  },
    { flag:'🇵🇰', name:'Pakistan',        code:'+92'  },
    { flag:'🇧🇩', name:'Bangladesh',      code:'+880' },
    { flag:'🇵🇭', name:'Philippines',     code:'+63'  },
    { flag:'🇮🇩', name:'Indonesia',       code:'+62'  },
    { flag:'🇬🇧', name:'United Kingdom',  code:'+44'  },
    { flag:'🇺🇸', name:'United States',   code:'+1'   },
    { flag:'🇨🇦', name:'Canada',          code:'+1'   },
    { flag:'🇩🇪', name:'Germany',         code:'+49'  },
    { flag:'🇫🇷', name:'France',          code:'+33'  },
    { flag:'🇮🇹', name:'Italy',           code:'+39'  },
    { flag:'🇪🇸', name:'Spain',           code:'+34'  },
    { flag:'🇳🇱', name:'Netherlands',     code:'+31'  },
    { flag:'🇸🇪', name:'Sweden',          code:'+46'  },
    { flag:'🇳🇴', name:'Norway',          code:'+47'  },
    { flag:'🇩🇰', name:'Denmark',         code:'+45'  },
    { flag:'🇨🇭', name:'Switzerland',     code:'+41'  },
    { flag:'🇦🇺', name:'Australia',       code:'+61'  },
    { flag:'🇳🇿', name:'New Zealand',     code:'+64'  },
    { flag:'🇸🇬', name:'Singapore',       code:'+65'  },
    { flag:'🇲🇾', name:'Malaysia',        code:'+60'  },
    { flag:'🇹🇭', name:'Thailand',        code:'+66'  },
    { flag:'🇯🇵', name:'Japan',           code:'+81'  },
    { flag:'🇨🇳', name:'China',           code:'+86'  },
    { flag:'🇰🇷', name:'South Korea',     code:'+82'  },
    { flag:'🇷🇺', name:'Russia',          code:'+7'   },
    { flag:'🇿🇦', name:'South Africa',    code:'+27'  },
    { flag:'🇳🇬', name:'Nigeria',         code:'+234' },
    { flag:'🇰🇪', name:'Kenya',           code:'+254' },
    { flag:'🇧🇷', name:'Brazil',          code:'+55'  },
    { flag:'🇲🇽', name:'Mexico',          code:'+52'  },
];

/* ── Phone extension dropdown factory ── */
function initPhoneDropdown(btnId, flagId, codeId, valId, ddId, listId) {
    const btn      = document.getElementById(btnId);
    const flagEl   = document.getElementById(flagId);
    const codeEl   = document.getElementById(codeId);
    const valEl    = document.getElementById(valId);
    const dd       = document.getElementById(ddId);
    const listEl   = document.getElementById(listId);
    const searchEl = dd.querySelector('.phone-dd-search');

    function renderList(q) {
        q = (q || '').toLowerCase();
        listEl.innerHTML = '';
        COUNTRIES
            .filter(c => !q || c.name.toLowerCase().includes(q) || c.code.includes(q))
            .forEach(c => {
                const item = document.createElement('div');
                item.className = 'phone-dd-item';
                item.innerHTML =
                    '<span class="dd-flag">' + c.flag + '</span>' +
                    '<span class="dd-name">' + c.name + '</span>' +
                    '<span class="dd-code">' + c.code + '</span>';
                item.addEventListener('click', function() {
                    flagEl.textContent = c.flag;
                    codeEl.textContent = c.code;
                    valEl.value = c.code;
                    dd.classList.remove('open');
                });
                listEl.appendChild(item);
            });
    }

    renderList('');
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        dd.classList.toggle('open');
        if (dd.classList.contains('open')) searchEl.focus();
    });
    searchEl.addEventListener('input', function() { renderList(this.value); });
    document.addEventListener('click', function(e) {
        if (!dd.contains(e.target) && e.target !== btn) dd.classList.remove('open');
    });
}

/* ── Searchable select factory ── */
function initSearchableSelect(btnId, ddId, listId, hiddenId, opts) {
    opts = opts || {};
    var btn    = document.getElementById(btnId);
    var dd     = document.getElementById(ddId);
    var listEl = document.getElementById(listId);
    var hidden = document.getElementById(hiddenId);
    var textEl = btn.querySelector('.ss-text');
    var search = dd.querySelector('.ss-search');

    function applyFilter() {
        var q = search.value.toLowerCase();
        var visible = 0;
        Array.from(listEl.querySelectorAll('.ss-item')).forEach(function(el) {
            if (el.classList.contains('ss-placeholder')) {
                el.style.display = q ? 'none' : '';
                return;
            }
            var show = !q || el.textContent.toLowerCase().includes(q);
            el.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        var emptyEl = listEl.querySelector('.ss-empty');
        if (q && visible === 0) {
            if (!emptyEl) {
                emptyEl = document.createElement('div');
                emptyEl.className = 'ss-empty';
                listEl.appendChild(emptyEl);
            }
            emptyEl.textContent = 'No results for "' + search.value + '"';
            emptyEl.style.display = '';
        } else if (emptyEl) {
            emptyEl.style.display = 'none';
        }
    }

    function selectItem(val, txt, isPh) {
        hidden.value = val;
        textEl.textContent = txt;
        textEl.style.color = isPh ? 'var(--text-sub)' : '';
        listEl.querySelectorAll('.ss-selected').forEach(function(el) { el.classList.remove('ss-selected'); });
        if (!isPh) {
            var found = listEl.querySelector('[data-value="' + val + '"]');
            if (found) found.classList.add('ss-selected');
        }
        closeSelect();
        if (opts.onChange) opts.onChange(val, txt);
    }

    function wireItem(el) {
        el.addEventListener('click', function() {
            selectItem(el.dataset.value, el.textContent.trim(), el.classList.contains('ss-placeholder'));
        });
    }

    function openSelect() {
        document.querySelectorAll('.searchable-select-dd.is-open').forEach(function(el) {
            if (el !== dd) {
                el.classList.remove('is-open');
                var parentBtn = el.parentElement.querySelector('.searchable-select-btn');
                if (parentBtn) parentBtn.classList.remove('is-open');
            }
        });
        dd.classList.add('is-open');
        btn.classList.add('is-open');
        search.value = '';
        applyFilter();
        search.focus();
    }

    function closeSelect() {
        dd.classList.remove('is-open');
        btn.classList.remove('is-open');
    }

    // Wire Blade-rendered items
    Array.from(listEl.querySelectorAll('.ss-item')).forEach(wireItem);

    // Restore display text from old() value
    var initVal = hidden.value;
    if (initVal) {
        var initEl = listEl.querySelector('[data-value="' + initVal + '"]');
        if (initEl && !initEl.classList.contains('ss-placeholder')) {
            textEl.textContent = initEl.textContent.trim();
            textEl.style.color = '';
            initEl.classList.add('ss-selected');
        }
    }

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        dd.classList.contains('is-open') ? closeSelect() : openSelect();
    });
    search.addEventListener('input', applyFilter);
    document.addEventListener('click', function(e) {
        if (!dd.contains(e.target) && e.target !== btn) closeSelect();
    });

    // Public API
    btn._populate = function(items, placeholder, restoreValue) {
        listEl.innerHTML = '';
        var ph = document.createElement('div');
        ph.className = 'ss-item ss-placeholder';
        ph.dataset.value = '';
        ph.textContent = placeholder;
        wireItem(ph);
        listEl.appendChild(ph);

        var restored = false;
        items.forEach(function(item) {
            var el = document.createElement('div');
            el.className = 'ss-item';
            el.dataset.value = String(item.value);
            el.textContent = item.text;
            wireItem(el);
            if (restoreValue && String(item.value) === String(restoreValue)) {
                el.classList.add('ss-selected');
                textEl.textContent = item.text;
                textEl.style.color = '';
                hidden.value = String(item.value);
                restored = true;
            }
            listEl.appendChild(el);
        });

        if (!restored) {
            textEl.textContent = placeholder;
            textEl.style.color = 'var(--text-sub)';
            hidden.value = '';
        }
    };

    btn._reset = function(placeholder) {
        listEl.innerHTML = '';
        var ph = document.createElement('div');
        ph.className = 'ss-item ss-placeholder';
        ph.dataset.value = '';
        ph.textContent = placeholder;
        wireItem(ph);
        listEl.appendChild(ph);
        textEl.textContent = placeholder;
        textEl.style.color = 'var(--text-sub)';
    };

    btn._setEnabled = function(enabled) {
        btn.style.opacity        = enabled ? '1' : '.6';
        btn.style.cursor         = enabled ? ''  : 'not-allowed';
        btn.style.pointerEvents  = enabled ? ''  : 'none';
    };

    return btn;
}

/* ── City → Area cascade ── */
const AREAS_URL  = '{{ route("admin.api.areas") }}';
const OLD_AREA_ID = '{{ old("area_id", $client->area_id) }}';
var cityBtn, areaBtn;

function loadAreas(cityId) {
    if (!cityId) {
        areaBtn._reset('— Select Governorate First —');
        areaBtn._setEnabled(false);
        return;
    }
    areaBtn._reset('Loading…');
    areaBtn._setEnabled(false);
    fetch(AREAS_URL + '?city_id=' + cityId)
        .then(function(r) { return r.json(); })
        .then(function(areas) {
            var items = areas.map(function(a) {
                return { value: a.id, text: a.name + (a.name_ar ? ' (' + a.name_ar + ')' : '') };
            });
            areaBtn._populate(items, '— Select Area —', OLD_AREA_ID);
            areaBtn._setEnabled(true);
        });
}

/* ── Logo preview ── */
document.getElementById('logoInput').addEventListener('change', function() {
    if (!this.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('logoPreviewImg').src = e.target.result;
        document.getElementById('logoFileName').textContent = document.getElementById('logoInput').files[0].name;
        document.getElementById('logoPlaceholder').style.display = 'none';
        document.getElementById('logoPreviewWrap').style.display = 'block';
        document.getElementById('clearLogoBtn').style.display = 'inline';
    };
    reader.readAsDataURL(this.files[0]);
});

function clearLogo() {
    document.getElementById('logoInput').value = '';
    document.getElementById('logoPreviewImg').src = '';
    document.getElementById('logoPlaceholder').style.display = 'block';
    document.getElementById('logoPreviewWrap').style.display = 'none';
    document.getElementById('clearLogoBtn').style.display = 'none';
}

/* ── Dynamic attachment rows ── */
var attIndex = 0;
function addAttachRow() {
    // Check if any existing row has an empty label or empty file input
    const rows = document.querySelectorAll('#attachContainer .attach-row');
    for (let r of rows) {
        const labelInput = r.querySelector('input[type="text"]');
        const fileInput = r.querySelector('input[type="file"]');
        if (labelInput && !labelInput.value.trim()) {
            showWarningToast('Please fill in the attachment label before adding a new one.');
            labelInput.focus();
            return;
        }
        if (fileInput && !fileInput.files.length) {
            showWarningToast('Please choose a file for the attachment before adding a new one.');
            return;
        }
    }

    var i = attIndex++;
    var row = document.createElement('div');
    row.className = 'attach-row';
    row.id = 'att-row-' + i;
    row.innerHTML =
        '<div><input type="text" name="attachment_labels[' + i + ']" class="form-input" ' +
             'placeholder="Label (e.g. Trade License, Insurance)" style="height:38px;"></div>' +
        '<div style="display:flex;gap:8px;align-items:center;">' +
            '<input type="file" name="attachment_files[' + i + ']" class="form-input" ' +
                 'style="height:38px;padding:6px 8px;flex:1;min-width:0;">' +
            '<button type="button" class="btn-remove-att" onclick="removeAttachRow(' + i + ')" style="flex-shrink:0;">&#10005;</button>' +
        '</div>';
    document.getElementById('attachContainer').appendChild(row);
}
function removeAttachRow(i) {
    var el = document.getElementById('att-row-' + i);
    if (el) el.remove();
}

/* ── Toast validation warning ── */
let validationToastTimeout = null;
function showWarningToast(message) {
    const toast = document.getElementById('validationToast');
    const msgEl = document.getElementById('validationToastMessage');
    msgEl.textContent = message;
    
    if (validationToastTimeout) {
        clearTimeout(validationToastTimeout);
    }
    
    toast.style.display = 'flex';
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    }, 10);
    
    validationToastTimeout = setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(40px)';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 300);
    }, 3000);
}

/* ── OTP channel toggle ── */
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

/* ── Init ── */
document.addEventListener('DOMContentLoaded', function() {
    const personalCode = document.getElementById('personalExtVal').value || '+962';
    const companyCode = document.getElementById('companyExtVal').value || '+962';

    // Set personal flag based on code
    const personalCountry = COUNTRIES.find(c => c.code === personalCode) || { flag: '🇯🇴', code: '+962' };
    document.getElementById('personalExtFlag').textContent = personalCountry.flag;
    document.getElementById('personalExtCode').textContent = personalCountry.code;
    
    // Set company flag based on code
    const companyCountry = COUNTRIES.find(c => c.code === companyCode) || { flag: '🇯🇴', code: '+962' };
    document.getElementById('companyExtFlag').textContent = companyCountry.flag;
    document.getElementById('companyExtCode').textContent = companyCountry.code;

    initPhoneDropdown('personalExtBtn','personalExtFlag','personalExtCode','personalExtVal','personalExtDropdown','personalExtList');
    initPhoneDropdown('companyExtBtn','companyExtFlag','companyExtCode','companyExtVal','companyExtDropdown','companyExtList');

    cityBtn = initSearchableSelect('citySelectBtn', 'citySelectDd', 'citySelectList', 'citySelectVal', {
        onChange: function(val) { loadAreas(val); }
    });
    areaBtn = initSearchableSelect('areaSelectBtn', 'areaSelectDd', 'areaSelectList', 'areaSelectVal');

    // Restore areas if city was already selected
    var oldCityId = document.getElementById('citySelectVal').value;
    if (oldCityId) loadAreas(oldCityId);
});

/* ── Client Form Validation ── */
(function() {
    var form = document.querySelector('form');
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

    function isEmail(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()); }

    form.addEventListener('submit', function(e) {
        clearErrors();
        var first = null;

        function req(name, msg) {
            var el = getField(name);
            if (!el || el.value.trim()) return;
            showFieldError(el, msg);
            if (!first) first = el;
        }

        req('name',         'Full name is required.');
        req('username',     'Username is required.');
        req('company_name', 'Company name (EN) is required.');

        var eEl = getField('email');
        if (eEl && eEl.value.trim() && !isEmail(eEl.value)) {
            showFieldError(eEl, 'Please enter a valid email address.');
            if (!first) first = eEl;
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
