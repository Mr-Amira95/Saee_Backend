@extends('client.layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <h1 style="font-size:1.35rem;font-weight:800;">Notifications</h1>
        <p style="font-size:.82rem;color:var(--text-sub);">{{ $notifications->total() }} total &middot; {{ $unreadCount }} unread</p>
    </div>
    @if($unreadCount > 0)
    <form method="POST" action="{{ route('client.notifications.read-all') }}">
        @csrf
        <button type="submit" class="btn-secondary" style="font-size:.82rem;padding:7px 14px;">Mark all as read</button>
    </form>
    @endif
</div>

<div class="card" style="padding:0;overflow:hidden;">
    @forelse($notifications as $notif)
    @php $read = (bool)$notif->read_at; @endphp
    <div style="display:flex;align-items:flex-start;gap:14px;padding:16px 18px;border-bottom:1px solid var(--bdr);{{ !$read ? 'background:rgba(220,38,38,.025);' : '' }}transition:background .12s;"
         onmouseover="this.style.background='rgba(255,255,255,.018)'" onmouseout="this.style.background='{{ !$read ? 'rgba(220,38,38,.025)' : '' }}'">
        <div style="width:10px;height:10px;border-radius:50%;background:{{ !$read ? 'var(--red)' : 'rgba(255,255,255,.12)' }};flex-shrink:0;margin-top:6px;"></div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.88rem;font-weight:{{ !$read ? '600' : '500' }};color:var(--text);line-height:1.45;">{{ $notif->title }}</div>
            @if($notif->body)
            <div style="font-size:.82rem;color:var(--text-sub);margin-top:3px;line-height:1.45;">{{ $notif->body }}</div>
            @endif
            <div style="font-size:.74rem;color:var(--text-dim);margin-top:5px;">{{ $notif->created_at->diffForHumans() }}</div>
        </div>
        @if(!$read)
        <form method="POST" action="{{ route('client.notifications.read', $notif->id) }}" style="flex-shrink:0;">
            @csrf
            <button type="submit" style="background:none;border:none;color:var(--text-dim);cursor:pointer;font-size:.75rem;padding:4px 8px;border-radius:6px;transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.06)'" onmouseout="this.style.background='none'">Mark read</button>
        </form>
        @endif
    </div>
    @empty
    <div style="padding:48px;text-align:center;color:var(--text-dim);">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="margin-bottom:12px;display:block;margin-left:auto;margin-right:auto;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <div style="font-size:.88rem;">No notifications yet</div>
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
<div style="margin-top:16px;">{{ $notifications->links() }}</div>
@endif

@endsection
