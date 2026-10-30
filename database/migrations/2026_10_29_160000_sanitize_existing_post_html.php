<?php

use App\Support\Html\PostHtml;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One-off clean-up: run the post HTML sanitizer over content saved before it
 * existed. Safe to re-run; already-clean HTML comes out unchanged.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('posts')->select('id', 'content', 'excerpt')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $clean = ['content' => PostHtml::clean($row->content), 'excerpt' => PostHtml::plainText($row->excerpt)];
                if ($clean['content'] !== $row->content || $clean['excerpt'] !== $row->excerpt) {
                    DB::table('posts')->where('id', $row->id)->update($clean);
                }
            }
        });

        DB::table('post_revisions')->select('id', 'content', 'excerpt')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('post_revisions')->where('id', $row->id)->update([
                    'content' => PostHtml::clean($row->content),
                    'excerpt' => PostHtml::plainText($row->excerpt),
                ]);
            }
        });

        // A featured image is an <img src>: only http(s) URLs are allowed from now on.
        DB::table('posts')->whereNotNull('featured_image')
            ->where('featured_image', 'not like', 'http://%')
            ->where('featured_image', 'not like', 'https://%')
            ->update(['featured_image' => null]);
    }

    public function down(): void
    {
        // Removed markup can't be restored.
    }
};
