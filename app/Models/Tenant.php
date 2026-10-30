<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tenant extends Model
{
    protected $fillable = [
        'created_by', 'name', 'slug', 'domain', 'logo', 'status', 'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'admin');
    }

    // Without the tenant scope, so counts are correct whichever tenant is current.
    public function posts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Post::class)->withoutGlobalScope('tenant');
    }

    public static function default(): ?self
    {
        return static::where('slug', config('tenancy.default'))->first();
    }

    /**
     * The brand's public site, e.g. https://blog.acme.com or https://acme.niralagam.com.
     * Scheme and port come from FRONTEND_URL, so local dev gives http://acme.localhost:5173-style URLs.
     */
    public function publicUrl(): string
    {
        $front  = parse_url(config('tenancy.frontend_url'));
        $scheme = $front['scheme'] ?? 'https';
        $port   = isset($front['port']) ? ':'.$front['port'] : '';
        $host   = $this->domain
            ?: ($this->isDefault() ? ($front['host'] ?? 'localhost') : $this->slug.'.'.config('tenancy.central_domain'));

        return "{$scheme}://{$host}{$port}";
    }

    public function isDefault(): bool
    {
        return $this->slug === config('tenancy.default');
    }

    /** The user's membership role here (admin|editor|author|subscriber), or null. */
    public function roleFor(User $user): ?string
    {
        return $this->users()->whereKey($user->id)->first()?->pivot->role;
    }

    /** The brand's languages: { default: 'en', enabled: ['en', 'ta', …] } (codes from config/languages.php). */
    public function languages(): array
    {
        $saved   = $this->settings['languages'] ?? [];
        $default = $saved['default'] ?? 'en';
        $enabled = array_values(array_unique([$default, ...($saved['enabled'] ?? [])]));

        return ['default' => $default, 'enabled' => $enabled];
    }

    public function allowsPublicPosts(): bool
    {
        return (bool) ($this->settings['allow_public_posts'] ?? false);
    }

    /** True when any content still references this tenant (deletes are restricted). */
    public function hasContent(): bool
    {
        return $this->posts()->exists()
            || Category::withoutGlobalScope('tenant')->where('tenant_id', $this->id)->exists()
            || Tag::withoutGlobalScope('tenant')->where('tenant_id', $this->id)->exists();
    }
}
