@extends('layouts.admin')

@section('title', 'Create Artist - Admin Panel')
@section('description', 'Create a new artist in the system.')

@section('content')
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Create New Artist</h1>
            <p class="text-gray-600">Add a new artist to the system</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.artists.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- User Selection -->
                    <div class="md:col-span-2">
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">User *</label>
                        <select id="user_id" name="user_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('user_id') border-red-500 @enderror">
                            <option value="">Select a user</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Stage Name -->
                    <div>
                        <label for="stage_name" class="block text-sm font-medium text-gray-700 mb-2">Stage Name *</label>
                        <input type="text" id="stage_name" name="stage_name" value="{{ old('stage_name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('stage_name') border-red-500 @enderror">
                        @error('stage_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Real Name -->
                    <div>
                        <label for="real_name" class="block text-sm font-medium text-gray-700 mb-2">Real Name</label>
                        <input type="text" id="real_name" name="real_name" value="{{ old('real_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('real_name') border-red-500 @enderror">
                        @error('real_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Genre -->
                    <div>
                        <label for="genre" class="block text-sm font-medium text-gray-700 mb-2">Genre *</label>
                        <select id="genre" name="genre" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('genre') border-red-500 @enderror">
                            <option value="">Select a genre</option>
                            <option value="rock" {{ old('genre') == 'rock' ? 'selected' : '' }}>Rock</option>
                            <option value="pop" {{ old('genre') == 'pop' ? 'selected' : '' }}>Pop</option>
                            <option value="jazz" {{ old('genre') == 'jazz' ? 'selected' : '' }}>Jazz</option>
                            <option value="classical" {{ old('genre') == 'classical' ? 'selected' : '' }}>Classical</option>
                            <option value="electronic" {{ old('genre') == 'electronic' ? 'selected' : '' }}>Electronic</option>
                            <option value="hip-hop" {{ old('genre') == 'hip-hop' ? 'selected' : '' }}>Hip-Hop</option>
                            <option value="country" {{ old('genre') == 'country' ? 'selected' : '' }}>Country</option>
                            <option value="other" {{ old('genre') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('genre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Experience Level -->
                    <div>
                        <label for="experience_level" class="block text-sm font-medium text-gray-700 mb-2">Experience Level</label>
                        <select id="experience_level" name="experience_level"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('experience_level') border-red-500 @enderror">
                            <option value="">Select experience level</option>
                            <option value="beginner" {{ old('experience_level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                            <option value="intermediate" {{ old('experience_level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                            <option value="advanced" {{ old('experience_level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                            <option value="professional" {{ old('experience_level') == 'professional' ? 'selected' : '' }}>Professional</option>
                        </select>
                        @error('experience_level')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bio -->
                    <div class="md:col-span-2">
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                        <textarea id="bio" name="bio" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('bio') border-red-500 @enderror">{{ old('bio') }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Profile Picture -->
                    <div class="md:col-span-2">
                        <label for="profile_picture" class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('profile_picture') border-red-500 @enderror">
                        @error('profile_picture')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-8">
                    <a href="{{ route('admin.artists.index') }}" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                        Create Artist
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

