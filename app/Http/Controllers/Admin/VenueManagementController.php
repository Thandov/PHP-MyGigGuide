<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VenueManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Venue::with('user');

        if ($request->has('search') && $request->search) {
            $rawSearch = trim((string) $request->search);
            // Multi-word search on venue NAME only; all words must appear in name
            $terms = preg_split('/\s+/', $rawSearch, -1, PREG_SPLIT_NO_EMPTY);

            $query->where(function ($outer) use ($terms) {
                foreach ($terms as $term) {
                    $like = '%'.$term.'%';
                    $outer->where('name', 'like', $like);
                }
            });
        }

        // Handle per page parameter (WordPress style)
        $perPage = $request->input('per_page', 15);
        $perPage = in_array($perPage, [15, 30, 50, 100]) ? $perPage : 15;

        $venues = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

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
            'city' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'contact_email' => 'nullable|email',
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
            'name', 'address', 'capacity', 'latitude', 'longitude',
        ]);

        // Map city -> location (DB column)
        if ($request->filled('city')) {
            $data['location'] = $request->string('city');
        }

        // Contact email - required by DB (unique, not null). Generate a safe default if absent.
        $contactEmail = $request->input('contact_email');
        if (! $contactEmail) {
            $base = Str::slug($data['name'] ?? 'venue');
            $contactEmail = $base.'+'.time().'@example.local';
        }
        $data['contact_email'] = $contactEmail;

        // Required relational fields
        $userId = Auth::check() ? (int) Auth::id() : 1;
        $data['user_id'] = $userId;
        $data['owner_id'] = $userId;
        $data['owner_type'] = \App\Models\User::class;

        // Ensure the owner's settings (including folder_name) exist before saving uploads
        $owner = User::find($userId);
        if ($owner) {
            $owner->getOrCreateFolderSettings();
        }

        // Handle main picture
        if ($request->hasFile('main_picture')) {
            $baseFolder = $owner ? $owner->getFolderPath() : 'users/unknown';
            $data['main_picture'] = $request->file('main_picture')->store($baseFolder.'/venues/main_pictures', 'public');
        }

        // Handle gallery
        if ($request->hasFile('gallery')) {
            $baseFolder = $owner ? $owner->getFolderPath() : 'users/unknown';
            $galleryPaths = [];
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store($baseFolder.'/venues/gallery', 'public');
            }
            $data['venue_gallery'] = $galleryPaths;
        }

        Venue::create($data);

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
        $venue->loadMissing('user');
        $owner = $venue->user;

        $rules = [
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
        ];

        if ($owner) {
            $rules['owner_email'] = [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($owner->id),
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'name', 'description', 'address', 'city', 'capacity', 'contact_email', 'contact_phone', 'latitude', 'longitude',
        ]);

        // Handle main picture
        if ($request->hasFile('main_picture')) {
            if ($venue->main_picture && Storage::disk('public')->exists($venue->main_picture)) {
                Storage::disk('public')->delete($venue->main_picture);
            }
            $data['main_picture'] = $request->file('main_picture')->store('venues/main_pictures', 'public');
        }

        // Handle gallery
        if ($request->hasFile('gallery')) {
            // delete old gallery files if stored as array of paths
            if (is_array($venue->venue_gallery)) {
                foreach ($venue->venue_gallery as $path) {
                    if ($path && Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
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

        if ($owner && $request->filled('owner_email')) {
            $newEmail = $request->input('owner_email');

            if ($newEmail !== $owner->email) {
                if (! auth()->check() || ! auth()->user()->hasRole('superuser')) {
                    abort(403, 'Only superusers may update owner email addresses.');
                }

                $owner->forceFill([
                    'email' => $newEmail,
                    'email_verified_at' => null,
                ])->save();
            }
        }

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
        $venue->update(['is_active' => ! $venue->is_active]);

        return back()->with('success', 'Venue status updated successfully.');
    }

    public function importVenues(Request $request)
    {
        try {
            // Check if mapping file exists
            $mappingPath = public_path('venues_to_upload/venue_mapping.json');
            if (!file_exists($mappingPath)) {
                return back()->with('error', 'Venue mapping file not found. Please ensure the Excel file and venue folders are uploaded.');
            }

            // Run the import command
            Artisan::call('venues:import', [
                '--no-interaction' => true,
            ]);

            $output = Artisan::output();

            // Check if successful
            if (str_contains($output, 'Successfully imported')) {
                preg_match('/Successfully imported (\d+) venues/', $output, $matches);
                $count = $matches[1] ?? 'some';
                return redirect()->route('admin.venues.index')
                    ->with('success', "Successfully imported {$count} venues from Excel spreadsheet!");
            }

            return back()->with('info', 'Import completed. Check the venue list for results.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function bulkAction(Request $request)
    {
        // Log the request for debugging
        \Log::info('Bulk action request received', [
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'Unknown',
            'bulk_action' => $request->input('bulk_action'),
            'venue_ids' => $request->input('venue_ids'),
            'all_input' => $request->all(),
        ]);

        $request->validate([
            'bulk_action' => 'required|string',
            'venue_ids' => 'required|array|min:1',
            'venue_ids.*' => 'exists:venues,id',
        ]);

        $action = $request->input('bulk_action');
        $venueIds = $request->input('venue_ids');
        $count = count($venueIds);

        \Log::info('Bulk action validated', [
            'action' => $action,
            'venue_count' => $count,
            'venue_ids' => $venueIds,
        ]);

        switch ($action) {
            case 'delete':
                // Delete selected venues (handle foreign key constraints)
                try {
                    // First, delete related events
                    \DB::table('events')->whereIn('venue_id', $venueIds)->delete();
                    
                    // Then delete venues
                    Venue::whereIn('id', $venueIds)->delete();
                    
                    \Log::info('Bulk delete completed', [
                        'deleted_count' => $count,
                        'venue_ids' => $venueIds,
                    ]);
                    return redirect()->route('admin.venues.index')
                        ->with('success', "Successfully deleted {$count} venue(s) and their related events.");
                        
                } catch (\Exception $e) {
                    \Log::error('Bulk delete failed', [
                        'error' => $e->getMessage(),
                        'venue_ids' => $venueIds,
                    ]);
                    return redirect()->route('admin.venues.index')
                        ->with('error', "Failed to delete venues: " . $e->getMessage());
                }

            case 'export':
                // TODO: Implement export functionality
                return back()->with('info', 'Export functionality coming soon.');

            default:
                \Log::warning('Invalid bulk action attempted', ['action' => $action]);
                return back()->with('error', 'Invalid bulk action selected.');
        }
    }
}
