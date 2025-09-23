<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venue;
use Illuminate\Support\Facades\Validator;

class VenueManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Venue::with('user');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $venues = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.venues.index', compact('venues'));
    }

    public function create()
    {
        return view('admin.venues.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'contact_email' => 'required|email',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Venue::create($request->all());

        return redirect()->route('admin.venues.index')
            ->with('success', 'Venue created successfully.');
    }

    public function show(Venue $venue)
    {
        $venue->load('user');
        return view('admin.venues.show', compact('venue'));
    }

    public function edit(Venue $venue)
    {
        return view('admin.venues.edit', compact('venue'));
    }

    public function update(Request $request, Venue $venue)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'main_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'gallery' => 'nullable|array|max:10',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'name','description','address','city','capacity','contact_email','contact_phone','latitude','longitude'
        ]);

        // Handle main picture
        if ($request->hasFile('main_picture')) {
            if ($venue->main_picture && \Storage::disk('public')->exists($venue->main_picture)) {
                \Storage::disk('public')->delete($venue->main_picture);
            }
            $data['main_picture'] = $request->file('main_picture')->store('venues/main_pictures', 'public');
        }

        // Handle gallery
        if ($request->hasFile('gallery')) {
            // delete old gallery files if stored as array of paths
            if (is_array($venue->venue_gallery)) {
                foreach ($venue->venue_gallery as $path) {
                    if ($path && \Storage::disk('public')->exists($path)) {
                        \Storage::disk('public')->delete($path);
                    }
                }
            }
            $galleryPaths = [];
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store('venues/gallery', 'public');
            }
            $data['venue_gallery'] = $galleryPaths;
        }

        $venue->update($data);

        return redirect()->route('admin.venues.index')
            ->with('success', 'Venue updated successfully.');
    }

    public function destroy(Venue $venue)
    {
        $venue->delete();

        return redirect()->route('admin.venues.index')
            ->with('success', 'Venue deleted successfully.');
    }

    public function toggleStatus(Venue $venue)
    {
        $venue->update(['is_active' => !$venue->is_active]);

        return back()->with('success', 'Venue status updated successfully.');
    }
}

