<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class VenueController extends Controller
{
    /**
     * Search venues for the venue selector component
     */
    public function search(Request $request)
    {
        $query = Venue::with(['owner']);

        // Handle search term
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('address', 'like', "%{$searchTerm}%")
                    ->orWhere('city', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Handle location filter
        if ($request->filled('location')) {
            $query->where('city', 'like', "%{$request->location}%");
        }

        // Handle capacity filters
        if ($request->filled('capacity_min')) {
            $query->where('capacity', '>=', $request->capacity_min);
        }
        if ($request->filled('capacity_max')) {
            $query->where('capacity', '<=', $request->capacity_max);
        }

        // Get user role and ID for smart ordering
        $userRole = $request->get('user_role', 'all');
        $organiserId = $request->get('organiser_id');
        $artistId = $request->get('artist_id');

        // Smart ordering based on user role
        if ($userRole === 'organiser' && $organiserId) {
            // Show own venues first, then others
            $query->orderByRaw("CASE WHEN owner_id = ? AND owner_type = 'organiser' THEN 0 ELSE 1 END", [$organiserId]);
        } elseif ($userRole === 'artist' && $artistId) {
            // Show venues where artist has performed, then others
            $query->orderByRaw("CASE WHEN id IN (SELECT DISTINCT venue_id FROM event_artists ea JOIN events e ON ea.event_id = e.id WHERE ea.artist_id = ?) THEN 0 ELSE 1 END", [$artistId]);
        }

        // Default ordering
        $query->orderBy('name');

        // Pagination
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $offset = ($page - 1) * $limit;

        $total = $query->count();
        $venues = $query->offset($offset)->limit($limit)->get();

        // Add ownership information
        $venues = $venues->map(function ($venue) use ($userRole, $organiserId, $artistId) {
            $venue->isOwnVenue = false;
            
            if ($userRole === 'organiser' && $organiserId && $venue->owner_id == $organiserId && $venue->owner_type === 'organiser') {
                $venue->isOwnVenue = true;
            } elseif ($userRole === 'artist' && $artistId && $venue->owner_id == $artistId && $venue->owner_type === 'artist') {
                $venue->isOwnVenue = true;
            }

            return $venue;
        });

        return response()->json([
            'venues' => $venues,
            'pagination' => [
                'page' => (int) $page,
                'limit' => (int) $limit,
                'total' => $total,
                'totalPages' => ceil($total / $limit)
            ]
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Venue::with(['owner', 'events']);

        // Handle search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('address', 'like', "%{$searchTerm}%")
                    ->orWhere('city', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Handle capacity filter
        if ($request->filled('capacity_filter')) {
            switch ($request->capacity_filter) {
                case 'large':
                    $query->where('capacity', '>=', 500);
                    break;
                case 'medium':
                    $query->whereBetween('capacity', [100, 499]);
                    break;
                case 'small':
                    $query->where('capacity', '<', 100);
                    break;
            }
        }

        // Handle sort
        $sortBy = $request->get('sort', 'newest');
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'capacity':
                $query->orderBy('capacity', 'desc');
                break;
            case 'events':
                $query->withCount('events')->orderBy('events_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = $request->get('per_page', 12);
        $venues = $query->paginate($perPage);

        // Handle AJAX requests - return only the results content
        if ($request->ajax()) {
            return view('venues._list', compact('venues'));
        }

        return view('venues.index', compact('venues'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('venues.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'gallery' => 'nullable|array|max:10', // Max 10 images
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:255',
        ]);

        // Ensure contact_email is not null for DB constraint
        if (empty($validated['contact_email'])) {
            $base = Str::slug($validated['name'] ?? 'venue');
            $validated['contact_email'] = $base.'+'.(Auth::id() ?? 'guest').'-'.time().'@example.local';
        }

        // Set owner and user based on authentication status
        if (Auth::check()) {
            $validated['user_id'] = Auth::id();
            $validated['owner_id'] = Auth::id();
            $validated['owner_type'] = Auth::user()->hasRole('artist') ? 'artist' : 'organiser';
            
            // Get user's folder path for authenticated users
            $userFolder = Auth::user()->getFolderPath();
            $venueFolder = $this->createVenueFolder($userFolder, $validated['name']);
        } else {
            // For unauthenticated users, set default values
            $validated['user_id'] = null;
            $validated['owner_id'] = null;
            $validated['owner_type'] = 'guest';
            
            // Create a default folder structure for guest users
            $venueFolder = 'public/guest_venues/' . Str::slug($validated['name']) . '_' . time();
            Storage::disk('public')->makeDirectory($venueFolder.'/images');
            Storage::disk('public')->makeDirectory($venueFolder.'/gallery');
            Storage::disk('public')->makeDirectory($venueFolder.'/documents');
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['main_picture'] = $request->file('image')->store($venueFolder.'/images', 'public');
        }

        // Handle gallery uploads
        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store($venueFolder.'/gallery', 'public');
            }
            $validated['venue_gallery'] = json_encode($galleryPaths);
        }

        // Convert amenities array to JSON
        if (isset($validated['amenities'])) {
            $validated['amenities'] = json_encode($validated['amenities']);
        }

        $venue = Venue::create($validated);

        return redirect()->route('venues.show', $venue)
            ->with('success', 'Venue created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Venue $venue)
    {
        $venue->load(['owner', 'events' => function ($query) {
            $query->orderBy('date', 'asc');
        }]);

        // Normalize gallery to array for the view
        $gallery = [];
        if (is_array($venue->venue_gallery)) {
            $gallery = $venue->venue_gallery;
        } elseif (is_string($venue->venue_gallery) && ! empty($venue->venue_gallery)) {
            $parsed = json_decode($venue->venue_gallery, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                $gallery = $parsed;
            } else {
                $gallery = array_filter(array_map('trim', explode(',', $venue->venue_gallery)));
            }
        }

        $upcomingEvents = $venue->events
            ->filter(function ($e) {
                return $e->date && $e->date >= now();
            })
            ->values();

        $ratingAvg = round((float) ($venue->ratings()->avg('rating') ?? 0), 1);

        return view('venues.show', [
            'venue' => $venue,
            'gallery' => $gallery,
            'upcomingEvents' => $upcomingEvents,
            'ratingAvg' => $ratingAvg,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venue $venue)
    {
        // Authorize: admin/superuser OR creator OR linked owner user
        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole(['admin', 'superuser']);

        $ownsViaCreator = $venue->user_id === Auth::id();

        $ownsViaOwner = false;
        if ($venue->owner_id && $venue->owner_type) {
            // If the owner is a User record
            if ($venue->owner_type === \App\Models\User::class) {
                $ownsViaOwner = $venue->owner_id === Auth::id();
            } else {
                // Owner might be Artist or Organiser models that have a user_id
                $ownerModel = $venue->owner; // polymorphic relation
                if ($ownerModel && isset($ownerModel->user_id)) {
                    $ownsViaOwner = (int) $ownerModel->user_id === (int) Auth::id();
                }
            }
        }

        if (! $isAdmin && ! $ownsViaCreator && ! $ownsViaOwner) {
            abort(403, 'Unauthorized');
        }

        return view('venues.edit', compact('venue'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venue $venue)
    {
        // Authorize: admin/superuser OR creator OR linked owner user
        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole(['admin', 'superuser']);
        $ownsViaCreator = $venue->user_id === Auth::id();
        $ownsViaOwner = false;
        if ($venue->owner_id && $venue->owner_type) {
            if ($venue->owner_type === \App\Models\User::class) {
                $ownsViaOwner = $venue->owner_id === Auth::id();
            } else {
                $ownerModel = $venue->owner;
                if ($ownerModel && isset($ownerModel->user_id)) {
                    $ownsViaOwner = (int) $ownerModel->user_id === (int) Auth::id();
                }
            }
        }
        if (! $isAdmin && ! $ownsViaCreator && ! $ownsViaOwner) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'gallery' => 'nullable|array|max:10', // Max 10 images
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:255',
        ]);

        // Ensure contact_email is not null for DB constraint
        if (empty($validated['contact_email'])) {
            $base = Str::slug($validated['name'] ?? 'venue');
            $validated['contact_email'] = $base.'+'.(Auth::id() ?? 'sys').'-'.time().'@example.local';
        }

        // Get user's folder path
        $userFolder = Auth::user()->getFolderPath();

        // Create venue-specific folder
        $venueFolder = $this->createVenueFolder($userFolder, $validated['name']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($venue->main_picture) {
                Storage::disk('public')->delete($venue->main_picture);
            }

            $validated['main_picture'] = $request->file('image')->store($venueFolder.'/images', 'public');
        }

        // Handle gallery uploads
        if ($request->hasFile('gallery')) {
            // Delete old gallery images if they exist
            if ($venue->venue_gallery) {
                foreach ($venue->venue_gallery as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $galleryPaths = [];
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store($venueFolder.'/gallery', 'public');
            }
            $validated['venue_gallery'] = json_encode($galleryPaths);
        }

        // Convert amenities array to JSON
        if (isset($validated['amenities'])) {
            $validated['amenities'] = json_encode($validated['amenities']);
        }

        $venue->update($validated);

        return redirect()->route('venues.show', $venue)
            ->with('success', 'Venue updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venue $venue)
    {
        // Check if user owns this venue or is admin/superuser
        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole(['admin', 'superuser']);
        if (! $isAdmin && $venue->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Delete image if exists
        if ($venue->main_picture) {
            Storage::disk('public')->delete($venue->main_picture);
        }

        $venue->delete();

        return redirect()->route('venues.index')
            ->with('success', 'Venue deleted successfully!');
    }

    /**
     * Quick create a venue with minimal information
     */
    public function quickStore(Request $request)
    {
        try {
            // Normalize optional fields so empty strings don't fail validation
            $payload = $request->all();
            if (isset($payload['capacity']) && $payload['capacity'] === '') {
                unset($payload['capacity']);
            }
            if (isset($payload['phone']) && $payload['phone'] === '') {
                unset($payload['phone']);
            }

            $validated = validator($payload, [
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'capacity' => 'sometimes|integer|min:1',
                'phone' => 'sometimes|string|max:20',
            ])->validate();

            // Ensure contact_email is not null for DB constraint
            $base = Str::slug($validated['name'] ?? 'venue');
            $validated['contact_email'] = $base.'+'.(Auth::id() ?? 'guest').'-'.time().'@example.local';

            // Set owner and user based on authentication status
            if (Auth::check()) {
                $validated['user_id'] = Auth::id();
                $validated['owner_id'] = Auth::id();
                $validated['owner_type'] = Auth::user()->hasRole('artist') ? 'artist' : 'organiser';
            } else {
                $validated['user_id'] = null;
                $validated['owner_id'] = null;
                $validated['owner_type'] = 'guest';
            }

            // Map phone -> phone_number DB column if provided
            if (isset($validated['phone'])) {
                $validated['phone_number'] = $validated['phone'];
                unset($validated['phone']);
            }

            $venue = Venue::create($validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'id' => $venue->id,
                    'name' => $venue->name,
                    'address' => $venue->address,
                    'capacity' => $venue->capacity,
                    'latitude' => $venue->latitude,
                    'longitude' => $venue->longitude,
                ]);
            }

            return back()->with('success', 'Venue created');
        } catch (ValidationException $e) {
            Log::warning('Quick venue validation failed', [
                'errors' => $e->errors(),
                'payload' => $request->all(),
                'user_id' => Auth::id(),
            ]);
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            Log::error('Quick venue create failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
                'user_id' => Auth::id(),
            ]);
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Server error creating venue',
                ], 500);
            }
            return back()->with('error', 'Server error creating venue');
        }
    }

    /**
     * Create a venue-specific folder structure.
     */
    private function createVenueFolder($userFolder, $venueName)
    {
        // Create a safe folder name
        $safeName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $venueName));
        $randomSuffix = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $date = now()->format('Y-m-d');

        $folderName = "venue_{$randomSuffix}_{$safeName}_{$date}";
        $venueFolder = $userFolder.'/venues/'.$folderName;

        // Create the folder structure
        Storage::disk('public')->makeDirectory($venueFolder.'/images');
        Storage::disk('public')->makeDirectory($venueFolder.'/gallery');
        Storage::disk('public')->makeDirectory($venueFolder.'/documents');

        return $venueFolder;
    }
}
