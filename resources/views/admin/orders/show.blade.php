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
            {{-- Quick status update triggers --}}
            <button class="btn-secondary" onclick="openModal('assignDriverModal')">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                {{ $order->driver_id ? 'Reassign Driver' : 'Assign Driver' }}
            </button>
            <button class="btn-primary" onclick="openModal('updateStatusModal')">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Update Status
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
                    <strong>{{ $order->receiver_name }}</strong>
                </div>
                <div class="info-row">
                    <span>Receiver Phone:</span>
                    <strong>{{ $order->receiver_phone }}</strong>
                </div>
                <div class="info-row">
                    <span>City / Area:</span>
                    <strong>{{ $order->city->name }} • {{ $order->area->name }}</strong>
                </div>
                <div class="info-row">
                    <span>Address Text:</span>
                    <strong>{{ $order->address_text }}</strong>
                </div>
                @if($order->address_location)
                    <div class="info-row">
                        <span>GPS Coordinates:</span>
                        <strong>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $order->address_location }}" target="_blank" style="color: var(--red-lt);">
                                {{ $order->address_location }} (Open Map)
                            </a>
                        </strong>
                    </div>
                @endif
                <div class="info-row">
                    <span>Description:</span>
                    <strong>{{ $order->order_description ?: 'No description' }}</strong>
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
                        <span class="badge {{ $order->payment_type === 'cod' ? 'badge-pv' : 'badge-no' }}" style="text-transform: uppercase;">
                            {{ $order->payment_type }}
                        </span>
                    </strong>
                </div>
                @if($order->payment_type === 'cod')
                    <div class="info-row">
                        <span>Goods Price (COD):</span>
                        <strong style="font-size: 1.1rem; color: var(--red-lt);">{{ number_format($order->order_price, 2) }} JD</strong>
                    </div>
                @endif
                <div class="info-row">
                    <span>Shipping Fee:</span>
                    <strong>{{ number_format($order->delivery_amount, 2) }} JD ({{ $order->delivery_on_customer ? 'Paid by Customer' : 'Paid by Client' }})</strong>
                </div>
                @if($order->delivery_on_customer)
                    <div class="info-row">
                        <span>Customer Delivery Fee:</span>
                        <strong>{{ number_format($order->delivery_customer_amount, 2) }} JD</strong>
                    </div>
                @endif
                <div class="info-row" style="border-top: 1px solid var(--bdr); padding-top: 10px; margin-top: 10px;">
                    <span>Total Cash to Collect:</span>
                    <strong style="font-size: 1.25rem; font-weight: 800; color: #22c55e;">
                        @php
                            $totalCollect = ($order->payment_type === 'cod' ? $order->order_price : 0) 
                                + ($order->delivery_on_customer ? $order->delivery_customer_amount : 0);
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
                    <div class="timeline-item {{ $loop->first ? 'active' : 'completed' }}">
                        <div class="timeline-icon">
                            @if($log->to_status === 'delivered')
                                <svg width="10" height="10" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                            @elseif($log->to_status === 'rejected')
                                <svg width="10" height="10" fill="currentColor" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                            @else
                                <span class="badge-dot" style="margin:0"></span>
                            @endif
                        </div>
                        <div class="timeline-time">{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                        <div class="timeline-title">
                            Status: <span style="color: var(--red-lt);">{{ ucfirst(str_replace('_', ' ', $log->to_status)) }}</span>
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
                            <option value="{{ $driver->id }}" {{ $order->driver_id == $driver->id ? 'selected' : '' }}>
                                {{ $driver->name }} ({{ $driver->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('assignDriverModal')">Cancel</button>
                    <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); box-shadow: 0 4px 14px rgba(59,130,246,0.3);">Assign</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: Update Status --}}
    <div class="modal-overlay" id="updateStatusModal">
        <div class="modal-card" style="border-color: rgba(220,38,38,0.25); max-width: 460px;">
            <h3>Update Shipment Status</h3>
            <p style="margin-bottom: 18px;">Select the new status for this order and provide required details.</p>
            <form action="{{ route('admin.orders.update', $order) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                @if($order->driver_id)
                    <input type="hidden" name="driver_id" value="{{ $order->driver_id }}">
                @endif
                
                <div class="form-group" style="text-align: left; margin-bottom: 16px;">
                    <label class="form-label" for="status_select">New Status</label>
                    <select name="status" id="status_select" class="form-select" required style="width:100%">
                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending Collection</option>
                        <option value="picked_up" {{ $order->status === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                        <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="rejected" {{ $order->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="returned" {{ $order->status === 'returned' ? 'selected' : '' }}>Returned</option>
                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                {{-- Delivery Proof Fields (shown only when Delivered is selected) --}}
                <div id="deliveredFields" style="display: none; text-align: left; margin-bottom: 16px; border-top: 1px solid var(--bdr); padding-top: 16px;">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" for="signature">Customer Signature Image (Optional)</label>
                        <input type="file" name="signature" id="signature" class="form-input" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="proof_image">Delivery Proof Photo (Optional)</label>
                        <input type="file" name="proof_image" id="proof_image" class="form-input" accept="image/*">
                    </div>
                </div>

                {{-- Rejection Fields (shown only when Rejected is selected) --}}
                <div id="rejectedFields" style="display: none; text-align: left; margin-bottom: 16px; border-top: 1px solid var(--bdr); padding-top: 16px;">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" for="rejection_reason_id">Rejection Reason <span class="req">*</span></label>
                        <select name="rejection_reason_id" id="rejection_reason_id" class="form-select" style="width:100%">
                            <option value="">Select Reason</option>
                            @foreach($rejectionReasons ?? [] as $reason)
                                <option value="{{ $reason->id }}">{{ $reason->reason }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="modal_notes">Rejection Notes</label>
                        <textarea name="notes" id="modal_notes" class="form-textarea" placeholder="Provide extra details on why delivery failed..."></textarea>
                    </div>
                </div>

                <div class="modal-actions" style="margin-top: 24px;">
                    <button type="button" class="btn-secondary" onclick="closeModal('updateStatusModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Update</button>
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

        // Handle Modal Overlay Click Dismissals
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) closeModal(this.id);
            });
        });

        // Dynamic Status fields toggling
        const statusSelect = document.getElementById('status_select');
        const deliveredFields = document.getElementById('deliveredFields');
        const rejectedFields = document.getElementById('rejectedFields');
        const rejectionReasonInput = document.getElementById('rejection_reason_id');

        statusSelect.addEventListener('change', function() {
            if (this.value === 'delivered') {
                deliveredFields.style.display = 'block';
                rejectedFields.style.display = 'none';
                rejectionReasonInput.required = false;
            } else if (this.value === 'rejected') {
                deliveredFields.style.display = 'none';
                rejectedFields.style.display = 'block';
                rejectionReasonInput.required = true;
            } else {
                deliveredFields.style.display = 'none';
                rejectedFields.style.display = 'none';
                rejectionReasonInput.required = false;
            }
        });
        
        // Initial check
        statusSelect.dispatchEvent(new Event('change'));
    </script>
@endsection
