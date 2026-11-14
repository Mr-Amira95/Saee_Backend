{{-- Media attachment inside a chat bubble (image, sticker, video, audio / voice, document). --}}
@php
    $media = $log->mediaInfo() ?? [];
    $url   = route('admin.whatsapp-logs.media', $log);
    $size  = isset($media['size'])
        ? ($media['size'] >= 1048576 ? round($media['size'] / 1048576, 1) . ' MB' : max(1, round($media['size'] / 1024)) . ' KB')
        : null;
@endphp

@if(empty($media['path']) && empty($media['id']))
    <div class="wa-media-missing">{{ __('Media unavailable') }}</div>
@elseif(in_array($log->message_type, ['image', 'sticker'], true))
    <a href="{{ $url }}" target="_blank" rel="noopener" class="wa-media-img-link">
        <img src="{{ $url }}" alt="" loading="lazy" class="wa-media-img {{ $log->message_type === 'sticker' ? 'sticker' : '' }}">
    </a>
@elseif($log->message_type === 'video')
    <video src="{{ $url }}" class="wa-media-video" controls preload="metadata"></video>
@elseif($log->message_type === 'audio')
    <div class="wa-media-audio">
        @if(!empty($media['voice']))
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><title>{{ __('Voice message') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/></svg>
        @endif
        <audio src="{{ $url }}" controls preload="metadata"></audio>
    </div>
@else
    <a href="{{ $url }}?download=1" class="wa-media-doc">
        <span class="wa-media-doc-icon">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        </span>
        <span class="wa-media-doc-info">
            <span class="wa-media-doc-name" dir="auto">{{ $media['filename'] ?? __('Document') }}</span>
            <span class="wa-media-doc-sub">{{ collect([$size, isset($media['mime']) ? strtoupper(\Illuminate\Support\Str::afterLast($media['mime'], '/')) : null])->filter()->implode(' · ') }}</span>
        </span>
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><title>{{ __('Download') }}</title><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
    </a>
@endif
