@extends('layouts.admin')

@section('title', 'Venues Management - Admin Panel')
@section('description', 'Manage all venues in the system.')

@section('styles')
<style>
    .skeleton-loader {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: skeleton-loading 1.5s infinite;
    }

    @keyframes skeleton-loading {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }

    .lazy-image {
        transition: opacity 0.3s ease-in-out;
    }

    .lazy-image-container {
        position: relative;
    }
</style>
@endsection

@section('content')
<div class="p-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('info') }}</span>
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Venues Management</h1>
            <p class="text-gray-600">Manage all venues in the system</p>
        </div>
        
        <div class="flex space-x-3">
            <!-- Import Venues Button -->
            <form method="POST" action="{{ route('admin.venues.import') }}" onsubmit="return confirm('Import venues from Excel spreadsheet? This will add new venues that don\'t exist yet.');">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span>Import from Excel</span>
                </button>
            </form>

            <!-- Create Venue Button -->
            <a href="{{ route('admin.venues.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors duration-200 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Create Venue</span>
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" id="ajax-search-form" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search venues..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            <div>
                <select name="venue_type" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="concert_hall" {{ request('venue_type') == 'concert_hall' ? 'selected' : '' }}>Concert Hall</option>
                    <option value="club" {{ request('venue_type') == 'club' ? 'selected' : '' }}>Club</option>
                    <option value="bar" {{ request('venue_type') == 'bar' ? 'selected' : '' }}>Bar</option>
                    <option value="outdoor" {{ request('venue_type') == 'outdoor' ? 'selected' : '' }}>Outdoor</option>
                    <option value="theater" {{ request('venue_type') == 'theater' ? 'selected' : '' }}>Theater</option>
                    <option value="stadium" {{ request('venue_type') == 'stadium' ? 'selected' : '' }}>Stadium</option>
                    <option value="other" {{ request('venue_type') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            @if(request()->hasAny(['search', 'venue_type']))
            <a href="#" data-clear-filters class="text-purple-600 hover:text-purple-800 px-4 py-2 flex items-center font-medium transition-colors duration-200">
                Clear Filters
            </a>
            @endif
        </form>
    </div>

    <!-- Venues Table -->
    <div id="ajax-results">
    <form id="bulk-action-form" method="POST" action="{{ route('admin.venues.bulk-action') }}">
        @csrf
        <!-- DEBUG: Route URL = {{ route('admin.venues.bulk-action') }} -->
        <!-- DEBUG: CSRF Token = {{ csrf_token() }} -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <!-- Bulk Actions Top -->
            <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <select name="bulk_action" id="bulk-action-select" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Bulk Actions</option>
                        <option value="delete">Delete</option>
                        <option value="export">Export</option>
                    </select>
                    <button type="submit" class="px-4 py-1.5 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200" onclick="return confirmBulkAction(event)">
                        Apply
                    </button>
                    <span id="selected-count" class="text-sm text-gray-600">0 selected</span>
                </div>
            </div>

            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer" onclick="toggleAllCheckboxes(this)">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($venues as $venue)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="venue_ids[]" value="{{ $venue->id }}" class="venue-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer" onchange="updateSelectedCount()">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12">
                                    @if($venue->main_picture)
                                        <div class="lazy-image-container h-12 w-12 rounded-lg overflow-hidden bg-gray-200 relative">
                                            <!-- Skeleton loader -->
                                            <div class="skeleton-loader absolute inset-0 bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 animate-pulse"></div>
                                            <!-- Actual image -->
                                            <img 
                                                class="lazy-image h-12 w-12 rounded-lg object-cover opacity-0 transition-opacity duration-300" 
                                                data-src="{{ Storage::url($venue->main_picture) }}" 
                                                alt="{{ $venue->name }}"
                                                onload="this.style.opacity='1'; this.previousElementSibling.style.display='none';"
                                                onerror="this.style.opacity='0'; this.previousElementSibling.style.display='flex'; this.previousElementSibling.innerHTML='<svg class=\'h-6 w-6 text-gray-400\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\' /></svg>';"
                                            >
                                        </div>
                                    @else
                                        <div class="h-12 w-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $venue->name }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($venue->description, 30) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                {{ ucfirst(str_replace('_', ' ', $venue->venue_type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $venue->address }}</div>
                            <div class="text-sm text-gray-500">{{ $venue->city }}, {{ $venue->country }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($venue->capacity) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $venue->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.venues.show', $venue) }}" class="text-purple-600 hover:text-purple-900">View</a>
                                <a href="{{ route('admin.venues.edit', $venue) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                <form method="POST" action="{{ route('admin.venues.destroy.post', $venue) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this venue?')">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No venues found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new venue.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Bulk Actions Bottom -->
        @if($venues->count() > 0)
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center space-x-3">
                <select class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" onchange="syncBulkAction(this.value)">
                    <option value="">Bulk Actions</option>
                    <option value="delete">Delete</option>
                    <option value="export">Export</option>
                </select>
                <button type="submit" class="px-4 py-1.5 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200" onclick="return confirmBulkAction(event)">
                    Apply
                </button>
            </div>
        </div>
        @endif
        
        <!-- Pagination and Items Per Page -->
        <div class="px-6 py-4 border-t border-gray-200 bg-white">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <!-- Results Info and Per Page Selector -->
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-700">
                        @if($venues->total() > 0)
                            Showing 
                            <span class="font-medium">{{ $venues->firstItem() }}</span>
                            to 
                            <span class="font-medium">{{ $venues->lastItem() }}</span>
                            of 
                            <span class="font-medium">{{ $venues->total() }}</span>
                            results
                        @else
                            No results found
                        @endif
                    </div>
                    
                    @if($venues->total() > 0)
                    <div class="flex items-center gap-2">
                        <label for="per-page" class="text-sm text-gray-600">Show:</label>
                        <select id="per-page" onchange="changePerPage(this.value)" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    @endif
                </div>

                <!-- Pagination Links -->
                @if($venues->hasPages())
                <nav class="inline-flex rounded-md shadow-sm" role="navigation">
                    <div class="flex items-center space-x-1">
                        {{-- First Page --}}
                        @if($venues->onFirstPage())
                            <span class="px-3 py-2 text-sm text-gray-400 bg-white border border-gray-300 rounded-l-md cursor-not-allowed">
                                « First
                            </span>
                        @else
                            <a href="{{ $venues->url(1) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 transition-colors">
                                « First
                            </a>
                        @endif

                        {{-- Previous Page --}}
                        @if($venues->onFirstPage())
                            <span class="px-3 py-2 text-sm text-gray-400 bg-white border-t border-b border-gray-300 cursor-not-allowed">
                                ‹
                            </span>
                        @else
                            <a href="{{ $venues->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300 hover:bg-gray-50 transition-colors">
                                ‹
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @php
                            $start = max($venues->currentPage() - 2, 1);
                            $end = min($start + 4, $venues->lastPage());
                            $start = max($end - 4, 1);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $venues->url(1) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300 hover:bg-gray-50 transition-colors">
                                1
                            </a>
                            @if($start > 2)
                                <span class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300">
                                    ...
                                </span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            @if($page == $venues->currentPage())
                                <span class="px-3 py-2 text-sm font-semibold text-white bg-purple-600 border border-purple-600">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $venues->url($page) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300 hover:bg-gray-50 transition-colors">
                                    {{ $page }}
                                </a>
                            @endif
                        @endfor

                        @if($end < $venues->lastPage())
                            @if($end < $venues->lastPage() - 1)
                                <span class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300">
                                    ...
                                </span>
                            @endif
                            <a href="{{ $venues->url($venues->lastPage()) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300 hover:bg-gray-50 transition-colors">
                                {{ $venues->lastPage() }}
                            </a>
                        @endif

                        {{-- Next Page --}}
                        @if($venues->hasMorePages())
                            <a href="{{ $venues->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300 hover:bg-gray-50 transition-colors">
                                ›
                            </a>
                        @else
                            <span class="px-3 py-2 text-sm text-gray-400 bg-white border-t border-b border-gray-300 cursor-not-allowed">
                                ›
                            </span>
                        @endif

                        {{-- Last Page --}}
                        @if($venues->hasMorePages())
                            <a href="{{ $venues->url($venues->lastPage()) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 transition-colors">
                                Last »
                            </a>
                        @else
                            <span class="px-3 py-2 text-sm text-gray-400 bg-white border border-gray-300 rounded-r-md cursor-not-allowed">
                                Last »
                            </span>
                        @endif
                    </div>
                </nav>
                @endif
            </div>
        </div>
        </div>
    </form>
    </div>
</div>

<!-- Bulk Actions JavaScript -->
<script>
    // Toggle all checkboxes
    function toggleAllCheckboxes(source) {
        const checkboxes = document.querySelectorAll('.venue-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = source.checked;
        });
        updateSelectedCount();
    }

    // Update selected count
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.venue-checkbox:checked');
        const count = checkboxes.length;
        const countElement = document.getElementById('selected-count');
        
        if (count > 0) {
            countElement.textContent = count + ' selected';
            countElement.classList.add('font-semibold', 'text-purple-600');
        } else {
            countElement.textContent = '0 selected';
            countElement.classList.remove('font-semibold', 'text-purple-600');
        }

        // Update "select all" checkbox state
        const allCheckboxes = document.querySelectorAll('.venue-checkbox');
        const selectAllCheckbox = document.getElementById('select-all');
        
        if (count === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (count === allCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }

    // Confirm bulk action
    function confirmBulkAction(event) {
        const checkboxes = document.querySelectorAll('.venue-checkbox:checked');
        const action = document.getElementById('bulk-action-select').value;
        
        if (checkboxes.length === 0) {
            event.preventDefault();
            alert('Please select at least one venue.');
            return false;
        }
        
        if (!action) {
            event.preventDefault();
            alert('Please select an action.');
            return false;
        }
        
        if (action === 'delete') {
            const confirmed = confirm(`Are you sure you want to delete ${checkboxes.length} venue(s)? This action cannot be undone.`);
            if (!confirmed) {
                event.preventDefault();
                return false;
            }
        }
        
        return true;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateSelectedCount();
    });

    // Change items per page
    function changePerPage(perPage) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('page', 1); // Reset to first page
        window.location.href = url.toString();
    }

    // Sync bulk action between top and bottom dropdowns
    function syncBulkAction(value) {
        document.getElementById('bulk-action-select').value = value;
    }

    // Add error handling for form submission
    document.getElementById('bulk-action-form').addEventListener('submit', function(e) {
        console.log('=== FORM SUBMISSION DEBUG ===');
        console.log('Form action:', this.action);
        console.log('Form method:', this.method);
        console.log('CSRF token:', this.querySelector('input[name="_token"]').value);
        console.log('Selected venues:', Array.from(document.querySelectorAll('.venue-checkbox:checked')).map(cb => cb.value));
        console.log('Bulk action:', document.getElementById('bulk-action-select').value);
        console.log('Form submit event triggered');
        
        // Check if we have a valid CSRF token
        const csrfToken = this.querySelector('input[name="_token"]');
        if (!csrfToken || !csrfToken.value) {
            console.error('ERROR: No CSRF token found!');
            alert('Error: No CSRF token found. Please refresh the page and try again.');
            e.preventDefault();
            return false;
        }
        
        // Check if we have selected venues
        const selectedVenues = document.querySelectorAll('.venue-checkbox:checked');
        if (selectedVenues.length === 0) {
            console.error('ERROR: No venues selected!');
            alert('Please select at least one venue.');
            e.preventDefault();
            return false;
        }
        
        // Check if we have a bulk action selected
        const bulkAction = document.getElementById('bulk-action-select').value;
        if (!bulkAction) {
            console.error('ERROR: No bulk action selected!');
            alert('Please select a bulk action.');
            e.preventDefault();
            return false;
        }
        
        console.log('Form validation passed, submitting...');
    });

    // Add global error handler
    window.addEventListener('error', function(e) {
        console.error('JavaScript Error:', e.error);
    });

    // Modern Lazy Loading with Intersection Observer
    class ModernLazyLoader {
        constructor() {
            this.imageObserver = null;
            this.init();
        }

        init() {
            // Check if Intersection Observer is supported
            if ('IntersectionObserver' in window) {
                this.setupIntersectionObserver();
            } else {
                // Fallback for older browsers
                this.loadAllImages();
            }
        }

        setupIntersectionObserver() {
            const options = {
                root: null,
                rootMargin: '50px', // Start loading 50px before image comes into view
                threshold: 0.1
            };

            this.imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                        this.imageObserver.unobserve(entry.target);
                    }
                });
            }, options);

            // Observe all lazy images
            document.querySelectorAll('.lazy-image').forEach(img => {
                this.imageObserver.observe(img);
            });
        }

        loadImage(img) {
            const src = img.getAttribute('data-src');
            if (src) {
                img.src = src;
                img.removeAttribute('data-src');
            }
        }

        loadAllImages() {
            // Fallback: load all images immediately
            document.querySelectorAll('.lazy-image').forEach(img => {
                this.loadImage(img);
            });
        }
    }

    // Initialize modern lazy loading when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        new ModernLazyLoader();
    });
