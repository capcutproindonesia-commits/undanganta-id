<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('themes')) {
            Schema::create('themes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 100);
                $table->string('description', 500)->nullable();
                $table->string('minimum_plan', 30)->default('free');
                $table->string('version', 30)->default('1.0.0');
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(100);
                $table->timestamps();
            });
        }

        $now = now();

        DB::table('themes')->insertOrIgnore([
            ['code' => 'modern', 'name' => 'Modern', 'description' => 'Editorial, tegas, dan kontemporer.', 'minimum_plan' => 'free', 'version' => '1.0.0', 'is_active' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'minimal', 'name' => 'Minimal', 'description' => 'Tenang, lapang, dan fokus pada cerita.', 'minimum_plan' => 'premium', 'version' => '1.0.0', 'is_active' => true, 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'classic', 'name' => 'Classic', 'description' => 'Formal dan elegan untuk acara tradisional.', 'minimum_plan' => 'premium', 'version' => '1.0.0', 'is_active' => true, 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'floral', 'name' => 'Floral', 'description' => 'Romantis dengan karakter bunga yang lembut.', 'minimum_plan' => 'pro', 'version' => '1.0.0', 'is_active' => true, 'sort_order' => 40, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
