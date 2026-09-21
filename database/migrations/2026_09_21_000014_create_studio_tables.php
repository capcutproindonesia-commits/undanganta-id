<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->string('status', 20)->default('draft');
            $table->string('min_plan', 30)->default('free');
            $table->boolean('is_customer_editable')->default(true);
            $table->json('canvas')->nullable();
            $table->json('settings')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('studio_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_template_id')->nullable()->constrained('studio_templates')->nullOnDelete();
            $table->string('type', 30);
            $table->string('name', 190);
            $table->string('path');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('custom_fonts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('family', 180)->unique();
            $table->string('path');
            $table->string('mime', 120)->nullable();
            $table->unsignedSmallInteger('weight')->default(400);
            $table->string('style', 20)->default('normal');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fonts');
        Schema::dropIfExists('studio_assets');
        Schema::dropIfExists('studio_templates');
    }
};
