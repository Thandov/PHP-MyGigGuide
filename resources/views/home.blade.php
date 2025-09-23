@extends('layouts.app')

@section('title', 'Home - Discover Amazing Events')
@section('description', 'Find the best concerts, festivals, and events happening in your area')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50">
  
  <!-- Clean Hero Section -->
  <section class="relative py-20 lg:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center">
        <!-- Simple Icon -->
        <div class="flex justify-center mb-8">
          <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-4 rounded-2xl shadow-sm">
            <svg class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
            </svg>
          </div>
        </div>

        <!-- Clean Title with Rotating Words -->
        <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
          Discover Amazing 
          <span class="relative inline-block min-w-[200px] md:min-w-[300px]" id="rotating-words-container">
            <span class="absolute top-0 left-0 bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent transition-all duration-1000 ease-in-out transform opacity-100 translate-y-0 scale-100" id="word-0">Events</span>
            <span class="absolute top-0 left-0 bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent transition-all duration-1000 ease-in-out transform opacity-0 translate-y-4 scale-95" id="word-1">Artists</span>
            <span class="absolute top-0 left-0 bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent transition-all duration-1000 ease-in-out transform opacity-0 translate-y-4 scale-95" id="word-2">Concerts</span>
            <span class="absolute top-0 left-0 bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent transition-all duration-1000 ease-in-out transform opacity-0 translate-y-4 scale-95" id="word-3">Festivals</span>
            <!-- Invisible placeholder to maintain consistent width -->
            <span class="opacity-0 select-none bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
              Festivals
            </span>
          </span>
        </h1>
        
        <p class="text-xl md:text-2xl text-gray-600 mb-12 max-w-3xl mx-auto">
          Find the best concerts, festivals, and events happening in your area
        </p>

        <!-- Clean Search Bar -->
        <div class="max-w-2xl mx-auto mb-8">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <form action="{{ route('events.index') }}" method="GET" class="relative">
              <input
                type="text"
                name="search"
                placeholder="Search events, artists, venues..."
                class="block w-full pl-12 pr-32 py-4 border border-purple-200 rounded-2xl text-lg placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white/80 backdrop-blur-sm shadow-sm"
              />
              <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                <button
                  type="submit"
                  class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-medium py-2 px-6 rounded-xl transition-all duration-300 flex items-center gap-2 hover:scale-105 shadow-sm"
                >
                  <span>Search</span>
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Clean Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <a 
            href="{{ route('events.index') }}"
            class="bg-white border border-purple-200 text-purple-700 hover:bg-purple-50 px-8 py-3 rounded-xl font-medium transition-all duration-300 flex items-center space-x-2 shadow-sm hover:shadow-md"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Browse All Events</span>
          </a>
          <a 
            href="{{ route('artists.index') }}"
            class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-xl font-medium transition-all duration-300 flex items-center space-x-2 shadow-sm hover:shadow-md"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
            </svg>
            <span>Discover Artists</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured (Paid) Sections -->
  <section class="py-8 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Featured Artists - Simple version like React -->
      @if($artists->count() > 0)
      <section class="my-8">
        <h3 class="text-lg font-semibold mb-3">Featured Artists This Week</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          @foreach($artists->take(4) as $artist)
          <a href="{{ route('artists.show', $artist) }}" class="block bg-white border rounded-lg p-4 hover:shadow">
            <div class="text-sm text-gray-600">Artist ID #{{ $artist->id }}</div>
            <div class="text-xs text-gray-500">{{ $artist->stage_name }}</div>
          </a>
          @endforeach
        </div>
      </section>
      @endif

      <!-- Featured Events - Simple version like React -->
      @if($events->count() > 0)
      <section class="my-8">
        <h3 class="text-lg font-semibold mb-3">Featured Events</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          @foreach($events->take(4) as $event)
          <a href="{{ route('events.show', $event) }}" class="block bg-white border rounded-lg p-4 hover:shadow">
            <div class="text-sm text-gray-600">Event ID #{{ $event->id }}</div>
            <div class="text-xs text-gray-500">{{ $event->name }}</div>
          </a>
          @endforeach
        </div>
      </section>
      @endif
    </div>
  </section>

  <!-- Live Events Map Section -->
  <section class="py-16 bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <div class="flex justify-center mb-6">
          <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-3 rounded-xl shadow-sm">
            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Events 
          <span class="bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
            Near You
          </span>
        </h2>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-8">
          Discover what's happening around your location. Click on any marker to see event details.
        </p>
      </div>

      <!-- Map Container -->
      <div class="relative">
        <x-google-map 
          :events="$events" 
          height="400px" 
          :show-legend="true" 
          :compact="false"
          id="home-map"
        />
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-white border border-purple-100 rounded-xl p-6 text-center shadow-sm">
          <div class="text-2xl font-bold text-purple-600 mb-2">{{ $events->count() }}</div>
          <div class="text-gray-600">Upcoming Events</div>
        </div>
        <div class="bg-white border border-purple-100 rounded-xl p-6 text-center shadow-sm">
          <div class="text-2xl font-bold text-blue-600 mb-2">{{ $venues->count() }}</div>
          <div class="text-gray-600">Active Venues</div>
        </div>
        <div class="bg-white border border-purple-100 rounded-xl p-6 text-center shadow-sm">
          <div class="text-2xl font-bold text-pink-600 mb-2">{{ $events->count() > 0 ? 'Live' : 'None' }}</div>
          <div class="text-gray-600">Map Status</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured Events Section -->
  <section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Happening 
          <span class="bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
            Soon
          </span>
        </h2>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
          Don't miss out on these amazing upcoming events
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @forelse($events->take(3) as $event)
        <div class="bg-white border border-purple-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-300 hover:scale-105">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">{{ $event->name }}</h3>
            <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm font-medium">
              @if($event->price)
                R{{ number_format($event->price, 2) }}
              @else
                Free
              @endif
            </span>
          </div>
          
          <div class="space-y-3 text-gray-600">
            @if($event->artists->count() > 0)
            <div class="flex items-center">
              <svg class="h-4 w-4 text-purple-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
              </svg>
              <span>{{ $event->artists->first()->stage_name ?? 'Various Artists' }}</span>
            </div>
            @endif
            @if($event->venue)
            <div class="flex items-center">
              <svg class="h-4 w-4 text-purple-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>{{ $event->venue->name }}</span>
            </div>
            @endif
            <div class="flex items-center">
              <svg class="h-4 w-4 text-purple-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>{{ $event->date->format('M d, Y') }}</span>
            </div>
            <div class="flex items-center">
              <svg class="h-4 w-4 text-purple-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ $event->time->format('H:i A') }}</span>
            </div>
          </div>

          <a
            href="{{ route('events.show', $event) }}"
            class="mt-6 w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white py-3 px-4 rounded-xl font-medium transition-all duration-300 flex items-center justify-center gap-2 hover:scale-105"
          >
            <span>View Details</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No events</h3>
          <p class="mt-1 text-sm text-gray-500">No upcoming events found.</p>
        </div>
        @endforelse
      </div>

      <div class="text-center mt-12">
        <a
          href="{{ route('events.index') }}"
          class="inline-flex items-center text-purple-600 hover:text-purple-700 font-medium"
        >
          View all events
          <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </a>
      </div>
    </div>
  </section>

  <!-- Simple CTA Section -->
  <section class="py-16 bg-gradient-to-r from-purple-50 to-blue-50">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
      <h2 class="text-3xl font-bold text-gray-900 mb-4">
        Ready to Experience Something Amazing?
      </h2>
      <p class="text-lg text-gray-600 mb-8">
        Join thousands of music lovers discovering the best events in their city
      </p>
      <a
        href="{{ route('events.index') }}"
        class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-8 py-4 rounded-2xl font-semibold text-lg transition-all duration-300 shadow-sm hover:shadow-md hover:scale-105"
      >
        Start Exploring
      </a>
    </div>
  </section>
