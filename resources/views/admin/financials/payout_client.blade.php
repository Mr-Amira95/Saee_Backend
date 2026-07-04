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

    <form action="{{ route('admin.financials.payout-client.submit', $client) }}" method="POST" id="payoutForm" enctype="multipart/form-data">
        @csrf

        {{-- Orders Table --}}
        <div class="table-card" style="margin-bottom: 20px;">
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
                            <th>Customer Delivery</th>
                            <th>Net Payout</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="orders[]" value="{{ $order->id }}" class="order-checkbox"
                                           data-cod="{{ $order->cod_amount }}"
                                           data-delivery="{{ $order->customer_delivery }}"
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
                                    <div class="cell-sub">{{ $order->city?->name ?? '—' }}</div>
                                </td>
                                <td>
                                    <strong>{{ number_format($order->cod_amount, 2) }} JD</strong>
                                </td>
                                <td>
                                    @if($order->customer_delivery > 0)
                                        <strong style="color: #22c55e;">{{ number_format($order->customer_delivery, 2) }} JD</strong>
                                    @else
                                        <span style="color: var(--text-dim);">—</span>
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

        {{-- Payout Settlement Box --}}
        <div class="form-section">
            <div class="form-section-title">Payout Settlement</div>

            {{-- Summary stat cards --}}
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px;">
                <div style="background: var(--bg); border: 1px solid var(--bdr); border-radius: 10px; padding: 14px 16px;">
                    <div style="font-size: .72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px;">Total Balance Due</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--text);">{{ number_format($netPayoutAmount, 2) }} JD</div>
                </div>
                <div style="background: var(--bg); border: 1px solid var(--bdr); border-radius: 10px; padding: 14px 16px;">
                    <div style="font-size: .72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px;">Selected COD</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--text);" id="selectedCod">0.00 JD</div>
                </div>
                <div style="background: var(--bg); border: 1px solid var(--bdr); border-radius: 10px; padding: 14px 16px;">
                    <div style="font-size: .72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px;">Customer Delivery</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--text);" id="selectedShipping">+0.00 JD</div>
                </div>
                <div style="background: rgba(34,197,94,.07); border: 1px solid rgba(34,197,94,.25); border-radius: 10px; padding: 14px 16px;">
                    <div style="font-size: .72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px;">Net Payout · <span id="selectedCount">0</span> orders</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: #22c55e;" id="selectedNet">0.00 JD</div>
                </div>
            </div>

            {{-- Form inputs --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="reference_number">Bank Transfer Reference</label>
                    <input type="text" name="reference_number" id="reference_number" class="form-input" placeholder="e.g. Bank Ref #TXN982173">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="attachment">Attachment (e.g. Bank Receipt)</label>
                    <input type="file" name="attachment" id="attachment" class="form-input" style="padding: 8px 12px; height: 42px; display: flex; align-items: center;">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="notes">Payout Notes</label>
                    <textarea name="notes" id="notes" class="form-textarea" rows="1" placeholder="Optional notes..." style="height: 42px; resize: none;"></textarea>
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                @if(auth()->user()->hasAdminAction('finances.settlements'))
                <button type="submit" class="btn-primary" style="flex: 1; justify-content: center; height: 42px; background: linear-gradient(135deg, #16a34a, #22c55e); box-shadow: 0 4px 14px rgba(34,197,94,.25);" id="submitButton" disabled>
                    Confirm Payout to Client
                </button>
                @endif
                <a href="{{ route('admin.financials.index') }}" class="btn-secondary" style="justify-content: center; height: 42px; padding: 0 24px; display: flex; align-items: center; box-sizing: border-box;">
                    Cancel
                </a>
            </div>
        </div>

    </form>
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
                    shipping += parseFloat(cb.dataset.delivery);
                    net += parseFloat(cb.dataset.net);
                    count++;
                }
            });

            selectedCodSpan.textContent = cod.toFixed(2) + ' JD';
            selectedShippingSpan.textContent = '+' + shipping.toFixed(2) + ' JD';
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
