<?php

namespace App\Http\Controllers;

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Http\Request;

class ScanSetupController extends Controller
{
    public function show(string $uuid)
    {
        $location = Location::where('uuid', $uuid)->firstOrFail();

        // Already configured — redirect to scan view
        if ($location->configured) {
            return redirect()->route('scan.show', $uuid);
        }

        // Only supervisors can configure
        if (!auth()->check() || !auth()->user()->isSupervisor()) {
            abort(403, 'Only supervisors can configure QR codes.');
        }

        return view('pages.scan.setup', ['location' => $location]);
    }

    public function save(Request $request, string $uuid)
    {
        $location = Location::where('uuid', $uuid)->firstOrFail();

        if ($location->configured) {
            return redirect()->route('scan.show', $uuid);
        }

        if (!auth()->check() || !auth()->user()->isSupervisor()) {
            abort(403, 'Only supervisors can configure QR codes.');
        }

        $typeValues = array_map(fn ($t) => $t->value, LocationType::simpleTypes());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', $typeValues),
        ]);

        $location->configure(
            name: $validated['name'],
            type: LocationType::from($validated['type']),
            userId: auth()->id()
        );

        return redirect()->route('scan.show', $uuid)
            ->with('success', 'QR code configured successfully!');
    }
}
