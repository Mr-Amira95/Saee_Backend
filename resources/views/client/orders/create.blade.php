@extends('client.layouts.app')
@section('title', 'New Order')
@section('page-title', 'New Order')

@push('styles')
<style>
    .form-wrap { max-width: 100%; }
    .form-section {
        background: var(--card);
        border: 1px solid var(--bdr);
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 18px;
        backdrop-filter: blur(8px);
    }
    .form-section-title {
        font-size: .8rem;
        font-weight: 700;
        color: var(--text-sub);
        letter-spacing: .07em;
        text-transform: uppercase;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--bdr);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-section-title svg { color: var(--red-lt); opacity: .7; }
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 768px) { .form-grid-2 { grid-template-columns: 1fr; } }
    .form-group  { display: flex; flex-direction: column; gap: 6px; }
    .form-label .req { color: var(--red-lt); margin-left: 2px; }
    .form-actions { display: flex; align-items: center; gap: 10px; justify-content: flex-end; padding-top: 4px; }
    /* Toggle switch */
    .toggle-switch { display: inline-flex; align-items: center; cursor: pointer; }
    .toggle-switch input { display: none; }
    .toggle-track { width: 34px; height: 18px; background: rgba(255,255,255,.12); border-radius: 9px; position: relative; transition: background .2s; }
    .toggle-thumb { position: absolute; top: 2px; left: 2px; width: 14px; height: 14px; background: #fff; border-radius: 50%; transition: transform .2s; }
    .toggle-switch input:checked ~ .toggle-track { background: var(--red); }
    .toggle-switch input:checked ~ .toggle-track .toggle-thumb { transform: translateX(16px); }
    /* Searchable select */
    .searchable-select { position: relative; width: 100%; }
    .searchable-select .form-input { width: 100%; box-sizing: border-box; display: block; }
    .search-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: var(--card); border: 1px solid var(--bdr); border-radius: 8px; max-height: 220px; overflow-y: auto; z-index: 200; margin-top: 4px; box-shadow: 0 8px 24px rgba(0,0,0,.35); }
    .search-dropdown-item { padding: 10px 14px; cursor: pointer; font-size: .875rem; color: var(--text); transition: background .15s; }
    .search-dropdown-item:hover { background: rgba(255,255,255,.08); }
    .search-dropdown-item.selected { font-weight: 600; color: var(--red-lt); }
    .search-dropdown-item.no-results { color: var(--text-dim); cursor: default; font-style: italic; }
</style>
@endpush

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
    <a href="{{ route('client.orders.index') }}" class="btn-secondary" style="padding:7px 14px;font-size:.82rem;">← Back</a>
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;">Create New Order</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">Fill in details to register a new shipment.</p>
    </div>
</div>

