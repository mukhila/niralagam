<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // draft → pending (in review) → scheduled | published, or → rejected (with a note)
            $table->enum('status', ['draft', 'pending', 'scheduled', 'published', 'rejected'])->default('draft')->change();
            $table->timestamp('submitted_at')->nullable()->after('published_at');
            $table->foreignId('reviewed_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_note')->nullable()->after('reviewed_at');
            $table->index(['tenant_id', 'status', 'published_at']);
        });

        Schema::create('post_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // who made the edit that replaced this version
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['post_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_revisions');

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status', 'published_at']);
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['submitted_at', 'reviewed_at', 'review_note']);
            $table->enum('status', ['draft', 'published'])->default('draft')->change();
        });
    }
};
