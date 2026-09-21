<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFont extends Model
{
    protected $fillable = [
        'name',
        'family',
        'path',
        'mime',
        'weight',
        'style',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'weight' => 'integer',
        ];
    }
}
