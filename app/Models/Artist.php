<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Artist extends Model
{
    protected $fillable = [
        'user_id',
        'stage_name',
        'real_name',
        'genre',
        'bio',
        'phone_number',
        'instagram',
        'facebook',
        'twitter',
        'profile_picture',
        'gallery',
        'settings',
    ];

    protected $casts = [
        'gallery' => 'array',
        'settings' => 'array',
    ];

    /**
     * Get the user that owns the artist profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the events for the artist.
     */
    public function events(): BelongsToMany
    {
        // Align with Event model which uses 'event_artist' pivot table
        return $this->belongsToMany(Event::class, 'event_artist');
    }

    /**
     * Get the venues owned by the artist.
     */
    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class, 'owner_id')->where('owner_type', self::class);
    }

    /**
     * Get the ratings for the artist.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Get the users who favorited this artist.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_artist_favorites');
    }
}