</script>

<script>
    // Lightweight inline AJAX search (fallback if external asset isn't available)
    (function(){
        class InlineAjaxSearch {
            constructor() {
                this.form = document.getElementById('ajax-search-form');
                this.results = document.getElementById('ajax-results');
                this.debounceTimer = null;
                if (!this.form || !this.results) return;
                this.bind();
            }
            bind(){
                // Inputs
                this.form.querySelectorAll('input[type="text"],input[type="search"],input[type="date"]').forEach(inp=>{
                    inp.addEventListener('input', ()=>{
                        clearTimeout(this.debounceTimer);
                        this.debounceTimer = setTimeout(()=>this.search(), 400);
                    });
                });
                // Selects
                this.form.querySelectorAll('select').forEach(sel=>{
                    sel.addEventListener('change', ()=>this.search());
                });
                // Submit
                this.form.addEventListener('submit', (e)=>{ e.preventDefault(); this.search(); });
                // Clear filters triggers
                document.querySelectorAll('[data-clear-filters]').forEach(el=>{
                    el.addEventListener('click', (e)=>{ e.preventDefault(); this.clearFilters(); });
                });
            }
            urlWithParams(extra={}){
                const fd = new FormData(this.form);
                Object.entries(extra).forEach(([k,v])=>fd.set(k,v));
                const params = new URLSearchParams(fd);
                return `${window.location.pathname}?${params.toString()}`;
            }
            search(){
                const url = this.urlWithParams();
                this.loading(true);
                fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest','Accept':'text/html' }})
                    .then(r=>r.text())
                    .then(html=>{
                        const doc = new DOMParser().parseFromString(html,'text/html');
                        const repl = doc.getElementById('ajax-results');
                        if (repl) {
                            this.results.innerHTML = repl.innerHTML;
                            window.history.pushState({}, '', url);
                            this.attachPerPage();
                        }
                    })
                    .finally(()=>this.loading(false));
            }
            attachPerPage(){
                const per = this.results.querySelector('#per-page');
                if (per) {
                    per.addEventListener('change', (e)=>{
                        const url = this.urlWithParams({ per_page: e.target.value, page: 1 });
                        this.load(url);
                    });
                }
                this.results.querySelectorAll('a[href*="page="]').forEach(a=>{
                    a.addEventListener('click', (e)=>{ e.preventDefault(); this.load(a.href); });
                });
            }
            load(url){
                this.loading(true);
                fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest','Accept':'text/html' }})
                    .then(r=>r.text())
                    .then(html=>{
                        const doc = new DOMParser().parseFromString(html,'text/html');
                        const repl = doc.getElementById('ajax-results');
                        if (repl) {
                            this.results.innerHTML = repl.innerHTML;
                            window.history.pushState({}, '', url);
                            this.attachPerPage();
                        }
                    })
                    .finally(()=>this.loading(false));
            }
            clearFilters(){
                this.form.querySelectorAll('input[type="text"],input[type="search"],input[type="date"]').forEach(i=>i.value='');
                this.form.querySelectorAll('select').forEach(s=>{ s.selectedIndex = 0; s.dispatchEvent(new Event('change', { bubbles:true })); });
                this.search();
            }
            loading(on){
                this.results.style.opacity = on ? '0.5' : '1';
                this.results.style.pointerEvents = on ? 'none' : 'auto';
            }
        }
        document.addEventListener('DOMContentLoaded', function(){
            window.ajaxSearchInstance = new InlineAjaxSearch();
        });
    })();
</script>
@endsection

