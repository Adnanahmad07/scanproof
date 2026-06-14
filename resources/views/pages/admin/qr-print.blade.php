<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>QR Codes - ScanProof</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #fff; }
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            padding: 24px;
        }
        @media (min-width: 640px) {
            .qr-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (min-width: 1024px) {
            .qr-grid { grid-template-columns: repeat(4, 1fr); }
        }
        .qr-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            page-break-inside: avoid;
        }
        .qr-card svg { width: 150px; height: 150px; }
        .qr-name { font-weight: 600; font-size: 14px; margin-top: 8px; color: #111827; }
        .qr-type { font-size: 12px; color: #6b7280; margin-top: 2px; }
        .qr-location { font-size: 10px; color: #9ca3af; margin-top: 4px; word-break: break-all; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .qr-card { border-color: #d1d5db; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="padding: 16px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
        <h1 style="font-size: 18px; font-weight: 700;">QR Codes ({{ $locations->count() }})</h1>
        <button onclick="window.print()" style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px;">Print</button>
    </div>
    <div class="qr-grid">
        @foreach ($locations as $location)
            <div class="qr-card">
                {!! $qr->inlineSvg($location->scanUrl(), 150) !!}
                <div class="qr-name">{{ $location->name }}</div>
                <div class="qr-type">{{ $location->type->label() }}</div>
                @if ($location->building || $location->floor)
                    <div class="qr-location">{{ collect([$location->building, $location->floor])->filter()->implode(' · ') }}</div>
                @endif
                <div class="qr-location">{{ $location->scanUrl() }}</div>
            </div>
        @endforeach
    </div>
</body>
</html>
