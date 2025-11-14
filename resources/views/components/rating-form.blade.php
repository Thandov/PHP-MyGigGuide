@props([
    'model' => null, // The item being rated (Artist, Event, Venue, etc.)
    'type' => 'artist', // 'artist', 'event', 'venue', 'organiser'
    'showReviews' => true,
    'maxReviews' => 5
])

@auth
<div class="rating-section bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Rate this {{ ucfirst($type) }}</h3>
    
    <!-- Star Rating Selection -->
    <div class="text-center mb-6">
        <div class="flex justify-center space-x-2" id="star-rating">
            @for($i = 1; $i <= 5; $i++)
                <button type="button" class="star-btn text-4xl text-gray-300 hover:text-yellow-400 transition-all duration-300 transform hover:scale-110" data-rating="{{ $i }}">
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.888c-.783.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </button>
            @endfor
        </div>
        <p class="text-sm text-gray-500 mt-2">Click a star to rate this {{ $type }}</p>
    </div>

    <!-- Review Form Modal/Dropdown (Hidden Initially) -->
    <div id="review-form-container" class="hidden">
        <div class="bg-gradient-to-br from-purple-50 to-blue-50 rounded-xl p-6 border border-purple-200">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-900">Your Rating</h4>
                <button type="button" id="close-review-form" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="mb-4">
                <div class="flex justify-center space-x-1" id="selected-rating-display">
                    <!-- Selected stars will be displayed here -->
                </div>
                <p class="text-center text-sm text-gray-600 mt-2" id="rating-text"></p>
            </div>
            
            <form id="rating-form" class="space-y-4">
                @csrf
                <input type="hidden" name="rateable_type" value="{{ get_class($model) }}">
                <input type="hidden" name="rateable_id" value="{{ $model->id }}">
                <input type="hidden" name="rating" id="rating-input" value="">
                
                <div>
                    <label for="review" class="block text-sm font-medium text-gray-700 mb-2">Your Review (Optional)</label>
                    <textarea id="review" name="review" rows="4" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                              placeholder="Share your experience with this {{ $type }}..."></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" id="cancel-rating" 
                            class="flex-1 bg-gray-200 text-gray-700 py-3 px-6 rounded-lg hover:bg-gray-300 transition-colors duration-200 font-medium">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 bg-purple-600 text-white py-3 px-6 rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors duration-200 font-medium">
                        Submit Rating
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Recent Reviews -->
    @if($showReviews)
        <div class="mt-8">
            <x-reviews-section :model="$model" :type="$type" :maxInitialReviews="$maxReviews" />
        </div>
    @endif
</div>
@else
<div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 text-center">
    <p class="text-gray-600 mb-4">Please <a href="{{ route('login') }}" class="text-purple-600 hover:text-purple-700 font-medium">login</a> to rate this {{ $type }}.</p>
</div>
@endauth

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const starButtons = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('rating-input');
    const form = document.getElementById('rating-form');
    const reviewFormContainer = document.getElementById('review-form-container');
    const selectedRatingDisplay = document.getElementById('selected-rating-display');
    const ratingText = document.getElementById('rating-text');
    const closeReviewForm = document.getElementById('close-review-form');
    const cancelRating = document.getElementById('cancel-rating');
    
    let selectedRating = 0;
    
    // Rating text descriptions
    const ratingDescriptions = {
        1: 'Poor - Not recommended',
        2: 'Fair - Below average',
        3: 'Good - Average experience',
        4: 'Very Good - Above average',
        5: 'Excellent - Highly recommended'
    };
    
    // Star rating functionality
    starButtons.forEach(button => {
        button.addEventListener('click', function() {
            selectedRating = parseInt(this.dataset.rating);
            ratingInput.value = selectedRating;
            
            // Update star display
            updateStarDisplay(selectedRating);
            
            // Show the review form modal
            showReviewForm();
        });
        
        // Hover effects for initial stars
        button.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            updateStarDisplay(rating);
        });
        
        button.addEventListener('mouseleave', function() {
            updateStarDisplay(selectedRating);
        });
    });
    
    function updateStarDisplay(rating) {
        starButtons.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }
    
    function showReviewForm() {
        // Update the selected rating display in the modal
        selectedRatingDisplay.innerHTML = '';
        for (let i = 1; i <= 5; i++) {
            const star = document.createElement('div');
            star.innerHTML = `
                <svg class="w-6 h-6 ${i <= selectedRating ? 'text-yellow-400' : 'text-gray-300'}" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.888c-.783.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            `;
            selectedRatingDisplay.appendChild(star);
        }
        
        // Update rating text
        ratingText.textContent = ratingDescriptions[selectedRating];
        
        // Show the modal with animation
        reviewFormContainer.classList.remove('hidden');
        reviewFormContainer.style.opacity = '0';
        reviewFormContainer.style.transform = 'translateY(-20px)';
        
        // Animate in
        setTimeout(() => {
            reviewFormContainer.style.transition = 'all 0.3s ease-out';
            reviewFormContainer.style.opacity = '1';
            reviewFormContainer.style.transform = 'translateY(0)';
        }, 10);
    }
    
    function hideReviewForm() {
        reviewFormContainer.style.transition = 'all 0.3s ease-out';
        reviewFormContainer.style.opacity = '0';
        reviewFormContainer.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            reviewFormContainer.classList.add('hidden');
            // Reset form
            form.reset();
            ratingInput.value = '';
            selectedRating = 0;
            updateStarDisplay(0);
        }, 300);
    }
    
    // Close modal handlers
    if (closeReviewForm) {
        closeReviewForm.addEventListener('click', hideReviewForm);
    }
    
    if (cancelRating) {
        cancelRating.addEventListener('click', hideReviewForm);
    }
    
    // Click outside to close
    if (reviewFormContainer) {
        reviewFormContainer.addEventListener('click', function(e) {
            if (e.target === this) {
                hideReviewForm();
            }
        });
    }
    
    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';
            
            fetch('{{ route("ratings.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const contentType = response.headers.get('content-type') || '';
                let data;
                if (contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    // If not JSON (e.g. redirected HTML), synthesize an error
                    data = { success: false, message: 'Unexpected response. Please ensure you are logged in.' };
                }
                return { ok: response.ok, status: response.status, data };
            })
            .then(({ ok, status, data }) => {
                if (data && data.success) {
                    // Show success message
                    alert('Rating submitted successfully!');
                    
                    // Hide modal and reset
                    hideReviewForm();
                    
                    // Reload page to show updated rating
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Build friendly error from validation
                    let msg = 'Failed to submit rating';
                    if (data && data.message) msg = data.message;
                    if (data && data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey) {
                            msg = data.errors[firstKey][0] || msg;
                        }
                    }
                    alert(msg);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the rating');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });
    }
});
</script>
@endpush
