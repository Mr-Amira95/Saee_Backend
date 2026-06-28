@extends('admin.layouts.app')

@section('title', 'Order Details')
@section('page-title', 'Order Details')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.orders.index') }}">Orders</a>
    <span class="sep">/</span>
    <span class="current">#{{ $order->order_number }}</span>
@endsection

@section('head')
    <style>
        /* Timeline Styles */
        .timeline {
            position: relative;
            padding-left: 32px;
            margin-top: 14px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 9px;
            top: 6px;
            bottom: 6px;
            width: 2px;
            background: rgba(255, 255, 255, 0.08);
        }
        .timeline-item {
            position: relative;
            margin-bottom: 24px;
        }
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        .timeline-icon {
            position: absolute;
            left: -32px;
            top: 2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--bg-2);
            border: 2px solid var(--text-dim);
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .timeline-item.active .timeline-icon {
            border-color: var(--red-lt);
            background: var(--red-lt);
            box-shadow: 0 0 10px var(--red-glow);
        }
        .timeline-item.status-delivered.active .timeline-icon {
            border-color: #4ade80 !important;
            background: #4ade80 !important;
            box-shadow: 0 0 10px rgba(74, 222, 128, 0.4) !important;
        }
        .timeline-item.status-rejected.active .timeline-icon,
        .timeline-item.status-cancelled.active .timeline-icon {
            border-color: #f87171 !important;
            background: #f87171 !important;
            box-shadow: 0 0 10px rgba(248, 113, 113, 0.4) !important;
        }
        .timeline-item.completed .timeline-icon {
            border-color: var(--success);
            background: var(--success);
        }
        .timeline-time {
            font-size: 0.72rem;
            color: var(--text-dim);
            font-weight: 600;
        }
        .timeline-title {
            font-size: 0.86rem;
            font-weight: 700;
            color: var(--text);
            margin-top: 2px;
        }
        .timeline-desc {
            font-size: 0.81rem;
            color: var(--text-sub);
            margin-top: 3px;
            line-height: 1.4;
        }
        .timeline-user {
            font-size: 0.74rem;
            color: var(--red-lt);
            font-weight: 500;
            margin-top: 4px;
        }
    </style>
@endsection

