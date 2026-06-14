<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>QR Codes - ScanProof</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; margin: 0; padding: 0; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        td { width: 33.33%; vertical-align: top; padding: 8px; }
        .qr-card {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            page-break-inside: avoid;
        }
        .qr-card img { width: 120px; height: 120px; display: block; margin: 0 auto; }
        .qr-name { font-weight: bold; font-size: 11px; margin-top: 6px; color: #111827; }
        .qr-type { font-size: 9px; color: #6b7280; margin-top: 2px; }
        .qr-location { font-size: 8px; color: #9ca3af; margin-top: 3px; word-break: break-all; }
    </style>
</head>
<body>
    <div style="padding: 16px 20px; border-bottom: 1px solid #d1d5db;">
        <h1 style="font-size: 16px; font-weight: 700; margin: 0;">ScanProof QR Codes ({{ $locations->count() }})</h1>
    </div>
    <table>
        @foreach ($locations->chunk(3) as $chunk)
            <tr>
                @foreach ($chunk as $location)
                    <td>
                        <div class="qr-card">
                            <img src="{{ $qr->svgDataUri($location->scanUrl(), 120) }}" width="120" height="120" alt="QR">
                            <div class="qr-name">{{ $location->name }}</div>
                            <div class="qr-type">{{ $location->type->label() }}</div>
                            @if ($location->building || $location->floor)
                                <div class="qr-location">{{ collect([$location->building, $location->floor])->filter()->implode(' · ') }}</div>
                            @endif
                            <div class="qr-location">{{ $location->scanUrl() }}</div>
                        </div>
                    </td>
                @endforeach
                @if ($chunk->count() < 3)
                    @for ($i = $chunk->count(); $i < 3; $i++)
                        <td></td>
                    @endfor
                @endif
            </tr>
        @endforeach
    </table>
</body>
</html>