</div>

@push('scripts')
<script>
// Rotating words animation - Exact React logic
document.addEventListener('DOMContentLoaded', function() {
    // State management (equivalent to React useState)
    let currentWordIndex = 0;
    const rotatingWords = ['Events', 'Artists', 'Concerts', 'Festivals'];
    
    // Find the rotating words container
    const container = document.getElementById('rotating-words-container');
    if (!container) return;
    
    // Get all word spans
    const wordSpans = rotatingWords.map((word, index) => {
        return document.getElementById(`word-${index}`);
    });
    
    // Auto-rotate words every 5 seconds (exact React useEffect logic)
    const interval = setInterval(() => {
        // Hide current word
        wordSpans[currentWordIndex].classList.remove('opacity-100', 'translate-y-0', 'scale-100');
        wordSpans[currentWordIndex].classList.add('opacity-0', '-translate-y-4', 'scale-95');
        
        // Move to next word (exact React logic)
        currentWordIndex = (currentWordIndex + 1) % rotatingWords.length;
        
        // Show next word
        wordSpans[currentWordIndex].classList.remove('opacity-0', 'translate-y-4', 'scale-95');
        wordSpans[currentWordIndex].classList.add('opacity-100', 'translate-y-0', 'scale-100');
    }, 5000);
    
    // Cleanup function (equivalent to React useEffect cleanup)
    return () => clearInterval(interval);
});
</script>
@endpush
@endsection