<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // All optional: the site falls back to title / excerpt / featured image.
        Schema::table('posts', function (Blueprint $table) {
            $table->string('meta_title', 70)->nullable()->after('featured_image');
            $table->string('meta_description', 170)->nullable()->after('meta_title');
            $table->string('og_image', 2048)->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'og_image']);
        });
    }
};
