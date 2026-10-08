{{--
    Realtime page refresh (admin + client layouts). Must be included right after the
    `.content` element closes so the originally rendered HTML is captured before page JS
    changes it. The Pusher connection itself (window.saeePusher) is created by the layout.
--}}
@auth
    @php
        $realtimeChannel = \App\Realtime\RealtimePages::channelFor(auth()->user());
        $realtimeTypes   = \App\Realtime\RealtimePages::typesFor(request()->route()?->getName());
        $realtimeConfig  = ['root' => '.content', 'channel' => $realtimeChannel, 'types' => $realtimeTypes];
    @endphp

    @if($realtimeChannel && $realtimeTypes !== null)
        <script>
            window.SaeeRealtimeConfig = @json($realtimeConfig);
        </script>
        <script src="{{ asset('vendor/idiomorph/idiomorph.min.js') }}"></script>
        <script src="{{ asset('js/realtime.js') }}?v={{ filemtime(public_path('js/realtime.js')) }}"></script>
    @endif
@endauth
