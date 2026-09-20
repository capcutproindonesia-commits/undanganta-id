<?php

return [
    'plans' => [
        'free' => 10,
        'premium' => 20,
        'pro' => 30,
    ],

    'catalog' => [
        'modern' => [
            'name' => 'Modern',
            'description' => 'Editorial, tegas, dan kontemporer.',
            'minimum_plan' => 'free',
            'version' => '1.0.0',
            'is_active' => true,
            'sort_order' => 10,
        ],
        'minimal' => [
            'name' => 'Minimal',
            'description' => 'Tenang, lapang, dan fokus pada cerita.',
            'minimum_plan' => 'premium',
            'version' => '1.0.0',
            'is_active' => true,
            'sort_order' => 20,
        ],
        'classic' => [
            'name' => 'Classic',
            'description' => 'Formal dan elegan untuk acara tradisional.',
            'minimum_plan' => 'premium',
            'version' => '1.0.0',
            'is_active' => true,
            'sort_order' => 30,
        ],
        'floral' => [
            'name' => 'Floral',
            'description' => 'Romantis dengan karakter bunga yang lembut.',
            'minimum_plan' => 'pro',
            'version' => '1.0.0',
            'is_active' => true,
            'sort_order' => 40,
        ],
    ],
];
