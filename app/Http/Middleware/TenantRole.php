<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Usage: ->middleware('tenant.role:admin,editor'). Super admins always pass. */
class TenantRole
{
    public function __construct(private CurrentTenant $current) {}

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $this->current->userHasRole($request->user(), $roles)) {
            abort(403, 'You do not have permission for this action in this tenant.');
        }

        return $next($request);
    }
}
