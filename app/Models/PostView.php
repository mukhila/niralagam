<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostView extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = ['post_id', 'user_id', 'ip_address', 'user_agent', 'source', 'referrer_host', 'device'];

    protected $casts = ['viewed_at' => 'datetime'];

    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
