@extends('client.layouts.app')
@section('title', __('Home'))
@section('page-title', __('Home'))

@push('styles')
<style>
    .welcome-hero {
        background: linear-gradient(135deg, rgba(220,38,38,0.15) 0%, rgba(12,18,48,0.9) 100%);
        border: 1px solid var(--bdr-red);
        border-radius: 18px;
        padding: 28px 30px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        animation: fu .4s both;
    }
    .welcome-title {
        font-size: 1.6rem;
        font-weight: 900;
        letter-spacing: -.02em;
        margin-bottom: 6px;
        color: var(--text);
    }
    .welcome-name {
        background: linear-gradient(to right, #fca5a5, var(--red-lt));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .welcome-subtitle {
        font-size: .88rem;
        color: var(--text-sub);
        max-width: 580px;
        line-height: 1.5;
    }
    .status-widget {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--bdr);
        padding: 8px 14px;
        border-radius: 12px;
        align-items: center;
    }
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--success);
        display: inline-block;
    }
    .status-text {
        font-size: .78rem;
        font-weight: 600;
        color: var(--text-sub);
    }

    /* ─── Light Mode ────────────────────────────── */
    html.light-theme .welcome-hero {
        background: linear-gradient(135deg, rgba(220,38,38,0.05) 0%, rgba(255,255,255,0.9) 100%);
        border-color: rgba(220,38,38,0.12);
    }
    html.light-theme .welcome-name {
        background: linear-gradient(to right, var(--red), var(--red-lt));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    html.light-theme .status-widget {
        background: rgba(15, 23, 42, 0.02);
        border-color: rgba(15, 23, 42, 0.08);
    }
</style>
@endpush

@section('content')

{{-- Welcome Header Hero --}}
<div class="welcome-hero">
    <div>
        <h1 class="welcome-title">
            {{ __('Welcome back,') }} <span class="welcome-name">{{ $profile->company_name }}</span>!
        </h1>
        <p class="welcome-subtitle">
            {{ __('Manage your logistics pipeline, track incoming and outgoing shipments, and keep your business moving efficiently.') }}
        </p>
    </div>
    <div class="status-widget">
        <span class="status-dot"></span>
        <span class="status-text">{{ __('Sa\'ee Logistics Status: Operational') }}</span>
    </div>
</div>

{{-- Quick Action Shortcuts Grid --}}
<div style="margin-bottom: 24px; animation: fu .45s .05s both;">
    <div style="font-size: .76rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: .1em; margin-bottom: 12px;">{{ __('Quick Actions') }}</div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
        <!-- Shortcut 1: Create Order -->
        <a href="{{ route('client.orders.create') }}" style="text-decoration: none;">
            <div style="background: var(--card); border: 1px solid var(--bdr); border-radius: 16px; padding: 22px; transition: border-color .2s, transform .2s, box-shadow .2s; height: 100%; display: flex; flex-direction: column; justify-content: space-between;" 
                 onmouseenter="this.style.borderColor='rgba(220,38,38,0.4)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 24px rgba(220,38,38,0.06)';" 
                 onmouseleave="this.style.borderColor='var(--bdr)'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <div>
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(220,38,38,0.12); color: var(--red-lt); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: 16px;">📦</div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text); margin-bottom: 6px;">{{ __('Create Order') }}</h3>
                    <p style="font-size: .8rem; color: var(--text-dim); line-height: 1.4;">{{ __('Ship a new parcel immediately. Enter customer information and print labels.') }}</p>
                </div>
                <div style="font-size: .8rem; font-weight: 600; color: var(--red-lt); margin-top: 18px; display: flex; align-items: center; gap: 4px;">
                    {{ __('New Shipment') }} <span>→</span>
                </div>
            </div>
        </a>

        <!-- Shortcut 2: Bulk Import -->
        <a href="{{ route('client.orders.import') }}" style="text-decoration: none;">
            <div style="background: var(--card); border: 1px solid var(--bdr); border-radius: 16px; padding: 22px; transition: border-color .2s, transform .2s, box-shadow .2s; height: 100%; display: flex; flex-direction: column; justify-content: space-between;" 
                 onmouseenter="this.style.borderColor='rgba(59,130,246,0.4)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 24px rgba(59,130,246,0.06)';" 
                 onmouseleave="this.style.borderColor='var(--bdr)'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <div>
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(59,130,246,0.12); color: #60a5fa; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: 16px;">📥</div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text); margin-bottom: 6px;">{{ __('Import Orders') }}</h3>
                    <p style="font-size: .8rem; color: var(--text-dim); line-height: 1.4;">{{ __('Upload multiple shipments at once using an Excel or CSV template sheet.') }}</p>
                </div>
                <div style="font-size: .8rem; font-weight: 600; color: #60a5fa; margin-top: 18px; display: flex; align-items: center; gap: 4px;">
                    {{ __('Bulk Import') }} <span>→</span>
                </div>
            </div>
        </a>

        <!-- Shortcut 3: Create Support Ticket -->
        <a href="{{ route('client.support.index') }}" style="text-decoration: none;">
            <div style="background: var(--card); border: 1px solid var(--bdr); border-radius: 16px; padding: 22px; transition: border-color .2s, transform .2s, box-shadow .2s; height: 100%; display: flex; flex-direction: column; justify-content: space-between;" 
                 onmouseenter="this.style.borderColor='rgba(245,158,11,0.4)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 24px rgba(245,158,11,0.06)';" 
                 onmouseleave="this.style.borderColor='var(--bdr)'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                <div>
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(245,158,11,0.12); color: #fbbf24; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12c0-4.97 4.03-9 9-9s9 4.03 9 9" />
                            <rect x="1" y="10" width="3" height="6" rx="1.5" />
                            <rect x="20" y="10" width="3" height="6" rx="1.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 14c0 3 2.5 5 5.5 5" />
                        </svg>
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--text); margin-bottom: 6px;">{{ __('Support Center') }}</h3>
                    <p style="font-size: .8rem; color: var(--text-dim); line-height: 1.4;">{{ __('Need help? Submit support tickets or chat with agents about delivery issues.') }}</p>
                </div>
                <div style="font-size: .8rem; font-weight: 600; color: #fbbf24; margin-top: 18px; display: flex; align-items: center; gap: 4px;">
                    {{ __('Get Support') }} <span>→</span>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Dedicated Track Shipment Section --}}
