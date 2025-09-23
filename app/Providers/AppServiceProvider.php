<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Artist;
use App\Models\Organiser;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set up morph map for polymorphic relationships
        Relation::morphMap([
            'artist' => Artist::class,
            'organiser' => Organiser::class,
            'user' => User::class,
        ]);
    }
}
