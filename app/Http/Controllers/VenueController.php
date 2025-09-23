<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VenueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $venues = Venue::with(['owner', 'events'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

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

        // Set owner and user based on authenticated user
        $validated['user_id'] = Auth::id();
        $validated['owner_id'] = Auth::id();
        $validated['owner_type'] = Auth::user()->hasRole('artist') ? 'artist' : 'organiser';

        // Get user's folder path
        $userFolder = Auth::user()->getFolderPath();

        // Create venue-specific folder
        $venueFolder = $this->createVenueFolder($userFolder, $validated['name']);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['main_picture'] = $request->file('image')->store($venueFolder . '/images', 'public');
        }

        // Handle gallery uploads
        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store($venueFolder . '/gallery', 'public');
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
        $venue->load(['owner', 'events' => function($query) {
            $query->whereDate('date', '>=', now()->format('Y-m-d'))->orderBy('date', 'asc');
        }]);

        return view('venues.show', compact('venue'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venue $venue)
    {
        // Check if user owns this venue
        if ($venue->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('venues.edit', compact('venue'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venue $venue)
    {
        // Check if user owns this venue
        if ($venue->owner_id !== Auth::id()) {
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
            
            $validated['main_picture'] = $request->file('image')->store($venueFolder . '/images', 'public');
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
                $galleryPaths[] = $image->store($venueFolder . '/gallery', 'public');
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
        // Check if user owns this venue
        if ($venue->owner_id !== Auth::id()) {
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
     * Create a venue-specific folder structure.
     */
    private function createVenueFolder($userFolder, $venueName)
    {
        // Create a safe folder name
        $safeName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $venueName));
        $randomSuffix = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $date = now()->format('Y-m-d');
        
        $folderName = "venue_{$randomSuffix}_{$safeName}_{$date}";
        $venueFolder = $userFolder . '/venues/' . $folderName;
        
        // Create the folder structure
        Storage::disk('public')->makeDirectory($venueFolder . '/images');
        Storage::disk('public')->makeDirectory($venueFolder . '/gallery');
        Storage::disk('public')->makeDirectory($venueFolder . '/documents');
        
        return $venueFolder;
    }
}
