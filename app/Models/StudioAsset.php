<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudioAsset extends Model
{
    protected $fillable = [
        'studio_template_id',
        'type',
        'name',
        'path',
        'mime',
        'size',
        'metadata',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(StudioTemplate::class, 'studio_template_id');
    }
}
