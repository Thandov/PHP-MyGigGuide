@extends('layouts.app')

@section('title', 'Edit Venue - My Gig Guide')
@section('description', 'Update your venue information.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Venue</h1>
                <p class="text-gray-600">Update your venue information</p>
            </div>

            <form action="{{ route('venues.update', $venue) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Venue Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Venue Name *
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $venue->name) }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('name') border-red-300 @enderror"
                        placeholder="Enter venue name"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address with Google Maps Autocomplete -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Address *
                    </label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        value="{{ old('address', $venue->address) }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('address') border-red-300 @enderror"
                        placeholder="Start typing address..."
                        autocomplete="off"
                    >
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Start typing to search for an address</p>
                </div>

                <!-- Hidden fields for coordinates -->
                <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $venue->latitude) }}">
                <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $venue->longitude) }}">

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('description') border-red-300 @enderror"
                        placeholder="Describe your venue..."
                    >{{ old('description', $venue->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Capacity -->
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                        Capacity *
                    </label>
                    <input
                        type="number"
                        id="capacity"
                        name="capacity"
                        value="{{ old('capacity', $venue->capacity) }}"
                        min="1"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('capacity') border-red-300 @enderror"
                        placeholder="Maximum number of people"
                    >
                    @error('capacity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Phone
                        </label>
                        <input
                            type="tel"
                            id="contact_phone"
                            name="contact_phone"
                            value="{{ old('contact_phone', $venue->phone_number) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('contact_phone') border-red-300 @enderror"
                            placeholder="Phone number"
                        >
                        @error('contact_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Email
                        </label>
                        <input
                            type="email"
                            id="contact_email"
                            name="contact_email"
                            value="{{ old('contact_email', $venue->contact_email) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('contact_email') border-red-300 @enderror"
                            placeholder="Email address"
                        >
                        @error('contact_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Website -->
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                        Website
                    </label>
                    <input
                        type="url"
                        id="website"
                        name="website"
                        value="{{ old('website', $venue->website) }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('website') border-red-300 @enderror"
                        placeholder="https://example.com"
                    >
                    @error('website')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Upload -->
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                        Venue Image
                    </label>
                    @if($venue->main_picture)
                        <div class="mb-2">
                            <img src="{{ Storage::url($venue->main_picture) }}" alt="{{ $venue->name }}" class="h-20 w-20 object-cover rounded-lg">
                            <p class="text-sm text-gray-500 mt-1">Current image</p>
                        </div>
                    @endif
                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/*"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('image') border-red-300 @enderror"
                    >
                    <p class="mt-1 text-sm text-gray-500">Upload a new image for your venue (max 10MB)</p>
                    @error('image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amenities -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Amenities
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                            $amenities = [
                                'Parking', 'WiFi', 'Air Conditioning', 'Sound System',
                                'Lighting', 'Stage', 'Bar', 'Kitchen',
                                'Restrooms', 'Accessibility', 'Outdoor Space', 'Security'
                            ];
                            $selectedAmenities = $venue->amenities ? json_decode($venue->amenities, true) : [];
                        @endphp
                        @foreach($amenities as $amenity)
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="amenities[]"
                                    value="{{ $amenity }}"
                                    {{ in_array($amenity, old('amenities', $selectedAmenities)) ? 'checked' : '' }}
                                    class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                                >
                                <span class="ml-2 text-sm text-gray-700">{{ $amenity }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('amenities')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('dashboard') }}" class="btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Venue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let autocomplete;
let map;

function initAutocomplete() {
    // Create the autocomplete object
    autocomplete = new google.maps.places.Autocomplete(
        document.getElementById('address'),
        {
            types: ['establishment', 'geocode'],
            componentRestrictions: { country: 'za' } // Restrict to South Africa
        }
    );

    // When the user selects an address from the dropdown
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        
        if (!place.geometry || !place.geometry.location) {
            console.log('No details available for input: ' + place.name);
            return;
        }

        // Get the formatted address
        const address = place.formatted_address;
        
        // Get coordinates
        const lat = place.geometry.location.lat();
        const lng = place.geometry.location.lng();

        // Update the form fields
        document.getElementById('address').value = address;
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        console.log('Address selected:', address);
        console.log('Coordinates:', lat, lng);
    });
}

// Load Google Maps API
function loadGoogleMaps() {
    // Use centralized loader instead of injecting script directly
    addGoogleMapsCallback(() => initAutocomplete());
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadGoogleMaps();
});
</script>
@endpush
@endsection

