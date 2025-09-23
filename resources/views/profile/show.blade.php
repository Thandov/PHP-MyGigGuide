@extends('layouts.app')

@section('title', 'Profile - My Gig Guide')
@section('description', 'View and manage your profile information.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Profile</h1>
                    <p class="text-gray-600 mt-2">Manage your account information and preferences</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Profile
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Information -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Account Information</h2>
                    
                    <div class="space-y-6">
                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                <p class="text-gray-900">{{ $user->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                                <p class="text-gray-900">{{ $user->username }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <p class="text-gray-900">{{ $user->email }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Account Type</label>
                                <div class="flex space-x-2">
                                    @foreach($user->roles as $role)
                                        <span class="inline-block px-3 py-1 text-sm font-medium bg-purple-100 text-purple-800 rounded-full">
                                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Role-specific Information -->
                        @if($profile)
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                    @if($user->hasRole('artist'))
                                        Artist Profile
                                    @elseif($user->hasRole('organiser'))
                                        Organization Profile
                                    @endif
                                </h3>
                                
                                @if($user->hasRole('artist'))
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Stage Name</label>
                                            <p class="text-gray-900">{{ $profile->stage_name ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Real Name</label>
                                            <p class="text-gray-900">{{ $profile->real_name ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Genre</label>
                                            <p class="text-gray-900">{{ $profile->genre ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                                            <p class="text-gray-900">{{ $profile->contact_phone ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                                            <p class="text-gray-900">{{ $profile->contact_email ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                                            <p class="text-gray-900">
                                                @if($profile->website)
                                                    <a href="{{ $profile->website }}" target="_blank" class="text-purple-600 hover:text-purple-700">
                                                        {{ $profile->website }}
                                                    </a>
                                                @else
                                                    Not set
                                                @endif
                                            </p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                                            <p class="text-gray-900">{{ $profile->bio ?? 'No bio available' }}</p>
                                        </div>
                                    </div>
                                @elseif($user->hasRole('organiser'))
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Organization Name</label>
                                            <p class="text-gray-900">{{ $profile->organisation_name ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                                            <p class="text-gray-900">{{ $profile->contact_phone ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                                            <p class="text-gray-900">{{ $profile->contact_email ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                                            <p class="text-gray-900">
                                                @if($profile->website)
                                                    <a href="{{ $profile->website }}" target="_blank" class="text-purple-600 hover:text-purple-700">
                                                        {{ $profile->website }}
                                                    </a>
                                                @else
                                                    Not set
                                                @endif
                                            </p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                            <p class="text-gray-900">{{ $profile->description ?? 'No description available' }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Profile Picture -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Profile Picture</h3>
                    <div class="flex items-center space-x-4">
                        <div class="h-20 w-20 rounded-full bg-purple-100 flex items-center justify-center">
                            <span class="text-purple-600 text-2xl font-bold">
                                {{ substr($user->name, 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Profile picture</p>
                            <button class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                Upload new photo
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Account Stats -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Stats</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Member since</span>
                            <span class="font-medium">{{ $user->created_at->format('M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Last updated</span>
                            <span class="font-medium">{{ $user->updated_at->diffForHumans() }}</span>
                        </div>
                        @if($user->email_verified_at)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email verified</span>
                                <span class="text-green-600 font-medium">Yes</span>
                            </div>
                        @else
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email verified</span>
                                <span class="text-red-600 font-medium">No</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('profile.edit') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                            Edit Profile
                        </a>
                        <a href="{{ route('dashboard') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                            Go to Dashboard
                        </a>
                        @if($user->hasRole('artist'))
                            <a href="{{ route('events.create') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                                Create Event
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