<div style="background: var(--card); border: 1px solid var(--bdr); border-radius: 16px; padding: 24px 26px; margin-bottom: 24px; animation: fu .4s both;">
    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
        <span style="font-size: 1.1rem;">🔍</span>
        <div style="font-size: .8rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: .1em;">{{ __('Track a Shipment') }}</div>
    </div>
    <form method="GET" action="{{ route('client.track') }}" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <div class="filter-search-wrap" style="flex: 1; min-width: 260px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input name="q" type="text" class="filter-input" placeholder="{{ __('Enter order reference, customer name, or phone number…') }}" value="{{ request('q') }}">
        </div>
        <button type="submit" class="btn-primary" style="padding: 10px 22px;">
            {{ __('Search Order') }}
        </button>
    </form>
</div>

{{-- Dynamic Overview Cards & Volume History --}}
@php
    $maxVal = max(1, max($daysTrend));
    $points = [];
    $fillPoints = [];
    $idx = 0;
    $totalDays = count($daysTrend);
    foreach ($daysTrend as $date => $cnt) {
        $x = ($idx / ($totalDays - 1)) * 380 + 10;
        $y = 80 - (($cnt / $maxVal) * 60) - 10;
        $points[] = "$x,$y";
        if ($idx === 0) {
            $fillPoints[] = "10,80";
        }
        $fillPoints[] = "$x,$y";
        if ($idx === $totalDays - 1) {
            $fillPoints[] = "$x,80";
        }
        $idx++;
    }
    $polylinePoints = implode(' ', $points);
    $polygonPoints = implode(' ', $fillPoints);
