<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A reader following the current brand (author_id null) or one of its authors. */
class Follow extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'user_id', 'author_id'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
}
