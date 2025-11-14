<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrganiserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\FeaturePurchaseController;
use App\Http\Controllers\VerificationController;

// Public venue creation routes (MUST come before admin routes)
Route::get('/venues/create', [VenueController::class, 'create'])->name('venues.create');
Route::post('/venues', [VenueController::class, 'store'])->name('venues.store');
Route::post('/venues/quick', [VenueController::class, 'quickStore'])->name('venues.quick-store');
Route::get('/api/venues/search', [VenueController::class, 'search'])->name('api.venues.search');

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/test-auth', function () {
    return view('test-auth');
})->name('test-auth');
// About page
Route::view('/about', 'about')->name('about');

// Legal pages
Route::view('/popia', 'popia')->name('popia');

// Venue selector demo
Route::view('/venue-selector-demo', 'venue-selector-demo')->name('venue-selector-demo');

// User selector demo
Route::view('/user-selector-demo', 'user-selector-demo')->name('user-selector-demo');

// Contact routes
Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contact/submit', [ContactController::class, 'submit'])->name('contact.submit');

// Protected routes (require login)
Route::middleware('auth')->group(function () {
    // Event routes
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::get('/events/{event}/rate', [EventController::class, 'rate'])->name('events.rate');
    Route::get('/events/{event}/calendar', [EventController::class, 'calendar'])->name('events.calendar');
});

Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Artist listing and show routes (require login)
Route::middleware('auth')->group(function () {
    Route::get('/artists', [ArtistController::class, 'index'])->name('artists.index');
    Route::get('/artists/{artist}', [ArtistController::class, 'show'])->name('artists.show');
    Route::get('/artists/{artist}/dispute', [ArtistController::class, 'dispute'])->name('artist.dispute');
});
Route::get('/venues/{venue}', [VenueController::class, 'show'])->name('venues.show');
Route::get('/organisers/{organiser}', [OrganiserController::class, 'show'])->name('organisers.show');

