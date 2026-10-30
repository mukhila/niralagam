<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** An image in a brand's media library, with resized WebP variants. */
class Media extends Model
{
    use BelongsToTenant;

    protected $table = 'media';

    protected $fillable = ['tenant_id', 'user_id', 'path', 'original_name', 'mime', 'size', 'width', 'height', 'alt', 'variants'];

    protected $casts = ['variants' => 'array'];

    protected $hidden = ['path', 'variants'];

    protected $appends = ['url', 'urls'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /** { original, thumb, medium, large } — a size falls back to the original when the image is smaller. */
    public function getUrlsAttribute(): array
    {
        $urls = ['original' => $this->url];
        foreach (['thumb', 'medium', 'large'] as $size) {
            $urls[$size] = isset($this->variants[$size])
                ? Storage::disk('public')->url($this->variants[$size]['path'])
                : $this->url;
        }

        return $urls;
    }

    /** Every stored file (original and variants). */
    public function files(): array
    {
        return array_values(array_unique([$this->path, ...array_column($this->variants ?? [], 'path')]));
    }
}
