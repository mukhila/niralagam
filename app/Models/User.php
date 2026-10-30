<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role',
        'google_id', 'avatar', 'bio', 'website',
        'otp', 'otp_expires_at', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token', 'otp'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at'    => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }

    /** Author page handle: the username, or the id for users without one. */
    public function handle(): string
    {
        return $this->username ?: (string) $this->id;
    }

    /** User id for an author handle (0 when unknown, so queries match nothing). */
    public static function idForHandle(string $handle): int
    {
        return (int) (static::where('username', $handle)->value('id')
            ?? (ctype_digit($handle) && static::whereNull('username')->whereKey((int) $handle)->exists() ? (int) $handle : 0));
    }

    public function posts(): HasMany { return $this->hasMany(Post::class); }
    public function tenants(): BelongsToMany {
        return $this->belongsToMany(Tenant::class, 'tenant_user')->withPivot('role')->withTimestamps();
    }
    public function createdTenants(): HasMany { return $this->hasMany(Tenant::class, 'created_by'); }
    public function wishlists(): HasMany { return $this->hasMany(Wishlist::class); }
    public function bookmarks(): HasMany { return $this->hasMany(Bookmark::class); }
    public function comments(): HasMany { return $this->hasMany(Comment::class); }
}
