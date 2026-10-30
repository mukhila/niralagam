<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'user'])->default('user')->after('name');
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('avatar')->nullable()->after('google_id');
            $table->string('otp', 6)->nullable()->after('avatar');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
            $table->timestamp('last_login_at')->nullable()->after('otp_expires_at');
            $table->string('password')->nullable()->change(); // allow null for OAuth/OTP users
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'google_id', 'avatar', 'otp', 'otp_expires_at', 'last_login_at']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
