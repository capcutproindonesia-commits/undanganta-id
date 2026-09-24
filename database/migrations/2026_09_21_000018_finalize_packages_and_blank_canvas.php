<?php

use App\Support\PlanCapabilities;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $plans = [
            ['name' => 'Basic', 'code' => 'basic', 'price' => 300000, 'duration_days' => 365],
            ['name' => 'Premium / Intimate', 'code' => 'premium', 'price' => 670000, 'duration_days' => 365],
            ['name' => 'Royal', 'code' => 'royal', 'price' => 850000, 'duration_days' => 365],
        ];

        foreach ($plans as $plan) {
            DB::table('plans')->updateOrInsert(
                ['code' => $plan['code']],
                array_merge($plan, [
                    'features' => json_encode([]),
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );
        }

        DB::table('plans')
            ->whereIn('code', ['free', 'pro', 'intimate'])
            ->update(['is_active' => false, 'updated_at' => $now]);

        DB::table('studio_templates')->updateOrInsert(
            ['slug' => '__system-blank-canvas'],
            [
                'name' => 'Blank Canvas',
                'status' => 'published',
                'min_plan' => 'premium',
                'is_customer_editable' => true,
                'canvas' => json_encode(PlanCapabilities::blankCanvas()),
                'settings' => json_encode([
                    'system_blank' => true,
                    'hidden_from_template_gallery' => true,
                ]),
                'thumbnail_path' => null,
                'created_by' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('studio_templates')->where('slug', '__system-blank-canvas')->delete();
        DB::table('plans')->whereIn('code', ['basic', 'royal'])->delete();
    }
};
