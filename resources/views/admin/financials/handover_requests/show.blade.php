@extends('admin.layouts.app')

@section('title', 'Handover Request Details')
@section('page-title', 'Handover Request Details')

@section('breadcrumb')
    <a href="{{ route('admin.financials.index') }}">Finance Dashboard</a>
    <span class="sep">/</span>
    <a href="{{ route('admin.financials.handover-requests.index') }}">Checkout Approvals</a>
    <span class="sep">/</span>
    <span class="current">Details</span>
@endsection

@section('content')
    {{-- Back Link --}}
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.financials.handover-requests.index') }}" style="display: inline-flex; align-items: center; gap: 8px; color: var(--text-sub); text-decoration: none; font-size: 0.88rem; font-weight: 500;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Checkout Approvals
        </a>
    </div>

    {{-- Session Messages --}}
    @if(session('error'))
        <div style="margin-bottom: 15px; padding: 12px 16px; background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; border-radius: 8px; color: #f87171; font-size: 0.88rem;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Top Row: Handover Summary + Action Required (each half width) --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; margin-bottom: 24px;">

        {{-- Summary Card --}}
        <div class="table-card" style="padding: 20px; @if($handoverRequest->status !== 'pending') grid-column: 1 / -1; @endif">
            <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--text-sub); border-bottom: 1px solid var(--bdr); padding-bottom: 12px; margin-bottom: 16px;">
                Handover Summary
            </h3>

            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.88rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-dim);">Driver:</span>
                    <strong style="color: var(--text-sub);">{{ $handoverRequest->driver->name }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-dim);">Total Cash:</span>
                    <strong style="color: var(--green); font-size: 1.05rem;">{{ number_format($totalCash, 2) }} JD</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-dim);">Returns Count:</span>
                    <strong style="color: var(--text-sub);">{{ $rejectedOrders->count() }} orders</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--text-dim);">Status:</span>
                    @if($handoverRequest->status === 'pending')
                        <span class="badge badge-pending" style="font-size: 0.72rem;">Pending Approval</span>
                    @else
                        <span class="badge badge-success" style="font-size: 0.72rem;">Approved</span>
                    @endif
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-dim);">Submitted:</span>
                    <span style="color: var(--text-dim);">{{ $handoverRequest->created_at->format('Y-m-d H:i') }}</span>
                </div>
                @if($handoverRequest->payment_method)
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-dim);">Handover Method:</span>
                        <strong style="color: var(--text-sub); text-transform: capitalize;">{{ str_replace('_', ' ', $handoverRequest->payment_method) }}</strong>
                    </div>
                @endif

                @if($handoverRequest->status === 'approved')
                    <div style="border-top: 1px solid var(--bdr); padding-top: 12px; margin-top: 4px; display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-dim);">Approved By:</span>
                            <span style="color: var(--text-sub);">{{ $handoverRequest->approver?->name ?? 'System' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-dim);">Approved At:</span>
                            <span style="color: var(--text-dim);">{{ $handoverRequest->approved_at?->format('Y-m-d H:i') ?? '-' }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Approval Action --}}
        @if($handoverRequest->status === 'pending' && auth()->user()->hasAdminAction('finances.checkout_approvals'))
            <div class="table-card" style="padding: 20px; border: 1px solid rgba(245, 158, 11, 0.3);">
                <h3 style="font-size: 0.95rem; font-weight: 700; color: #f59e0b; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Action Required
                </h3>
                <p style="font-size: 0.8rem; color: var(--text-dim); line-height: 1.4; margin-bottom: 16px;">
                    Verify that you have physically received the cash amount and returned orders. Once confirmed, approve the handover to post ledger entries.
                </p>
                <form action="{{ route('admin.financials.handover-requests.approve', $handoverRequest) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; height: 40px; box-shadow: none; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Approve Handover
                    </button>
                </form>
            </div>
        @endif

    </div>

    {{-- Notes Card (full width) --}}
    @if($handoverRequest->notes)
        <div class="table-card" style="padding: 16px; margin-bottom: 24px;">
            <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; margin-bottom: 8px;">Driver Notes</h4>
            <p style="font-size: 0.88rem; color: var(--text-sub); line-height: 1.4; font-style: italic; background: rgba(255,255,255,0.02); padding: 10px; border-radius: 6px; border-left: 3px solid #f59e0b; margin: 0;">
                "{{ $handoverRequest->notes }}"
            </p>
        </div>
    @endif

    {{-- Cash Handover Proof --}}
    @if($handoverRequest->proof_image_path)
        <div class="table-card" style="padding: 16px; margin-bottom: 24px;">
            <h4 style="font-size: 0.85rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; margin-bottom: 8px;">
                Cash Handover Proof ({{ str_replace('_', ' ', $handoverRequest->payment_method) }})
            </h4>
            <a href="{{ Storage::disk('public')->url($handoverRequest->proof_image_path) }}" target="_blank">
                <img src="{{ Storage::disk('public')->url($handoverRequest->proof_image_path) }}" alt="Handover proof" style="max-width: 260px; max-height: 260px; border-radius: 8px; border: 1px solid var(--bdr);">
            </a>
        </div>
    @endif

    {{-- Delivered Orders (Cash Collection) --}}
    <div class="table-card" style="margin-bottom: 24px;">
        <div style="padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02)">
            <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.08em; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Delivered Orders (Cash to Settle)
            </h3>
            <span class="badge badge-success">{{ $deliveredOrders->count() }} orders</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Client / Merchant</th>
                        <th>COD Price</th>
                        <th>Customer Delivery Fee</th>
                        <th>Cash Collected</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveredOrders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" style="color: #f59e0b; font-weight: 600; text-decoration: none;">
                                    #{{ $order->order_number }}
                                </a>
                            </td>
                            <td>
                                <div class="cell-main">{{ $order->clientProfile?->company_name ?? 'N/A' }}</div>
                            </td>
                            <td>{{ number_format($order->payment?->order_amount ?? 0, 2) }} JD</td>
                            <td>
                                @if($order->payment?->delivery_on_customer)
                                    {{ number_format($order->payment?->customer_delivery_amount ?? 0, 2) }} JD
                                @else
                                    <span style="color: var(--text-dim); font-size: 0.8rem; font-style: italic;">Paid by client</span>
                                @endif
                            </td>
                            <td>
                                <strong style="color: var(--green);">{{ number_format($order->cash_held, 2) }} JD</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-dim); padding: 30px;">
                                No delivered cash orders in this handover request.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Rejected Orders (Returns) --}}
    <div class="table-card">
        <div style="padding: 16px; border-bottom: 1px solid var(--bdr); display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02)">
            <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--text-sub); text-transform: uppercase; letter-spacing: 0.08em; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                Rejected Orders (Returns to Confirm)
            </h3>
            <span class="badge badge-pending">{{ $rejectedOrders->count() }} orders</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Client / Merchant</th>
                        <th>Rejection Reason</th>
                        <th>Receiver Info</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rejectedOrders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" style="color: #f59e0b; font-weight: 600; text-decoration: none;">
                                    #{{ $order->order_number }}
                                </a>
                            </td>
                            <td>
                                <div class="cell-main">{{ $order->clientProfile?->company_name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="cell-main">{{ $order->rejectionReason?->reason ?? 'Not specified' }}</div>
                                @if($order->notes)
                                    <div class="cell-sub" style="font-style: italic;">Notes: {{ $order->notes }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="cell-main">{{ $order->receiver?->receiver_name ?? 'N/A' }}</div>
                                <div class="cell-sub">{{ $order->receiver?->receiver_phone ?? '' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-dim); padding: 30px;">
                                No rejected orders in this handover request.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
