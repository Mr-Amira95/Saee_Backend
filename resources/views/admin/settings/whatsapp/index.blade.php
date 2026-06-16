@extends('admin.layouts.app')

@section('title', 'WhatsApp Templates')
@section('page-title', 'WhatsApp Messages Templates')

@section('breadcrumb')
    <span class="sep">/</span> <span class="current">WhatsApp Templates</span>
@endsection

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    {{-- Grid for editing templates & placeholders guide --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; align-items: start;">
        
        {{-- Templates Editing Forms --}}
        <div style="display: flex; flex-direction: column; gap: 16px;">
            @foreach($templates as $tpl)
                <div class="form-section" style="margin-bottom: 0;">
                    <div class="form-section-title">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        @if($tpl->event === 'order_created')
                            New Order Created Template
                        @elseif($tpl->event === 'order_delivered')
                            Order Delivered (Review Request) Template
                        @elseif($tpl->event === 'order_rejected')
                            Order Rejected (Feedback Request) Template
                        @else
                            {{ ucfirst(str_replace('_', ' ', $tpl->event)) }} Template
                        @endif
                    </div>

                    <form action="{{ route('admin.whatsapp-templates.update', $tpl) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label" for="body_{{ $tpl->id }}">Message Body</label>
                            <textarea 
                                name="template_body" 
                                id="body_{{ $tpl->id }}" 
                                class="form-textarea" 
                                style="min-height: 110px; line-height: 1.5; font-family: inherit;" 
                                required
                            >{{ $tpl->template_body }}</textarea>
                            @error('template_body')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-actions" style="padding-top: 0;">
                            <button type="submit" class="btn-primary" style="padding: 8px 16px; font-size: .8rem;">
                                Save Template
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>

        {{-- Help & Placeholders Guide --}}
        <div class="form-section" style="margin-bottom: 0;">
            <div class="form-section-title" style="color: var(--warning); border-bottom: 1px solid rgba(245,158,11,.15);">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Placeholders Guide
            </div>
            <p style="font-size: .78rem; color: var(--text-sub); line-height: 1.5; margin-bottom: 14px;">
                You can use the following tags inside the templates. They will be automatically replaced with live order details before the WhatsApp message is compiled:
            </p>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div style="background: rgba(255,255,255,.02); border: 1px solid var(--bdr); padding: 8px 12px; border-radius: 8px;">
                    <strong style="font-size: .78rem; color: var(--red-lt); font-family: monospace;">{customer_name}</strong>
                    <div style="font-size: .72rem; color: var(--text-dim); margin-top: 2px;">Receiver's full name.</div>
                </div>
                <div style="background: rgba(255,255,255,.02); border: 1px solid var(--bdr); padding: 8px 12px; border-radius: 8px;">
                    <strong style="font-size: .78rem; color: var(--red-lt); font-family: monospace;">{order_number}</strong>
                    <div style="font-size: .72rem; color: var(--text-dim); margin-top: 2px;">The 12-digit structured order ID.</div>
                </div>
                <div style="background: rgba(255,255,255,.02); border: 1px solid var(--bdr); padding: 8px 12px; border-radius: 8px;">
                    <strong style="font-size: .78rem; color: var(--red-lt); font-family: monospace;">{driver_name}</strong>
                    <div style="font-size: .72rem; color: var(--text-dim); margin-top: 2px;">Assigned driver's name.</div>
                </div>
                <div style="background: rgba(255,255,255,.02); border: 1px solid var(--bdr); padding: 8px 12px; border-radius: 8px;">
                    <strong style="font-size: .78rem; color: var(--red-lt); font-family: monospace;">{driver_phone}</strong>
                    <div style="font-size: .72rem; color: var(--text-dim); margin-top: 2px;">Driver's phone number.</div>
                </div>
                <div style="background: rgba(255,255,255,.02); border: 1px solid var(--bdr); padding: 8px 12px; border-radius: 8px;">
                    <strong style="font-size: .78rem; color: var(--red-lt); font-family: monospace;">{location_link}</strong>
                    <div style="font-size: .72rem; color: var(--text-dim); margin-top: 2px;">URL pointing to customer location submission page.</div>
                </div>
                <div style="background: rgba(255,255,255,.02); border: 1px solid var(--bdr); padding: 8px 12px; border-radius: 8px;">
                    <strong style="font-size: .78rem; color: var(--red-lt); font-family: monospace;">{rejection_reason}</strong>
                    <div style="font-size: .72rem; color: var(--text-dim); margin-top: 2px;">Rejection reason text (only for rejected event).</div>
                </div>
            </div>
        </div>

    </div>

    {{-- Audit Logs Card --}}
    <div class="table-card">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--bdr); display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 700; font-size: .95rem; display: flex; align-items: center; gap: 8px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                WhatsApp Log Trail (Simulated Notifications)
            </div>
            <span style="font-size: .72rem; color: var(--text-dim);">Shows messages generated during order lifecycle triggers</span>
        </div>
        
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 140px;">Timestamp</th>
                        <th style="width: 130px;">Order Number</th>
                        <th style="width: 120px;">Recipient</th>
                        <th>Message Content</th>
                        <th style="width: 100px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td style="color: var(--text-sub); font-size: .78rem; white-space: nowrap;">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td>
                                @if($log->order)
                                    <a href="{{ route('admin.orders.show', $log->order) }}" style="color: var(--red-lt); text-decoration: none; font-weight: 600;">
                                        #{{ $log->order->order_number }}
                                    </a>
                                @else
                                    <span style="color: var(--text-dim)">Deleted Order</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: .8rem;">{{ $log->phone }}</div>
                                @if($log->order)
                                    <div style="font-size: .7rem; color: var(--text-dim)">{{ $log->order->receiver_name }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="background: rgba(0,0,0,.15); padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,.02); font-family: monospace; font-size: .76rem; color: var(--text-sub); white-space: pre-wrap; word-break: break-word; line-height: 1.4;">{{ $log->message }}</div>
                            </td>
                            <td>
                                @if($log->status === 'simulated' || $log->status === 'sent')
                                    <span class="badge badge-active">
                                        <span class="badge-dot"></span> Simulated
                                    </span>
                                @else
                                    <span class="badge badge-suspended">
                                        <span class="badge-dot"></span> Failed
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5"/>
                                    </svg>
                                    <h3>No WhatsApp Logs found</h3>
                                    <p>Logs will appear here once orders are created, delivered, or rejected.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
            <div class="pagination-wrap">
                <div class="pag-info">
                    Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} logs
                </div>
                <div class="pag-links">
                    {{ $logs->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
