<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('path');                 // original, on the public disk
            $table->string('original_name');
            $table->string('mime', 50);
            $table->unsignedInteger('size');        // bytes, of the stored original
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->string('alt', 255)->nullable();
            $table->json('variants')->nullable();   // { thumb|medium|large: { path, width, height } }
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
