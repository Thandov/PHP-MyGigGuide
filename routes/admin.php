<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\EventManagementController;
use App\Http\Controllers\Admin\VenueManagementController;
use App\Http\Controllers\Admin\ArtistManagementController;
use App\Http\Controllers\Admin\OrganiserManagementController;

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Public admin routes (no middleware)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Protected admin routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        // User Management
        Route::resource('users', UserManagementController::class);
        Route::patch('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');

        // Event Management
        Route::resource('events', EventManagementController::class);
        Route::patch('events/{event}/toggle-status', [EventManagementController::class, 'toggleStatus'])->name('events.toggle-status');

        // Venue Management
        Route::resource('venues', VenueManagementController::class);
        Route::patch('venues/{venue}/toggle-status', [VenueManagementController::class, 'toggleStatus'])->name('venues.toggle-status');

        // Artist Management
        Route::resource('artists', ArtistManagementController::class);
        Route::patch('artists/{artist}/toggle-status', [ArtistManagementController::class, 'toggleStatus'])->name('artists.toggle-status');

        // Organiser Management
        Route::resource('organisers', OrganiserManagementController::class);
        Route::patch('organisers/{organiser}/toggle-status', [OrganiserManagementController::class, 'toggleStatus'])->name('organisers.toggle-status');
    });
});

