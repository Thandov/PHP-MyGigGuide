<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Artist;
use App\Models\Organiser;

class ProfileController extends Controller
{
    /**
     * Show the user profile.
     */
    public function show()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Get role-specific profile data
        $profile = null;
        if ($user->hasRole('artist')) {
            $profile = $user->artist;
        } elseif ($user->hasRole('organiser')) {
            $profile = $user->organiser;
        }

        return view('profile.show', compact('user', 'profile'));
    }

    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Get role-specific profile data
        $profile = null;
        if ($user->hasRole('artist')) {
            $profile = $user->artist;
        } elseif ($user->hasRole('organiser')) {
            $profile = $user->organiser;
        }

        return view('profile.edit', compact('user', 'profile'));
    }

    /**
     * Update the user profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Update basic user info
        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            if (!$request->current_password || !Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Update role-specific profile
        if ($user->hasRole('artist')) {
            $this->updateArtistProfile($user, $request);
        } elseif ($user->hasRole('organiser')) {
            $this->updateOrganiserProfile($user, $request);
        }

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }

    /**
     * Update artist profile.
     */
    private function updateArtistProfile($user, $request)
    {
        $request->validate([
            'stage_name' => 'required|string|max:255',
            'real_name' => 'required|string|max:255',
            'genre' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'social_media' => 'nullable|string|max:1000',
        ]);

        $artist = $user->artist;
        if (!$artist) {
            $artist = Artist::create(['user_id' => $user->id]);
        }

        $artist->update([
            'stage_name' => $request->stage_name,
            'real_name' => $request->real_name,
            'genre' => $request->genre,
            'bio' => $request->bio,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'website' => $request->website,
            'social_media' => $request->social_media,
        ]);
    }

    /**
     * Update organiser profile.
     */
    private function updateOrganiserProfile($user, $request)
    {
        $request->validate([
            'organisation_name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $organiser = $user->organiser;
        if (!$organiser) {
            $organiser = Organiser::create(['user_id' => $user->id]);
        }

        $organiser->update([
            'organisation_name' => $request->organisation_name,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'website' => $request->website,
            'description' => $request->description,
        ]);
    }
}

