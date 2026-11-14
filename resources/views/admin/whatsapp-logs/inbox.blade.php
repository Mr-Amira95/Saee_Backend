@extends('admin.layouts.app')

@section('title', $active ? __('WhatsApp') . ' — ' . ($active->name ?? $active->displayPhone) : __('WhatsApp Messages'))
@section('page-title', __('WhatsApp Messages'))

@section('breadcrumb')
    <span class="sep">/</span>
    @if($active)
        <a href="{{ route('admin.whatsapp-logs.index') }}">{{ __('WhatsApp Messages') }}</a>
        <span class="sep">/</span>
        <span class="current">{{ $active->name ?? $active->displayPhone }}</span>
    @else
        <span class="current">{{ __('WhatsApp Messages') }}</span>
    @endif
@endsection

@section('head')
<style>
    .wa {
        --wa-green: #22c55e;
        --wa-out: rgba(34,197,94,.16); --wa-out-bdr: rgba(34,197,94,.24);
        --wa-in: rgba(255,255,255,.06); --wa-in-bdr: rgba(255,255,255,.07);
        --wa-hover: rgba(255,255,255,.04); --wa-active: rgba(255,255,255,.07);
        --wa-dots: rgba(255,255,255,.035);
        display: grid; grid-template-columns: 360px 1fr;
        height: calc(100vh - 58px - 48px); height: calc(100dvh - 58px - 48px); min-height: 460px;
        background: var(--card); border: 1px solid var(--bdr); border-radius: 14px;
        overflow: hidden; backdrop-filter: blur(8px);
    }
    html.light-theme .wa {
        --wa-out: #dcfce7; --wa-out-bdr: #bbf7d0;
        --wa-in: #ffffff; --wa-in-bdr: rgba(15,23,42,.08);
        --wa-hover: rgba(15,23,42,.03); --wa-active: rgba(15,23,42,.06);
        --wa-dots: rgba(15,23,42,.05);
    }

    /* ── Chat list ── */
    .wa-list { display: flex; flex-direction: column; min-height: 0; border-inline-end: 1px solid var(--bdr); }
    .wa-list-hd { padding: 16px 16px 10px; display: flex; flex-direction: column; gap: 10px; }
    .wa-list-title { font-size: 1.1rem; font-weight: 800; letter-spacing: -.02em; display: flex; align-items: center; justify-content: space-between; }
    .wa-list-title span { font-size: .72rem; font-weight: 600; color: var(--text-dim); }
    .wa-search { position: relative; }
    .wa-search svg { position: absolute; inset-inline-start: 11px; top: 50%; transform: translateY(-50%); color: var(--text-dim); pointer-events: none; }
    .wa-search input {
        width: 100%; padding: 8px 12px; padding-inline-start: 34px;
        background: var(--in-bg); border: 1px solid var(--in-bdr); border-radius: 9px;
        color: var(--text); font-size: .84rem; outline: none;
    }
    .wa-search input:focus { border-color: var(--wa-out-bdr); }
    .wa-chips { display: flex; gap: 6px; }
    .wa-chip {
        padding: 4px 12px; border-radius: 999px; font-size: .76rem; font-weight: 600; cursor: pointer;
        background: var(--in-bg); border: 1px solid var(--in-bdr); color: var(--text-sub);
    }
    .wa-chip.active { background: var(--wa-out); border-color: var(--wa-out-bdr); color: var(--text); }

    .wa-items { flex: 1; overflow-y: auto; min-height: 0; }
    .wa-item {
        display: flex; align-items: center; gap: 12px; padding: 10px 16px;
        text-decoration: none; color: inherit; border-top: 1px solid var(--bdr);
    }
    .wa-item:hover { background: var(--wa-hover); }
    .wa-item.active { background: var(--wa-active); }
    .wa-item.is-hidden { display: none; }
    .wa-item-body { flex: 1; min-width: 0; }
    .wa-item-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .wa-item-name { font-size: .88rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wa-item-time { font-size: .7rem; color: var(--text-dim); flex-shrink: 0; }
    .wa-item.unread .wa-item-time { color: var(--wa-green); font-weight: 600; }
    .wa-item-preview {
        font-size: .79rem; color: var(--text-sub); margin-top: 3px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        display: flex; align-items: center; gap: 4px;
    }
    .wa-item-preview > span { overflow: hidden; text-overflow: ellipsis; }
    .wa-unread {
        min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; flex-shrink: 0;
        background: var(--wa-green); color: #fff; font-size: .68rem; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .wa-list-empty { padding: 40px 20px; text-align: center; color: var(--text-dim); font-size: .82rem; }

    .wa-avatar {
        width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: .95rem; font-weight: 700; text-transform: uppercase;
    }
    .wa-avatar.sm { width: 38px; height: 38px; font-size: .85rem; }
    .wa-c0 { background: linear-gradient(135deg,#16a34a,#4ade80); }
    .wa-c1 { background: linear-gradient(135deg,#4f46e5,#818cf8); }
    .wa-c2 { background: linear-gradient(135deg,#b45309,#fbbf24); }
    .wa-c3 { background: linear-gradient(135deg,#0e7490,#22d3ee); }
    .wa-c4 { background: linear-gradient(135deg,#7f1d1d,#dc2626); }
    .wa-c5 { background: linear-gradient(135deg,#86198f,#e879f9); }

    .wa-tick { flex-shrink: 0; color: var(--text-dim); }
    .wa-tick.failed { color: var(--red-lt); }

    /* ── Chat pane ── */
    .wa-chat { display: flex; flex-direction: column; min-height: 0; min-width: 0; }
    .wa-chat-hd {
        display: flex; align-items: center; gap: 12px; padding: 10px 16px;
        border-bottom: 1px solid var(--bdr); min-height: 60px;
    }
    .wa-chat-hd-info { flex: 1; min-width: 0; }
    .wa-chat-name { font-size: .92rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wa-chat-sub { font-size: .74rem; color: var(--text-sub); display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 2px; }
    .wa-role { padding: 1px 7px; border-radius: 5px; background: var(--in-bg); border: 1px solid var(--in-bdr); font-size: .66rem; font-weight: 600; color: var(--text-sub); }
    .wa-orders { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; max-width: 45%; }
    .wa-order {
        font-size: .72rem; padding: 3px 8px; border-radius: 7px; text-decoration: none;
        background: var(--in-bg); border: 1px solid var(--in-bdr); color: var(--text-sub); white-space: nowrap;
    }
    .wa-order:hover { color: var(--text); }
    .wa-back { display: none; }

    .wa-body {
        flex: 1; overflow-y: auto; min-height: 0; padding: 18px 6%;
        display: flex; flex-direction: column; gap: 4px;
        background-image: radial-gradient(var(--wa-dots) 1px, transparent 1px);
        background-size: 18px 18px;
    }
    .wa-day {
        align-self: center; margin: 10px 0 6px; padding: 4px 12px; border-radius: 8px;
        background: var(--wa-in); border: 1px solid var(--wa-in-bdr);
        font-size: .7rem; color: var(--text-sub);
    }
    .wa-msg { display: flex; }
    .wa-msg.outbound { justify-content: flex-end; }
    .wa-bubble {
        max-width: min(65%, 560px); padding: 7px 10px 5px; border-radius: 10px;
        font-size: .86rem; line-height: 1.5; word-break: break-word;
        border: 1px solid var(--wa-in-bdr); background: var(--wa-in);
    }
    .wa-msg.inbound  .wa-bubble { border-start-start-radius: 3px; }
    .wa-msg.outbound .wa-bubble { border-start-end-radius: 3px; background: var(--wa-out); border-color: var(--wa-out-bdr); }
    .wa-msg.failed .wa-bubble { border-color: rgba(239,68,68,.45); }
    .wa-bubble a { color: inherit; }
    .wa-label { font-size: .66rem; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 2px; }
    .wa-bubble-meta {
        display: flex; align-items: center; justify-content: flex-end; gap: 4px;
        font-size: .66rem; color: var(--text-dim); margin-top: 2px;
    }
    .wa-fail-note { font-size: .7rem; color: var(--red-lt); margin-top: 3px; }

    .wa-compose { border-top: 1px solid var(--bdr); padding: 10px 14px; }
    .wa-compose-row, .wa-recording { display: flex; align-items: flex-end; gap: 8px; }
    .wa-compose-row[hidden], .wa-recording[hidden], .wa-attach[hidden], .wa-send[hidden], #waAttachImg[hidden], .wa-media-doc-icon[hidden] { display: none; }
    .wa-recording { align-items: center; }
    .wa-icon-btn {
        width: 42px; height: 42px; border-radius: 50%; border: none; flex-shrink: 0; cursor: pointer;
        background: transparent; color: var(--text-sub);
        display: flex; align-items: center; justify-content: center;
    }
    .wa-icon-btn:hover { background: var(--wa-hover); color: var(--text); }
    .wa-rec-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--red-lt); animation: dot-p 1.2s infinite; flex-shrink: 0; }
    .wa-rec-time { font-size: .9rem; font-weight: 600; font-variant-numeric: tabular-nums; }
    .wa-rec-label { flex: 1; font-size: .8rem; color: var(--text-sub); }
    .wa-attach {
        display: flex; align-items: center; gap: 10px; margin-bottom: 8px; padding: 8px 10px;
        border-radius: 10px; background: var(--in-bg); border: 1px solid var(--in-bdr);
    }
    #waAttachImg { width: 52px; height: 52px; object-fit: cover; border-radius: 8px; flex-shrink: 0; }

    .wa-media-img-link { display: block; margin: -3px -6px 4px; }
    .wa-media-img { display: block; max-width: 100%; width: 300px; max-height: 340px; object-fit: cover; border-radius: 8px; background: var(--wa-hover); }
    .wa-media-img.sticker { width: 130px; height: 130px; object-fit: contain; background: none; }
    .wa-media-video { display: block; width: 300px; max-width: 100%; max-height: 340px; border-radius: 8px; margin: -3px -6px 4px; background: #000; }
    .wa-media-audio { display: flex; align-items: center; gap: 6px; color: var(--wa-green); }
    .wa-media-audio audio { height: 36px; width: 260px; max-width: 100%; }
    .wa-media-doc {
        display: flex; align-items: center; gap: 10px; min-width: 220px; padding: 8px 10px; margin-bottom: 2px;
        border-radius: 8px; background: var(--wa-hover); text-decoration: none; color: inherit;
    }
    .wa-bubble .wa-media-doc { color: inherit; }
    .wa-media-doc-icon {
        width: 38px; height: 38px; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(99,102,241,.16); color: #818cf8;
    }
    .wa-media-doc-info { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .wa-media-doc-name { font-size: .82rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .wa-media-doc-sub { font-size: .68rem; color: var(--text-dim); }
    .wa-media-missing { font-size: .78rem; font-style: italic; color: var(--text-dim); }
    .wa-caption { margin-top: 2px; }
    .wa-tick.delivered { color: var(--text-dim); }
    .wa-tick.read { color: #38bdf8; }
    .wa-compose textarea {
        flex: 1; resize: none; max-height: 140px; min-height: 42px;
        padding: 10px 14px; border-radius: 10px; outline: none;
        background: var(--in-bg); border: 1px solid var(--in-bdr);
        color: var(--text); font: inherit; font-size: .88rem; line-height: 1.45;
    }
    .wa-compose textarea:focus { border-color: var(--wa-out-bdr); }
    .wa-send {
        width: 42px; height: 42px; border-radius: 50%; border: none; flex-shrink: 0; cursor: pointer;
        background: var(--wa-green); color: #fff;
        display: flex; align-items: center; justify-content: center;
    }
    .wa-send:disabled { opacity: .55; cursor: default; }
    html[dir="rtl"] .wa-send svg, html[dir="rtl"] .wa-back svg { transform: scaleX(-1); }
    .wa-compose-error { font-size: .76rem; color: var(--red-lt); margin-bottom: 8px; }
    .wa-closed {
        border-top: 1px solid var(--bdr); padding: 14px 18px;
        display: flex; align-items: center; gap: 10px;
        font-size: .8rem; color: var(--text-sub); background: var(--in-bg);
    }
    .wa-closed svg { flex-shrink: 0; color: var(--text-dim); }
    .wa-closed-btn { padding: 8px 14px; font-size: .8rem; white-space: nowrap; flex-shrink: 0; }

    .wa-tpl-card { max-width: 480px; text-align: start; padding: 26px 26px 22px; max-height: 90vh; overflow-y: auto; }
    .wa-tpl-card h3 { margin-bottom: 6px; }
    .wa-tpl-card p.wa-tpl-hint { font-size: .8rem; margin-bottom: 16px; }
    .wa-tpl-card .form-label { display: block; margin: 12px 0 6px; }
    .wa-tpl-card .form-input, .wa-tpl-card .form-select { width: 100%; }
    .wa-tpl-card .form-hint { margin-top: 4px; }
    .wa-tpl-card .modal-actions { margin-top: 20px; }
    .wa-tpl-card .modal-actions .btn-primary { flex: 1; justify-content: center; }
    .wa-tpl-fields[hidden] { display: none; }
    .wa-tpl-preview {
        padding: 10px 12px; border-radius: 10px; border-start-end-radius: 3px;
        background: var(--wa-out, rgba(34,197,94,.16)); border: 1px solid rgba(34,197,94,.24);
        font-size: .84rem; line-height: 1.55; white-space: pre-wrap; word-break: break-word;
    }
    html.light-theme .wa-tpl-preview { background: #dcfce7; border-color: #bbf7d0; }
    .wa-tpl-preview mark { background: rgba(251,191,36,.25); color: inherit; border-radius: 3px; padding: 0 2px; }

    .wa-empty {
        flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 10px; padding: 30px; text-align: center; color: var(--text-sub);
    }
    .wa-empty-icon {
        width: 76px; height: 76px; border-radius: 50%; margin-bottom: 6px;
        background: var(--wa-out); border: 1px solid var(--wa-out-bdr); color: var(--wa-green);
        display: flex; align-items: center; justify-content: center;
    }
    .wa-empty h3 { font-size: 1.1rem; font-weight: 700; color: var(--text); }
    .wa-empty p { font-size: .82rem; max-width: 360px; line-height: 1.6; }

    @media(max-width:900px) {
        .wa { grid-template-columns: 1fr; }
        .wa.has-chat .wa-list, .wa:not(.has-chat) .wa-chat { display: none; }
        .wa-list { border-inline-end: none; }
        .wa-back { display: flex; }
        .wa-orders { display: none; }
        .wa-bubble { max-width: 85%; }
        .wa-body { padding: 14px 12px; }
    }
    @media(max-width:600px) {
        .wa { height: calc(100vh - 58px - 32px); height: calc(100dvh - 58px - 32px); border-radius: 10px; }
    }
</style>
@endsection

@section('content')
@php
    $filter = request('filter') === 'awaiting' ? 'awaiting' : 'all';

    $listTime = function ($date) {
        if ($date->isToday()) return $date->format('H:i');
        if ($date->isYesterday()) return __('Yesterday');
        if ($date->gt(now()->subDays(6)->startOfDay())) return $date->translatedFormat('l');
        return $date->format('d/m/Y');
    };
    $avatarColor = fn ($phone) => 'wa-c' . (crc32($phone) % 6);
    $initial = fn ($c) => $c->name ? mb_substr($c->name, 0, 1) : null;

    // One-line chat list preview of a message.
    $preview = function ($log) {
        $caption = $log->caption();

        return match ($log->message_type) {
            'location' => '📍 ' . __('Location'),
            'image'    => '📷 ' . ($caption ?: __('Photo')),
            'video'    => '🎥 ' . ($caption ?: __('Video')),
            'audio'    => !empty($log->mediaInfo()['voice']) ? '🎤 ' . __('Voice message') : '🎵 ' . __('Audio'),
            'document' => '📄 ' . ($caption ?: ($log->mediaInfo()['filename'] ?? __('Document'))),
            'sticker'  => '🏷️ ' . __('Sticker'),
            default    => $log->displayBody(),
        };
    };
    $maxUpload = min(
        \Symfony\Component\HttpFoundation\File\UploadedFile::getMaxFilesize(),
        \App\Services\WhatsAppMediaService::LIMITS['document']['max_mb'] * 1024 * 1024
    );

    $inboxTemplates = collect(config('whatsapp.inbox_templates', []))
        ->map(fn ($t, $key) => [
            'label'  => __($t['label'] ?? $key),
            'body'   => config("whatsapp.templates.{$key}", ''),
            'fields' => collect($t['fields'] ?? [])->map(fn ($f) => ['label' => __($f['label']), 'prefill' => $f['prefill'] ?? null])->all(),
        ])
        ->all();
    // Reopen the template modal with what was typed after a failed send or validation error.
    $templateReopen = session('wa_template_error') || $errors->has('phone') || $errors->has('template') || $errors->has('params') || $errors->has('params.*');
    $templateOld = $templateReopen ? old() : null;
@endphp

<div class="wa {{ $active ? 'has-chat' : '' }}">

    {{-- Chat list --}}
    <aside class="wa-list">
        <div class="wa-list-hd">
            <div class="wa-list-title">
                <div>{{ __('Chats') }} <span>{{ $conversations->count() }}</span></div>
                @if($inboxTemplates)
                    <button type="button" class="icon-btn" title="{{ __('New chat') }}" data-wa-template-open>
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                @endif
            </div>
            <div class="wa-search">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" id="waSearch" value="{{ request('q') }}" placeholder="{{ __('Search name, phone or order…') }}" autocomplete="off" data-live-allow-typing>
            </div>
            <div class="wa-chips">
                <button type="button" class="wa-chip {{ $filter === 'all' ? 'active' : '' }}" data-wa-filter="all">{{ __('All') }}</button>
                <button type="button" class="wa-chip {{ $filter === 'awaiting' ? 'active' : '' }}" data-wa-filter="awaiting">{{ __('Awaiting Reply') }}</button>
            </div>
        </div>

        <div class="wa-items" id="waItems">
            @foreach($conversations as $c)
                @php
                    $last = $c->lastLog;
                    $search = mb_strtolower(implode(' ', array_filter([$c->name, ...$c->orders->pluck('order_number')->all()])));
                @endphp
                <a href="{{ route('admin.whatsapp-logs.show', $c->phone) }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}"
                   class="wa-item {{ $active?->phone === $c->phone ? 'active' : '' }} {{ $c->awaiting ? 'unread' : '' }}"
                   data-search="{{ $search }}" data-phone="{{ $c->phone }}" data-awaiting="{{ $c->awaiting ? 1 : 0 }}">
                    <div class="wa-avatar {{ $avatarColor($c->phone) }}">
                        @if($initial($c))
                            {{ $initial($c) }}
                        @else
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        @endif
                    </div>
                    <div class="wa-item-body">
                        <div class="wa-item-row">
                            <span class="wa-item-name" dir="auto">{{ $c->name ?? $c->displayPhone }}</span>
                            <span class="wa-item-time">{{ $listTime($c->lastActivity) }}</span>
                        </div>
                        <div class="wa-item-row">
                            <div class="wa-item-preview">
                                @if($last->direction === 'outbound')
                                    @include('admin.whatsapp-logs.partials.tick', ['log' => $last])
                                @endif
                                <span dir="auto">{{ Str::limit(preg_replace('/\s+/', ' ', $preview($last)), 80) }}</span>
                            </div>
                            @if($c->awaiting)
                                <span class="wa-unread">{{ $c->awaiting }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach

            <div class="wa-list-empty" id="waNoResults" @if($conversations->isNotEmpty()) hidden @endif>
                {{ $conversations->isEmpty() ? __('No WhatsApp activity yet') : __('No chats match your search.') }}
            </div>
        </div>
    </aside>

    {{-- Chat pane --}}
    <section class="wa-chat">
        @if(! $active)
            <div class="wa-empty">
                <div class="wa-empty-icon">
                    <svg width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>
                </div>
                <h3>{{ __('WhatsApp Messages') }}</h3>
                <p>{{ __('Select a chat to read messages and reply. Replies are sent from the business WhatsApp number.') }}</p>
            </div>
        @else
            <header class="wa-chat-hd">
                <a href="{{ route('admin.whatsapp-logs.index') }}" class="icon-btn wa-back" title="{{ __('Back') }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div class="wa-avatar sm {{ $avatarColor($active->phone) }}">
                    @if($initial($active))
                        {{ $initial($active) }}
                    @else
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    @endif
                </div>
                <div class="wa-chat-hd-info">
                    <div class="wa-chat-name" dir="auto">{{ $active->name ?? $active->displayPhone }}</div>
                    <div class="wa-chat-sub">
                        <span dir="ltr">{{ $active->displayPhone }}</span>
                        @if($active->role)
                            <span class="wa-role">{{ $active->role }}</span>
                        @endif
                    </div>
                </div>
                @if($active->orders->isNotEmpty())
                    <div class="wa-orders">
                        @foreach($active->orders->take(3) as $o)
                            <a href="{{ route('admin.orders.show', $o) }}" class="wa-order" title="{{ ucfirst(str_replace('_', ' ', $o->status)) }}">#{{ $o->order_number }}</a>
                        @endforeach
                        @if($active->orders->count() > 3)
                            <span class="wa-order">+{{ $active->orders->count() - 3 }}</span>
                        @endif
                    </div>
                @endif
            </header>

            <div class="wa-body" id="waBody">
                @php $lastDay = null; @endphp
                @foreach($thread as $log)
                    @php $day = $log->created_at->format('Y-m-d'); @endphp
                    @if($day !== $lastDay)
                        <div class="wa-day">{{ $log->created_at->isToday() ? __('Today') : ($log->created_at->isYesterday() ? __('Yesterday') : $log->created_at->format('d M Y')) }}</div>
                        @php $lastDay = $day; @endphp
                    @endif
                    <div class="wa-msg {{ $log->direction }} {{ $log->status === 'failed' ? 'failed' : '' }}" id="wa-msg-{{ $log->id }}">
                        <div class="wa-bubble">
                            @if($log->direction === 'outbound' && isset($senders[$log->meta['sent_by'] ?? null]))
                                <div class="wa-label">{{ $senders[$log->meta['sent_by']] }}{{ $log->message_type === 'template' ? ' · ' . __('Template') : '' }}</div>
                            @elseif($log->message_type === 'template')
                                <div class="wa-label">{{ __('Automatic message') }}</div>
                            @endif

                            @if($log->message_type === 'location' && !empty($log->meta['latitude']) && !empty($log->meta['longitude']))
                                📍 <a href="https://maps.google.com/?q={{ $log->meta['latitude'] }},{{ $log->meta['longitude'] }}" target="_blank" rel="noopener">{{ $log->message }}</a>
                            @elseif(in_array($log->message_type, \App\Models\WhatsAppLog::MEDIA_TYPES, true))
                                @include('admin.whatsapp-logs.partials.media', ['log' => $log])
                                @if($log->caption() !== '')
                                    <div class="wa-caption" dir="auto">{!! nl2br(e($log->caption())) !!}</div>
                                @endif
                            @else
                                <div dir="auto">{!! nl2br(e($log->displayBody())) !!}</div>
                            @endif

                            <div class="wa-bubble-meta">
                                @if($log->order && $active->orders->count() > 1)
                                    <span>#{{ $log->order->order_number }} ·</span>
                                @endif
                                {{ $log->created_at->format('H:i') }}
                                @if($log->direction === 'outbound')
                                    @include('admin.whatsapp-logs.partials.tick', ['log' => $log])
                                @endif
                            </div>
                            @if($log->status === 'failed')
                                <div class="wa-fail-note">{{ __('Not sent') }}@if(!empty($log->meta['error'])): {{ $log->meta['error'] }}@endif</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($canReply)
                <div class="wa-compose">
                    @if(session('wa_error'))
                        <div class="wa-compose-error">{{ session('wa_error') }}</div>
                    @endif
                    <form method="POST" action="{{ route('admin.whatsapp-logs.send', $active->phone) }}" id="waForm"
                          enctype="multipart/form-data" data-max-upload="{{ $maxUpload }}">
                        @csrf
                        <input type="file" name="file" id="waFile" hidden>
                        <input type="hidden" name="voice" id="waVoice" value="0">

                        <div class="wa-attach" id="waAttach" hidden>
                            <img id="waAttachImg" alt="" hidden>
                            <span class="wa-media-doc-icon" id="waAttachIcon">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            </span>
                            <span class="wa-media-doc-info">
                                <span class="wa-media-doc-name" id="waAttachName" dir="auto"></span>
                                <span class="wa-media-doc-sub" id="waAttachSize"></span>
                            </span>
                            <button type="button" class="wa-icon-btn" id="waAttachRemove" title="{{ __('Remove') }}">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="wa-compose-row" id="waComposeRow">
                            <button type="button" class="wa-icon-btn" id="waAttachBtn" title="{{ __('Attach file') }}">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            </button>
                            <textarea name="message" id="waInput" rows="1" maxlength="4096" dir="auto"
                                      placeholder="{{ __('Type a message') }}" data-caption-placeholder="{{ __('Add a caption…') }}"
                                      data-live-allow-typing>{{ old('message') }}</textarea>
                            <button type="submit" class="wa-send" id="waSend" title="{{ __('Send') }}">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </button>
                            <button type="button" class="wa-send" id="waMic" title="{{ __('Record voice message') }}" hidden>
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/></svg>
                            </button>
                        </div>

                        <div class="wa-recording" id="waRecording" hidden>
                            <button type="button" class="wa-icon-btn" id="waRecCancel" title="{{ __('Cancel') }}">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            <span class="wa-rec-dot"></span>
                            <span class="wa-rec-time" id="waRecTime" dir="ltr">0:00</span>
                            <span class="wa-rec-label">{{ __('Recording…') }}</span>
                            <button type="button" class="wa-send" id="waRecSend" title="{{ __('Send') }}">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="wa-closed">
                    @if(session('wa_error'))
                        <span style="color:var(--red-lt)">{{ session('wa_error') }}</span>
                    @endif
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span style="flex:1">{{ __('Replies are only possible within 24 hours of the contact\'s last message. The contact needs to message you first.') }}</span>
                    @if($inboxTemplates)
                        <button type="button" class="btn-primary wa-closed-btn" data-wa-template-open
                                data-phone="{{ $active->displayPhone }}" data-name="{{ $active->name }}">
                            {{ __('Send template') }}
                        </button>
                    @endif
                </div>
            @endif
        @endif
    </section>

</div>

@if($inboxTemplates)
<div class="modal-overlay" id="waTemplateModal">
    <div class="modal-card wa-tpl-card">
        <h3>{{ __('Send a template message') }}</h3>
        <p class="wa-tpl-hint">{{ __('Templates are approved by WhatsApp and can be sent at any time. Once the contact replies, you can chat freely for 24 hours.') }}</p>

        @if(session('wa_template_error'))
            <div class="wa-compose-error">{{ session('wa_template_error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.whatsapp-logs.template') }}" id="waTemplateForm">
            @csrf
            <label class="form-label" for="waTplPhone">{{ __('Phone number') }}</label>
            <input type="text" class="form-input" id="waTplPhone" name="phone" dir="ltr" required
                   placeholder="+9627XXXXXXXX" value="{{ $templateOld['phone'] ?? '' }}">
            <div class="form-hint">{{ __('Include the country code, e.g. +9627XXXXXXXX.') }}</div>

            <label class="form-label" for="waTplSelect">{{ __('Template') }}</label>
            <select class="form-select" id="waTplSelect" name="template">
                @foreach($inboxTemplates as $key => $t)
                    <option value="{{ $key }}" @selected(($templateOld['template'] ?? null) === $key)>{{ $t['label'] }}</option>
                @endforeach
            </select>

            @foreach($inboxTemplates as $key => $t)
                <div class="wa-tpl-fields" data-template="{{ $key }}">
                    @foreach($t['fields'] as $i => $field)
                        <label class="form-label">{{ $field['label'] }}</label>
                        <input type="text" class="form-input" name="params[{{ $i }}]" maxlength="500" required dir="auto"
                               data-prefill="{{ $field['prefill'] }}"
                               value="{{ ($templateOld['template'] ?? null) === $key ? ($templateOld['params'][$i] ?? '') : '' }}">
                    @endforeach
                </div>
            @endforeach

            <label class="form-label">{{ __('Preview') }}</label>
            <div class="wa-tpl-preview" id="waTplPreview" dir="auto"></div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" data-wa-template-close>{{ __('Cancel') }}</button>
                <button type="submit" class="btn-primary" id="waTplSend">{{ __('Send') }}</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
(function () {
    // ── Chat list: instant search + filter chips (kept in the URL) ──
    var search = document.getElementById('waSearch');
    var items  = document.getElementById('waItems');
    var filter = @json($filter);

    function applyFilter() {
        var q = search.value.trim().toLowerCase();
        var digits = q.replace(/\D/g, '').replace(/^0+/, '');
        var nodes = items.querySelectorAll('.wa-item');
        var shown = 0;

        nodes.forEach(function (item) {
            var match = !q || item.dataset.search.indexOf(q) !== -1 || (digits && item.dataset.phone.indexOf(digits) !== -1);
            var ok = match && (filter === 'all' || item.dataset.awaiting === '1');
            item.classList.toggle('is-hidden', !ok);
            if (ok) shown++;
        });

        document.getElementById('waNoResults').hidden = !nodes.length ? false : shown > 0;
        document.querySelectorAll('[data-wa-filter]').forEach(function (chip) {
            chip.classList.toggle('active', chip.dataset.waFilter === filter);
        });

        var params = new URLSearchParams();
        if (q) params.set('q', search.value.trim());
        if (filter !== 'all') params.set('filter', filter);
        var qs = params.toString();
        history.replaceState(null, '', location.pathname + (qs ? '?' + qs : ''));
    }

    search.addEventListener('input', applyFilter);
    document.querySelectorAll('[data-wa-filter]').forEach(function (chip) {
        chip.addEventListener('click', function () { filter = chip.dataset.waFilter; applyFilter(); });
    });
    // Carry the current search/filter into the chat being opened.
    items.addEventListener('click', function (e) {
        var item = e.target.closest('.wa-item');
        if (item) item.href = item.href.split('?')[0] + location.search;
    });
    applyFilter();

    var active = items.querySelector('.wa-item.active');
    if (active) active.scrollIntoView({ block: 'nearest' });

    // ── Thread: start at the newest message, follow new ones while at the bottom ──
    var body = document.getElementById('waBody');
    var stick = true;
    function toBottom() { if (body) body.scrollTop = body.scrollHeight; }
    if (body) {
        toBottom();
        body.addEventListener('scroll', function () {
            stick = body.scrollHeight - body.scrollTop - body.clientHeight < 80;
        });
    }
    document.addEventListener('realtime:updated', function () {
        if (stick) toBottom();
        applyFilter();
    });

    // ── Template modal: start a new chat, or reopen one after the 24-hour window ──
    var modal = document.getElementById('waTemplateModal');
    if (modal) {
        var templates = @json($inboxTemplates);
        var tplForm   = document.getElementById('waTemplateForm');
        var tplPhone  = document.getElementById('waTplPhone');
        var tplSelect = document.getElementById('waTplSelect');
        var preview   = document.getElementById('waTplPreview');

        var esc = function (s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; };
        var activeGroup = function () { return modal.querySelector('.wa-tpl-fields[data-template="' + tplSelect.value + '"]'); };

        var renderPreview = function () {
            var t = templates[tplSelect.value];
            var inputs = activeGroup().querySelectorAll('input');
            preview.innerHTML = esc(t.body).replace(/\{\{(\d+)\}\}/g, function (m, n) {
                var input = inputs[n - 1], field = t.fields[n - 1];
                var value = input ? input.value.trim() : '';
                return value ? esc(value) : '<mark>' + esc(field ? field.label : m) + '</mark>';
            });
        };

        // Only the selected template's fields are enabled, so only they are submitted.
        var syncTemplate = function () {
            modal.querySelectorAll('.wa-tpl-fields').forEach(function (group) {
                var on = group.dataset.template === tplSelect.value;
                group.hidden = !on;
                group.querySelectorAll('input').forEach(function (input) { input.disabled = !on; });
            });
            renderPreview();
        };

        // phone undefined = reopen as-is (after a failed send); '' = new chat; a number = this chat.
        var openModal = function (phone, name) {
            if (phone !== undefined) {
                tplPhone.value = phone;
                tplPhone.readOnly = phone !== '';
                modal.querySelectorAll('input[data-prefill="name"]').forEach(function (input) { input.value = name || ''; });
            }
            syncTemplate();
            modal.classList.add('open');
            var first = tplPhone.readOnly ? activeGroup().querySelector('input') : tplPhone;
            if (first) first.focus();
        };
        var closeModal = function () { modal.classList.remove('open'); };

        document.querySelectorAll('[data-wa-template-open]').forEach(function (btn) {
            btn.addEventListener('click', function () { openModal(btn.dataset.phone || '', btn.dataset.name); });
        });
        modal.querySelectorAll('[data-wa-template-close]').forEach(function (btn) { btn.addEventListener('click', closeModal); });
        modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });
        tplSelect.addEventListener('change', syncTemplate);
        modal.addEventListener('input', renderPreview);
        tplForm.addEventListener('submit', function () { document.getElementById('waTplSend').disabled = true; });

        @if($templateReopen)
            openModal();
        @endif
    }

    // ── Composer: Enter sends, Shift+Enter adds a line, box grows with the text ──
    var form  = document.getElementById('waForm');
    var input = document.getElementById('waInput');
    if (!form) return;

    var fileInput  = document.getElementById('waFile');
    var voiceInput = document.getElementById('waVoice');
    var sendBtn    = document.getElementById('waSend');
    var micBtn     = document.getElementById('waMic');
    var attachBox  = document.getElementById('waAttach');
    var attachImg  = document.getElementById('waAttachImg');
    var maxUpload  = parseInt(form.dataset.maxUpload, 10);
    var textPlaceholder = input.placeholder;
    var canRecord  = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia && window.MediaRecorder);
    var msg = {
        tooLarge: @json(__('This file is too large to upload. The limit is :max MB.', ['max' => floor($maxUpload / 1048576)])),
        noMic:    @json(__('Microphone access is needed to record a voice message.')),
    };

    function hasContent() { return input.value.trim() !== '' || fileInput.files.length > 0; }

    // WhatsApp-style: mic when the box is empty, send button otherwise.
    function syncButtons() {
        var showMic = canRecord && !hasContent();
        micBtn.hidden = !showMic;
        sendBtn.hidden = showMic;
    }

    function grow() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 140) + 'px';
    }

    function formatSize(bytes) {
        return bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
    }

    function setFile(file) {
        if (file && file.size > maxUpload) {
            alert(msg.tooLarge);
            file = null;
        }

        var dt = new DataTransfer();
        if (file) dt.items.add(file);
        fileInput.files = dt.files;

        attachBox.hidden = !file;
        if (attachImg.src) URL.revokeObjectURL(attachImg.src);
        var isImage = !!file && /^image\//.test(file.type);
        attachImg.hidden = !isImage;
        attachImg.src = isImage ? URL.createObjectURL(file) : '';
        document.getElementById('waAttachIcon').hidden = isImage;
        document.getElementById('waAttachName').textContent = file ? file.name : '';
        document.getElementById('waAttachSize').textContent = file ? formatSize(file.size) : '';
        input.placeholder = file && !/^audio\//.test(file.type) ? input.dataset.captionPlaceholder : textPlaceholder;
        syncButtons();
    }

    input.addEventListener('input', function () { grow(); syncButtons(); });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
            e.preventDefault();
            if (hasContent()) form.requestSubmit();
        }
    });
    // Paste a screenshot / image straight into the chat.
    input.addEventListener('paste', function (e) {
        var files = e.clipboardData && e.clipboardData.files;
        if (files && files.length) { e.preventDefault(); setFile(files[0]); }
    });

    document.getElementById('waAttachBtn').addEventListener('click', function () { fileInput.click(); });
    fileInput.addEventListener('change', function () { setFile(fileInput.files[0] || null); });
    document.getElementById('waAttachRemove').addEventListener('click', function () { setFile(null); input.focus(); });

    form.addEventListener('submit', function (e) {
        if (!hasContent() || form.dataset.sending) { e.preventDefault(); return; }
        form.dataset.sending = '1';
        sendBtn.disabled = true;
        document.getElementById('waRecSend').disabled = true;
    });

    // ── Voice messages: record in the browser, send as an audio file ──
    // Formats WhatsApp accepts come first; the server converts others when ffmpeg is installed.
    var RECORD_TYPES = ['audio/ogg;codecs=opus', 'audio/mp4;codecs=mp4a.40.2', 'audio/webm;codecs=opus', 'audio/mp4', 'audio/webm'];
    var recRow = document.getElementById('waRecording');
    var composeRow = document.getElementById('waComposeRow');
    var recorder = null, recTimer = null, recCancelled = false;

    function showRecording(on) {
        recRow.hidden = !on;
        composeRow.hidden = on;
        attachBox.hidden = on || !fileInput.files.length;
    }

    micBtn.addEventListener('click', function () {
        navigator.mediaDevices.getUserMedia({ audio: true }).then(function (stream) {
            var type = RECORD_TYPES.filter(function (t) { return MediaRecorder.isTypeSupported(t); })[0];
            var chunks = [];
            var started = Date.now();

            recorder = new MediaRecorder(stream, type ? { mimeType: type } : undefined);
            recCancelled = false;
            recorder.ondataavailable = function (e) { if (e.data.size) chunks.push(e.data); };
            recorder.onstop = function () {
                stream.getTracks().forEach(function (t) { t.stop(); });
                clearInterval(recTimer);
                showRecording(false);
                if (recCancelled || !chunks.length) return;

                var mime = (recorder.mimeType || type || 'audio/webm').split(';')[0];
                var ext = mime.indexOf('ogg') !== -1 ? 'ogg' : (mime.indexOf('mp4') !== -1 ? 'm4a' : 'webm');
                setFile(new File(chunks, 'voice-message.' + ext, { type: mime }));
                voiceInput.value = '1';
                form.requestSubmit();
            };

            recorder.start();
            document.getElementById('waRecTime').textContent = '0:00';
            recTimer = setInterval(function () {
                var s = Math.floor((Date.now() - started) / 1000);
                document.getElementById('waRecTime').textContent = Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2);
            }, 250);
            showRecording(true);
        }).catch(function () { alert(msg.noMic); });
    });
    document.getElementById('waRecCancel').addEventListener('click', function () { recCancelled = true; if (recorder) recorder.stop(); });
    document.getElementById('waRecSend').addEventListener('click', function () { if (recorder) recorder.stop(); });

    grow();
    syncButtons();
    if (window.matchMedia('(min-width: 901px)').matches) {
        input.focus();
        input.setSelectionRange(input.value.length, input.value.length);
    }
})();
</script>
@endsection
