<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudioTemplateInstance extends Model
{
    protected $fillable = [
        'studio_template_id',
        'invitation_id',
        'owner_id',
        'status',
        'public_token',
        'template_snapshot',
        'content',
        'design_overrides',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'template_snapshot' => 'array',
            'content' => 'array',
            'design_overrides' => 'array',
            'meta' => 'array',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(StudioTemplate::class, 'studio_template_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
