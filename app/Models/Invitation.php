<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'title',

        'groom_name',
        'groom_photo_path',
        'groom_photo_x',
        'groom_photo_y',
        'groom_parent_text',
        'groom_origin',
        'groom_instagram',

        'bride_name',
        'bride_photo_path',
        'bride_photo_x',
        'bride_photo_y',
        'bride_parent_text',
        'bride_origin',
        'bride_instagram',

        'event_date',
        'venue_name',
        'venue_address',
        'maps_url',
        'theme',
        'music_url',
        'quote',
        'story',
        'cover_path',
        'gallery',
        'gift_accounts',
        'sections',
        'is_published',
        'plan',
        'expires_at',
        'custom_domain',
        'views_count',

        // Studio-only customer flow.
        'studio_template_id',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'gallery' => 'array',
            'gift_accounts' => 'array',
            'sections' => 'array',
            'is_published' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (!$model->slug) {
                $model->slug =
                    Str::slug($model->title)
                    . '-'
                    . Str::lower(Str::random(5));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function wishes()
    {
        return $this->hasMany(Wish::class)->latest();
    }

    public function photos()
    {
        return $this->hasMany(GuestPhoto::class)->latest();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function latestOrder()
    {
        return $this->hasOne(Order::class)->latestOfMany();
    }

    public function studioTemplate()
    {
        return $this->belongsTo(StudioTemplate::class, 'studio_template_id');
    }

    public function studioInstances()
    {
        return $this->hasMany(StudioTemplateInstance::class);
    }
}
