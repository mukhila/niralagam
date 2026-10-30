<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\Html\PostHtml;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A previous version of a post's title, excerpt and content. */
class PostRevision extends Model
{
    use BelongsToTenant;

    public const UPDATED_AT = null;

    /** Older revisions beyond this many per post are pruned. */
    public const KEEP = 30;

    protected $fillable = ['tenant_id', 'post_id', 'user_id', 'title', 'excerpt', 'content'];

    // Same cleaning as Post, so a restored version can never reintroduce unsafe HTML.
    protected function content(): Attribute
    {
        return Attribute::set(fn (?string $value) => PostHtml::clean($value));
    }

    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
