<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class EventManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with(['venue', 'owner']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $events = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        // Get all users with their roles for creating events on behalf of them
        $users = User::with('roles')->get()->map(function($user) {
            $user->role_name = $user->roles->first()->name ?? 'user';
            return $user;
        });
        
        $venues = Venue::all();
        
        return view('admin.events.create', compact('users', 'venues'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date|after:today',
            'time' => 'required|date_format:H:i',
            'venue_id' => 'required|exists:venues,id',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
            'user_id' => 'required|exists:users,id',
            'capacity' => 'nullable|integer|min:1',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'gallery' => 'nullable|array|max:10',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Get the user to create event on behalf of
        $user = User::find($request->user_id);
        $userRole = $user->roles->first()->name ?? 'user';

        // Create event folder structure
        $userFolder = $user->getFolderPath();
        $eventFolder = $this->createEventFolder($userFolder, $request->name);

        // Handle poster upload
        $posterPath = null;
        if ($request->hasFile('poster')) {
            $posterPath = $request->file('poster')->store($eventFolder . '/poster', 'public');
        }

        // Handle gallery uploads
        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store($eventFolder . '/gallery', 'public');
            }
        }

        $event = Event::create([
            'name' => $request->name,
            'description' => $request->description,
            'date' => $request->date,
            'time' => $request->time,
            'venue_id' => $request->venue_id,
            'price' => $request->price,
            'status' => $request->status,
            'capacity' => $request->capacity,
            'poster' => $posterPath,
            'gallery' => json_encode($galleryPaths),
            'owner_id' => $request->user_id,
            'owner_type' => $userRole,
        ]);

        return redirect()->route('admin.events.index')
            ->with('success', "Event created successfully on behalf of {$user->name} ({$userRole}).");
    }

    private function createEventFolder($userFolder, $eventName)
    {
        $safeName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $eventName));
        $randomSuffix = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $date = now()->format('Y-m-d');
        
        $folderName = "event_{$randomSuffix}_{$safeName}_{$date}";
        $eventFolder = $userFolder . '/events/' . $folderName;
        
        Storage::disk('public')->makeDirectory($eventFolder . '/poster');
        Storage::disk('public')->makeDirectory($eventFolder . '/gallery');
        Storage::disk('public')->makeDirectory($eventFolder . '/documents');
        
        return $eventFolder;
    }

    public function show(Event $event)
    {
        $event->load(['venue', 'owner', 'artists']);
        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        // Get all users with their roles for editing events on behalf of them
        $users = User::with('roles')->get()->map(function($user) {
            $user->role_name = $user->roles->first()->name ?? 'user';
            return $user;
        });
        
        $venues = Venue::all();
        $event->load(['venue', 'owner']);
        
        return view('admin.events.edit', compact('event', 'users', 'venues'));
    }

    public function update(Request $request, Event $event)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'venue_id' => 'required|exists:venues,id',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
            'user_id' => 'required|exists:users,id',
            'capacity' => 'nullable|integer|min:1',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'gallery' => 'nullable|array|max:10',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Get the user to transfer ownership to
        $user = User::find($request->user_id);
        $userRole = $user->roles->first()->name ?? 'user';

        // Get user folder for file uploads
        $userFolder = $user->getFolderPath();
        $eventFolder = $this->createEventFolder($userFolder, $request->name);

        // Handle poster upload
        $posterPath = $event->poster; // Keep existing poster
        if ($request->hasFile('poster')) {
            // Delete old poster if exists
            if ($event->poster && Storage::disk('public')->exists($event->poster)) {
                Storage::disk('public')->delete($event->poster);
            }
            $posterPath = $request->file('poster')->store($eventFolder . '/poster', 'public');
        }

        // Handle gallery uploads
        $galleryPaths = $event->gallery ? json_decode($event->gallery, true) : [];
        if ($request->hasFile('gallery')) {
            // Delete old gallery images if exists
            if ($event->gallery) {
                foreach (json_decode($event->gallery, true) as $image) {
                    if (Storage::disk('public')->exists($image)) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }
            $galleryPaths = [];
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store($eventFolder . '/gallery', 'public');
            }
        }

        $event->update([
            'name' => $request->name,
            'description' => $request->description,
            'date' => $request->date,
            'time' => $request->time,
            'venue_id' => $request->venue_id,
            'price' => $request->price,
            'status' => $request->status,
            'capacity' => $request->capacity,
            'poster' => $posterPath,
            'gallery' => json_encode($galleryPaths),
            'owner_id' => $request->user_id,
            'owner_type' => $userRole,
        ]);

        return redirect()->route('admin.events.show', $event)
            ->with('success', "Event updated successfully. Ownership transferred to {$user->name} ({$userRole}).");
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully.');
    }

    public function toggleStatus(Event $event)
    {
        $newStatus = $event->status === 'active' ? 'cancelled' : 'active';
        $event->update(['status' => $newStatus]);

        return back()->with('success', 'Event status updated successfully.');
    }
}
