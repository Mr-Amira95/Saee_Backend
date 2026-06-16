@extends('admin.layouts.app')

@section('title', 'Client Payout')
@section('page-title', 'Client Payout')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.financials.index') }}">Finance Dashboard</a>
    <span class="sep">/</span>
    <span class="current">Client Payout</span>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Process Payout for Client: {{ $client->company_name }}</h1>
            <p>Select COD orders to pay to the client, minus the shipping charges if applicable.</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1.35fr 0.65fr; gap: 20px; align-items: start;">
        
        {{-- Left: Orders list with checkboxes --}}
        <form action="{{ route('admin.financials.payout-client.submit', $client) }}" method="POST" id="payoutForm">
            @csrf
            
            <div class="table-card">
                <div style="padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="selectAll" style="width: 16px; height: 16px; accent-color: var(--red);">
                        <label for="selectAll" style="font-size: .8rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; cursor: pointer; user-select: none;">Select All Orders</label>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-dim);">Only delivered COD orders needing payout are listed</span>
                </div>
                
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">Select</th>
                                <th>Order #</th>
                                <th>Receiver Info</th>
                                <th>COD Collected</th>
                                <th>Shipping Fee</th>
                                <th>Net Payout</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="orders[]" value="{{ $order->id }}" class="order-checkbox" 
                                               data-cod="{{ $order->cod_amount }}" 
                                               data-shipping="{{ $order->shipping_fee }}"
                                               data-net="{{ $order->net_payout }}"
                                               style="width: 16px; height: 16px; accent-color: var(--red);">
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" target="_blank" style="color: var(--red-lt); font-weight: 700; text-decoration: none;">
                                            #{{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="cell-main">{{ $order->receiver_name }}</div>
                                        <div class="cell-sub">{{ $order->city->name }}</div>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($order->cod_amount, 2) }} JD</strong>
                                    </td>
                                    <td>
                                        <span style="color: var(--text-dim);">
                                            {{ number_format($order->shipping_fee, 2) }} JD
                                        </span>
                                        @if($order->delivery_on_customer)
                                            <div class="cell-sub" style="font-size: 0.65rem; color: #22c55e;">Paid by Cust.</div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong style="color: #22c55e;">{{ number_format($order->net_payout, 2) }} JD</strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-dim); padding: 35px;">
                                        No pending payouts registered for this client.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            {{-- Hidden Submit trigger --}}
            <div style="display: none;">
                <input type="submit" id="hiddenSubmitBtn">
            </div>
        </form>

        {{-- Right: Payout Panel --}}
        <div class="form-section">
            <div class="form-section-title">
                Payout Settlement
            </div>
            
            <div class="info-rows" style="margin-bottom: 20px;">
                <div class="info-row">
                    <span>Full Net Balance Due:</span>
                    <strong>{{ number_format($netPayoutAmount, 2) }} JD</strong>
                </div>
                <div class="info-row" style="border-top: 1px solid var(--bdr); padding-top: 10px; margin-top: 10px;">
                    <span>Selected COD:</span>
                    <strong id="selectedCod">0.00 JD</strong>
                </div>
                <div class="info-row">
                    <span>Selected Shipping:</span>
                    <strong id="selectedShipping" style="color: #fca5a5;">-0.00 JD</strong>
                </div>
                <div class="info-row" style="border-top: 1px dashed var(--bdr); padding-top: 8px; margin-top: 8px;">
                    <span>Net Payout Amount:</span>
                    <strong style="font-size: 1.35rem; color: #22c55e;" id="selectedNet">0.00 JD</strong>
                </div>
                <div class="info-row">
                    <span>Selected Count:</span>
                    <strong id="selectedCount">0 orders</strong>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" for="reference_number">Bank Transfer Reference</label>
                <input type="text" form="payoutForm" name="reference_number" id="reference_number" class="form-input" placeholder="e.g. Bank Ref #TXN982173">
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="notes">Payout Notes</label>
                <textarea form="payoutForm" name="notes" id="notes" class="form-textarea" placeholder="Optional bank accounts, transfer date, etc..."></textarea>
            </div>

            <button type="button" class="btn-primary" style="width: 100%; justify-content: center; height: 42px; background: linear-gradient(135deg, #16a34a, #22c55e); box-shadow: 0 4px 14px rgba(34,197,94,0.25);" id="submitButton" disabled onclick="document.getElementById('hiddenSubmitBtn').click()">
                Confirm Payout to Client
            </button>
            <a href="{{ route('admin.financials.index') }}" class="btn-secondary" style="width: 100%; justify-content: center; margin-top: 8px; box-sizing: border-box;">
                Cancel
            </a>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.order-checkbox');
        const selectedCodSpan = document.getElementById('selectedCod');
        const selectedShippingSpan = document.getElementById('selectedShipping');
        const selectedNetSpan = document.getElementById('selectedNet');
        const selectedCountSpan = document.getElementById('selectedCount');
        const submitButton = document.getElementById('submitButton');

        function updateTotals() {
            let cod = 0;
            let shipping = 0;
            let net = 0;
            let count = 0;

            checkboxes.forEach(cb => {
                if (cb.checked) {
                    cod += parseFloat(cb.dataset.cod);
                    shipping += parseFloat(cb.dataset.shipping);
                    net += parseFloat(cb.dataset.net);
                    count++;
                }
            });

            selectedCodSpan.textContent = cod.toFixed(2) + ' JD';
            selectedShippingSpan.textContent = '-' + shipping.toFixed(2) + ' JD';
            selectedNetSpan.textContent = net.toFixed(2) + ' JD';
            selectedCountSpan.textContent = count + ' order' + (count !== 1 ? 's' : '');
            
            if (count > 0) {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        }

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateTotals();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (!this.checked) {
                    selectAll.checked = false;
                } else {
                    const allChecked = Array.from(checkboxes).every(c => c.checked);
                    selectAll.checked = allChecked;
                }
                updateTotals();
            });
        });
    </script>
@endsection