@section('content')
    {{-- Page Header --}}
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Order #{{ $order->order_number }}</h1>
            <p>Created by {{ $order->clientProfile->company_name }} on {{ $order->created_at->format('Y-m-d H:i') }}</p>
        </div>
        <div class="page-hd-right" style="display: flex; gap: 8px;">
            @if($order->status === 'pending')
                <button class="btn-danger" onclick="openModal('cancelOrderModal')">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Cancel Order
                </button>
            @endif
            <a href="{{ route('admin.orders.print', $order) }}" target="_blank" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Waybill
            </a>
            <a href="{{ route('admin.orders.edit', $order) }}" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Order
            </a>
            <button class="btn-primary" onclick="openModal('assignDriverModal')">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                {{ $order->driver ? 'Reassign Driver' : 'Assign Driver' }}
            </button>
        </div>
    </div>

    {{-- Details Grid --}}
    <div class="info-grid">
        {{-- Left Info: Receiver & Client --}}
        <div class="info-card">
            <div class="info-card-title">Shipment Information</div>
            <div class="info-rows">
                <div class="info-row">
                    <span>Client:</span>
                    <strong>{{ $order->clientProfile->company_name }} (ID: {{ $order->clientProfile->id }})</strong>
                </div>
                <div class="info-row">
                    <span>Receiver Name:</span>
                    <strong>{{ $order->receiver?->receiver_name }}</strong>
                </div>
                <div class="info-row">
                    <span>Receiver Phone:</span>
                    <strong>{{ $order->receiver?->receiver_phone }}</strong>
                </div>
                <div class="info-row">
                    <span>City / Area:</span>
                    <strong>{{ $order->receiver?->city?->name }} • {{ $order->receiver?->area?->name }}</strong>
                </div>
                <div class="info-row">
                    <span>Address Text:</span>
                    <strong>{{ $order->receiver?->address_text }}</strong>
                </div>
                <div class="info-row">
                    <span>Description:</span>
                    <strong>{{ $order->order_description ?: 'No description' }}</strong>
                </div>
                <div class="info-row">
                    <span>Delivery Shift:</span>
                    <strong>{{ $order->delivery_shift?->label() ?? "Doesn't Matter" }}</strong>
                </div>
                @if($order->batch_number)
                    <div class="info-row">
                        <span>Batch #:</span>
                        <strong style="font-family: monospace; letter-spacing: 0.05em; color: var(--red-lt);">
                            <a href="{{ route('admin.orders.index', ['batch_number' => $order->batch_number]) }}" style="color: var(--red-lt); text-decoration: none;" title="View all orders in this batch">
                                {{ $order->batch_number }}
                            </a>
                        </strong>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Info: Financial Metrics --}}
        <div class="info-card">
            <div class="info-card-title">Financial Details</div>
            <div class="info-rows">
                <div class="info-row">
                    <span>Payment Type:</span>
                    <strong>
                        <span class="badge {{ $order->payment?->payment_type === 'cod' ? 'badge-pv' : 'badge-no' }}" style="text-transform: uppercase;">
                            {{ $order->payment?->payment_type }}
                        </span>
                    </strong>
                </div>
                @if($order->payment?->payment_type === 'cod')
                    <div class="info-row">
                        <span>Goods Price (COD):</span>
                        <strong style="font-size: 1.1rem; color: var(--red-lt);">{{ number_format($order->payment?->order_amount ?? 0, 2) }} JD</strong>
                    </div>
                @endif
                <div class="info-row">
                    <span>Shipping Fee:</span>
                    <strong>{{ number_format($order->payment?->client_delivery_amount ?? 0, 2) }} JD</strong>
                </div>
                @if($order->payment?->delivery_on_customer)
                    <div class="info-row">
                        <span>Customer Delivery Fee:</span>
                        <strong>{{ number_format($order->payment?->customer_delivery_amount ?? 0, 2) }} JD</strong>
                    </div>
                @endif
                <div class="info-row" style="border-top: 1px solid var(--bdr); padding-top: 10px; margin-top: 10px;">
                    <span>Total Cash to Collect:</span>
                    <strong style="font-size: 1.25rem; font-weight: 800; color: #22c55e;">
                        @php
                            $totalCollect = ($order->payment?->payment_type === 'cod' ? (float)($order->payment?->order_amount ?? 0) : 0)
                                + ($order->payment?->delivery_on_customer ? (float)($order->payment?->customer_delivery_amount ?? 0) : 0);
                        @endphp
                        {{ number_format($totalCollect, 2) }} JD
                    </strong>
                </div>
                <div class="info-row">
                    <span>Payment Status:</span>
                    <strong>
                        <span class="badge {{ $order->payment_status === 'paid' ? 'badge-active' : 'badge-pending' }}">
                            {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                        </span>
                    </strong>
                </div>
                @if($order->driver)
                    <div class="info-row">
                        <span>Assigned Driver:</span>
                        <strong>{{ $order->driver->name }} ({{ $order->driver->phone }})</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- QR Code --}}
    <div class="table-card" style="margin-top: 18px; padding: 24px; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap;">
        <div>
            <div style="font-size: 0.74rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Order QR Code</div>
            <p style="font-size: 0.82rem; color: var(--text-sub); max-width: 380px; line-height: 1.6;">
                Scan to retrieve order <strong style="color: var(--text);">#{{ $order->order_number }}</strong>.<br>
                Use this QR for quick lookup at pickup points or during delivery handoff.
            </p>
        </div>
        <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
            <div style="background: #fff; border-radius: 12px; padding: 14px; display: inline-block; border: 1px solid var(--bdr);">
                {!! QrCode::size(160)->generate($order->order_number) !!}
            </div>
            <span style="font-size: 0.78rem; font-weight: 700; color: var(--text-sub); letter-spacing: 0.08em; font-family: monospace;">#{{ $order->order_number }}</span>
        </div>
    </div>

    {{-- Financial Ledger View --}}
    <div class="table-card" style="margin-top: 18px;">
        <div style="padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.08em;">Financial Ledger Audit Trail</h3>
            <span style="font-size: 0.76rem; color: var(--text-dim);">Real-time financial reconciliation ledger</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>From Account</th>
                        <th>To Account</th>
                        <th>Transaction Type</th>
                        <th>Amount</th>
                        <th>Recorded By</th>
                        <th>Reference #</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->financialLedgerEntries as $entry)
                        <tr>
                            <td>#{{ $entry->id }}</td>
                            <td>
                                <span class="badge badge-no" style="font-size: 0.72rem;">{{ strtoupper($entry->from_account) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-info" style="font-size: 0.72rem;">{{ strtoupper($entry->to_account) }}</span>
                            </td>
                            <td>
                                <span class="cell-main" style="font-size: 0.8rem;">
                                    {{ ucfirst(str_replace('_', ' ', $entry->type)) }}
                                </span>
                                <div class="cell-sub" style="font-size: 0.7rem;">{{ $entry->notes }}</div>
                            </td>
                            <td>
                                <strong style="color: {{ in_array($entry->type, ['cod_collection', 'delivery_collection', 'shipping_charge']) ? '#22c55e' : '#fca5a5' }}">
                                    {{ number_format($entry->amount, 2) }} JD
                                </strong>
                            </td>
                            <td>{{ $entry->recordedBy->name }}</td>
                            <td>{{ $entry->reference_number ?: 'N/A' }}</td>
                            <td><span class="cell-sub">{{ $entry->created_at->format('Y-m-d H:i') }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-dim); padding: 30px;">
                                No transactions posted to ledger yet. Financial entries will log automatically when delivery is confirmed.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tracking & Operations --}}
    <div class="info-grid" style="margin-top: 18px;">
        {{-- Left: Tracking log timeline --}}
        <div class="info-card" style="flex: 1.3;">
            <div class="info-card-title">Tracking History Timeline</div>
            <div class="timeline">
                @foreach($order->trackingLogs as $log)
                    <div class="timeline-item {{ $loop->first ? 'active' : 'completed' }} status-{{ $log->to_status }}">
                        <div class="timeline-icon">
                            @if($log->to_status === 'delivered')
                                <svg width="10" height="10" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                            @elseif(in_array($log->to_status, ['rejected', 'cancelled']))
                                <svg width="10" height="10" fill="currentColor" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                            @else
                                <span class="badge-dot" style="margin:0"></span>
                            @endif
                        </div>
                        <div class="timeline-time">{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                        <div class="timeline-title">
                            Status: <span style="color: {{ $log->to_status === 'delivered' ? '#4ade80' : (in_array($log->to_status, ['rejected', 'cancelled', 'returned']) ? '#f87171' : 'var(--red-lt)') }};">{{ ucfirst(str_replace('_', ' ', $log->to_status)) }}</span>
                        </div>
                        <div class="timeline-desc">{{ $log->description }}</div>
                        <div class="timeline-user">Updated by: {{ $log->user->name }} ({{ ucfirst($log->user->role) }})</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Right: Details (Signature, Rejection, Notes) --}}
        <div class="info-card" style="flex: 0.7;">
            <div class="info-card-title">Proof of Delivery / Operational Notes</div>
            <div class="info-rows" style="gap: 16px;">
                @if($order->status === 'delivered')
                    <div class="form-group">
                        <label class="form-label">Customer Signature</label>
                        @if($order->signature_path)
                            <div style="background: white; border-radius: 8px; padding: 10px; display: inline-block; border: 1px solid var(--bdr);">
                                <img src="{{ asset('storage/' . $order->signature_path) }}" alt="Customer Signature" style="max-height: 100px; width: auto; max-width: 100%;">
                            </div>
                        @else
                            <span class="cell-sub" style="font-style: italic;">No digital signature captured</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">Proof of Delivery Photo</label>
                        @if($order->proof_image_path)
                            <div style="border-radius: 8px; overflow: hidden; max-width: 200px; border: 1px solid var(--bdr);">
                                <img src="{{ asset('storage/' . $order->proof_image_path) }}" alt="Proof of Delivery" style="width: 100%; height: auto;">
                            </div>
                        @else
                            <span class="cell-sub" style="font-style: italic;">No proof photo uploaded</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            National ID Attachment
                            @if($order->clientProfile?->require_national_id)
                                <span style="font-size:.72rem;font-weight:600;color:var(--red);background:rgba(220,38,38,.1);border:1px solid rgba(220,38,38,.25);border-radius:4px;padding:1px 6px;margin-left:6px;">Required</span>
                            @else
                                <span style="font-size:.72rem;color:var(--text-dim);margin-left:6px;">(optional)</span>
                            @endif
                        </label>
                        @if($order->national_id_attachment_path)
                            @php $ext = strtolower(pathinfo($order->national_id_attachment_path, PATHINFO_EXTENSION)); @endphp
                            @if(in_array($ext, ['jpg','jpeg','png','webp']))
                                <div style="border-radius:8px;overflow:hidden;max-width:200px;border:1px solid var(--bdr);">
                                    <img src="{{ asset('storage/' . $order->national_id_attachment_path) }}" alt="National ID" style="width:100%;height:auto;">
                                </div>
                            @else
                                <a href="{{ asset('storage/' . $order->national_id_attachment_path) }}" target="_blank"
                                   style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.25);border-radius:8px;color:var(--red);font-size:.85rem;text-decoration:none;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                                    View PDF
                                </a>
                            @endif
                        @else
                            <span class="cell-sub" style="font-style:italic;">No national ID uploaded</span>
                        @endif
                    </div>
                @elseif($order->status === 'rejected')
                    <div class="form-group">
                        <label class="form-label" style="color: #ef4444;">Rejection Reason</label>
                        <strong style="color: #ef4444;">{{ $order->rejectionReason ? $order->rejectionReason->reason : 'Not specified' }}</strong>
                    </div>
                @endif
                <div class="form-group">
                    <label class="form-label">Administrative Notes</label>
                    <textarea class="form-textarea" readonly style="background: rgba(255,255,255,0.01); border-color: var(--bdr); cursor: not-allowed;" placeholder="No special notes recorded.">{{ $order->notes }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: Cancel Order --}}
    @if($order->status === 'pending')
    <div class="modal-overlay" id="cancelOrderModal">
        <div class="modal-card" style="border-color: rgba(239,68,68,0.3); max-width: 420px;">
            <h3 style="color: #ef4444;">Cancel Order</h3>
            <p style="margin-bottom: 18px;">Are you sure you want to cancel order <strong>#{{ $order->order_number }}</strong>? This action cannot be undone.</p>
            <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <input type="hidden" name="driver_id" value="{{ $order->driver_id }}">
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('cancelOrderModal')">Go Back</button>
                    <button type="submit" class="btn-danger">Cancel Order</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL 1: Assign Driver --}}
    <div class="modal-overlay" id="assignDriverModal">
        <div class="modal-card" style="border-color: rgba(59,130,246,0.3); max-width: 440px;">
            <h3>Assign Driver to Order</h3>
            <p style="margin-bottom: 18px;">Select a driver to handle delivery for this order.</p>
            <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $order->status }}">
                
                <div class="form-group" style="text-align: left; margin-bottom: 22px;">
                    <label class="form-label" for="driver_id">Driver Name</label>
                    <select name="driver_id" class="form-select" required style="width:100%">
                        <option value="">Select Driver</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ $order->driver?->id == $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }} ({{ $driver->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('assignDriverModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('open');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
        }

        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) closeModal(this.id);
            });
        });
    </script>
@endsection
