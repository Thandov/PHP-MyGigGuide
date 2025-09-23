@props([
    'events' => collect(),
    'height' => '400px',
    'width' => '100%',
    'showLegend' => true,
    'compact' => false,
    'zoomDelta' => 0,
    'center' => ['lat' => -26.1550, 'lng' => 28.0595], // Default to Johannesburg
    'radiusKm' => 50
])

@php
    // Filter events that have venue coordinates
    $eventsWithCoordinates = $events->filter(function($event) {
        return $event->venue && 
               $event->venue->latitude && 
               $event->venue->longitude &&
               !is_null($event->venue->latitude) && 
               !is_null($event->venue->longitude);
    });

    // Calculate center if events have coordinates
    if ($eventsWithCoordinates->count() > 0) {
        $avgLat = $eventsWithCoordinates->avg('venue.latitude');
        $avgLng = $eventsWithCoordinates->avg('venue.longitude');
        $center = ['lat' => $avgLat, 'lng' => $avgLng];
    }
@endphp

<div class="w-full rounded-2xl overflow-hidden shadow-sm border border-purple-100 relative" style="height: {{ $height }}; width: {{ $width }};">
    {{-- Map Legend --}}
    @if($showLegend && !$compact)
    <div class="absolute top-4 left-4 z-10 bg-white rounded-lg shadow-lg p-3 border border-gray-200">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">Map Legend</h3>
        <div class="space-y-2">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-blue-500 rounded-full mr-2"></div>
                <span class="text-xs text-gray-700">Your Location</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-purple-500 rounded mr-2 flex items-center justify-center">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#ffffff"/>
                        <circle cx="12" cy="9" r="2" fill="#7c3aed"/>
                    </svg>
                </div>
                <span class="text-xs text-gray-700">Event Venues ({{ $eventsWithCoordinates->count() }})</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Location Button --}}
    @if(!$compact)
    <button
        id="get-location-btn"
        class="absolute top-4 right-4 z-10 bg-white rounded-lg shadow-lg p-2 border border-gray-200 hover:bg-gray-50 transition-colors duration-200"
        title="Get my location"
    >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#3b82f6"/>
        </svg>
    </button>
    @endif

    {{-- Map Container --}}
    <div id="google-map-{{ $attributes->get('id', 'default') }}" class="w-full h-full"></div>

    {{-- Loading State --}}
    <div id="map-loading-{{ $attributes->get('id', 'default') }}" class="absolute inset-0 w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-50 to-blue-50">
        <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Loading map...</p>
        </div>
    </div>

    {{-- No Events State --}}
    @if($eventsWithCoordinates->count() === 0)
    <div class="absolute inset-0 w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="text-center">
            <div class="text-gray-400 mb-2">
                <svg class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <p class="text-gray-600 mb-2">No upcoming events found</p>
            <p class="text-gray-500 text-sm">Check back later for new events</p>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapId = '{{ $attributes->get("id", "default") }}';
    
    // Map configuration
    const mapConfig = {
        center: { lat: {{ $center['lat'] }}, lng: {{ $center['lng'] }} },
        zoom: {{ $eventsWithCoordinates->count() > 1 ? 10 : 12 }},
        events: {!! json_encode($eventsWithCoordinates->map(function($event) {
            return [
                'id' => $event->id,
                'name' => $event->name,
                'date' => $event->date->toISOString(),
                'time' => $event->time ? $event->time->format('H:i') : null,
                'price' => $event->price,
                'venue' => [
                    'name' => $event->venue->name,
                    'latitude' => (float) $event->venue->latitude,
                    'longitude' => (float) $event->venue->longitude,
                ]
            ];
        })) !!},
        showLegend: {{ $showLegend ? 'true' : 'false' }},
        compact: {{ $compact ? 'true' : 'false' }},
        zoomDelta: {{ $zoomDelta }},
        apiKey: '{{ config('services.google_maps.api_key', 'YOUR_API_KEY_HERE') }}',
        radiusKm: {{ (int) $radiusKm }}
    };

    // Initialize the map
    if (typeof initGoogleMap === 'function') {
        initGoogleMap(mapId, mapConfig);
    } else {
        console.error('Google Maps initialization function not loaded');
    }
});
</script>
@endpush