<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('language', 10)->default('en')->after('status'); // BCP 47 code, see config/languages.php
            // Translations share a group: the id of the first post written (the group's own id for that post).
            $table->unsignedBigInteger('translation_group')->nullable()->after('language');
            $table->index(['tenant_id', 'language']);
            $table->index('translation_group');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'language']);
            $table->dropIndex(['translation_group']);
            $table->dropColumn(['language', 'translation_group']);
        });
    }
};
