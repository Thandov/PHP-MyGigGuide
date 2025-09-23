<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\Artist;
use App\Models\Organiser;
use App\Models\Venue;
use App\Models\Rating;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Show the appropriate dashboard based on user role.
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Redirect to role-specific dashboard
        if ($user->hasRole('artist')) {
            return $this->artistDashboard();
        } elseif ($user->hasRole('organiser')) {
            return $this->organiserDashboard();
        } elseif ($user->hasRole('venue_owner')) {
            return $this->venueOwnerDashboard();
        } elseif ($user->hasRole('admin') || $user->hasRole('superuser')) {
            return $this->adminDashboard();
        } else {
            return $this->userDashboard();
        }
    }

    /**
     * Artist Dashboard
     */
    public function artistDashboard()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $artist = $user->artist;
        
        if (!$artist) {
            // Create artist profile if it doesn't exist
            $artist = Artist::create([
                'user_id' => $user->id,
                'stage_name' => $user->name,
                'real_name' => $user->name,
                'genre' => 'Unknown',
                'bio' => 'Artist bio coming soon...',
            ]);
        }

        // Get all events where artist is involved (either as owner or performer)
        $allEvents = Event::where(function($query) use ($artist) {
            // Events owned by the artist
            $query->where(function($subQuery) use ($artist) {
                $subQuery->where('owner_type', 'artist')
                        ->where('owner_id', $artist->id);
            })
            // OR events where artist is performing
            ->orWhereHas('artists', function($artistQuery) use ($artist) {
                $artistQuery->where('artist_id', $artist->id);
            });
        })
        ->with(['venue', 'artists', 'owner'])
        ->orderBy('date', 'desc')
        ->get();

        // Get upcoming events (scheduled and future dates)
        $upcomingEvents = $allEvents->filter(function($event) {
            return $event->status === 'scheduled' && $event->date >= now();
        })->sortBy('date')->take(5);

        // Get past events
        $pastEvents = $allEvents->filter(function($event) {
            return $event->status === 'completed' || $event->date < now();
        })->sortByDesc('date')->take(5);

        // Get venues owned by the user (not the artist profile)
        $venues = Venue::where('user_id', $user->id)
            ->get();

        // Get ratings for the artist
        $ratings = Rating::where('rateable_type', 'artist')
            ->where('rateable_id', $artist->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $stats = [
            'total_events' => $allEvents->count(),
            'upcoming_events' => $upcomingEvents->count(),
            'venues_owned' => $venues->count(),
            'average_rating' => $ratings->avg('rating') ?? 0,
            'total_ratings' => $ratings->count(),
        ];

        return view('dashboard.artist', compact('artist', 'upcomingEvents', 'pastEvents', 'venues', 'ratings', 'stats'));
    }

    /**
     * Organiser Dashboard
     */
    public function organiserDashboard()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $organiser = $user->organiser;
        
        if (!$organiser) {
            // Create organiser profile if it doesn't exist
            $organiser = Organiser::create([
                'user_id' => $user->id,
                'organisation_name' => $user->name . ' Events',
                'contact_email' => $user->email,
                'description' => 'Event organiser profile coming soon...',
            ]);
        }

        // Get all organiser's events
        $allEvents = Event::where('owner_type', 'organiser')
            ->where('owner_id', $organiser->id)
            ->with(['venue', 'artists', 'owner'])
            ->orderBy('date', 'desc')
            ->get();

        // Get upcoming events (scheduled and future dates)
        $upcomingEvents = $allEvents->filter(function($event) {
            return $event->status === 'scheduled' && $event->date >= now();
        })->sortBy('date')->take(5);

        // Get past events
        $pastEvents = $allEvents->filter(function($event) {
            return $event->status === 'completed' || $event->date < now();
        })->sortByDesc('date')->take(5);

        // Get venues owned by the user (not the organiser profile)
        $venues = Venue::where('user_id', $user->id)
            ->get();

        // Get ratings for the organiser
        $ratings = Rating::where('rateable_type', 'organiser')
            ->where('rateable_id', $organiser->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $stats = [
            'total_events' => $allEvents->count(),
            'upcoming_events' => $upcomingEvents->count(),
            'venues_owned' => $venues->count(),
            'average_rating' => $ratings->avg('rating') ?? 0,
            'total_ratings' => $ratings->count(),
        ];

        return view('dashboard.organiser', compact('organiser', 'upcomingEvents', 'pastEvents', 'venues', 'ratings', 'stats'));
    }

    /**
     * Venue Owner Dashboard
     */
    public function venueOwnerDashboard()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Get venue owner's venues
        $venues = Venue::where('user_id', $user->id)->get();

        // Get events at these venues
        $venueIds = $venues->pluck('id');
        $upcomingEvents = Event::whereIn('venue_id', $venueIds)
            ->where('status', 'scheduled')
            ->whereDate('date', '>=', now()->format('Y-m-d'))
            ->with(['venue', 'artists', 'owner'])
            ->orderBy('date', 'asc')
            ->get();

        $pastEvents = Event::whereIn('venue_id', $venueIds)
            ->where('status', 'completed')
            ->with(['venue', 'artists', 'owner'])
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // Get ratings for venues
        $ratings = Rating::where('rateable_type', 'venue')
            ->whereIn('rateable_id', $venueIds)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $stats = [
            'total_venues' => $venues->count(),
            'total_events' => $upcomingEvents->count() + $pastEvents->count(),
            'upcoming_events' => $upcomingEvents->count(),
            'average_rating' => $ratings->avg('rating') ?? 0,
            'total_ratings' => $ratings->count(),
        ];

        return view('dashboard.venue-owner', compact('venues', 'upcomingEvents', 'pastEvents', 'ratings', 'stats'));
    }

    /**
     * Admin Dashboard
     */
    public function adminDashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_artists' => Artist::count(),
            'total_organisers' => Organiser::count(),
            'total_venues' => Venue::count(),
            'total_events' => Event::count(),
            'upcoming_events' => Event::where('status', 'scheduled')
                ->whereDate('date', '>=', now()->format('Y-m-d'))
                ->count(),
        ];

        $recentEvents = Event::with(['venue', 'artists', 'owner'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentUsers = User::with('roles')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.admin', compact('stats', 'recentEvents', 'recentUsers'));
    }

    /**
     * Regular User Dashboard
     */
    public function userDashboard()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Get user's favorite events
        $favoriteEvents = Event::whereHas('favoritedBy', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['venue', 'artists', 'owner'])
        ->orderBy('date', 'asc')
        ->get();

        // Get upcoming events near user (sample - would need location logic)
        $upcomingEvents = Event::where('status', 'scheduled')
            ->whereDate('date', '>=', now()->format('Y-m-d'))
            ->with(['venue', 'artists', 'owner'])
            ->orderBy('date', 'asc')
            ->limit(6)
            ->get();

        // Get user's ratings
        $userRatings = Rating::where('user_id', $user->id)
            ->with('rateable')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $stats = [
            'favorite_events' => $favoriteEvents->count(),
            'upcoming_events' => $upcomingEvents->count(),
            'ratings_given' => $userRatings->count(),
        ];

        return view('dashboard.user', compact('favoriteEvents', 'upcomingEvents', 'userRatings', 'stats'));
    }
}
