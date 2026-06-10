<?php

namespace App\Http\Middleware;

use App\Models\SupervisorInvitation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateInvitationToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token');

        $invitation = SupervisorInvitation::where('token', $token)
            ->first();

        if (!$invitation) {
            abort(404, 'Invalid invitation link.');
        }

        if ($invitation->isAccepted()) {
            return redirect()
                ->route('login')
                ->with('error', 'This invitation has already been accepted.');
        }

        if ($invitation->isExpired()) {
            $invitation->markExpired();
            abort(410, 'This invitation has expired. Please contact your administrator.');
        }

        $request->attributes->set('invitation', $invitation);

        return $next($request);
    }
}
