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
                        <label class="form-label" for="delivery_on_customer" style="cursor: pointer;">Delivery Charges On Customer</label>
                    </div>

                    <div class="form-group" id="customerAmountGroup" style="display: none;">
                        <label class="form-label" for="delivery_customer_amount">Customer Delivery Fee (JD) <span class="req">*</span></label>
                        <input type="number" name="delivery_customer_amount" id="delivery_customer_amount" step="0.01" class="form-input @error('delivery_customer_amount') err @enderror" value="{{ old('delivery_customer_amount', '0.00') }}">
                        @error('delivery_customer_amount') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Shipping Price Estimator Display --}}
                <div style="margin-top: 18px; padding: 14px; background: rgba(255,255,255,0.02); border: 1px dashed var(--bdr); border-radius: 9px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: .8rem; color: var(--text-sub);">Calculated Client Shipping Charge:</div>
                    <div id="shippingEstimate" style="font-size: 1.15rem; font-weight: 800; color: var(--red-lt);">-- JD</div>
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

                <div class="form-grid-2" style="margin-top: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="address_location">GPS Location Coordinates (Optional)</label>
                        <input type="text" name="address_location" id="address_location" class="form-input @error('address_location') err @enderror" value="{{ old('address_location') }}" placeholder="e.g. 24.7136, 46.6753">
                        @error('address_location') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="driver_id">Assign Driver (Optional)</label>
                        <select name="driver_id" id="driver_id" class="form-select @error('driver_id') err @enderror">
                            <option value="">Leave Unassigned (Pending)</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('driver_id') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
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
        // Global variables for cities & areas
        const citiesData = @json($cities);

        const clientSelect = document.getElementById('client_profile_id');
        const citySelect = document.getElementById('city_id');
        const areaSelect = document.getElementById('area_id');
        const paymentTypeSelect = document.getElementById('payment_type');
        const orderPriceGroup = document.getElementById('orderPriceGroup');
        const deliveryOnCustomerCheck = document.getElementById('delivery_on_customer');
        const customerAmountGroup = document.getElementById('customerAmountGroup');
        const shippingEstimateDiv = document.getElementById('shippingEstimate');

        // Dynamic Areas Population
        citySelect.addEventListener('change', function() {
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
            calculateShippingEstimate();
        });

        // Trigger city change on load to set areas if old value exists
        if (citySelect.value) {
            citySelect.dispatchEvent(new Event('change'));
        }

        // Toggle order price display based on COD/Prepaid
        paymentTypeSelect.addEventListener('change', function() {
            if (this.value === 'cod') {
                orderPriceGroup.style.display = 'flex';
                document.getElementById('order_price').required = true;
            } else {
                orderPriceGroup.style.display = 'none';
                document.getElementById('order_price').required = false;
            }
        });
        paymentTypeSelect.dispatchEvent(new Event('change'));

        // Toggle customer delivery amount display
        deliveryOnCustomerCheck.addEventListener('change', function() {
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

        // Trigger dynamic pricing fetch
        clientSelect.addEventListener('change', calculateShippingEstimate);

        function calculateShippingEstimate() {
            const clientId = clientSelect.value;
            const cityId = citySelect.value;

            if (!clientId || !cityId) {
                shippingEstimateDiv.textContent = '-- JD';
                return;
            }

            shippingEstimateDiv.textContent = 'Calculating...';

            fetch("{{ route('admin.orders.calculate-price') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    client_profile_id: clientId,
                    city_id: cityId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    shippingEstimateDiv.textContent = parseFloat(data.price).toFixed(2) + ' JD';
                } else {
                    shippingEstimateDiv.textContent = 'Error';
                }
            })
            .catch(() => {
                shippingEstimateDiv.textContent = 'Error';
            });
        }

        if (clientSelect.value && citySelect.value) {
            calculateShippingEstimate();
        }
    </script>
@endsection
