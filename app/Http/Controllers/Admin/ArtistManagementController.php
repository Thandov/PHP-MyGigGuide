<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ArtistManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Artist::with('user');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('stage_name', 'like', "%{$search}%")
                  ->orWhere('real_name', 'like', "%{$search}%")
                  ->orWhere('genre', 'like', "%{$search}%");
            });
        }

        $artists = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.artists.index', compact('artists'));
    }

    public function create()
    {
        $users = User::whereHas('roles', function($q) {
            $q->where('name', 'artist');
        })->get();
        
        return view('admin.artists.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stage_name' => 'required|string|max:255',
            'real_name' => 'required|string|max:255',
            'genre' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'website' => 'nullable|url',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Artist::create($request->all());

        return redirect()->route('admin.artists.index')
            ->with('success', 'Artist created successfully.');
    }

    public function show(Artist $artist)
    {
        $artist->load('user');
        return view('admin.artists.show', compact('artist'));
    }

    public function edit(Artist $artist)
    {
        $users = User::whereHas('roles', function($q) {
            $q->where('name', 'artist');
        })->get();
        
        return view('admin.artists.edit', compact('artist', 'users'));
    }

    public function update(Request $request, Artist $artist)
    {
        $validator = Validator::make($request->all(), [
            'stage_name' => 'required|string|max:255',
            'real_name' => 'required|string|max:255',
            'genre' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'instagram' => 'nullable|url',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($artist->profile_picture && \Storage::disk('public')->exists($artist->profile_picture)) {
                \Storage::disk('public')->delete($artist->profile_picture);
            }
            
            // Store new profile picture
            $profilePicturePath = $request->file('profile_picture')->store('artists/profile_pictures', 'public');
            $request->merge(['profile_picture' => $profilePicturePath]);
        }

        $artist->update($request->all());

        return redirect()->route('admin.artists.show', $artist)
            ->with('success', 'Artist updated successfully.');
    }

    public function destroy(Artist $artist)
    {
        $artist->delete();

        return redirect()->route('admin.artists.index')
            ->with('success', 'Artist deleted successfully.');
    }

    public function toggleStatus(Artist $artist)
    {
        $artist->update(['is_active' => !$artist->is_active]);

        return back()->with('success', 'Artist status updated successfully.');
    }
}
