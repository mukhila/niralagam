<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\Tenancy\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Picks the tenant for an API request, in order:
 *   1. X-Tenant header (slug) — admin tenant switcher, local dev
 *   2. X-Tenant-Domain header, else the request host — custom domain or {slug}.{central_domain}
 *   3. the default tenant
 * Runs before route-model binding so bound models are tenant-scoped.
 */
class ResolveTenant
{
    public function __construct(private CurrentTenant $current) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolve($request);

        if (! $tenant) {
            abort(404, 'Tenant not found.');
        }

        if ($tenant->status !== 'active' && ! $request->user('sanctum')?->isSuperAdmin()) {
            abort(404, 'Tenant not available.');
        }

        $this->current->set($tenant);

        return $next($request);
    }

    private function resolve(Request $request): ?Tenant
    {
        // An explicit slug that doesn't exist is a 404, not a silent fallback.
        if ($slug = $request->header('X-Tenant')) {
            return Tenant::where('slug', $slug)->first();
        }

        $host    = Str::lower($request->header('X-Tenant-Domain') ?: $request->getHost());
        $central = Str::lower(config('tenancy.central_domain'));

        if ($tenant = Tenant::where('domain', $host)->first()) {
            return $tenant;
        }

        if (Str::endsWith($host, '.'.$central)) {
            $sub = Str::beforeLast($host, '.'.$central);
            if ($sub !== 'www' && ! str_contains($sub, '.')) {
                return Tenant::where('slug', $sub)->first();
            }
        }

        return Tenant::default();
    }
}
