<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Category;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Event::with(['venue', 'artists', 'owner', 'categories'])
            ->whereIn('status', ['upcoming', 'ongoing']);

        // Default: rolling 30-day window from today
        $startDateDefault = now()->toDateString();
        $endDateDefault = now()->addDays(30)->toDateString();
        $query->whereBetween('date', [$startDateDefault, $endDateDefault]);

        // Handle search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('category', 'like', "%{$searchTerm}%")
                    ->orWhereHas('categories', function ($categoryQuery) use ($searchTerm) {
                        $categoryQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('slug', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('venue', function ($venueQuery) use ($searchTerm) {
                        $venueQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('address', 'like', "%{$searchTerm}%")
                            ->orWhere('city', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('artists', function ($artistQuery) use ($searchTerm) {
                        $artistQuery->where('stage_name', 'like', "%{$searchTerm}%")
                            ->orWhere('genre', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Handle category filter
        if ($request->filled('category')) {
            $categoryFilter = $request->category;

            $categoryRecord = Category::query()
                ->when(is_numeric($categoryFilter), function ($q) use ($categoryFilter) {
                    $q->where('id', (int) $categoryFilter);
                }, function ($q) use ($categoryFilter) {
                    $q->where('slug', $categoryFilter);
                })
                ->first();

            $resolvedSlug = $categoryRecord?->slug ?? Str::slug($categoryFilter);
            $resolvedName = $categoryRecord?->name ?? $categoryFilter;

            $query->where(function ($categoryScope) use ($categoryFilter, $resolvedSlug, $resolvedName) {
                $categoryScope->whereHas('categories', function ($categoryQuery) use ($categoryFilter, $resolvedSlug, $resolvedName) {
                    $categoryQuery->whereIn('slug', array_filter([$categoryFilter, $resolvedSlug]))
                        ->orWhere(function ($nameQuery) use ($categoryFilter, $resolvedName) {
                            $nameQuery->whereRaw('LOWER(name) = ?', [strtolower($categoryFilter)])
                                ->orWhereRaw('LOWER(name) = ?', [strtolower($resolvedName)]);
                        });
                })->orWhere(function ($legacyCategoryQuery) use ($categoryFilter, $resolvedName, $resolvedSlug) {
                    $legacyCategoryQuery->whereRaw('LOWER(category) = ?', [strtolower($categoryFilter)])
                        ->orWhereRaw('LOWER(category) = ?', [strtolower($resolvedName)])
                        ->orWhereRaw('REPLACE(LOWER(category), " ", "-") = ?', [strtolower($resolvedSlug)]);
                });
            });
        }

        // Handle date filter overrides
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $dateFrom = $request->get('date_from', $startDateDefault);
            $dateTo = $request->get('date_to', $endDateDefault);
            $query->whereBetween('date', [$dateFrom, $dateTo]);
        }

        $perPage = $request->get('per_page', 12);
        $events = $query->orderBy('date', 'asc')->paginate($perPage);

        // Handle AJAX requests - return only the results content
        if ($request->ajax()) {
            return view('events._list', compact('events'));
        }

        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $venues = Venue::all();
        $artists = Artist::all();
        $selectedVenueId = $request->get('venue_id');

        return view('events.create', compact('venues', 'artists', 'selectedVenueId'));
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
            'time' => 'required|date_format:H:i',
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
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        // Get user's folder path
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'You must be logged in to create an event.');
        }
        $userFolder = $user->getFolderPath();

        // Create event-specific folder
        $eventFolder = $this->createEventFolder($userFolder, $validated['name'], $validated['date']);

        // Handle poster upload
        if ($request->hasFile('poster')) {
            $validated['poster'] = $request->file('poster')->store($eventFolder.'/poster', 'public');
        }

        // Handle gallery uploads
        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store($eventFolder.'/gallery', 'public');
            }
            $validated['gallery'] = json_encode($galleryPaths);
        }

        // Set owner based on authenticated user
        $validated['owner_id'] = auth()->id();
        $validated['owner_type'] = auth()->user()->hasRole('artist') ? 'artist' : 'organiser';
        $validated['status'] = 'upcoming';

        $event = Event::create($validated);

        // Attach artists
        if ($request->has('artists')) {
            $event->artists()->attach($request->artists);
        }

        // Attach categories
        if ($request->has('categories')) {
            $event->categories()->attach($request->categories);
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Event created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Event $event)
    {
        $event->load(['venue', 'artists', 'owner', 'ratings.user']);

        if ($this->isSocialPreviewRequest($request)) {
            $shareData = $this->buildSocialPreviewData($event);

            return response()
                ->view('events.share-preview', compact('event', 'shareData'))
                ->header('Cache-Control', 'public, max-age=600')
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        if (! $request->user()) {
            return redirect()->route('login', ['continue' => $request->fullUrl()]);
        }

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
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'price' => 'nullable|numeric|min:0',
            'ticket_url' => 'nullable|url',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'gallery' => 'nullable|array|max:10',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'category' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'venue_id' => 'nullable|exists:venues,id',
            'artist_ids' => 'nullable|array',
            'artist_ids.*' => 'exists:artists,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        // Prepare event storage folder for any uploads
        $userFolder = auth()->user()->getFolderPath();
        $eventFolder = $this->createEventFolder($userFolder, $validated['name'], $validated['date']);

        // Handle poster upload
        if ($request->hasFile('poster')) {
            // Delete old poster if exists
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }
            $validated['poster'] = $request->file('poster')->store($eventFolder.'/poster', 'public');
        }

        // Merge new gallery images with existing ones
        if ($request->hasFile('gallery')) {
            // Normalize existing gallery to array
            $existingGallery = is_array($event->gallery)
                ? $event->gallery
                : (is_string($event->gallery) ? json_decode($event->gallery, true) : []);
            if (!is_array($existingGallery)) {
                $existingGallery = [];
            }

            foreach ($request->file('gallery') as $image) {
                $existingGallery[] = $image->store($eventFolder.'/gallery', 'public');
            }

            $validated['gallery'] = json_encode(array_values($existingGallery));
        }

        $event->update($validated);

        // Sync artists
        if ($request->has('artist_ids')) {
            $event->artists()->sync($request->artist_ids);
        } else {
            $event->artists()->detach();
        }

        // Sync categories
        if ($request->has('categories')) {
            $event->categories()->sync($request->categories);
        } else {
            $event->categories()->detach();
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
     * Generate ICS calendar file for an event.
     */
    public function calendar(Event $event)
    {
        $event->load(['venue']);

        // Create ICS content
        $icsContent = $this->generateIcsContent($event);

        $filename = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $event->name).'.ics';

        return response($icsContent)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Cache-Control', 'no-cache, must-revalidate');
    }

    private function isSocialPreviewRequest(Request $request): bool
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        $previewAgents = [
            'facebookexternalhit',
            'facebot',
            'twitterbot',
            'pinterest',
            'linkedinbot',
            'slackbot',
            'discordbot',
            'whatsapp',
            'telegrambot',
            'vkshare',
            'skypeuripreview',
        ];

        foreach ($previewAgents as $agent) {
            if ($userAgent && str_contains($userAgent, $agent)) {
                return true;
            }
        }

        return $request->boolean('share_preview');
    }

    private function buildSocialPreviewData(Event $event): array
    {
        $description = Str::limit(strip_tags($event->description ?? 'Discover live events on My Gig Guide.'), 160);

        $imageUrl = null;

        if ($event->poster) {
            $imageUrl = url(Storage::url($event->poster));
        } elseif ($event->venue && $event->venue->main_picture) {
            $imageUrl = url(Storage::url($event->venue->main_picture));
        } else {
            $imageUrl = asset('logos/logo1.jpeg');
        }

        return [
            'title' => $event->name.' - My Gig Guide',
            'description' => $description,
            'image' => $imageUrl,
            'url' => route('events.show', $event),
        ];
    }

    /**
     * Generate ICS content for an event.
     */
    private function generateIcsContent(Event $event)
    {
        $startDate = $event->date ? $event->date->format('Ymd\THis\Z') : now()->format('Ymd\THis\Z');
        $endDate = $event->date ? $event->date->addHour()->format('Ymd\THis\Z') : now()->addHour()->format('Ymd\THis\Z');
        $now = now()->format('Ymd\THis\Z');

        $description = str_replace(["\r\n", "\n", "\r"], '\\n', strip_tags($event->description ?? ''));
        $location = $event->venue ? $event->venue->name : '';
        $summary = $event->name;

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//MyGigGuide//Event Calendar//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "DTSTART:{$startDate}\r\n";
        $ics .= "DTEND:{$endDate}\r\n";
        $ics .= "DTSTAMP:{$now}\r\n";
        $ics .= 'UID:'.uniqid()."@mygigguide.co.za\r\n";
        $ics .= "SUMMARY:{$summary}\r\n";
        $ics .= "DESCRIPTION:{$description}\r\n";
        $ics .= "LOCATION:{$location}\r\n";
        $ics .= 'URL:'.route('events.show', $event)."\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "TRANSP:OPAQUE\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return $ics;
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
        $eventFolder = $userFolder.'/events/'.$folderName;

        // Create the folder structure
        \Storage::disk('public')->makeDirectory($eventFolder.'/gallery');
        \Storage::disk('public')->makeDirectory($eventFolder.'/documents');

        return $eventFolder;
    }
}
