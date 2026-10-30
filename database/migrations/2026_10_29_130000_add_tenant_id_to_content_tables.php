<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tables whose `slug` becomes unique per tenant instead of globally. */
    private array $sluggable = ['posts', 'categories', 'tags'];

    private array $tables = ['posts', 'categories', 'tags', 'comments', 'post_views'];

    public function up(): void
    {
        // A default tenant must exist before any user does (fresh installs, tests).
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->change();
        });

        Schema::table('tenant_user', function (Blueprint $table) {
            $table->enum('role', ['admin', 'editor', 'author', 'subscriber'])->default('subscriber')->change();
        });

        $slug = config('tenancy.default', 'main');
        $defaultId = DB::table('tenants')->where('slug', $slug)->value('id')
            ?? DB::table('tenants')->insertGetId([
                'name'       => 'Niralagam',
                'slug'       => $slug,
                'status'     => 'active',
                'created_by' => DB::table('users')->where('role', 'super_admin')->value('id'),
                // Keep today's behaviour on the main site: any signed-in user may post.
                'settings'   => json_encode(['allow_public_posts' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                // restrict: deleting a tenant must never silently wipe its content.
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            });

            DB::table($name)->whereNull('tenant_id')->update(['tenant_id' => $defaultId]);
        }

        foreach ($this->sluggable as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->dropUnique("{$name}_slug_unique");
                $table->unique(['tenant_id', 'slug']);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->sluggable as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->dropUnique("{$name}_tenant_id_slug_unique");
                $table->unique('slug');
            });
        }

        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }

        Schema::table('tenant_user', function (Blueprint $table) {
            $table->enum('role', ['admin', 'editor', 'subscriber'])->default('subscriber')->change();
        });
    }
};
