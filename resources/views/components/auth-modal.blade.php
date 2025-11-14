<!-- Auth Modal -->
<div 
    x-data="{ modal: false }" 
    x-init="globalThis.authModal = () => $store.authModal = true;"
    x-show="modal"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    @click.self="modal = false"
    @keydown.esc.window="modal = false"
>
    <!-- Modal Box -->
    <div 
        class="bg-white/95 backdrop-blur-lg rounded-3xl shadow-2xl max-w-md w-full mx-4 relative transition-all duration-300 transform"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100" 
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-90 opacity-0"
    >
        <form action="/register" method="POST" x-data="{ formStep: 'social' }">
            @csrf
            
            <!-- Pass current page URL for continue redirect -->
            <input type="hidden" name="continue" value="{{ request()->fullUrl() }}">
            
            <div class="p-8">
                <!-- Header/Icon -->
                <div class="text-center mb-8">
                    <div class="h-20 w-20 mx-auto mb-6 bg-gradient-to-r from-pink-400 to-red-400 rounded-full flex items-center justify-center shadow-xl">
                        <svg class="h-10 w-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12,21.1L10.3,19.5L4.7,14L4.5,14.1L13.4,23.7L12,21.1ZM15,19.4L21.2,13.2L17.8,9.8L11.6,16.1L15,19.4ZM5.5,12L9.5,8L12,5.5C10.5,5 7.1,5 5.5,12ZM18.5,18.5C16,21 10.5,21 9,18.5C8,17.5 8.5,16.5 9,15.5L6,12.5L7.5,11.5L10.5,14.5L9.5,13L12.5,10L11.5,8.5L17,14C17.5,14.5 17.5,16 18.5,18.5Z"/>
                        </svg>
                    </div>
                    
                    <!-- Heart emoji title or locale-dependent title -->         
                    <h2 class="text-3xl sm:text-4xl font-bold bg-gradient-to-r from-purple-600 via-pink-600 to-blue-500 bg-clip-text text-transparent leading-normal inline-block">Follow Your Favorites</h2>
                    <p class="text-lg mt-3 text-gray-700 >	Sign up and browse your best events tailored for you." %-->
                    
                <p class="text-lg mt-3 text-gray-600">Save and revisit your favorite venues, artists and events safely—in just seconds via Google auth, or an Email form below</p>
                    
                </div>
                
                <!-- Animated Heart fill effect (Flutter root hero/focus mode)-->
                <div class="flex justify-center mb-6 mt-4">
                    <svg width="22" height="25" class="text-purple-600 drop-shadow" viewBox="0 0 24 24" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>

                <!-- Social Quick Path -->
                <div x-show="formStep === 'social'" x-cloak="">
                    <!-- Google login button primary target-- redirect to google auth then b/c:continueUrl→ intended Venue full URL -->
                    <a 
                        href="/login?continue={{ urlencode(request()->fullUrl()) }}"
                        class="w-full inline-flex justify-center items-center py-3 px-4 border-2 border-transparent font-medium rounded-xl transition-colors-child gap-2.5 text-sm text-white bg-[linear-gradient(45deg,#6666FF,#dd5555_90%,#fff)] highlight-none transition-all ease-out duration-200 hover:scale-[1.02] shadow-lg shadow-purple-700/30" title="Fast + secure"">
                        Continue with Google
                        <span class="inline-flex items-center ml-1.5 w-5 h-5">
                            <svg width="24" height="24" class="" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                <path d="M22.56 10.22c0 .78-.1 1.55-.29 2.25H12v-2.25h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77l3.57-5.07z"/>
                            </svg>&nbsp;</span>
                    </a> 

                    <!-- Email fallback toggle -->  
                    <button 
                        x-on:click="formStep = 'email'" 
type="button" class="w-full mt-3 px-4 py-3 rounded-xl bg-white hover:bg-gray-100 border border-gray-200 font-semibold text-purple-700
                        Not keen on social? Use Email<i class="inline w-4 h-4 text-xl disabled:opacity-30 ml-2">↩️</i>
                    </button>
                </div>

                <div id="email-form-step" class="space-y-3">
                    <!-- Name Field -->
                    <div class="">
                    <input 
                        type="text" 
                        name="name" 
                        placeholder="Your name" 
                        required 
                        value="{{ old('name') ?? '' }}"
                        class="w-full px-4 py-3 rounded-xl border-gray-200 text-gray-900 border border-solid transition-colors focus:border-purple-400 focus:border-2 focus:ring-purple-500/10 focus:ring-0 bg-white/70">
                    </div>

                    <!-- Email Field -->
                    <div class="">
                        <input
                            type="email" 
                            name="email" 
                            placeholder="email@example.com" 
                            required 
                            value="{{ old('email') ?? '' }}"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 text-gray-900 border border-solid transition-colors focus:border-purple-400 focus:border-2 focus:ring-purple-500/10 bg-white/70">    
                    </div> 
                    
                    <!-- Password Field (< 20 chars min utility companies best practice) -->
                    <div class="">
                        <input 
                            type="password" 
                            name="password" 
                            placeholder="Create a strong password" 
                            required autocomplete="new-password"
                            minlength="8"
                            class="w-full px-4 py-3 rounded-xl border-gray-200 border border-solid transition-colors focus:border-purple-400 focus:border-2 focus:ring-purple-500/10 bg-white/70">     
                    </div>

                    <!-- Submit Button -->        
                    <button 
                        type="submit"
                        class="group relative text-white text-sm font-semibold uppercase"           
                        style="width: 100%; padding-top: 18px; padding-bottom: 18px; border-radius: 16px; transition: all 150ms ease-in-out; margin-top: 20px; letter-spacing: 1px;">
                        <span class="block w-full text-center py-2 px-4"><!-- inline-block instead of flex saves pixels--></span>Get Started → 
                        <span class="w-1 relative top-1 bg-pink-200 text-gray-800 text-xs rounded-full shadow-sm opacity-70 animate-pulse">&nbsp;</span>
                    </button>       
                    <p class="mt-2 text-xs text-center">Already have an account?<a href="{{ route('login') }}?continue={{ request()->fullUrl() }}" class="inline-block ml-1 underline text-purple-700 hover:text-pink-700 no-underline dec from-blue-600">Login here<span class="sr-only"> or submit form again hood raised-offset-down thingy.</span></a><noscript><em> Because you’re enabling files your modal auto-handle auth completion (no page reload)</em></noscript></p>
                </div>
            </div>
        </form>
    </div>
</div>

<style>/* Hero modal animations + Keyframes */
@keyframes slideUp{0% { transform: translateY(16px); opacity: 0.2; } 100% { transform: translateY(0); opacity: 1; } }
</style>

<script defer>
if (!window.Alpine) Alpine = { data: {} }; /* Alpine3 compatibility—unpaged valid'; */

// Global modal handling helper
window.openAuthGuard = function() { try { document.querySelector('[x-data]').__x.data.modal = true; } catch {  
   document.querySelector('#login-modal')?.open?.(true); 
} }
</script>
