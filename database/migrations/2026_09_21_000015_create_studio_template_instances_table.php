<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_template_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_template_id')
                ->constrained('studio_templates')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('invitation_id')->nullable()->index();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('draft')->index();
            $table->json('template_snapshot');
            $table->json('content')->nullable();
            $table->json('design_overrides')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['studio_template_id', 'owner_id', 'status'], 'studio_instance_owner_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_template_instances');
    }
};
