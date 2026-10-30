<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use BelongsToTenant, HasUniqueSlug;

    protected $fillable = ['name', 'slug', 'description', 'icon'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
