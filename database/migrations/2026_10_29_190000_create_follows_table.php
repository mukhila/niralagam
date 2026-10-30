<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A reader following a brand (author_id null) or an author within a brand.
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Uniqueness is enforced in code (a null author_id isn't unique in MySQL indexes).
            $table->index(['tenant_id', 'user_id', 'author_id']);
            $table->index(['tenant_id', 'author_id']);
        });

        // When followers were emailed about this post, so re-publishing doesn't email them twice.
        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('followers_notified_at')->nullable()->after('review_note');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('followers_notified_at');
        });
        Schema::dropIfExists('follows');
    }
};
