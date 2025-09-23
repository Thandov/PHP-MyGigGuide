@extends('layouts.app')

@section('title', $event->name . ' - My Gig Guide')

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
                } catch (Exception $e) {
                    $galleryImages = [];
                }
            }
            $mainImage = $event->poster ?: ($galleryImages[0] ?? null);
        @endphp

        @if(count($galleryImages) > 1)
            <!-- Owl Carousel for multiple gallery images -->
            <div class="owl-carousel owl-theme event-hero-carousel w-full h-full">
                @foreach($galleryImages as $index => $image)
                    <div class="item relative w-full h-full">
                        <img
                            src="{{ Storage::url($image) }}"
                            alt="{{ $event->name }} - Image {{ $index + 1 }}"
                            class="w-full h-full object-cover"
                        >
                        <div class="absolute inset-0 bg-black/40"></div>
                    </div>
                @endforeach
            </div>
            
            <!-- Custom Navigation Controls -->
            <div class="absolute inset-0 pointer-events-none">
                <button class="owl-prev absolute left-4 top-1/2 -translate-y-1/2 p-2 bg-white/20 hover:bg-white/30 rounded-full transition-all pointer-events-auto">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button class="owl-next absolute right-4 top-1/2 -translate-y-1/2 p-2 bg-white/20 hover:bg-white/30 rounded-full transition-all pointer-events-auto">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                
                <!-- Dots Container -->
                <div class="owl-dots absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2"></div>
            </div>
        @elseif($mainImage)
            <!-- Single image background -->
            <div class="relative w-full h-full">
                <img
                    src="{{ Storage::url($mainImage) }}"
                    alt="{{ $event->name }}"
                    class="w-full h-full object-cover"
                >
                <div class="absolute inset-0 bg-black/40"></div>
            </div>
        @else
            <!-- Fallback gradient background -->
            <div class="w-full h-full bg-gradient-to-br from-purple-600 to-blue-600"></div>
        @endif
        
        <!-- Hero Content -->
        <div class="absolute inset-0 flex items-end">
            <div class="w-full bg-gradient-to-t from-black/80 to-transparent p-6 md:p-8">
                <div class="max-w-7xl mx-auto">
                    <div class="flex items-end justify-between">
                        <div class="text-white">
                            <h1 class="text-4xl md:text-6xl font-bold mb-2">{{ $event->name }}</h1>
                            <div class="flex items-center space-x-6 text-lg">
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>{{ $event->date->format('l, F j, Y') }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>{{ $event->time ?: 'TBD' }}</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>{{ $event->venue->name ?? 'Venue TBD' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <button class="p-3 bg-white/20 hover:bg-white/30 rounded-full transition-all">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </button>
                            <button class="p-3 bg-white/20 hover:bg-white/30 rounded-full transition-all">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                </svg>
                            </button>
                            @auth
                                @if(auth()->id() == $event->owner_id)
                                    <a href="{{ route('events.edit', $event) }}" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-all">
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                    {{ $event->category ?: 'General' }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ $event->capacity ? $event->capacity . ' capacity' : 'Open event' }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    {{ $event->status ?: 'Active' }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="space-y-4">
                            <p class="text-gray-500 italic">No description provided for this event.</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                    {{ $event->category ?: 'General' }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Event Statistics -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Event Statistics</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">{{ rand(50, 200) }}</div>
                            <div class="text-sm text-gray-600">Interested</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">4.2</div>
                            <div class="text-sm text-gray-600">Rating</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">{{ $event->capacity ?: '∞' }}</div>
                            <div class="text-sm text-gray-600">Capacity</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">{{ count($galleryImages) }}</div>
                            <div class="text-sm text-gray-600">Photos</div>
                        </div>
                    </div>
                </div>
                <!-- Booked Artists -->
                @if($event->artists->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Booked Artists</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($event->artists as $artist)
                        <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                            @if($artist->profile_picture)
                            <img src="{{ Storage::url($artist->profile_picture) }}" alt="{{ $artist->stage_name }}" class="h-12 w-12 rounded-full object-cover">
                            @else
                            <div class="h-12 w-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <span class="text-purple-600 font-semibold">{{ substr($artist->stage_name, 0, 1) }}</span>
                            </div>
                            @endif
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">{{ $artist->stage_name }}</p>
                                @if($artist->genre)
                                <p class="text-sm text-gray-500">{{ $artist->genre }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- What to Expect -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">What to Expect</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start space-x-3">
                            <svg class="h-6 w-6 text-green-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900">Live Performance</p>
                                <p class="text-gray-600 text-sm">Experience amazing live entertainment</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="h-6 w-6 text-green-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900">Great Atmosphere</p>
                                <p class="text-gray-600 text-sm">Connect with fellow music lovers</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="h-6 w-6 text-green-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900">Quality Sound</p>
                                <p class="text-gray-600 text-sm">Professional audio equipment</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="h-6 w-6 text-green-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-8">
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
                            <a
                                href="{{ $event->ticket_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-full inline-flex items-center justify-center px-6 py-3 bg-purple-600 text-white rounded-xl font-semibold hover:bg-purple-700 transition-all shadow-lg hover:shadow-xl"
                            >
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                </svg>
                                Get Tickets Now
                            </a>
                        @else
                            <button class="w-full inline-flex items-center justify-center px-6 py-3 bg-gray-400 text-white rounded-xl font-semibold cursor-not-allowed">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                </svg>
                                Tickets Not Available
                            </button>
                        @endif

                        @if($event->capacity)
                            <p class="text-sm text-gray-600">{{ $event->capacity }} spots available</p>
                        @endif
                    </div>
                </div>

                <!-- Event Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Date & Time -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center space-x-3 mb-2">
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span class="font-semibold text-gray-900">Category</span>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            {{ $event->category ?: 'General' }}
                        </span>
                    </div>

                    <!-- Attendees & Rating -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <div class="flex items-center space-x-3 mb-2">
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="font-semibold text-gray-900">Interest</span>
                        </div>
                        <p class="text-gray-700">{{ rand(50, 200) }} interested</p>
                        <div class="flex items-center space-x-1 mt-1">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="h-4 w-4 {{ $i <= 4 ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            @endfor
                            <span class="text-sm text-gray-600 ml-1">4.2/5</span>
                        </div>
                    </div>
                </div>

                <!-- Event Gallery -->
                @if(count($galleryImages) > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Event Gallery</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($galleryImages as $index => $image)
                                <div class="relative group cursor-pointer">
                                    <img
                                        src="{{ Storage::url($image) }}"
                                        alt="{{ $event->name }} - Image {{ $index + 1 }}"
                                        class="w-full h-32 object-cover rounded-lg shadow-sm group-hover:shadow-md transition-shadow"
                                    >
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all rounded-lg flex items-center justify-center">
                                        <svg class="h-6 w-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Event Tips -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Event Tips</h2>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <p class="text-gray-700 text-sm">Arrive 15-30 minutes early for the best seats</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <p class="text-gray-700 text-sm">Bring a valid ID for age verification</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <p class="text-gray-700 text-sm">Check venue parking options in advance</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
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
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="h-4 w-4 {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
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
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Owl Carousel for event hero images
    if (document.querySelector('.event-hero-carousel')) {
        $('.event-hero-carousel').owlCarousel({
            items: 1,
            loop: true,
            autoplay: true,
            autoplayTimeout: 5000, // 5 seconds
            autoplayHoverPause: true,
            animateOut: 'fadeOut',
            animateIn: 'fadeIn',
            nav: false,
            dots: true,
            dotsContainer: '.owl-dots',
            smartSpeed: 1000,
            fluidSpeed: 1000,
            dragEndSpeed: 1000,
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
            animateOut: 'fadeOut',
            animateIn: 'fadeIn',
            navText: ['<i class="fa fa-angle-left" aria-hidden="true"></i>', '<i class="fa fa-angle-right" aria-hidden="true"></i>'],
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
        $('.owl-prev').click(function() {
            $('.event-hero-carousel').trigger('prev.owl.carousel');
        });
        
        $('.owl-next').click(function() {
            $('.event-hero-carousel').trigger('next.owl.carousel');
        });
    }
});
</script>
@endpush
@endsection
