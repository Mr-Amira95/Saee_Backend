@extends('admin.layouts.app')

@section('title', 'Import Validation Report')
@section('page-title', 'Import Validation Report')

@section('breadcrumb')
    <span class="sep">/</span>
    <a href="{{ route('admin.orders.index') }}">Orders</a>
    <span class="sep">/</span>
    <a href="{{ route('admin.orders.import') }}">Bulk Import</a>
    <span class="sep">/</span>
    <span class="current">Validation Report</span>
@endsection

@section('head')
    <style>
        .cell-invalid {
            box-shadow: inset 0 0 0 1px var(--red-lt);
            background: rgba(220, 38, 38, 0.05);
            border-radius: 6px;
        }
    </style>
@endsection

@section('content')
    <div class="page-hd">
        <div class="page-hd-left">
            <h1>Import Validation Report</h1>
            <p>We parsed your spreadsheet but found errors. Please correct the template and re-upload.</p>
        </div>
        <div class="page-hd-right">
            <a href="{{ route('admin.orders.import') }}" class="btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Go Back &amp; Re-upload
            </a>
        </div>
    </div>

    <div class="flash flash-err" style="margin-bottom: 22px; border-radius: 14px;">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <strong style="display: block; font-size: 0.95rem; margin-bottom: 3px;">Validation Failed</strong>
            We found formatting errors in the rows highlighted below. No orders were imported.
        </div>
    </div>

    {{-- Validation Grid --}}
    <div class="table-card" style="border-color: rgba(220, 38, 38, 0.25);">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 70px; text-align: center;">Row</th>
                        <th>Client ID</th>
                        <th>Receiver Info</th>
                        <th>Pricing / Payment</th>
                        <th>Destination</th>
                        <th>Validation Result</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $item)
                        @php
                            $hasRowErrors = !empty($item['errors']);
                            $errClient = $errReceiver = $errPricing = $errDestination = false;
                            foreach ($item['errors'] ?? [] as $error) {
                                if (str_contains($error, 'Client ID')) {
                                    $errClient = true;
                                } elseif (str_contains($error, 'Receiver name') || str_contains($error, 'Receiver phone')) {
                                    $errReceiver = true;
                                } elseif (str_contains($error, 'Payment type') || str_contains($error, 'Order price') || str_contains($error, 'delivery_on_customer') || str_contains($error, 'delivery_customer_amount') || str_contains($error, 'Delivery shift')) {
                                    $errPricing = true;
                                } elseif (str_contains($error, 'City ID') || str_contains($error, 'Area ID') || str_contains($error, 'Address text')) {
                                    $errDestination = true;
                                }
                            }
                        @endphp
                        <tr style="{{ $hasRowErrors ? 'background: rgba(220, 38, 38, 0.03);' : '' }}">
                            <td style="text-align: center; font-weight: 700; color: {{ $hasRowErrors ? 'var(--red-lt)' : 'var(--text-dim)' }};">
                                {{ $item['row_number'] }}
                            </td>
                            <td class="{{ $errClient ? 'cell-invalid' : '' }}">
                                <div class="cell-main">ID: {{ $item['data']['client_id'] ?? 'N/A' }}</div>
                            </td>
                            <td class="{{ $errReceiver ? 'cell-invalid' : '' }}">
                                <div class="cell-main">{{ $item['data']['receiver_name'] ?: 'N/A' }}</div>
                                <div class="cell-sub">{{ $item['data']['receiver_phone'] ?: 'N/A' }}</div>
                            </td>
                            <td class="{{ $errPricing ? 'cell-invalid' : '' }}">
                                <div class="cell-main" style="text-transform: uppercase;">
                                    {{ $item['data']['payment_type'] ?: 'N/A' }}
                                </div>
                                <div class="cell-sub">
                                    Price: {{ number_format((float)($item['data']['order_price'] ?? 0), 2) }} JD
                                </div>
                                <div class="cell-sub" style="margin-top: 2px; font-weight: 500;">
                                    Shift: {{ $item['data']['delivery_shift'] ?? 'doesnt_matter' }}
                                </div>
                            </td>
                            <td class="{{ $errDestination ? 'cell-invalid' : '' }}">
                                <div class="cell-main">City ID: {{ $item['data']['city_id'] ?? 'N/A' }}</div>
                                <div class="cell-sub">Area ID: {{ $item['data']['area_id'] ?? 'N/A' }}</div>
                            </td>
                            <td>
                                @if($hasRowErrors)
                                    <ul style="margin: 0; padding-left: 14px; color: #fca5a5; font-size: .8rem;">
                                        @foreach($item['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="badge badge-active">
                                        <span class="badge-dot"></span> Valid Row
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
