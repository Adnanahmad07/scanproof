<?php

namespace App\Http\Controllers\Admin;

use App\Models\Location;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QrPrintController
{
    public function printSheet(Request $request)
    {
        $this->authorizePrint($request);

        $locations = $this->resolveLocations($request);

        return view('pages.admin.qr-print', [
            'locations' => $locations,
            'qr' => app(QrCodeService::class),
        ]);
    }

    public function downloadPdf(Request $request)
    {
        $this->authorizePrint($request);

        $locations = $this->resolveLocations($request);

        $pdf = Pdf::loadView('pages.admin.qr-pdf', [
            'locations' => $locations,
            'qr' => app(QrCodeService::class),
        ])->setPaper('a4');

        return $pdf->download('qr-codes.pdf');
    }

    private function authorizePrint(Request $request): void
    {
        Gate::authorize('printQr');
    }

    private function resolveLocations(Request $request): \Illuminate\Support\Collection
    {
        if ($request->boolean('all')) {
            return Location::orderBy('name')->get();
        }

        $ids = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:locations,id',
        ]);

        return Location::whereIn('id', $ids['ids'])->orderBy('name')->get();
    }
}
