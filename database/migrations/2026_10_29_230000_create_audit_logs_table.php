<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only: nothing in the app updates or deletes rows except retention pruning.
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete(); // null = platform-level event
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();   // who did it (null = system)
            $table->string('actor_name')->nullable();     // kept even if the account is later deleted
            $table->string('action', 60);                 // e.g. post.published, member.role_changed
            $table->string('subject_type', 40)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label')->nullable();  // e.g. the post title at the time
            $table->json('changes')->nullable();          // { field: [old, new] } or extra details
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'action']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