// Protected routes (require login)
Route::middleware('auth')->group(function () {
    // Artist routes
    Route::get('/artists/create', [ArtistController::class, 'create'])->name('artists.create');
    Route::post('/artists', [ArtistController::class, 'store'])->name('artists.store');
    Route::get('/artists/{artist}/edit', [ArtistController::class, 'edit'])->name('artists.edit');
    Route::put('/artists/{artist}', [ArtistController::class, 'update'])->name('artists.update');
    Route::delete('/artists/{artist}', [ArtistController::class, 'destroy'])->name('artists.destroy');
    Route::post('/artists/quick', [ArtistController::class, 'quickStore'])->name('artists.quick-store');

    // Venue routes (protected - require login for management)
    Route::get('/venues', [VenueController::class, 'index'])->name('venues.index');
    Route::get('/venues/{venue}/edit', [VenueController::class, 'edit'])->name('venues.edit');
    Route::put('/venues/{venue}', [VenueController::class, 'update'])->name('venues.update');
    Route::delete('/venues/{venue}', [VenueController::class, 'destroy'])->name('venues.destroy');

    // Organiser routes
    Route::get('/organisers', [OrganiserController::class, 'index'])->name('organisers.index');
    Route::get('/organisers/create', [OrganiserController::class, 'create'])->name('organisers.create');
    Route::post('/organisers', [OrganiserController::class, 'store'])->name('organisers.store');
    Route::get('/organisers/{organiser}/edit', [OrganiserController::class, 'edit'])->name('organisers.edit');
    Route::put('/organisers/{organiser}', [OrganiserController::class, 'update'])->name('organisers.update');
    Route::delete('/organisers/{organiser}', [OrganiserController::class, 'destroy'])->name('organisers.destroy');

    // Rating routes
    Route::resource('ratings', RatingController::class);
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Convenience route for Venue Owner registration (SEO/links)
Route::get('/venue-owner/register', function () {
    // redirect to the standard registration page with role preselected
    return redirect()->route('register', ['role' => 'venue_owner']);
})->name('venue-owner.register');

// Email verification routes
Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Account activation routes
Route::get('/activate/{user}/{token}', [App\Http\Controllers\ActivationController::class, 'activate'])->name('activation.activate');
Route::post('/resend-activation', [App\Http\Controllers\ActivationController::class, 'resendActivation'])->name('activation.resend');
Route::get('/activation-required', function () {
    return view('auth.activation-required');
})->name('activation.required');

// Protected routes (require login)
Route::middleware('auth')->group(function () {
    // Map pages
    Route::get('/map', [HomeController::class, 'map'])->name('map');
    Route::get('/test-map', [HomeController::class, 'testMap'])->name('test-map');
    
    // Paid feature purchase flow
    Route::get('/boost/checkout', [FeaturePurchaseController::class, 'create'])->name('features.checkout');
    Route::post('/boost/purchase', [FeaturePurchaseController::class, 'store'])->name('features.purchase');
    
    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/artist', [DashboardController::class, 'artistDashboard'])->name('dashboard.artist');
    Route::get('/dashboard/organiser', [DashboardController::class, 'organiserDashboard'])->name('dashboard.organiser');
    Route::get('/dashboard/venue-owner', [DashboardController::class, 'venueOwnerDashboard'])->name('dashboard.venue-owner');
    Route::get('/dashboard/admin', [DashboardController::class, 'adminDashboard'])->name('dashboard.admin');
    Route::get('/dashboard/user', [DashboardController::class, 'userDashboard'])->name('dashboard.user');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Favorite routes (toggle favorites with AJAX)
    Route::post('/favorites/events/{event}/toggle', [FavoriteController::class, 'toggleEvent'])->name('favorites.events.toggle');
    Route::post('/favorites/venues/{venue}/toggle', [FavoriteController::class, 'toggleVenue'])->name('favorites.venues.toggle');
    Route::post('/favorites/artists/{artist}/toggle', [FavoriteController::class, 'toggleArtist'])->name('favorites.artists.toggle');
    Route::post('/favorites/organisers/{organiser}/toggle', [FavoriteController::class, 'toggleOrganiser'])->name('favorites.organisers.toggle');
    Route::get('/favorites/check', [FavoriteController::class, 'checkFavorites'])->name('favorites.check');

    // Rating routes
    Route::post('/ratings', [App\Http\Controllers\RatingController::class, 'store'])->name('ratings.store');
    Route::post('/reviews/load-more', [App\Http\Controllers\RatingController::class, 'loadMoreReviews'])->name('reviews.load-more');
});

// API routes for testing
Route::get('/api/user', function () {
    return response()->json([
        'authenticated' => Auth::check(),
        'user' => Auth::check() ? Auth::user() : null,
    ]);
});

// Temporary migration route - REMOVE AFTER USE
Route::get('/run-migrations', function () {
    try {
        // Test database connection
        $pdo = new PDO('mysql:host=localhost;port=3306;dbname=ecotribe_mygigguide;charset=utf8mb4', 'ecotribe_08600', 'p0QX(6S!17', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $result = ['success' => true, 'messages' => []];
        $result['messages'][] = '✅ Database connection successful!';

        // Check current tables
        $stmt = $pdo->query('SHOW TABLES');
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $result['messages'][] = 'Current tables: '.count($tables);

        // Run migrations
        $output = [];
        $returnCode = 0;
        exec('php artisan migrate --force 2>&1', $output, $returnCode);

        $result['migration_output'] = implode("\n", $output);
        $result['migration_success'] = $returnCode === 0;

        if ($returnCode === 0) {
            $result['messages'][] = '✅ Migrations completed successfully!';

            // Verify tables
            $stmt = $pdo->query('SHOW TABLES');
            $newTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $result['messages'][] = 'Tables after migration: '.count($newTables);

            // Check specific Laravel tables
            $laravelTables = ['users', 'migrations', 'venues', 'artists', 'events', 'organisers', 'ratings'];
            $result['table_status'] = [];
            foreach ($laravelTables as $table) {
                $result['table_status'][$table] = in_array($table, $newTables);
            }

        } else {
            $result['messages'][] = '❌ Migrations failed with return code: '.$returnCode;
        }

        return response()->json($result);

    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ]);
    }
});

// Include admin routes (MUST come after public routes)
require __DIR__.'/admin.php';
