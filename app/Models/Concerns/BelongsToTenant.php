<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Scopes every query to the current tenant and stamps tenant_id on create.
 * With no current tenant (console, seeders) queries are unscoped.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            if ($id = app(CurrentTenant::class)->id()) {
                $query->where($query->qualifyColumn('tenant_id'), $id);
            }
        });

        static::creating(function ($model) {
            $model->tenant_id ??= app(CurrentTenant::class)->forWrite()?->id;
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
