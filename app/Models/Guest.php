<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Guest extends Model
{
    protected $fillable = [
        'invitation_id',
        'name',
        'phone',
        'category',
        'token',
        'rsvp_status',
        'party_size',
        'note',
        'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Guest $guest) {
            if (
                is_string($guest->token)
                &&
                trim($guest->token) !== ''
            ) {
                return;
            }

            do {
                $token = Str::random(40);
            } while (
                static::query()
                    ->where('token', $token)
                    ->exists()
            );

            $guest->token = $token;
        });
    }

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }
}
