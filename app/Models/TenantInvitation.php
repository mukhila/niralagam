<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * An emailed invitation to join a brand. Not tenant-scoped: it is accepted from
 * a link, whatever brand the request resolves to.
 */
class TenantInvitation extends Model
{
    protected $fillable = ['tenant_id', 'email', 'role', 'token_hash', 'invited_by', 'expires_at'];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'expires_at'  => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function inviter(): BelongsTo { return $this->belongsTo(User::class, 'invited_by'); }

    public function scopePending(Builder $query): void
    {
        $query->whereNull('accepted_at');
    }

    public static function findByToken(string $token): ?self
    {
        return static::where('token_hash', hash('sha256', $token))->first();
    }

    /** Sets a fresh token and expiry; returns the raw token for the link. */
    public function refreshToken(): string
    {
        $token = Str::random(48);
        $this->token_hash = hash('sha256', $token);
        $this->expires_at = now()->addDays(config('tenancy.invite_days'));

        return $token;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public static function url(string $token): string
    {
        return rtrim(config('tenancy.frontend_url'), '/').'/invite/'.$token;
    }
}
