@extends('layouts.admin')

@section('title', 'Unclaimed Artists - Admin Panel')
@section('description', 'List of artists without linked user accounts')

@section('content')
<div class="p-6">
    <!-- learn how to add a link to the page that says "Learn how to claim an artist" -->
    <div class="mb-4">
        <a href="{{ url('/ARTIST_CLAIMING_PROCESS_SIMPLE.html') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Learn how to claim an artist
        </a>
    </div>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Unclaimed Artists</h1>
        <form method="GET" id="ajax-search-form" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
            @if(request('search'))
            <a href="#" onclick="event.preventDefault(); ajaxSearchInstance.clearFilters();" class="text-purple-600 hover:text-purple-800 px-4 py-2 flex items-center font-medium transition-colors duration-200">
                Clear Filters
            </a>
            @endif
        </form>
    </div>

    <div id="ajax-results">
    <div class="bg-white rounded-lg border border-gray-200">
        <table class="min-w-full table-fixed divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <!-- Checkbox -->
                    <th class="w-10 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="select-all-artists" class="form-checkbox h-4 w-4 text-purple-600">
                    </th>
                    <th class="ppCol px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profile Picture</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stage Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Genre</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($unclaimed as $artist)
                <tr>
                    <!-- Checkbox -->
                    <td class="w-10 px-4 py-3">
                        <input type="checkbox" name="artist_id[]" value="{{ $artist->id }}">
                    </td>
                    <!-- Profile Picture -->
                    <td class="ppCol px-4 py-3 overflow-hidden">
                        @if($artist->profile_picture)
                            <img src="{{ Storage::url($artist->profile_picture) }}" alt="{{ $artist->stage_name }}" class="w-10 h-10 rounded-full">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        @endif
                    </td> 
                    <td class="px-4 py-3">{{ $artist->stage_name }}</td>
                    <!-- email -->
                    <td class="px-4 py-3">
                        @if($artist->contact_email)
                            {{ $artist->contact_email }}
                        @else
                            <span class="text-gray-400 italic">No email</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $artist->genre }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $artist->created_at?->diffForHumans() }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.unclaimed-artists.edit', $artist) }}" class="text-purple-600 hover:text-purple-800 font-medium transition-colors duration-200">
                            Edit
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">No unclaimed artists found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $unclaimed->links() }}</div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/ajax-search.js') }}"></script>
<script>
    let ajaxSearchInstance;
    document.addEventListener('DOMContentLoaded', function() {
        ajaxSearchInstance = AjaxSearch.init();
    });
</script>
@endpush
@endsection

