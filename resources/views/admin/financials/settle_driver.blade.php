@extends('admin.layouts.app')

@section('title', 'Settle Driver Cash')
@section('page-title', 'Settle Driver Cash')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.financials.index') }}">Finance Dashboard</a>
    <span class="sep">/</span>
    <span class="current">Settle Driver</span>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Settle Cash for Driver: {{ $driver->name }}</h1>
            <p>Select the orders you are collecting cash for and record the transaction in the ledger.</p>
        </div>
    </div>

    <form action="{{ route('admin.financials.settle-driver.submit', $driver) }}" method="POST" id="settleForm">
        @csrf

        {{-- Orders Table --}}
        <div class="table-card" style="margin-bottom: 20px;">
            <div style="padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="selectAll" style="width: 16px; height: 16px; accent-color: var(--red);">
                    <label for="selectAll" style="font-size: .8rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; cursor: pointer; user-select: none;">Select All Orders</label>
                </div>
                <span style="font-size: 0.75rem; color: var(--text-dim);">Only delivered orders with uncollected cash are shown</span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">Select</th>
                            <th>Order #</th>
                            <th>Client</th>
                            <th>Receiver Info</th>
                            <th>Pricing / Delivery</th>
                            <th>Collected Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="orders[]" value="{{ $order->id }}" class="order-checkbox" data-amount="{{ $order->cash_held }}" style="width: 16px; height: 16px; accent-color: var(--red);">
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" target="_blank" style="color: var(--red-lt); font-weight: 700; text-decoration: none;">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td>{{ $order->clientProfile->company_name }}</td>
                                <td>
                                    <div class="cell-main">{{ $order->receiver_name }}</div>
                                    <div class="cell-sub">{{ $order->city?->name ?? '—' }}</div>
                                </td>
                                <td>
                                    <div class="cell-main">{{ $order->payment_type === 'cod' ? 'COD' : 'Prepaid' }}</div>
                                    <div class="cell-sub">Cust. shipping: {{ $order->delivery_on_customer ? 'Yes' : 'No' }}</div>
                                </td>
                                <td>
                                    <strong style="color: #22c55e;">{{ number_format($order->cash_held, 2) }} JD</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-dim); padding: 35px;">
                                    No uncollected cash registered for this driver.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Settlement Box --}}
        <div class="form-section">
            <div class="form-section-title">Settle Collections</div>

            {{-- Summary stats row --}}
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 20px;">
                <div style="background: var(--bg); border: 1px solid var(--bdr); border-radius: 10px; padding: 14px 16px;">
                    <div style="font-size: .72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px;">Total Cash Held</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--text);">{{ number_format($totalCash, 2) }} JD</div>
                </div>
                <div style="background: var(--bg); border: 1px solid var(--bdr); border-radius: 10px; padding: 14px 16px;">
                    <div style="font-size: .72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px;">Selected Cash</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: #22c55e;" id="selectedTotal">0.00 JD</div>
                </div>
                <div style="background: var(--bg); border: 1px solid var(--bdr); border-radius: 10px; padding: 14px 16px;">
                    <div style="font-size: .72rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px;">Selected Orders</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--text);" id="selectedCount">0</div>
                </div>
            </div>

            {{-- Form inputs row --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="reference_number">Receipt / Reference Number</label>
                    <input type="text" name="reference_number" id="reference_number" class="form-input" placeholder="e.g. Cash receipt #1827">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="notes">Settlement Notes</label>
                    <textarea name="notes" id="notes" class="form-textarea" rows="1" placeholder="Optional notes..." style="height: 42px; resize: none;"></textarea>
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                @if(auth()->user()->hasAdminAction('finances.settlements'))
                <button type="submit" class="btn-primary" style="flex: 1; justify-content: center; height: 42px;" id="submitButton" disabled>
                    Confirm Settle Cash
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
        const selectedTotalSpan = document.getElementById('selectedTotal');
        const selectedCountSpan = document.getElementById('selectedCount');
        const submitButton = document.getElementById('submitButton');

        function updateTotals() {
            let total = 0;
            let count = 0;

            checkboxes.forEach(cb => {
                if (cb.checked) {
                    total += parseFloat(cb.dataset.amount);
                    count++;
                }
            });

            selectedTotalSpan.textContent = total.toFixed(2) + ' JD';
            selectedCountSpan.textContent = count;
            
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
                // If any is unchecked, uncheck selectAll
                if (!this.checked) {
                    selectAll.checked = false;
                } else {
                    // Check if all are checked
                    const allChecked = Array.from(checkboxes).every(c => c.checked);
                    selectAll.checked = allChecked;
                }
                updateTotals();
            });
        });
    </script>
@endsection
