<?php

namespace App\Support\Tenancy;

use App\Models\Tenant;
use App\Models\User;

/**
 * Holds the tenant for the current request. Set by ResolveTenant; read by the
 * BelongsToTenant scope and by permission checks.
 */
class CurrentTenant
{
    private ?Tenant $tenant = null;

    public function set(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        return $this->tenant?->id;
    }

    /** The tenant new rows are written to: the current one, else the default. */
    public function forWrite(): ?Tenant
    {
        return $this->tenant ?? Tenant::default();
    }

    /** Super admins always pass; others need one of $roles in the current tenant. */
    public function userHasRole(?User $user, array $roles): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->tenant !== null && in_array($this->tenant->roleFor($user), $roles, true);
    }
}