@endphp
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 24px; animation: fu .45s .05s both;">
    <!-- Wallet & Credit card -->
    <div class="card" style="background: linear-gradient(135deg, rgba(220,38,38,0.08) 0%, rgba(12,18,48,0.85) 100%); display: flex; flex-direction: column; justify-content: space-between; border-color: var(--bdr-red);">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <span style="font-size: .74rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: .1em;">{{ __('Account Balance') }}</span>
                <span class="badge {{ $balance >= 0 ? 'badge-success' : 'badge-danger' }}" style="font-size: .68rem;">{{ $balance >= 0 ? __('Credit') : __('Debit') }}</span>
            </div>
            <div style="font-size: 2.2rem; font-weight: 900; color: {{ $balance >= 0 ? '#4ade80' : '#f87171' }}; letter-spacing: -.03em; line-height: 1;">
                {{ number_format($balance, 2) }} <span style="font-size: 1.1rem; font-weight: 600; color: var(--text-sub);">JD</span>
            </div>
            <div style="font-size: .78rem; color: var(--text-dim); margin-top: 8px;">
                {{ __('Credit Limit:') }} <strong style="color: var(--text-sub);">{{ number_format($creditLimit, 2) }} JD</strong>
            </div>
        </div>
        <div style="margin-top: 20px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,.04); display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: .74rem; color: var(--text-dim);">{{ __('Status: Active') }}</span>
            <a href="{{ route('client.financials.invoices') }}" class="btn-primary" style="padding: 6px 14px; font-size: .78rem; box-shadow: none; border-radius: 8px;">
                {{ __('Statement Details') }}
            </a>
        </div>
    </div>

    <!-- Trend line card -->
    <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-size: .74rem; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: .1em;">{{ __('Shipping Volume') }}</span>
                <span style="font-size: .74rem; color: var(--text-dim);">{{ __('Last 14 Days') }}</span>
            </div>
            <div style="font-size: 1.45rem; font-weight: 800; color: var(--text); letter-spacing: -.02em;">
                {{ array_sum($daysTrend) }} <span style="font-size: .84rem; font-weight: 500; color: var(--text-dim);">{{ __('Total Shipments') }}</span>
            </div>
        </div>
        
        <!-- SVG Sparkline -->
        <div style="height: 80px; width: 100%; margin-top: 15px; position: relative;">
            <svg viewBox="0 0 400 80" width="100%" height="80" preserveAspectRatio="none" style="overflow: visible;">
                <defs>
                    <linearGradient id="chartGlow" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="var(--red)" stop-opacity="0.2"/>
                        <stop offset="100%" stop-color="var(--red)" stop-opacity="0.0"/>
                    </linearGradient>
                </defs>
                <line x1="10" y1="10" x2="390" y2="10" stroke="rgba(255,255,255,.02)" stroke-width="1" />
                <line x1="10" y1="40" x2="390" y2="40" stroke="rgba(255,255,255,.02)" stroke-width="1" />
                <line x1="10" y1="70" x2="390" y2="70" stroke="rgba(255,255,255,.02)" stroke-width="1" />
                <polygon points="{{ $polygonPoints }}" fill="url(#chartGlow)" />
                <polyline points="{{ $polylinePoints }}" fill="none" stroke="var(--red-lt)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="filter: drop-shadow(0px 2px 4px rgba(220,38,38,0.2));" />
                @foreach ($daysTrend as $date => $cnt)
                    @php
                        $curX = ($loop->index / ($totalDays - 1)) * 380 + 10;
                        $curY = 80 - (($cnt / $maxVal) * 60) - 10;
                    @endphp
                    @if ($cnt > 0)
                        <circle cx="{{ $curX }}" cy="{{ $curY }}" r="3" fill="#080c1e" stroke="var(--red-lt)" stroke-width="1.5" />
                    @endif
                @endforeach
            </svg>
        </div>
    </div>
</div>

