<?php

namespace App\Http\Middleware;

use App\Filament\Pages\TenantBilling;
use App\Support\CurrentGymResolver;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckGymSubscriptionValidity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $gym = CurrentGymResolver::resolve();

        if ($gym && $gym->expires_at && $gym->expires_at->isPast()) {
            $billingRoute = TenantBilling::getRouteName(panel: Filament::getCurrentPanel()?->getId());

            if ($request->routeIs($billingRoute)) {
                return $next($request);
            }

            return redirect()->route($billingRoute);
        }

        return $next($request);
    }
}
