<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pricing — ScanProof</title>
    <meta name="description" content="Simple, transparent pricing for facility operations teams of every size.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-surface font-sans text-text-primary antialiased">
    <x-nav />

    <main class="pt-20">
        <div x-data="{ annual: true }">
            <x-pricing.hero />
            <x-pricing.plans />
        </div>
        <x-pricing.faq />
        <x-cta />
    </main>

    <x-footer />
</body>
</html>
