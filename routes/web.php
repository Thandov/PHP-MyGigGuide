<?php

use Illuminate\Support\Facades\Route;

// Include admin routes
require __DIR__.'/admin.php';
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\OrganiserController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/map', [HomeController::class, 'map'])->name('map');
Route::get('/test-map', [HomeController::class, 'testMap'])->name('test-map');
Route::get('/test-auth', function() { return view('test-auth'); })->name('test-auth');

// Event routes
Route::resource('events', EventController::class);
Route::get('/events/{event}/rate', [EventController::class, 'rate'])->name('events.rate');

// Artist routes
Route::resource('artists', ArtistController::class);

// Venue routes
Route::resource('venues', VenueController::class);

// Organiser routes
Route::resource('organisers', OrganiserController::class);

// Rating routes
Route::resource('ratings', RatingController::class);

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard routes (protected)
Route::middleware('auth')->group(function () {
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
});

// API routes for testing
Route::get('/api/user', function() {
    return response()->json([
        'authenticated' => Auth::check(),
        'user' => Auth::check() ? Auth::user() : null
    ]);
});

// Temporary migration route - REMOVE AFTER USE
Route::get('/run-migrations', function() {
    try {
        // Test database connection
        $pdo = new PDO('mysql:host=localhost;port=3306;dbname=ecotribe_mygigguide;charset=utf8mb4', 'ecotribe_08600', 'p0QX(6S!17', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        $result = ['success' => true, 'messages' => []];
        $result['messages'][] = '✅ Database connection successful!';
        
        // Check current tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $result['messages'][] = 'Current tables: ' . count($tables);
        
        // Run migrations
        $output = [];
        $returnCode = 0;
        exec('php artisan migrate --force 2>&1', $output, $returnCode);
        
        $result['migration_output'] = implode("\n", $output);
        $result['migration_success'] = $returnCode === 0;
        
        if ($returnCode === 0) {
            $result['messages'][] = '✅ Migrations completed successfully!';
            
            // Verify tables
            $stmt = $pdo->query("SHOW TABLES");
            $newTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $result['messages'][] = 'Tables after migration: ' . count($newTables);
            
            // Check specific Laravel tables
            $laravelTables = ['users', 'migrations', 'venues', 'artists', 'events', 'organisers', 'ratings'];
            $result['table_status'] = [];
            foreach ($laravelTables as $table) {
                $result['table_status'][$table] = in_array($table, $newTables);
            }
            
        } else {
            $result['messages'][] = '❌ Migrations failed with return code: ' . $returnCode;
        }
        
        return response()->json($result);
        
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});
