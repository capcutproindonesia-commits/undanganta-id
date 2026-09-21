<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudioTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status',
        'min_plan',
        'is_customer_editable',
        'canvas',
        'settings',
        'thumbnail_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'canvas' => 'array',
            'settings' => 'array',
            'is_customer_editable' => 'boolean',
        ];
    }

    public function assets(): HasMany
    {
        return $this->hasMany(StudioAsset::class);
    }
}
