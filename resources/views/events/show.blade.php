@extends('layouts.app')

@section('title', $event->name . ' - My Gig Guide')

@push('styles')
<style>
    /* Heart icon styling for favorited/unfavorited states */
    .favorite-toggle.favorited svg {
        fill: #ef4444 !important;
        stroke: #ef4444 !important;
    }

    .favorite-toggle:hover svg {
        transform: scale(1.1);
        transition: transform 0.2s ease;
    }

    .favorite-toggle.loading {
        opacity: 0.7;
        pointer-events: none;
    }

    /* Ensure SVG styling takes priority */
    .favorite-toggle svg[style*="fill: #ef4444"] {
        fill: #ef4444 !important;
        stroke: #ef4444 !important;
    }

</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="relative h-96 md:h-[500px] overflow-hidden">
        @php
        $galleryImages = [];
        if ($event->gallery) {
        try {
        $galleryImages = is_string($event->gallery) ? json_decode($event->gallery, true) : $event->gallery;
        if (!is_array($galleryImages)) {
        $galleryImages = [];
        }
        // Filter out invalid temp paths
        $galleryImages = array_filter($galleryImages, function($path) {
        return $path && !str_contains($path, '/tmp/php') && !str_contains($path, 'tmp.php');
        });
        } catch (Exception $e) {
        $galleryImages = [];
        }
        }
        $mainImage = $event->poster ?: ($galleryImages[0] ?? null);
        // Also check if mainImage is a valid path
        if ($mainImage && (str_contains($mainImage, '/tmp/php') || str_contains($mainImage, 'tmp.php'))) {
        $mainImage = null;
        }
        @endphp

        @if(count($galleryImages) > 1)
        <!-- Owl Carousel for multiple gallery images -->
        <div class="owl-carousel owl-theme event-hero-carousel w-full h-full">
            @foreach($galleryImages as $index => $image)
            <div class="item relative w-full h-full">
                <img src="{{ Storage::url($image) }}" alt="{{ $event->name }} - Image {{ $index + 1 }}"
                    class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/40"></div>
            </div>
            @endforeach
        </div>
        @elseif($mainImage)
        <!-- Single image background -->
        <div class="relative w-full h-full">
            <img src="{{ Storage::url($mainImage) }}" alt="{{ $event->name }}" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/40"></div>
        </div>
        @else
        <!-- Fallback gradient background -->
        <div class="w-full h-full bg-gradient-to-br from-purple-600 to-blue-600"></div>
        @endif

        <!-- Custom Navigation Controls (only for carousel) -->
        @if(count($galleryImages) > 1)
        <div class="absolute inset-0 pointer-events-none">
            <button
                class="owl-prev absolute left-4 top-1/2 -translate-y-1/2 p-2 bg-white/20 hover:bg-white/30 rounded-full transition-all pointer-events-auto">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button
                class="owl-next absolute right-4 top-1/2 -translate-y-1/2 p-2 bg-white/20 hover:bg-white/30 rounded-full transition-all pointer-events-auto">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <!-- Dots Container -->
            <div class="owl-dots absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2"></div>
        </div>
        @endif

        <style>
            #hello {
                z-index: 10 !important;
                position: absolute !important;
            }

            /* Prevent carousel animations from affecting overlay */
            .event-hero-carousel {
                z-index: 1;
            }

            .event-hero-carousel .owl-item {
                z-index: 1;
            }

            /* Ensure overlay stays on top and doesn't inherit carousel animations */
            #hello * {
                animation: none !important;
                transition: none !important;
            }

        </style>

        <!-- Breadcrumb -->
        <div class="max-w-7xl mx-auto">
            <x-hero-breadcrumb type="event" :item="$event" />
        </div>
        <!-- Hero Content - Now positioned outside carousel to prevent fading -->
        <div class="absolute inset-0 flex items-end pointer-events-none" id="hello">
            <div class="w-full bg-gradient-to-t from-black/80 to-transparent p-6 md:p-8 pointer-events-auto">
                <div class="max-w-7xl mx-auto">
                    <div class="flex items-end justify-between">
                        <div class="text-white">
                            <h1 class="text-4xl md:text-6xl font-bold mb-2">{{ $event->name }}</h1>
                            <div class="flex items-center space-x-6 text-lg">
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span>{{ $event->date->format('l, F j, Y') }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ $event->time ?: 'TBD' }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>{{ $event->venue->name ?? 'Venue TBD' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            @auth
                            @php
                            $isFavorited = false;
                            if (auth()->user()) {
                            $isFavorited = auth()->user()->favoriteEvents()->where('event_id', $event->id)->exists();
                            }
                            @endphp
                            <button id="favorite-btn" data-event-id="{{ $event->id }}"
                                class="favorite-toggle p-3 bg-white/20 hover:bg-white/30 rounded-full transition-all {{ $isFavorited ? 'favorited' : '' }}">
                                <svg class="h-6 w-6 text-white {{ $isFavorited ? 'fill-red-500' : 'fill-none' }}"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                    </path>
                                </svg>
                            </button>
                            @else
                            <button onclick="window.location.href='/login'"
                                class="p-3 bg-white/20 hover:bg-white/30 rounded-full transition-all"
                                title="Login to add favorites">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                    </path>
                                </svg>
                            </button>
                            @endauth
                            <button class="p-3 bg-white/20 hover:bg-white/30 rounded-full transition-all">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
                                    </path>
                                </svg>
                            </button>
                            @auth
                            @if(auth()->id() == $event->owner_id)
                            <a href="{{ route('events.edit', $event) }}"
                                class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-all">
                                Edit Event
                            </a>
                            @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="max-w-7xl mx-auto py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Left Column - Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- About Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Event</h2>
                    @if($event->description)
                    <div class="space-y-4">
                        <p class="text-gray-700 leading-relaxed text-lg">{{ $event->description }}</p>
                        <div class="flex flex-wrap gap-2">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                {{ $event->category ?: 'General' }}
                            </span>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $event->capacity ? $event->capacity . ' capacity' : 'Open event' }}
                            </span>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                {{ $event->status ?: 'Active' }}
                            </span>
                        </div>
                    </div>
                    @else
                    <div class="space-y-4">
                        <p class="text-gray-500 italic">No description provided for this event.</p>
                        <div class="flex flex-wrap gap-2">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                {{ $event->category ?: 'General' }}
                            </span>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Booked Artists -->
                @if($event->artists->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Booked Artists</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($event->artists as $artist)
                        @php
                        $avatarPath = $artist->profile_picture
                        ?: (optional($artist->user)->profile_picture ?? null);
                        $avatarValid = $avatarPath && !str_contains($avatarPath, '/tmp/php') &&
                        !str_contains($avatarPath, 'tmp.php');
                        $avatarUrl = $avatarValid ? Storage::url($avatarPath) : null;
                        @endphp
                        <a href="{{ route('artists.show', $artist) }}" class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="{{ $artist->stage_name }}"
                                class="h-12 w-12 rounded-full object-cover">
                            @else
                            <div class="h-12 w-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <span
                                    class="text-purple-600 font-semibold">{{ substr($artist->stage_name, 0, 1) }}</span>
                            </div>
                            @endif
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">{{ $artist->stage_name }}</p>
                                @if($artist->genre)
                                <p class="text-sm text-gray-500">{{ $artist->genre }}</p>
                                @endif
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
                <!-- Event Gallery -->
                @if(count($galleryImages) > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Event Gallery</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($galleryImages as $index => $image)
                        <div class="relative group cursor-pointer" data-gallery-index="{{ $index }}" role="button" tabindex="0" aria-label="Open image {{ $index + 1 }} in gallery">
                            <img src="{{ Storage::url($image) }}" alt="{{ $event->name }} - Image {{ $index + 1 }}"
                                class="w-full h-32 object-cover rounded-lg shadow-sm group-hover:shadow-md transition-shadow">
                            <div
                                class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-white opacity-0 group-hover:opacity-100 transition-opacity"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Lightbox Modal for Event Gallery -->
                <div id="event-gallery-modal" class="fixed inset-0 bg-black/90 hidden items-center justify-center z-[100]">
                    <button type="button" id="event-gallery-close" class="absolute top-4 right-4 p-2 text-white bg-white/10 hover:bg-white/20 rounded-full" aria-label="Close gallery">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <button type="button" id="event-gallery-prev" class="absolute left-4 md:left-8 p-3 text-white bg-white/10 hover:bg-white/20 rounded-full" aria-label="Previous image">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" id="event-gallery-next" class="absolute right-4 md:right-8 p-3 text-white bg-white/10 hover:bg-white/20 rounded-full" aria-label="Next image">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <div class="max-w-5xl w-full px-4">
                        <img id="event-gallery-image" src="" alt="Event image" class="max-h-[80vh] w-auto mx-auto rounded-lg shadow-2xl object-contain">
                        <div id="event-gallery-counter" class="text-center text-white/80 mt-3 text-sm"></div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Location</h2>
                    @if($event->venue && $event->venue->latitude && $event->venue->longitude)
                    <x-map :latitude="$event->venue->latitude" :longitude="$event->venue->longitude"
                        :address="$event->venue->address" class="w-full h-64 rounded-lg overflow-hidden" />
                    @if($event->venue->address)
                    <p class="mt-2 text-gray-700 text-sm">
                        <span class="font-semibold">Address:</span> {{ $event->venue->address }}
                    </p>
                    @endif
                    @else
                    <p class="text-gray-500">Location details are not available for this event.</p>
                    @endif
                </div>

                <!-- What to Expect -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">What to Expect</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start space-x-3">
                            <svg class="h-6 w-6 text-green-500 mt-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900">Live Performance</p>
                                <p class="text-gray-600 text-sm">Experience amazing live entertainment</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="h-6 w-6 text-green-500 mt-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900">Great Atmosphere</p>
                                <p class="text-gray-600 text-sm">Connect with fellow music lovers</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="h-6 w-6 text-green-500 mt-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900">Quality Sound</p>
                                <p class="text-gray-600 text-sm">Professional audio equipment</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="h-6 w-6 text-green-500 mt-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900">Memorable Experience</p>
                                <p class="text-gray-600 text-sm">Create lasting memories</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Details & Actions -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Ticket Purchase Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 top-8">
                    <div class="text-center space-y-4">
                        <div>
                            <div class="text-4xl font-bold text-gray-900">
                                @if($event->price && $event->price > 0)
                                R{{ number_format($event->price, 2) }}
                                @else
                                Free
                                @endif
                            </div>
                            <div class="text-gray-600">per ticket</div>
                        </div>

                        @if($event->ticket_url)
                        <a href="{{ $event->ticket_url }}" target="_blank" rel="noopener noreferrer"
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700 transition-all shadow-lg hover:shadow-xl">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
                                </path>
                            </svg>
                            Get Tickets Now
                        </a>
                        @else
                        <button
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-gray-400 text-white rounded-xl font-semibold cursor-not-allowed">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
                                </path>
                            </svg>
                            Tickets Not Available
                        </button>
                        @endif

                        @if($event->capacity)
                        <p class="text-sm text-gray-600">{{ $event->capacity }} spots available</p>
                        @endif
                    </div>
                </div>

                <!-- Add to Calendar Component -->
                <x-add-to-calendar :event="$event" />

                <!-- Event Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Date & Time -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center space-x-3 mb-2">
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span class="font-semibold text-gray-900">Date & Time</span>
                        </div>
                        <p class="text-gray-700">{{ $event->date->format('l, F j, Y') }}</p>
                        <p class="text-gray-600">{{ $event->time ?: 'TBD' }}</p>
                    </div>

                    <!-- Location -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center space-x-3 mb-2">
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="font-semibold text-gray-900">Location</span>
                        </div>
                        <p class="text-gray-700">{{ $event->venue->name ?? 'Venue TBD' }}</p>
                        @if($event->venue && $event->venue->address)
                        <p class="text-gray-600 text-sm">{{ $event->venue->address }}</p>
                        @endif
                    </div>

                    <!-- Category & Status -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center space-x-3 mb-2">
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                </path>
                            </svg>
                            <span class="font-semibold text-gray-900">Category</span>
                        </div>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            {{ $event->category ?: 'General' }}
                        </span>
                    </div>

                    <!-- Social Sharing -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Share this event</h3>
                    <div class="flex flex-wrap gap-3">
                        <!-- Facebook -->
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}&quote={{ urlencode($event->name . ' - ' . $event->description) }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Facebook
                        </a>

                        <!-- Twitter -->
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($event->name . ' - ' . $event->date->format('M j, Y') . ' at ' . $event->venue->name) }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                            Twitter
                        </a>

                        <!-- WhatsApp -->
                        <a href="https://wa.me/?text={{ urlencode($event->name . ' - ' . $event->date->format('M j, Y') . ' at ' . $event->venue->name . ' - ' . request()->url()) }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                            </svg>
                            WhatsApp
                        </a>

                        <!-- LinkedIn -->
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="flex items-center gap-2 px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            LinkedIn
                        </a>

                        <!-- Copy Link -->
                        <button onclick="copyEventLink()" 
                                class="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Copy Link
                        </button>
                    </div>
                </div>
                </div>

                

                <!-- Event Tips -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Event Tips</h2>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3">
                            <div
                                class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <p class="text-gray-700 text-sm">Arrive 15-30 minutes early for the best seats</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div
                                class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <p class="text-gray-700 text-sm">Bring a valid ID for age verification</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div
                                class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <p class="text-gray-700 text-sm">Check venue parking options in advance</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div
                                class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <p class="text-gray-700 text-sm">Follow the event on social media for updates</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Event Reviews -->
    @if($event->ratings->count() > 0)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Event Reviews</h2>
            <div class="space-y-4">
                @foreach($event->ratings->take(3) as $rating)
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                        <span class="text-purple-600 font-semibold">{{ substr($rating->user->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="font-semibold text-gray-900">{{ $rating->user->name }}</span>
                            <div class="flex items-center space-x-1">
                                @for($i = 1; $i <= 5; $i++) <svg
                                    class="h-4 w-4 {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                    </path>
                                    </svg>
                                    @endfor
                            </div>
                        </div>
                        <p class="text-gray-600 text-sm">{{ $rating->review ?: 'Great event!' }}</p>
                    </div>
                </div>
                @endforeach
                @if($event->ratings->count() > 3)
                <button class="w-full text-center py-2 text-purple-600 hover:text-purple-700 font-medium">
                    View All Reviews
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Wait to ensure jQuery has loaded
        function checkAndInitialize() {
            if (typeof jQuery === 'undefined' || typeof jQuery.fn === 'undefined') {
                setTimeout(checkAndInitialize, 100);
                return;
            }

            initializeEverything();
        }

        function initializeEverything() {
            // Initialize favorite toggle functionality
            const favoriteButton = document.querySelector('.favorite-toggle');
            if (favoriteButton) {
                favoriteButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    const eventId = this.dataset.eventId;
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const csrfToken = csrfMeta ? csrfMeta.content : '';

                    if (!eventId) return;

                    // Add loading state
                    const originalIcon = this.innerHTML;
                    this.innerHTML =
                        `<svg class="h-6 w-6 text-white animate-spin" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"/><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" stroke-dasharray="31.416" stroke-dashoffset="15.708"/></svg>`;
                    this.disabled = true;
                    this.classList.add('loading');

                    // Make AJAX request
                    fetch(`/favorites/events/${eventId}/toggle`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update visual state based on server response  
                                if (data.favorited) {
                                    // Make heart red - completely replace the SVG to force styling
                                    this.classList.add('favorited');
                                    // Store the new red heart as the "current state" so it doesn't get reverted
                                    const redSvg =
                                        `<svg class="h-6 w-6" fill="#ef4444" stroke="#ef4444" style="fill: #ef4444 !important; stroke: #ef4444 !important;" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>`;
                                    this.innerHTML = redSvg;
                                    // Update the originalIcon for consistency after state has changed
                                    this.dataset.lastFavoritedState = 'true';
                                    console.log('Event favorited - heart should be red now');
                                } else {
                                    // Remove red color - restore to original state
                                    this.classList.remove('favorited');
                                    const defaultSvg =
                                        `<svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>`;
                                    this.innerHTML = defaultSvg;
                                    this.dataset.lastFavoritedState = 'false';
                                    console.log('Event unfavorited - heart should be white now');
                                }

                                // Show toast notification or feedback
                                if (window.showToast) {
                                    window.showToast(data.message);
                                } else {
                                    console.log(data.message);
                                }
                            } else {
                                throw new Error(data.message || 'Failed to toggle favorite');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to update favorite. Please try again.');
                        })
                        .finally(() => {
                            // Restore button state - keep visual changes and only undo loading state
                            this.disabled = false;
                            this.classList.remove('loading');
                        });
                });
            }

            // Initialize Owl Carousel for event hero images
            if (document.querySelector('.event-hero-carousel') && typeof jQuery !== 'undefined' && typeof jQuery
                .fn.owlCarousel !== 'undefined') {
                jQuery('.event-hero-carousel').owlCarousel({
                    items: 1,
                    loop: true,
                    autoplay: true,
                    autoplayTimeout: 6000, // 1 minute
                    autoplayHoverPause: true,
                    nav: false,
                    dots: true,
                    dotsContainer: '.owl-dots',
                    smartSpeed: 1000,
                    mouseDrag: true,
                    touchDrag: true,
                    pullDrag: true,
                    freeDrag: false,
                    margin: 0,
                    stagePadding: 0,
                    merge: false,
                    mergeFit: true,
                    autoWidth: false,
                    startPosition: 0,
                    rtl: false,
                    slideBy: 1,
                    fallbackEasing: 'swing',
                    info: false,
                    nestedItemSelector: false,
                    itemElement: 'div',
                    stageElement: 'div',
                    refreshClass: 'owl-refresh',
                    loadedClass: 'owl-loaded',
                    loadingClass: 'owl-loading',
                    rtlClass: 'owl-rtl',
                    responsiveClass: 'owl-responsive',
                    dragClass: 'owl-drag',
                    itemClass: 'owl-item',
                    stageClass: 'owl-stage',
                    stageOuterClass: 'owl-stage-outer',
                    grabClass: 'owl-grab',
                    autoHeight: false,
                    autoHeightClass: 'owl-height',
                    video: false,
                    videoHeight: false,
                    videoWidth: false,
                    navText: ['<i class="fa fa-angle-left" aria-hidden="true"></i>',
                        '<i class="fa fa-angle-right" aria-hidden="true"></i>'
                    ],
                    responsive: {
                        0: {
                            items: 1,
                            nav: false,
                            dots: true
                        },
                        600: {
                            items: 1,
                            nav: false,
                            dots: true
                        },
                        1000: {
                            items: 1,
                            nav: false,
                            dots: true
                        }
                    }
                });

                // Custom navigation
                jQuery('.owl-prev').click(function () {
                    jQuery('.event-hero-carousel').trigger('prev.owl.carousel');
                });

                jQuery('.owl-next').click(function () {
                    jQuery('.event-hero-carousel').trigger('next.owl.carousel');
                });
            }
        }

        // Start the initialization now
        checkAndInitialize();
    });

    // Copy event link function
    function copyEventLink() {
        const url = window.location.href;
        
        if (navigator.clipboard && window.isSecureContext) {
            // Use modern clipboard API
            navigator.clipboard.writeText(url).then(function() {
                showCopySuccess();
            }).catch(function(err) {
                fallbackCopyTextToClipboard(url);
            });
        } else {
            // Fallback for older browsers
            fallbackCopyTextToClipboard(url);
        }
    }

    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        textArea.style.opacity = "0";
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess();
            } else {
                showCopyError();
            }
        } catch (err) {
            showCopyError();
        }
        
        document.body.removeChild(textArea);
    }

    function showCopySuccess() {
        // Create a temporary toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300';
        toast.textContent = 'Link copied to clipboard!';
        document.body.appendChild(toast);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    function showCopyError() {
        // Create a temporary error toast
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300';
        toast.textContent = 'Failed to copy link. Please try again.';
        document.body.appendChild(toast);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

</script>
@endpush
@endsection
