<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use BelongsToTenant;

    protected $fillable = ['user_id', 'post_id'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
}
