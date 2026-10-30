<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Builds a slug that is unique within the current tenant (the tenant scope
 * applies to the lookup), appending -2, -3, ... on collision.
 */
trait HasUniqueSlug
{
    public static function uniqueSlug(string $text, ?int $ignoreId = null): string
    {
        $base = Str::slug($text) ?: Str::lower(class_basename(static::class));
        $slug = $base;
        $i    = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
