@extends('admin.layouts.app')

@section('title', 'New Order')
@section('page-title', 'New Order')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.orders.index') }}">Orders</a>
    <span class="sep">/</span>
    <span class="current">Create</span>
@endsection

@section('head')
    <style>
        .form-wrap {
            max-width: 100% !important;
        }
        .searchable-select {
            position: relative;
            width: 100%;
        }
        .searchable-select .form-input {
            width: 100%;
            box-sizing: border-box;
            display: block;
        }
        .search-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--card);
            border: 1px solid var(--bdr);
            border-radius: 8px;
            max-height: 220px;
            overflow-y: auto;
            z-index: 200;
            margin-top: 4px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.35);
        }
        .search-dropdown-item {
            padding: 10px 14px;
            cursor: pointer;
            font-size: 0.875rem;
            color: var(--text);
            transition: background 0.15s;
        }
        .search-dropdown-item:hover {
            background: rgba(255,255,255,0.08);
        }
        .search-dropdown-item.selected {
            font-weight: 600;
            color: var(--red-lt, #e05454);
        }
        .search-dropdown-item.no-results {
            color: var(--text-dim);
            cursor: default;
            font-style: italic;
        }
    </style>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Create New Order</h1>
            <p>Fill in details to register a new shipment.</p>
        </div>
    </div>

    <div class="form-wrap">
        <form action="{{ route('admin.orders.store') }}" method="POST" id="orderForm">
            @csrf

            {{-- 1. Client & Description --}}
            <div class="form-section">
                <div class="form-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Client &amp; Description
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="client_profile_id">Client Name <span class="req">*</span></label>
                        <select name="client_profile_id" id="client_profile_id" class="form-select @error('client_profile_id') err @enderror" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_profile_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->company_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_profile_id') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="order_description">Shipment Contents / Description</label>
                        <input type="text" name="order_description" id="order_description" class="form-input @error('order_description') err @enderror" value="{{ old('order_description') }}" placeholder="e.g. Shoes, electronics, documents">
                        @error('order_description') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- 2. Batch Reference --}}
            <div class="form-section">
                <div class="form-section-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    Batch Reference
                </div>
                <div class="form-group">
                    <label class="form-label" for="batch_number">Batch Number <span style="color: var(--text-dim); font-weight: 400;">(Optional — used to group orders for driver assignment)</span></label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="batch_number" id="batch_number" class="form-input @error('batch_number') err @enderror" value="{{ old('batch_number') }}" placeholder="e.g. BATCH-260620-5-A3F7" style="flex: 1; font-family: monospace; letter-spacing: 0.05em;">
                        <button type="button" onclick="generateBatchNumber()" style="padding: 0 18px; height: 42px; background: rgba(255,255,255,0.06); border: 1px solid var(--bdr); border-radius: 8px; color: var(--text); cursor: pointer; font-size: 0.82rem; font-weight: 600; white-space: nowrap; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.06)'">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 5px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Generate
                        </button>
                    </div>
                    @error('batch_number') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- 3. Pricing & Payments --}}
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
                        <label class="form-label" for="delivery_on_customer" style="cursor: pointer;">Delivery Charges On Customer</label>
                    </div>

                    <div class="form-group" id="customerAmountGroup" style="display: none;">
                        <label class="form-label" for="delivery_customer_amount">Customer Delivery Fee (JD) <span class="req">*</span></label>
                        <input type="number" name="delivery_customer_amount" id="delivery_customer_amount" step="0.01" class="form-input @error('delivery_customer_amount') err @enderror" value="{{ old('delivery_customer_amount', '0.00') }}">
                        @error('delivery_customer_amount') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>

            </div>

            {{-- 4. Receiver & Address Details --}}
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
                        <input type="text" name="receiver_phone" id="receiver_phone" class="form-input @error('receiver_phone') err @enderror" value="{{ old('receiver_phone') }}" required placeholder="e.g. 05XXXXXXXX">
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
                <a href="{{ route('admin.orders.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create Order</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
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

                // Reflect existing selection on load
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
                    el.addEventListener('mousedown', (e) => {
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

            // Call after programmatically changing selectEl options
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
                    // Clear hidden value if user edits text
                    this.selectEl.value = '';
                    this._renderDropdown(this.input.value);
                    this.dropdown.style.display = 'block';
                });

                this.input.addEventListener('blur', () => {
                    // Restore display text to match current value on blur
                    setTimeout(() => {
                        this.dropdown.style.display = 'none';
                        if (!this.selectEl.value) {
                            this.input.value = '';
                        }
                    }, 150);
                });
            }
        }

        // ── Init ──────────────────────────────────────────────────────────
        const citiesData = @json($cities);

        const clientSelect = document.getElementById('client_profile_id');
        const citySelect   = document.getElementById('city_id');
        const areaSelect   = document.getElementById('area_id');

        const clientSS = new SearchableSelect(clientSelect, 'Search clients...');
        const citySS   = new SearchableSelect(citySelect,   'Search cities...');
        let   areaSS   = null;

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
                        if (area.id == "{{ old('area_id') }}") {
                            option.selected = true;
                        }
                        areaSelect.appendChild(option);
                    });
                }
            }

            // Refresh or init area searchable select
            if (areaSS) {
                areaSS.refresh();
                // Restore old area selection if options were added
                const oldArea = "{{ old('area_id') }}";
                if (oldArea && areaSelect.querySelector(`option[value="${oldArea}"]`)) {
                    const opt = areaSelect.querySelector(`option[value="${oldArea}"]`);
                    areaSS.setValue(oldArea, opt.textContent.trim());
                }
            } else {
                areaSS = new SearchableSelect(areaSelect, 'Search areas...');
            }
        });

        // Trigger city change on load to populate areas if old value exists
        if (citySelect.value) {
            citySelect.dispatchEvent(new Event('change'));
        }

        // ── Payment Type Toggle ───────────────────────────────────────────
        const paymentTypeSelect    = document.getElementById('payment_type');
        const orderPriceGroup      = document.getElementById('orderPriceGroup');
        const deliveryOnCustomerCheck = document.getElementById('delivery_on_customer');
        const customerAmountGroup  = document.getElementById('customerAmountGroup');

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
        if (deliveryOnCustomerCheck.checked) {
            deliveryOnCustomerCheck.dispatchEvent(new Event('change'));
        }

        // ── Batch Number Generator ────────────────────────────────────────
        function generateBatchNumber() {
            const clientId = document.getElementById('client_profile_id').value || 'X';
            const d = new Date();
            const yy = String(d.getFullYear()).slice(-2);
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            const rand = Math.random().toString(16).substring(2, 6).toUpperCase();
            document.getElementById('batch_number').value = `BATCH-${yy}${mm}${dd}-${clientId}-${rand}`;
        }
    </script>
@endsection
