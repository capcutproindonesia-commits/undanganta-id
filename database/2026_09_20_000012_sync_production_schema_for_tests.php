<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('invitations', 'groom_photo_path')) {
                $table->string('groom_photo_path')->nullable();
            }

            if (!Schema::hasColumn('invitations', 'groom_parent_text')) {
                $table->string('groom_parent_text', 300)->nullable();
            }

            if (!Schema::hasColumn('invitations', 'groom_origin')) {
                $table->string('groom_origin', 150)->nullable();
            }

            if (!Schema::hasColumn('invitations', 'groom_instagram')) {
                $table->string('groom_instagram', 100)->nullable();
            }

            if (!Schema::hasColumn('invitations', 'bride_photo_path')) {
                $table->string('bride_photo_path')->nullable();
            }

            if (!Schema::hasColumn('invitations', 'bride_parent_text')) {
                $table->string('bride_parent_text', 300)->nullable();
            }

            if (!Schema::hasColumn('invitations', 'bride_origin')) {
                $table->string('bride_origin', 150)->nullable();
            }

            if (!Schema::hasColumn('invitations', 'bride_instagram')) {
                $table->string('bride_instagram', 100)->nullable();
            }
        });

        Schema::table('guest_photos', function (Blueprint $table) {
            if (!Schema::hasColumn('guest_photos', 'is_approved')) {
                $table->boolean('is_approved')->default(false);
            }
        });
    }

    public function down(): void
    {
        // Intentionally empty.
        // Production already had these columns before this migration.
    }
};