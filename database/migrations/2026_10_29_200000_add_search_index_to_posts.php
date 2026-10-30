<?php

use App\Support\Html\PostHtml;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plain text of the content (kept up to date by Post::saving), so search
        // matches words, not HTML tags.
        Schema::table('posts', function (Blueprint $table) {
            $table->longText('search_text')->nullable()->after('content');
        });

        DB::table('posts')->select('id', 'content')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('posts')->where('id', $row->id)->update(['search_text' => PostHtml::searchText($row->content)]);
            }
        });

        // Full-text indexes (MySQL/MariaDB only; other drivers use the LIKE fallback in SearchController).
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('posts', function (Blueprint $table) {
                $table->fullText(['title'], 'posts_title_fulltext');
                $table->fullText(['title', 'excerpt', 'search_text'], 'posts_search_fulltext');
            });
        }
    }

    public function down(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropFullText('posts_search_fulltext');
                $table->dropFullText('posts_title_fulltext');
            });
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('search_text');
        });
    }
};
