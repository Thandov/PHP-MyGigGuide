<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Venue;
use App\Models\Artist;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Event::with(['venue', 'artists', 'owner'])
            ->where('status', 'scheduled');

        // Handle search
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('category', 'like', "%{$searchTerm}%")
                  ->orWhereHas('venue', function($venueQuery) use ($searchTerm) {
                      $venueQuery->where('name', 'like', "%{$searchTerm}%")
                                ->orWhere('location', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('artists', function($artistQuery) use ($searchTerm) {
                      $artistQuery->where('stage_name', 'like', "%{$searchTerm}%")
                                 ->orWhere('genre', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $events = $query->orderBy('date', 'asc')->paginate(12);

        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $venues = Venue::all();
        $artists = Artist::all();
        
        return view('events.create', compact('venues', 'artists'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required',
            'price' => 'nullable|numeric|min:0',
            'ticket_url' => 'nullable|url',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
            'gallery' => 'nullable|array|max:10', // Max 10 images
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'category' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'venue_id' => 'required|exists:venues,id',
            'artists' => 'nullable|array',
            'artists.*' => 'exists:artists,id',
        ]);

        // Get user's folder path
        $userFolder = auth()->user()->getFolderPath();

        // Create event-specific folder
        $eventFolder = $this->createEventFolder($userFolder, $validated['name'], $validated['date']);

        // Handle poster upload
        if ($request->hasFile('poster')) {
            $validated['poster'] = $request->file('poster')->store($eventFolder . '/poster', 'public');
        }

        // Handle gallery uploads
        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store($eventFolder . '/gallery', 'public');
            }
            $validated['gallery'] = json_encode($galleryPaths);
        }

        // Set owner based on authenticated user
        $validated['owner_id'] = auth()->id();
        $validated['owner_type'] = auth()->user()->hasRole('artist') ? 'artist' : 'organiser';
        $validated['status'] = 'scheduled';

        $event = Event::create($validated);

        // Attach artists
        if ($request->has('artists')) {
            $event->artists()->attach($request->artists);
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Event created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load(['venue', 'artists', 'owner', 'ratings.user']);
        
        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $venues = Venue::all();
        $artists = Artist::all();
        
        return view('events.edit', compact('event', 'venues', 'artists'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'required',
            'price' => 'nullable|numeric|min:0',
            'ticket_url' => 'nullable|url',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'venue_id' => 'nullable|exists:venues,id',
            'artist_ids' => 'nullable|array',
            'artist_ids.*' => 'exists:artists,id',
        ]);

        // Handle poster upload
        if ($request->hasFile('poster')) {
            // Delete old poster if exists
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }
            // Get user's folder path and create event folder
            $userFolder = auth()->user()->getFolderPath();
            $eventFolder = $this->createEventFolder($userFolder, $validated['name'], $validated['date']);
            $validated['poster'] = $request->file('poster')->store($eventFolder . '/poster', 'public');
        }

        $event->update($validated);

        // Sync artists
        if ($request->has('artist_ids')) {
            $event->artists()->sync($request->artist_ids);
        } else {
            $event->artists()->detach();
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Event updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        
        return redirect()->route('events.index')
            ->with('success', 'Event deleted successfully!');
    }

    /**
     * Show the rating form for an event.
     */
    public function rate(Event $event)
    {
        return view('events.rate', compact('event'));
    }

    /**
     * Create a unique folder for an event.
     */
    private function createEventFolder($userFolder, $eventName, $eventDate)
    {
        // Create a safe folder name
        $safeName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $eventName));
        $date = \Carbon\Carbon::parse($eventDate)->format('Y-m-d');
        $randomSuffix = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        
        $folderName = "event_{$randomSuffix}_{$safeName}_{$date}";
        $eventFolder = $userFolder . '/events/' . $folderName;
        
        // Create the folder structure
        \Storage::disk('public')->makeDirectory($eventFolder . '/gallery');
        \Storage::disk('public')->makeDirectory($eventFolder . '/documents');
        
        return $eventFolder;
    }
}
