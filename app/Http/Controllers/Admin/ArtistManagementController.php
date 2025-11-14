<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ArtistManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Artist::with('user');

        if ($request->has('search') && $request->search) {
            $search = trim($request->search);
            $like = "%{$search}%";
            $query->where(function ($q) use ($like) {
                // Substring matching across key fields
                $q->where('stage_name', 'like', $like)
                    ->orWhere('real_name', 'like', $like)
                    ->orWhere('genre', 'like', $like)
                    ->orWhereHas('user', function ($uq) use ($like) {
                        $uq->where('name', 'like', $like)
                           ->orWhere('username', 'like', $like)
                           ->orWhere('email', 'like', $like);
                    });
            });
        }

        $artists = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

        if ($request->ajax()) {
            return view('admin.artists._results', compact('artists'));
        }

        return view('admin.artists.index', compact('artists'));
    }

    public function create()
    {
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', 'artist');
        })->get();

        return view('admin.artists.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stage_name' => 'required|string|max:255',
            'real_name' => 'nullable|string|max:255',
            'genre' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'website' => 'nullable|url',
            'user_id' => 'nullable|exists:users,id',
            'is_unclaimed' => 'nullable|boolean',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        // Custom validation: contact_email is required for unclaimed artists
        $validator->after(function ($validator) use ($request) {
            if ($request->has('is_unclaimed') && $request->is_unclaimed && empty($request->contact_email)) {
                $validator->errors()->add('contact_email', 'Contact email is required for unclaimed artists.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except(['profile_picture']);
        
        // If marked as unclaimed or no user selected, set user_id to null
        if ($request->has('is_unclaimed') && $request->is_unclaimed) {
            $data['user_id'] = null;
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('artists/profile_pictures', 'public');
        }

        Artist::create($data);

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
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', 'artist');
        })->get();

        return view('admin.artists.edit', compact('artist', 'users'));
    }

    public function update(Request $request, Artist $artist)
    {
        // Log incoming request for debugging
        \Log::info('Artist update request received', [
            'artist_id' => $artist->id,
            'request_data' => $request->except(['profile_picture', '_token', '_method']),
        ]);

        $validator = Validator::make($request->all(), [
            'stage_name' => 'required|string|max:255',
            'real_name' => 'required|string|max:255',
            'genre' => 'required|string|max:255',
            'bio' => 'required|string',
            'phone_number' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'instagram' => 'nullable|url',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Custom validation: contact_email is required for unclaimed artists
        $validator->after(function ($validator) use ($request, $artist) {
            $isUnclaimed = empty($request->user_id) || $artist->user_id === null;
            if ($isUnclaimed && empty($request->contact_email)) {
                $validator->errors()->add('contact_email', 'Contact email is required for unclaimed artists.');
            }
        });

        if ($validator->fails()) {
            \Log::warning('Artist update validation failed', [
                'artist_id' => $artist->id,
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->except(['profile_picture', '_token', '_method']),
            ]);
            return back()->withErrors($validator)->withInput($request->except(['profile_picture']));
        }

        // Handle profile picture upload first
        $profilePicturePath = null;
        if ($request->hasFile('profile_picture')) {
            try {
                $file = $request->file('profile_picture');
                
                // Validate file was uploaded successfully
                if (!$file->isValid()) {
                    return back()->withErrors(['profile_picture' => 'The uploaded file is invalid.'])->withInput($request->except(['profile_picture']));
                }

                // Delete old profile picture if exists
                if ($artist->profile_picture && Storage::disk('public')->exists($artist->profile_picture)) {
                    Storage::disk('public')->delete($artist->profile_picture);
                }

                // Store new profile picture
                $profilePicturePath = $file->store('artists/profile_pictures', 'public');
                
                // Verify the file was stored
                if (!$profilePicturePath || !Storage::disk('public')->exists($profilePicturePath)) {
                    \Log::error('Profile picture storage failed', ['path' => $profilePicturePath ?? 'null']);
                    return back()->withErrors(['profile_picture' => 'Failed to save profile picture. Please try again.'])->withInput($request->except(['profile_picture']));
                }
            } catch (\Exception $e) {
                \Log::error('Profile picture upload error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                return back()->withErrors(['profile_picture' => 'Failed to upload profile picture: ' . $e->getMessage()])->withInput($request->except(['profile_picture']));
            }
        }

        // Prepare data array - collect all form fields
        $data = [
            'stage_name' => $request->input('stage_name'),
            'real_name' => $request->input('real_name'),
            'genre' => $request->input('genre'),
            'bio' => $request->input('bio'),
            'phone_number' => $request->input('phone_number'),
            'contact_email' => $request->input('contact_email'),
            'instagram' => $request->input('instagram'),
            'facebook' => $request->input('facebook'),
            'twitter' => $request->input('twitter'),
            'user_id' => $request->input('user_id', null), // Use null as default if empty
        ];

        // Add profile picture if uploaded
        if ($profilePicturePath !== null) {
            $data['profile_picture'] = $profilePicturePath;
        }

        // Remove null values but keep empty strings (for clearing fields)
        $updateData = [];
        foreach ($data as $key => $value) {
            if ($value !== null || $key === 'user_id') {
                // Allow null for user_id (for unclaimed artists)
                $updateData[$key] = $value;
            }
        }

        // Ensure we have data to update
        if (empty($updateData)) {
            \Log::warning('Artist update called with no data', ['artist_id' => $artist->id, 'request_data' => $request->all()]);
            return back()->withErrors(['general' => 'No data provided to update.'])->withInput($request->except(['profile_picture']));
        }

        try {
            $artist->update($updateData);
            \Log::info('Artist updated successfully', [
                'artist_id' => $artist->id, 
                'fields_updated' => array_keys($updateData)
            ]);
        } catch (\Exception $e) {
            \Log::error('Artist update failed', [
                'artist_id' => $artist->id,
                'error' => $e->getMessage(),
                'data_attempted' => $updateData,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['general' => 'Failed to update artist: ' . $e->getMessage()])->withInput($request->except(['profile_picture']));
        }

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
        $artist->update(['is_active' => ! $artist->is_active]);

        return back()->with('success', 'Artist status updated successfully.');
    }
}
