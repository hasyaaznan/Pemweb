<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Bandung Spiders Basketball' }}</title>
    <link rel="icon" href="{{ asset('images/logo-spiders.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:ital,wght@0,600;0,700;0,800;1,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body {{ $attributes->class("min-h-screen bg-ungu-50 font-sans text-ungu-900 antialiased") }}>
    {{ $slot }}
</body>
</html>
