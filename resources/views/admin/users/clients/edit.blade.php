@extends('admin.layouts.app')

@section('title', 'Edit – '.$client->company_name)

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
/* ── Phone extension dropdown (shared with create) ── */
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

/* ── Logo ── */
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
.exist-att-meta { font-size: .75rem; color: var(--text-sub); }
.exist-att-del { display: flex; align-items: center; gap: 6px; }
.exist-att-del label { font-size: .78rem; color: #f87171; cursor: pointer; }

</style>
@endsection

@section('content')
<div style="max-width:860px;margin:0 auto;">

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('admin.clients.show', $client) }}" class="btn-secondary" style="padding:6px 12px;">&#8592; Back</a>
        <div>
            <h1 style="font-size:1.3rem;font-weight:700;margin:0;">Edit Client</h1>
            <p style="font-size:.82rem;color:var(--text-sub);margin:2px 0 0;">{{ $client->company_name }}</p>
        </div>
    </div>

    @if($errors->any())
    <div style="background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.3);border-radius:10px;padding:14px 18px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;color:#fca5a5;font-size:.88rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.clients.update', $client) }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

        {{-- ── Master Account ── --}}
        <div class="form-section">
            <div class="form-section-title">Master Account</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                <div>
                    <label class="form-label">Full Name <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="name" class="form-input @error('name') is-error @enderror"
                           value="{{ old('name', $client->masterUser->name) }}" required>
                    @error('name')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="form-label">Email Address <span style="color:#dc2626;">*</span></label>
                    <input type="email" name="email" class="form-input @error('email') is-error @enderror"
                           value="{{ old('email', $client->masterUser->email) }}" required>
                    @error('email')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="form-label">Personal Phone <span class="opt">(optional)</span></label>
                    <div style="position:relative;">
                        <div class="phone-wrap">
                            <button type="button" class="phone-ext-btn" id="personalExtBtn">
                                <span class="flag" id="personalExtFlag">🇯🇴</span>
                                <span class="code" id="personalExtCode">{{ old('phone_country_code', $client->phone_country_code ?? '+962') }}</span>
                                <span class="arrow">▼</span>
                            </button>
                            <input type="hidden" name="phone_country_code" id="personalExtVal"
                                   value="{{ old('phone_country_code', $client->phone_country_code ?? '+962') }}">
                            <input type="text" name="phone" class="phone-input-field"
                                   value="{{ old('phone', $client->masterUser->phone) }}" placeholder="7X XXX XXXX">
                        </div>
                        <div class="phone-dropdown" id="personalExtDropdown">
                            <input type="text" class="phone-dd-search" placeholder="Search country or code…">
                            <div class="phone-dd-list" id="personalExtList"></div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="form-label">Account Status</label>
                    <select name="user_status" class="form-input">
                        <option value="active"    {{ old('user_status', $client->masterUser->status) === 'active'    ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ old('user_status', $client->masterUser->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="pending"   {{ old('user_status', $client->masterUser->status) === 'pending'   ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

            </div>
        </div>

        {{-- ── Company Info ── --}}
        <div class="form-section">
            <div class="form-section-title">Company Information</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                <div>
                    <label class="form-label">Company Name (EN) <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="company_name" class="form-input @error('company_name') is-error @enderror"
                           value="{{ old('company_name', $client->company_name) }}" required>
                    @error('company_name')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="form-label">Company Name (AR) <span class="opt">(optional)</span></label>
                    <input type="text" name="company_name_ar" class="form-input" dir="rtl"
                           value="{{ old('company_name_ar', $client->company_name_ar) }}">
                </div>

                <div>
                    <label class="form-label">CR Number <span class="opt">(optional)</span></label>
                    <input type="text" name="commercial_register_number" class="form-input"
                           value="{{ old('commercial_register_number', $client->commercial_register_number) }}">
                </div>

                <div>
                    <label class="form-label">VAT Number <span class="opt">(optional)</span></label>
                    <input type="text" name="vat_number" class="form-input"
                           value="{{ old('vat_number', $client->vat_number) }}">
                </div>

                <div>
                    <label class="form-label">Company Email <span class="opt">(optional)</span></label>
                    <input type="email" name="company_email" class="form-input @error('company_email') is-error @enderror"
                           value="{{ old('company_email', $client->email) }}">
                    @error('company_email')<span class="form-error">{{ $message }}</span>@enderror
                </div>

            </div>
        </div>

        {{-- ── Address ── --}}
        <div class="form-section">
            <div class="form-section-title">Address</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">

                <div style="grid-column:1/-1;">
                    <label class="form-label">Address Line <span class="opt">(optional)</span></label>
                    <input type="text" name="address_line1" class="form-input"
                           value="{{ old('address_line1', $client->address_line1) }}">
                </div>

                <div>
                    <label class="form-label">Governorate</label>
                    <select name="city_id" id="citySelect" class="form-input">
                        <option value="">— Select Governorate —</option>
                        @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ old('city_id', $client->city_id) == $city->id ? 'selected' : '' }}>
                            {{ $city->name }}{{ $city->name_ar ? ' ('.$city->name_ar.')' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Area / District</label>
                    <select name="area_id" id="areaSelect" class="form-input">
                        <option value="">— Select Governorate First —</option>
                    </select>
                </div>

            </div>
        </div>

        {{-- ── Financial & Status ── --}}
        <div class="form-section">
            <div class="form-section-title">Financial &amp; Account Settings</div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">

                <div>
                    <label class="form-label">Credit Limit</label>
                    <div style="position:relative;">
                        <input type="number" name="credit_limit" class="form-input @error('credit_limit') is-error @enderror"
                               value="{{ old('credit_limit', $client->credit_limit) }}" min="0" step="0.01"
                               style="padding-right:42px;">
                        <span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                     font-size:.82rem;font-weight:700;color:var(--red);pointer-events:none;">JD</span>
                    </div>
                    @error('credit_limit')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="form-label">Account Expiry Date <span class="opt">(optional)</span></label>
                    <input type="date" name="expiry_date" class="form-input @error('expiry_date') is-error @enderror"
                           value="{{ old('expiry_date', $client->expiry_date?->format('Y-m-d')) }}">
                    @error('expiry_date')<span class="form-error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="form-label">Client Status</label>
                    <select name="status" class="form-input">
                        <option value="active"               {{ old('status', $client->status) === 'active'               ? 'selected' : '' }}>Active</option>
                        <option value="pending_verification" {{ old('status', $client->status) === 'pending_verification' ? 'selected' : '' }}>Pending Verification</option>
                        <option value="suspended"            {{ old('status', $client->status) === 'suspended'            ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>

            </div>
        </div>

        {{-- ── Delivery Prices ── --}}
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
                                           value="{{ old('delivery_prices.'.$city->id, $existingPrices[$city->id] ?? '') }}"
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

        {{-- ── Logo ── --}}
        <div class="form-section">
            <div class="form-section-title">Company Logo
                <span class="opt" style="text-transform:none;font-size:.72rem;font-weight:400;">optional · max 2 MB</span>
            </div>

            @if($client->logo_path)
            <div style="margin-bottom:12px;display:flex;align-items:center;gap:12px;">
                <img src="{{ Storage::disk('public')->url($client->logo_path) }}" alt="Current logo"
                     style="max-width:100px;max-height:60px;object-fit:contain;border-radius:6px;background:var(--in-bg);padding:4px;border:1px solid var(--bdr);">
                <span style="font-size:.82rem;color:var(--text-sub);">Current logo</span>
            </div>
            @endif

            <div class="logo-upload-zone" id="logoZone">
                <input type="file" name="logo" id="logoInput" accept="image/*">
                <div id="logoPlaceholder">
                    <div style="font-size:.9rem;color:var(--text);margin-bottom:4px;">
                        {{ $client->logo_path ? 'Upload new logo to replace' : 'Click to upload logo' }}
                    </div>
                    <div class="logo-upload-hint">PNG, JPG, WEBP, SVG — up to 2 MB</div>
                </div>
                <div id="logoPreviewWrap" style="display:none;pointer-events:none;">
                    <img id="logoPreviewImg" style="max-width:120px;max-height:80px;object-fit:contain;border-radius:8px;display:block;margin:0 auto;" src="" alt="">
                    <div style="font-size:.78rem;color:var(--text-sub);margin-top:6px;" id="logoFileName"></div>
                </div>
            </div>
            <button type="button" id="clearLogoBtn" onclick="clearLogo()"
                    style="display:none;margin-top:6px;font-size:.78rem;color:#dc2626;background:none;border:none;cursor:pointer;padding:0;">
                &#10005; Cancel new upload
            </button>
            @error('logo')<span class="form-error">{{ $message }}</span>@enderror
        </div>

        {{-- ── Existing Attachments ── --}}
        @if($client->attachments->count())
        <div class="form-section">
            <div class="form-section-title">Existing Attachments</div>
            @foreach($client->attachments as $att)
            <div class="exist-att">
                <div style="font-size:1.2rem;">📎</div>
                <div style="flex:1;">
                    <div class="exist-att-name">{{ $att->label }}</div>
                    <div class="exist-att-meta">{{ $att->original_filename }} &middot; {{ $att->formatted_size }}</div>
                </div>
                <a href="{{ $att->url }}" target="_blank" style="font-size:.8rem;color:var(--red);margin-right:12px;">↗ View</a>
                <div class="exist-att-del">
                    <input type="checkbox" name="delete_attachment_ids[]" value="{{ $att->id }}" id="del_att_{{ $att->id }}">
                    <label for="del_att_{{ $att->id }}">Delete</label>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ── New Attachments ── --}}
        <div class="form-section">
            <div class="form-section-title">Add New Attachments
                <span class="opt" style="text-transform:none;font-size:.72rem;font-weight:400;">optional · up to 10 MB each</span>
            </div>
            <div id="attachContainer"></div>
            <button type="button" class="btn-add-att" onclick="addAttachRow()">
                <span style="font-size:1.1rem;line-height:1;">+</span> Add Attachment
            </button>
        </div>

        {{-- ── Actions ── --}}
        <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:8px;margin-bottom:40px;">
            <a href="{{ route('admin.clients.show', $client) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>

    </form>
</div>
@endsection

@section('scripts')
<script>
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

function initPhoneDropdown(btnId, flagId, codeId, valId, ddId, listId, initialCode) {
    var btn      = document.getElementById(btnId);
    var flagEl   = document.getElementById(flagId);
    var codeEl   = document.getElementById(codeId);
    var valEl    = document.getElementById(valId);
    var dd       = document.getElementById(ddId);
    var listEl   = document.getElementById(listId);
    var searchEl = dd.querySelector('.phone-dd-search');

    // Show saved code in button
    var match = COUNTRIES.find(function(c) { return c.code === initialCode; });
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

/* ── City → Area cascade ── */
var AREAS_URL  = '{{ route("admin.api.areas") }}';
var savedAreaId = '{{ old("area_id", $client->area_id) }}';

function loadAreas(cityId) {
    var sel = document.getElementById('areaSelect');
    if (!cityId) { sel.innerHTML = '<option value="">— Select Governorate First —</option>'; return; }
    sel.innerHTML = '<option value="">Loading…</option>';
    fetch(AREAS_URL + '?city_id=' + cityId)
        .then(function(r) { return r.json(); })
        .then(function(areas) {
            sel.innerHTML = '<option value="">— Select Area —</option>';
            areas.forEach(function(a) {
                var o = document.createElement('option');
                o.value = a.id;
                o.textContent = a.name + (a.name_ar ? ' (' + a.name_ar + ')' : '');
                if (String(a.id) === String(savedAreaId)) o.selected = true;
                sel.appendChild(o);
            });
        });
}

/* ── Logo ── */
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

/* ── Attachments ── */
var attIndex = 0;
function addAttachRow() {
    var i = attIndex++;
    var row = document.createElement('div');
    row.className = 'attach-row'; row.id = 'att-row-' + i;
    row.innerHTML =
        '<div><input type="text" name="attachment_labels[' + i + ']" class="form-input" placeholder="Label (e.g. Trade License)" style="height:38px;"></div>' +
        '<div style="display:flex;align-items:center;gap:8px;"><input type="file" name="attachment_files[' + i + ']" class="form-input" style="flex:1;height:38px;padding:6px 8px;"><button type="button" class="btn-remove-att" onclick="removeAttachRow(' + i + ')">&#10005;</button></div>';
    document.getElementById('attachContainer').appendChild(row);
}
function removeAttachRow(i) { var el = document.getElementById('att-row-' + i); if (el) el.remove(); }

/* ── Init ── */
document.addEventListener('DOMContentLoaded', function() {
    var personalCode = document.getElementById('personalExtVal').value || '+962';
    initPhoneDropdown('personalExtBtn','personalExtFlag','personalExtCode','personalExtVal','personalExtDropdown','personalExtList', personalCode);

    var cityId = document.getElementById('citySelect').value;
    if (cityId) loadAreas(cityId);
    document.getElementById('citySelect').addEventListener('change', function() { loadAreas(this.value); });
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
        req('email',        'Email address is required.');
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


