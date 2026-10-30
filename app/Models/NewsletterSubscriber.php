<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** A reader on a brand's mailing list (double opt-in). */
class NewsletterSubscriber extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'email', 'status', 'token'];

    protected $hidden = ['token'];

    protected $casts = [
        'confirmed_at'    => 'datetime',
        'unsubscribed_at' => 'datetime',
        'last_sent_at'    => 'datetime',
    ];

    public function scopeSubscribed(Builder $query): void
    {
        $query->where('status', 'subscribed');
    }

    public static function newToken(): string
    {
        return Str::random(48);
    }

    public function confirmUrl(): string
    {
        return $this->siteUrl().'/newsletter/confirm/'.$this->token;
    }

    public function unsubscribeUrl(): string
    {
        return $this->siteUrl().'/newsletter/unsubscribe/'.$this->token;
    }

    private function siteUrl(): string
    {
        return Tenant::find($this->tenant_id)?->publicUrl() ?? rtrim(config('tenancy.frontend_url'), '/');
    }
}
