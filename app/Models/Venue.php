<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Venue extends Model
{
    protected $fillable = [
        'name',
        'description',
        'city',
        'capacity',
        'contact_email',
        'phone_number',
        'website',
        'address',
        'latitude',
        'longitude',
        'user_id',
        'owner_id',
        'owner_type',
        'main_picture',
        'venue_gallery',
    ];

    protected $casts = [
        'venue_gallery' => 'array',
    ];

    /**
     * Get the user that created the venue.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the owner of the venue (Artist or Organiser).
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the events held at this venue.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the ratings for the venue.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Get the users who favorited this venue.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_venue_favorites');
    }
}
