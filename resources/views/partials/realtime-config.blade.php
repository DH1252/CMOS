@php
    $realtimeScheme = config('realtime.scheme') ?: (request()->isSecure() ? 'https' : 'http');
    $realtimeConfig = [
        'key' => config('realtime.key'),
        'host' => config('realtime.host') ?: request()->getHost(),
        'port' => (int) (config('realtime.port') ?: ($realtimeScheme === 'https' ? 443 : (request()->getPort() ?: 80))),
        'scheme' => $realtimeScheme,
    ];
@endphp
<script>
    window.__CMOS_REALTIME__ = {{ Illuminate\Support\Js::from($realtimeConfig) }};
</script>
