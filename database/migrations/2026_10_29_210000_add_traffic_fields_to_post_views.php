<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_views', function (Blueprint $table) {
            // Where the reader came from: direct | search | social | newsletter | referral
            $table->string('source', 20)->default('direct')->after('user_agent');
            $table->string('referrer_host', 255)->nullable()->after('source');
            // mobile | tablet | desktop
            $table->string('device', 10)->default('desktop')->after('referrer_host');
            $table->index(['tenant_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('post_views', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'viewed_at']);
            $table->dropColumn(['source', 'referrer_host', 'device']);
        });
    }
};
