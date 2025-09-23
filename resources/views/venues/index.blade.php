@extends('layouts.app')

@section('title', 'Venues - My Gig Guide')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Venues</h1>
                    <p class="mt-2 text-gray-600">Discover amazing venues for your next event</p>
                </div>
                
                @auth
                <div class="mt-4 md:mt-0">
                    <a href="{{ route('venues.create') }}" class="btn-primary">
                        <div class="btn-content">
                            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add New Venue
                        </div>
                    </a>
                </div>
                @endauth
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Filter by:</label>
                    <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option>All Venues</option>
                        <option>Large Venues (500+ capacity)</option>
                        <option>Medium Venues (100-500 capacity)</option>
                        <option>Small Venues (Under 100 capacity)</option>
                    </select>
                </div>
                
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">Sort by:</label>
                    <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option>Newest First</option>
                        <option>Name A-Z</option>
                        <option>Capacity</option>
                        <option>Most Events</option>
                    </select>
                </div>
                
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <input
                            type="text"
                            placeholder="Search venues..."
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 pl-10 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        >
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Venues Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($venues->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($venues as $venue)
                    <x-venue-card :venue="$venue" />
                @endforeach
            </div>

            <!-- Pagination -->
            @if($venues->hasPages())
            <div class="mt-8">
                {{ $venues->links() }}
            </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No venues found</h3>
                <p class="text-gray-500 mb-6">Be the first to add a venue to our platform!</p>
                @auth
                <a href="{{ route('venues.create') }}" class="btn-primary">
                    <div class="btn-content">
                        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Your First Venue
                    </div>
                </a>
                @endauth
            </div>
        @endif
    </div>
</div>
@endsection
