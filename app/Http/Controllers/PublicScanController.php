<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\ScanVisit;
use Illuminate\Http\Request;

class PublicScanController extends Controller
{
    public function show(string $uuid)
    {
        $location = Location::where('uuid', $uuid)->firstOrFail();

        ScanVisit::create([
            'location_id' => $location->id,
            'uuid' => $uuid,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('pages.scan.show', ['location' => $location]);
    }
}
