<?php

namespace App\Support;

use App\Models\Theme;
use Illuminate\Support\Facades\Schema;

final class ThemeCatalog
{
    private static ?array $themes = null;

    public static function all(): array
    {
        if (self::$themes !== null) {
            return self::$themes;
        }

        $themes = config('themes.catalog', []);

        if (Schema::hasTable('themes')) {
            $storedThemes = Theme::query()
                ->orderBy('sort_order')
                ->get();

            foreach ($storedThemes as $theme) {
                if (!array_key_exists($theme->code, $themes)) {
                    continue;
                }

                $themes[$theme->code] = array_merge(
                    $themes[$theme->code],
                    [
                        'name' => $theme->name,
                        'description' => $theme->description,
                        'minimum_plan' => $theme->minimum_plan,
                        'version' => $theme->version,
                        'is_active' => $theme->is_active,
                        'sort_order' => $theme->sort_order,
                    ]
                );
            }
        }

        uasort(
            $themes,
            fn (array $left, array $right) =>
                ($left['sort_order'] ?? 999)
                <=>
                ($right['sort_order'] ?? 999)
        );

        return self::$themes = $themes;
    }

    public static function active(): array
    {
        return array_filter(
            self::all(),
            fn (array $theme) => (bool) ($theme['is_active'] ?? false)
        );
    }

    public static function codes(): array
    {
        return array_keys(self::all());
    }

    public static function activeCodes(): array
    {
        return array_keys(self::active());
    }

    public static function allowedForPlan(string $planCode): array
    {
        $planCode = strtolower(trim($planCode));
        $planRanks = config('themes.plans', []);
        $selectedRank = $planRanks[$planCode] ?? -1;

        return array_keys(array_filter(
            self::active(),
            function (array $theme) use ($planRanks, $selectedRank): bool {
                $minimumRank = $planRanks[$theme['minimum_plan'] ?? ''] ?? PHP_INT_MAX;

                return $minimumRank <= $selectedRank;
            }
        ));
    }

    public static function allows(string $planCode, string $themeCode): bool
    {
        return in_array(
            strtolower(trim($themeCode)),
            self::allowedForPlan($planCode),
            true
        );
    }

    public static function compatibleWithPlan(
        string $planCode,
        string $themeCode
    ): bool {
        $themeCode = strtolower(trim($themeCode));
        $theme = self::all()[$themeCode] ?? null;

        if ($theme === null) {
            return false;
        }

        $planRanks = config('themes.plans', []);
        $selectedRank = $planRanks[strtolower(trim($planCode))] ?? -1;
        $minimumRank = $planRanks[$theme['minimum_plan'] ?? ''] ?? PHP_INT_MAX;

        return $minimumRank <= $selectedRank;
    }
}
