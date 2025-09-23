<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Event extends Model
{
    protected $fillable = [
        'name',
        'description',
        'date',
        'time',
        'price',
        'ticket_url',
        'poster',
        'gallery',
        'status',
        'category',
        'capacity',
        'venue_id',
        'owner_id',
        'owner_type',
    ];

    protected $casts = [
        'date' => 'datetime',
        'time' => 'datetime:H:i',
        'gallery' => 'array',
    ];

    /**
     * Get the venue where the event is held.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Get the owner of the event (Artist or Organiser).
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the artists performing at this event.
     */
    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'event_artist');
    }

    /**
     * Get the ratings for the event.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Get the users who favorited this event.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_event_favorites');
    }
}
