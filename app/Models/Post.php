<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasUniqueSlug;
use App\Support\Follows\FollowerAlerts;
use App\Support\Html\PostHtml;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use BelongsToTenant, HasUniqueSlug;

    /** Statuses an author can set on their own; the rest need an editor. */
    public const AUTHOR_STATUSES = ['draft', 'pending'];

    /** Statuses in which the author may still edit or delete their own post. */
    public const AUTHOR_EDITABLE = ['draft', 'pending', 'rejected'];

    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug',
        'excerpt', 'content', 'featured_image', 'status', 'published_at',
        'meta_title', 'meta_description', 'og_image', 'language', 'translation_group',
    ];

    // Internal: plain-text copy of the content for the search index.
    protected $hidden = ['search_text'];

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if ($post->isDirty('content') || $post->search_text === null) {
                $post->search_text = PostHtml::searchText($post->content);
            }
        });

        // Followers hear about a post once, when it first goes live (published,
        // or scheduled and flipped by `posts:publish-scheduled`).
        static::saved(function (Post $post) {
            if ($post->status === 'published' && ! $post->followers_notified_at
                && ($post->wasRecentlyCreated || $post->wasChanged('status'))) {
                app(FollowerAlerts::class)->postPublished($post);
            }
        });
    }

    protected $casts = [
        'followers_notified_at' => 'datetime',
        'published_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    // Post HTML is rendered raw on the site, so it is cleaned on every write
    // (create, update, revision restore). See PostHtml for what is allowed.
    protected function content(): Attribute
    {
        return Attribute::set(fn (?string $value) => PostHtml::clean($value));
    }

    protected function excerpt(): Attribute
    {
        return Attribute::set(fn (?string $value) => PostHtml::plainText($value));
    }

    protected function metaTitle(): Attribute
    {
        return Attribute::set(fn (?string $value) => PostHtml::plainText($value) ?: null);
    }

    protected function metaDescription(): Attribute
    {
        return Attribute::set(fn (?string $value) => PostHtml::plainText($value) ?: null);
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function tags(): BelongsToMany { return $this->belongsToMany(Tag::class); }
    public function views(): HasMany { return $this->hasMany(PostView::class); }
    public function wishlists(): HasMany { return $this->hasMany(Wishlist::class); }
    public function bookmarks(): HasMany { return $this->hasMany(Bookmark::class); }
    public function comments(): HasMany { return $this->hasMany(Comment::class); }
    public function revisions(): HasMany { return $this->hasMany(PostRevision::class)->latest('id'); }

    /** Other-language versions of this post (same translation group), not including itself. */
    public function translations(): Builder
    {
        return static::query()
            ->where('translation_group', $this->translation_group ?? $this->id)
            ->whereKeyNot($this->id);
    }

    /**
     * Live on the site: published, or scheduled with its time reached. The second
     * case keeps scheduled posts on time even if `posts:publish-scheduled` lags.
     */
    public function scopeLive(Builder $query): void
    {
        $query->where(fn ($q) => $q->where('status', 'published')
            ->orWhere(fn ($q) => $q->where('status', 'scheduled')->where('published_at', '<=', now())));
    }

    public function isLive(): bool
    {
        return $this->status === 'published'
            || ($this->status === 'scheduled' && $this->published_at?->isPast());
    }

    /** Store the current title/excerpt/content as a revision (call before changing them). */
    public function snapshot(?User $by): void
    {
        $this->revisions()->create([
            'tenant_id' => $this->tenant_id,
            'user_id'   => $by?->id,
            'title'     => $this->getOriginal('title'),
            'excerpt'   => $this->getOriginal('excerpt'),
            'content'   => $this->getOriginal('content'),
        ]);

        $keep = $this->revisions()->take(PostRevision::KEEP)->pluck('id');
        PostRevision::where('post_id', $this->id)->whereNotIn('id', $keep)->delete();
    }
}
