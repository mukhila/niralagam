<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One recorded action. Not tenant-scoped through BelongsToTenant on purpose:
 * platform events have no tenant, and readers filter explicitly.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id', 'user_id', 'actor_name', 'action', 'subject_type', 'subject_id',
        'subject_label', 'changes', 'ip_address',
    ];

    protected $casts = ['changes' => 'array', 'created_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
}
