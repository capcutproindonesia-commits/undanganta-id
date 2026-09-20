<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('themes')
            ->where('code', 'classic')
            ->update([
                'minimum_plan' => 'premium',
            ]);
    }

    public function down(): void
    {
        DB::table('themes')
            ->where('code', 'classic')
            ->update([
                'minimum_plan' => 'free',
            ]);
    }
};