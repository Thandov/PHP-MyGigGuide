<?php

use App\Http\Controllers\Admin\ArtistManagementController;
use App\Http\Controllers\Admin\ArtistClaimDisputeController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryManagementController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventManagementController;
use App\Http\Controllers\Admin\GenreManagementController;
use App\Http\Controllers\Admin\OrganiserManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VenueManagementController;
use App\Http\Controllers\Admin\PaidFeatureController;
use App\Http\Controllers\Admin\FeatureProgramController;
use App\Http\Controllers\Admin\FeaturePackageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UnclaimedArtistController;

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
        Route::get('/api/users/search', [UserManagementController::class, 'search'])->name('api.users.search');

        // Email verification management
        Route::patch('users/{user}/verify-email', [UserManagementController::class, 'verifyEmail'])->name('users.verify-email');
        Route::patch('users/{user}/unverify-email', [UserManagementController::class, 'unverifyEmail'])->name('users.unverify-email');
        Route::patch('users/{user}/update-email', [UserManagementController::class, 'updateEmail'])->name('users.update-email');

        // Event Management
        Route::resource('events', EventManagementController::class);
        Route::patch('events/{event}/toggle-status', [EventManagementController::class, 'toggleStatus'])->name('events.toggle-status');

        // Venue Management
        Route::resource('venues', VenueManagementController::class);
        Route::patch('venues/{venue}/toggle-status', [VenueManagementController::class, 'toggleStatus'])->name('venues.toggle-status');
        Route::post('venues/import', [VenueManagementController::class, 'importVenues'])->name('venues.import');
        Route::post('venues/bulk-action', [VenueManagementController::class, 'bulkAction'])->name('venues.bulk-action');
        // Some hosts block HTTP DELETE; provide POST fallback for destroy
        Route::post('venues/{venue}/delete', [VenueManagementController::class, 'destroy'])->name('venues.destroy.post');

        // Artist Management
        Route::resource('artists', ArtistManagementController::class);
        Route::patch('artists/{artist}/toggle-status', [ArtistManagementController::class, 'toggleStatus'])->name('artists.toggle-status');

        // Unclaimed Artists
        Route::get('unclaimed-artists', [UnclaimedArtistController::class, 'index'])->name('unclaimed-artists.index');
        Route::get('unclaimed-artists/{artist}/edit', [UnclaimedArtistController::class, 'edit'])->name('unclaimed-artists.edit');
        Route::put('unclaimed-artists/{artist}', [UnclaimedArtistController::class, 'update'])->name('unclaimed-artists.update');

        // Artist Claim Disputes
        Route::get('artist-disputes', [ArtistClaimDisputeController::class, 'index'])->name('artist-disputes.index');
        Route::get('artist-disputes/{artist}', [ArtistClaimDisputeController::class, 'show'])->name('artist-disputes.show');
        Route::post('artist-disputes/{artist}/approve', [ArtistClaimDisputeController::class, 'approve'])->name('artist-disputes.approve');
        Route::post('artist-disputes/{artist}/reject', [ArtistClaimDisputeController::class, 'reject'])->name('artist-disputes.reject');
        Route::post('artist-disputes/{artist}/clear-dispute', [ArtistClaimDisputeController::class, 'clearDispute'])->name('artist-disputes.clear-dispute');

        // Organiser Management
        Route::resource('organisers', OrganiserManagementController::class);
        Route::patch('organisers/{organiser}/toggle-status', [OrganiserManagementController::class, 'toggleStatus'])->name('organisers.toggle-status');

        // Genre Management
        Route::resource('genres', GenreManagementController::class);
        Route::patch('genres/{genre}/toggle-status', [GenreManagementController::class, 'toggleStatus'])->name('genres.toggle-status');

        // Category Management
        Route::resource('categories', CategoryManagementController::class);
        Route::patch('categories/{category}/toggle-status', [CategoryManagementController::class, 'toggleStatus'])->name('categories.toggle-status');

        // Paid Features CRUD
        Route::resource('paid-features', PaidFeatureController::class)->parameters([
            'paid-features' => 'paidFeature',
        ]);

        // Feature Programs
        Route::resource('feature-programs', FeatureProgramController::class)->parameters([
            'feature-programs' => 'featureProgram',
        ]);

        // Feature Packages nested under a Paid Feature
        Route::prefix('paid-features/{paidFeature}')->group(function () {
            Route::get('packages', [FeaturePackageController::class, 'index'])->name('feature-packages.index');
            Route::get('packages/create', [FeaturePackageController::class, 'create'])->name('feature-packages.create');
            Route::post('packages', [FeaturePackageController::class, 'store'])->name('feature-packages.store');
            Route::get('packages/{featurePackage}/edit', [FeaturePackageController::class, 'edit'])->name('feature-packages.edit');
            Route::put('packages/{featurePackage}', [FeaturePackageController::class, 'update'])->name('feature-packages.update');
            Route::delete('packages/{featurePackage}', [FeaturePackageController::class, 'destroy'])->name('feature-packages.destroy');
        });
    });
});
