<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Organiser extends Model
{
    protected $fillable = [
        'user_id',
        'organisation_name',
        'contact_email',
        'phone_number',
        'website',
        'description',
        'logo',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the user that owns the organiser profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the events created by the organiser.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'owner_id')->where('owner_type', self::class);
    }

    /**
     * Get the venues owned by the organiser.
     */
    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class, 'owner_id')->where('owner_type', self::class);
    }

    /**
     * Get the ratings for the organiser.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    /**
     * Get the users who favorited this organiser.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_organiser_favorites');
    }
}
