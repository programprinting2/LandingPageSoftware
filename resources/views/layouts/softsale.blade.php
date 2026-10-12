<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $site['name'] ?? 'Quick Spot Color' }} — {{ $site['tagline'] ?? 'Solusi SATSET Cetak DTF' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/spotcolor favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/softsale.css') }}">
    @stack('styles')
</head>
<body>
    @yield('content')

    <script>
        window.SOFTSALE_DATA = @json($landingData ?? []);
    </script>
    <script src="{{ asset('js/softsale.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
