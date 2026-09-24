<?php

namespace App\Support;

use App\Models\StudioTemplate;

final class PlanCapabilities
{
    public static function normalize(string $code): string
    {
        return match (strtolower(trim($code))) {
            'free', 'basic' => 'basic',
            'premium', 'intimate' => 'premium',
            'pro', 'royal' => 'royal',
            default => strtolower(trim($code)),
        };
    }

    public static function rank(string $code): int
    {
        return match (self::normalize($code)) {
            'basic' => 0,
            'premium' => 1,
            'royal' => 2,
            default => -1,
        };
    }

    public static function templateAllowed(string $plan, string $minimumPlan): bool
    {
        return self::rank($plan) >= self::rank($minimumPlan);
    }

    public static function fullEditor(string $plan): bool
    {
        return in_array(self::normalize($plan), ['premium', 'royal'], true);
    }

    public static function blankAllowed(string $plan): bool
    {
        return self::fullEditor($plan);
    }

    public static function finalLabel(string $plan): string
    {
        return match (self::normalize($plan)) {
            'basic' => 'Basic',
            'premium' => 'Premium / Intimate',
            'royal' => 'Royal',
            default => ucfirst($plan),
        };
    }

    public static function isSystemBlankTemplate(?StudioTemplate $template): bool
    {
        return $template && ($template->settings['system_blank'] ?? false) === true;
    }

    public static function blankCanvas(): array
    {
        return [
            'version' => 3,
            'settings' => [
                'desktopLayout' => 'cover-left',
                'desktopCoverWidth' => 56,
                'mobileBreakpoint' => 768,
            ],
            'desktopCover' => [
                'enabled' => true,
                'image' => '',
                'fit' => 'cover',
                'scale' => 1,
                'posX' => 0,
                'posY' => 0,
            ],
            'openingCover' => [
                'enabled' => true,
                'image' => '',
                'eyebrow' => 'The Wedding of',
                'names' => 'Nama & Nama',
                'bindNames' => true,
                'buttonText' => 'Buka Undangan',
                'textColor' => '#ffffff',
                'buttonColor' => '#2F6FED',
                'nameSize' => 46,
                'posX' => 50,
                'posY' => 50,
                'animation' => 'fade-up',
                'duration' => 0.8,
            ],
            'pages' => [[
                'id' => 'canvas-blank',
                'name' => 'Canvas 1',
                'role' => 'hero',
                'width' => 390,
                'height' => 844,
                'background' => '#ffffff',
                'transition' => [
                    'type' => 'fade',
                    'duration' => 0.8,
                    'easing' => 'ease-in-out',
                ],
                'layers' => [],
            ]],
        ];
    }
}
