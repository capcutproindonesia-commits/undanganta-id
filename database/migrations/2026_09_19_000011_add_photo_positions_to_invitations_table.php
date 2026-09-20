<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('invitations', 'groom_photo_x')) {
                $table->unsignedTinyInteger('groom_photo_x')->default(50);
            }

            if (!Schema::hasColumn('invitations', 'groom_photo_y')) {
                $table->unsignedTinyInteger('groom_photo_y')->default(50);
            }

            if (!Schema::hasColumn('invitations', 'bride_photo_x')) {
                $table->unsignedTinyInteger('bride_photo_x')->default(50);
            }

            if (!Schema::hasColumn('invitations', 'bride_photo_y')) {
                $table->unsignedTinyInteger('bride_photo_y')->default(50);
            }
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $columns = [
                'groom_photo_x',
                'groom_photo_y',
                'bride_photo_x',
                'bride_photo_y',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('invitations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