{{-- Shipment Analytics Grid --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 24px; animation: fu .45s .1s both;">
    <div class="card" style="padding: 16px 20px; display: flex; align-items: center; gap: 14px;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(245,158,11,.1); color: #fbbf24; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">📦</div>
        <div>
            <div style="font-size: .72rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; letter-spacing: .06em;">{{ __('Pending Pickup') }}</div>
            <div style="font-size: 1.35rem; font-weight: 800; color: var(--text); margin-top: 2px;">{{ $stats['pending'] ?? 0 }}</div>
        </div>
    </div>
    <div class="card" style="padding: 16px 20px; display: flex; align-items: center; gap: 14px;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(59,130,246,.1); color: #60a5fa; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">🚚</div>
        <div>
            <div style="font-size: .72rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; letter-spacing: .06em;">{{ __('In Transit') }}</div>
            <div style="font-size: 1.35rem; font-weight: 800; color: var(--text); margin-top: 2px;">{{ $stats['picked_up'] ?? 0 }}</div>
        </div>
    </div>
    <div class="card" style="padding: 16px 20px; display: flex; align-items: center; gap: 14px;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(34,197,94,.1); color: #4ade80; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">✓</div>
        <div>
            <div style="font-size: .72rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; letter-spacing: .06em;">{{ __('Delivered Today') }}</div>
            <div style="font-size: 1.35rem; font-weight: 800; color: var(--text); margin-top: 2px;">{{ $stats['delivered_today'] ?? 0 }}</div>
        </div>
    </div>
    <div class="card" style="padding: 16px 20px; display: flex; align-items: center; gap: 14px;">
        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(220,38,38,.1); color: #f87171; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">✕</div>
        <div>
            <div style="font-size: .72rem; color: var(--text-dim); text-transform: uppercase; font-weight: 700; letter-spacing: .06em;">{{ __('Returned/Failed') }}</div>
            <div style="font-size: 1.35rem; font-weight: 800; color: var(--text); margin-top: 2px;">{{ $stats['returned'] ?? 0 }}</div>
        </div>
    </div>
</div>

{{-- Active orders --}}
<div class="page-hd" style="margin-bottom:16px;">
    <div class="page-hd-left">
        <h1>{{ __('Active Orders') }}</h1>
        <p>{{ __('Your pending and in-transit shipments') }}</p>
    </div>
    <div class="page-hd-right">
        <a href="{{ route('client.orders.index') }}" class="btn-secondary" style="text-decoration:none;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            {{ __('All Orders') }}
        </a>
        <a href="{{ route('client.orders.create') }}" class="btn-primary" style="text-decoration:none;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            {{ __('New Order') }}
        </a>
    </div>
</div>

@if($activeOrders->isEmpty())
    <div style="background:var(--card);border:1px solid var(--bdr);border-radius:14px;padding:48px;text-align:center;animation:fu .45s .1s both;">
        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="color:var(--text-dim);margin-bottom:12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <div style="font-size:1rem;font-weight:600;color:var(--text-sub);">{{ __('No active orders') }}</div>
        <div style="font-size:.84rem;color:var(--text-dim);margin-top:6px;">{{ __('Create your first order to get started') }}</div>
        <a href="{{ route('client.orders.create') }}" class="btn-primary" style="margin-top:18px;display:inline-flex;text-decoration:none;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            {{ __('Create Order') }}
        </a>
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;animation:fu .45s .1s both;">
        @foreach($activeOrders as $order)
        @php
            $statusMap = [
                'pending'   => ['label' => __('Pending'),    'class' => 'badge-pending'],
                'picked_up' => ['label' => __('In Transit'), 'class' => 'badge-info'],
            ];
            $st = $statusMap[$order->status] ?? ['label' => ucfirst($order->status), 'class' => 'badge-neutral'];
        @endphp
        <a href="{{ route('client.orders.show', $order) }}" style="text-decoration:none;">
            <div style="background:var(--card);border:1px solid var(--bdr);border-radius:14px;padding:18px 20px;transition:border-color .15s,transform .15s;cursor:pointer;" onmouseenter="this.style.borderColor='rgba(220,38,38,.3)';this.style.transform='translateY(-2px)';" onmouseleave="this.style.borderColor='var(--bdr)';this.style.transform='translateY(0)';">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <span style="font-size:.78rem;font-weight:700;color:var(--text-dim);font-family:monospace;">{{ $order->order_number }}</span>
                    <span class="badge {{ $st['class'] }}"><span class="badge-dot"></span>{{ $st['label'] }}</span>
                </div>
                <div style="font-size:.93rem;font-weight:600;color:var(--text);margin-bottom:4px;">{{ $order->receiver?->receiver_name }}</div>
                <div style="font-size:.8rem;color:var(--text-dim);margin-bottom:10px;">
                    {{ $order->receiver?->city?->name }}{{ $order->receiver?->area ? ', ' . $order->receiver->area->name : '' }}
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;padding-top:10px;border-top:1px solid rgba(255,255,255,.04);">
                    <div>
                        @if(($order->payment?->payment_type ?? 'prepaid') === 'cod')
                            <span style="font-size:.8rem;color:var(--text-dim);">{{ __('COD') }}</span>
                            <span style="font-size:.95rem;font-weight:700;color:#fbbf24;margin-left:5px;">{{ number_format($order->payment?->order_price ?? 0, 2) }} JD</span>
                        @else
                            <span class="badge badge-prepaid">{{ __('Prepaid') }}</span>
                        @endif
                    </div>
                    <span style="font-size:.74rem;color:var(--text-dim);">{{ $order->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
@endif

@endsection