<div class="form-wrap">
    <form action="{{ route('client.orders.store') }}" method="POST" id="orderForm">
        @csrf

        {{-- 1. Shipment Description --}}
        <div class="form-section">
            <div class="form-section-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Shipment Description
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="order_description">Shipment Contents / Description</label>
                    <input type="text" name="order_description" id="order_description" class="form-input @error('order_description') err @enderror" value="{{ old('order_description') }}" placeholder="e.g. Shoes, electronics, documents">
                    @error('order_description') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="delivery_shift">Preferred Delivery Shift</label>
                    <select name="delivery_shift" id="delivery_shift" class="form-select @error('delivery_shift') err @enderror">
                        <option value="doesnt_matter" {{ old('delivery_shift', 'doesnt_matter') === 'doesnt_matter' ? 'selected' : '' }}>Doesn't Matter</option>
                        <option value="before_12pm" {{ old('delivery_shift') === 'before_12pm' ? 'selected' : '' }}>Before 12 PM</option>
                        <option value="after_12pm" {{ old('delivery_shift') === 'after_12pm' ? 'selected' : '' }}>After 12 PM</option>
                    </select>
                    @error('delivery_shift') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- 2. Pricing & Payments --}}
        <div class="form-section">
            <div class="form-section-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1m-4-6h8"/></svg>
                Pricing &amp; Payment Options
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="payment_type">Payment Type <span class="req">*</span></label>
                    <select name="payment_type" id="payment_type" class="form-select @error('payment_type') err @enderror" required>
                        <option value="cod" {{ old('payment_type', 'cod') === 'cod' ? 'selected' : '' }}>COD (Cash on Delivery)</option>
                        <option value="prepaid" {{ old('payment_type') === 'prepaid' ? 'selected' : '' }}>Prepaid</option>
                    </select>
                    @error('payment_type') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group" id="orderPriceGroup">
                    <label class="form-label" for="order_price">COD Order Price (JD) <span class="req">*</span></label>
                    <input type="number" name="order_price" id="order_price" step="0.01" class="form-input @error('order_price') err @enderror" value="{{ old('order_price') }}" placeholder="0.00">
                    @error('order_price') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-grid-2" style="margin-top: 18px; border-top: 1px solid var(--bdr); padding-top: 18px;">
                <div class="form-group" style="flex-direction: row; align-items: center; gap: 10px;">
                    <label class="toggle-switch">
                        <input type="checkbox" name="delivery_on_customer" id="delivery_on_customer" value="1" {{ old('delivery_on_customer') ? 'checked' : '' }}>
                        <span class="toggle-track"><span class="toggle-thumb"></span></span>
                    </label>
                    <label class="form-label" for="delivery_on_customer" style="cursor: pointer; margin-bottom: 0;">Delivery Charges On Customer</label>
                </div>

                <div class="form-group" id="customerAmountGroup" style="display: none;">
                    <label class="form-label" for="delivery_customer_amount">Customer Delivery Fee (JD) <span class="req">*</span></label>
                    <input type="number" name="delivery_customer_amount" id="delivery_customer_amount" step="0.01" class="form-input @error('delivery_customer_amount') err @enderror" value="{{ old('delivery_customer_amount', '0.00') }}">
                    @error('delivery_customer_amount') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- 3. Receiver & Address Details --}}
        <div class="form-section">
            <div class="form-section-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Receiver &amp; Destination Details
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="receiver_name">Receiver Name <span class="req">*</span></label>
                    <input type="text" name="receiver_name" id="receiver_name" class="form-input @error('receiver_name') err @enderror" value="{{ old('receiver_name') }}" required placeholder="e.g. John Doe">
                    @error('receiver_name') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="receiver_phone">Receiver Phone <span class="req">*</span></label>
                    <input type="text" name="receiver_phone" id="receiver_phone" class="form-input @error('receiver_phone') err @enderror" value="{{ old('receiver_phone') }}" required placeholder="e.g. 07XXXXXXXX">
                    @error('receiver_phone') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-grid-2" style="margin-top: 16px;">
                <div class="form-group">
                    <label class="form-label" for="city_id">City <span class="req">*</span></label>
                    <select name="city_id" id="city_id" class="form-select @error('city_id') err @enderror" required>
                        <option value="">Select City</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('city_id') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="area_id">Area <span class="req">*</span></label>
                    <select name="area_id" id="area_id" class="form-select @error('area_id') err @enderror" required>
                        <option value="">Select Area</option>
                    </select>
                    @error('area_id') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label class="form-label" for="address_text">Address Details <span class="req">*</span></label>
                <textarea name="address_text" id="address_text" class="form-textarea @error('address_text') err @enderror" required placeholder="Street name, building number, apartment number...">{{ old('address_text') }}</textarea>
                @error('address_text') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label class="form-label" for="address_location">GPS Location Coordinates (Optional)</label>
                <input type="text" name="address_location" id="address_location" class="form-input @error('address_location') err @enderror" value="{{ old('address_location') }}" placeholder="e.g. 24.7136, 46.6753">
                @error('address_location') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label class="form-label" for="notes">Special Delivery Instructions / Notes</label>
                <textarea name="notes" id="notes" class="form-textarea @error('notes') err @enderror" placeholder="Any remarks for the driver...">{{ old('notes') }}</textarea>
                @error('notes') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('client.orders.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary" id="submitBtn">Create Order</button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    // ── Searchable Select ──────────────────────────────────────────────
    class SearchableSelect {
        constructor(selectEl, placeholder) {
            this.selectEl = selectEl;
            this.placeholder = placeholder || 'Search...';
            this.options = [];
            this._build();
        }

        _build() {
            const wrapper = document.createElement('div');
            wrapper.className = 'searchable-select';
            this.selectEl.parentNode.insertBefore(wrapper, this.selectEl);
            wrapper.appendChild(this.selectEl);
            this.selectEl.style.display = 'none';
            this.wrapper = wrapper;

            this.input = document.createElement('input');
            this.input.type = 'text';
            this.input.className = 'form-input';
            this.input.placeholder = this.placeholder;
            this.input.autocomplete = 'off';
            wrapper.insertBefore(this.input, this.selectEl);

            this.dropdown = document.createElement('div');
            this.dropdown.className = 'search-dropdown';
            this.dropdown.style.display = 'none';
            wrapper.appendChild(this.dropdown);

            this._syncOptions();
            this._attachEvents();

            if (this.selectEl.value) {
                const match = this.options.find(o => o.value == this.selectEl.value);
                if (match) this.input.value = match.text;
            }
        }

        _syncOptions() {
            this.options = Array.from(this.selectEl.options)
                .filter(o => o.value !== '')
                .map(o => ({ value: o.value, text: o.text }));
        }

        _renderDropdown(filter) {
            const q = (filter || '').toLowerCase();
            const matches = q
                ? this.options.filter(o => o.text.toLowerCase().includes(q))
                : this.options;

            this.dropdown.innerHTML = '';

            if (matches.length === 0) {
                const el = document.createElement('div');
                el.className = 'search-dropdown-item no-results';
                el.textContent = 'No results found';
                this.dropdown.appendChild(el);
                return;
            }

            const currentVal = this.selectEl.value;
            matches.forEach(opt => {
                const el = document.createElement('div');
                el.className = 'search-dropdown-item' + (opt.value == currentVal ? ' selected' : '');
                el.textContent = opt.text;
                el.addEventListener('mousedown', e => {
                    e.preventDefault();
                    this.setValue(opt.value, opt.text);
                });
                this.dropdown.appendChild(el);
            });
        }

        setValue(value, text) {
            this.selectEl.value = value;
            this.input.value = text || '';
            this.dropdown.style.display = 'none';
            this.selectEl.dispatchEvent(new Event('change', { bubbles: true }));
        }

        refresh() {
            this._syncOptions();
            this.input.value = '';
            this.selectEl.value = '';
        }

        _attachEvents() {
            this.input.addEventListener('focus', () => {
                this._renderDropdown(this.input.value);
                this.dropdown.style.display = 'block';
            });

            this.input.addEventListener('input', () => {
                this.selectEl.value = '';
                this._renderDropdown(this.input.value);
                this.dropdown.style.display = 'block';
            });

            this.input.addEventListener('blur', () => {
                setTimeout(() => {
                    this.dropdown.style.display = 'none';
                    if (!this.selectEl.value) this.input.value = '';
                }, 150);
            });
        }
    }

    // ── Init ──────────────────────────────────────────────────────────
    const citiesData = @json($cities);

    const citySelect = document.getElementById('city_id');
    const areaSelect = document.getElementById('area_id');

    const citySS = new SearchableSelect(citySelect, 'Search cities...');
    let areaSS = null;

    // ── Dynamic Areas Population ──────────────────────────────────────
    citySelect.addEventListener('change', function () {
        const selectedCityId = parseInt(this.value);
        areaSelect.innerHTML = '<option value="">Select Area</option>';

        if (selectedCityId) {
            const city = citiesData.find(c => c.id === selectedCityId);
            if (city && city.areas) {
                city.areas.forEach(area => {
                    const option = document.createElement('option');
                    option.value = area.id;
                    option.textContent = area.name;
                    if (area.id == "{{ old('area_id') }}") option.selected = true;
                    areaSelect.appendChild(option);
                });
            }
        }

        if (areaSS) {
            areaSS.refresh();
            const oldArea = "{{ old('area_id') }}";
            if (oldArea && areaSelect.querySelector(`option[value="${oldArea}"]`)) {
                const opt = areaSelect.querySelector(`option[value="${oldArea}"]`);
                areaSS.setValue(oldArea, opt.textContent.trim());
            }
        } else {
            areaSS = new SearchableSelect(areaSelect, 'Search areas...');
        }
    });

    if (citySelect.value) citySelect.dispatchEvent(new Event('change'));

    // ── Payment Type Toggle ───────────────────────────────────────────
    const paymentTypeSelect       = document.getElementById('payment_type');
    const orderPriceGroup         = document.getElementById('orderPriceGroup');
    const deliveryOnCustomerCheck = document.getElementById('delivery_on_customer');
    const customerAmountGroup     = document.getElementById('customerAmountGroup');

    paymentTypeSelect.addEventListener('change', function () {
        if (this.value === 'cod') {
            orderPriceGroup.style.display = 'flex';
            document.getElementById('order_price').required = true;
        } else {
            orderPriceGroup.style.display = 'none';
            document.getElementById('order_price').required = false;
        }
    });
    paymentTypeSelect.dispatchEvent(new Event('change'));

    deliveryOnCustomerCheck.addEventListener('change', function () {
        if (this.checked) {
            customerAmountGroup.style.display = 'flex';
            document.getElementById('delivery_customer_amount').required = true;
        } else {
            customerAmountGroup.style.display = 'none';
            document.getElementById('delivery_customer_amount').required = false;
        }
    });
    if (deliveryOnCustomerCheck.checked) deliveryOnCustomerCheck.dispatchEvent(new Event('change'));

    document.getElementById('orderForm').addEventListener('submit', () => {
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').style.opacity = '.6';
    });
</script>
@endpush
