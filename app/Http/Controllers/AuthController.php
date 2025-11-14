<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Artist;
use App\Mail\EmailVerificationMail;
use App\Mail\ArtistClaimWarningMail;
use App\Mail\PendingClaimNoticeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        $credentials = $request->only('username', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user has verified email
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Check for unclaimed artist
                $unclaimedArtist = Artist::whereNull('user_id')
                    ->whereRaw('LOWER(contact_email) = ?', [strtolower($user->email)])
                    ->first();

                if ($unclaimedArtist) {
                    return redirect()->route('verification.notice')
                        ->with('error', 'Please verify your email to claim your artist profile and access your account. A verification email has been sent to ' . $user->email . '.');
                }

                return redirect()->route('verification.notice')
                    ->with('error', 'Please verify your email address before logging in. A verification email has been sent to ' . $user->email . '.');
            }

            // Handle continue parameter to redirect user back to where they were
            if ($request->has('continue')) {
                return redirect($request->get('continue'));
            }

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'username' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        // Preselect role if provided via query (e.g. ?role=venue_owner)
        $requestedRole = request('role');
        if ($requestedRole && in_array($requestedRole, ['user', 'artist', 'organiser', 'venue_owner'])) {
            // Flash old input so the select shows the desired value
            $old = session('_old_input', []);
            $old['role'] = $requestedRole;
            session()->flash('_old_input', $old);
        }

        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        // If this is coming from auth modal, we have simplified fields
        if ($request->has('continue')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'continue' => 'sometimes|string',
            ]);

            // Generate username from email
            $username = str_replace('@', '', $request->email);
            $originalUsername = $username;
            $counter = 1;

            // Ensure username is unique
            while (User::where('username', $username)->exists()) {
                $username = $originalUsername.$counter;
                $counter++;
            }

            $user = User::create([
                'name' => $request->name,
                'username' => $username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Check for unclaimed artist with matching email (case-insensitive)
            $unclaimedArtist = Artist::whereNull('user_id')
                ->whereRaw('LOWER(contact_email) = ?', [strtolower($request->email)])
                ->first();

            $hasPendingArtist = $unclaimedArtist !== null;
            $gracePeriodEnabled = config('artist_claims.enable_grace_period', false);
            $gracePeriodEnds = null;

            if ($hasPendingArtist) {
                // Calculate grace period end time if enabled
                if ($gracePeriodEnabled) {
                    $gracePeriodEnds = Carbon::now()->addHours(config('artist_claims.grace_period_hours', 48));
                }

                // Set up pending claim
                $unclaimedArtist->update([
                    'pending_claim_user_id' => $user->id,
                    'pending_claim_at' => Carbon::now(),
                    'claim_status' => 'pending',
                    'grace_period_ends_at' => $gracePeriodEnds,
                ]);

                // Send warning email to artist's contact email (if not already sent)
                if (!$unclaimedArtist->warning_email_sent_at) {
                    try {
                        Mail::to($unclaimedArtist->contact_email)->send(
                            new ArtistClaimWarningMail($unclaimedArtist, $user, $gracePeriodEnds)
                        );
                        $unclaimedArtist->update(['warning_email_sent_at' => Carbon::now()]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send artist claim warning email: ' . $e->getMessage());
                    }
                }

                // Send notice to registrant about pending claim
                try {
                    Mail::to($user->email)->send(
                        new PendingClaimNoticeMail($unclaimedArtist, $gracePeriodEnds)
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send pending claim notice email: ' . $e->getMessage());
                }

                $gracePeriodText = $gracePeriodEnabled 
                    ? " Your claim will be processed after {$gracePeriodEnds->diffForHumans()}."
                    : "";
                
                $successMessage = "Account created! We found an artist profile '{$unclaimedArtist->stage_name}' linked to your email. Please verify your email to claim your profile.{$gracePeriodText}";
            } else {
                // Assign default role as 'user'
                $user->addRole('user');
                $successMessage = 'Account created successfully! Please verify your email to complete registration.';
            }

            // Create user folder and settings
            $user->getOrCreateFolderSettings();

            // Don't auto-login - require email verification first
            // Store email in session for resend functionality
            session(['pending_verification_email' => $user->email]);
            
            // Send verification email with artist info
            Mail::to($user->email)->send(new EmailVerificationMail($user, $unclaimedArtist));

            // Redirect to verification notice
            return redirect()->route('verification.notice')->with('success', $successMessage);
        }

        // Handle full registration with additional fields
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:user,artist,organiser,venue_owner',
            'terms' => 'required|accepted',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Check for unclaimed artist with matching email (case-insensitive)
        $unclaimedArtist = Artist::whereNull('user_id')
            ->whereRaw('LOWER(contact_email) = ?', [strtolower($request->email)])
            ->first();

        $hasPendingArtist = $unclaimedArtist !== null;
        $gracePeriodEnabled = config('artist_claims.enable_grace_period', false);
        $gracePeriodEnds = null;

        if ($hasPendingArtist) {
            // Calculate grace period end time if enabled
            if ($gracePeriodEnabled) {
                $gracePeriodEnds = Carbon::now()->addHours(config('artist_claims.grace_period_hours', 48));
            }

            // Set up pending claim
            $unclaimedArtist->update([
                'pending_claim_user_id' => $user->id,
                'pending_claim_at' => Carbon::now(),
                'claim_status' => 'pending',
                'grace_period_ends_at' => $gracePeriodEnds,
            ]);

            // Send warning email to artist's contact email (if not already sent)
            if (!$unclaimedArtist->warning_email_sent_at) {
                try {
                    Mail::to($unclaimedArtist->contact_email)->send(
                        new ArtistClaimWarningMail($unclaimedArtist, $user, $gracePeriodEnds)
                    );
                    $unclaimedArtist->update(['warning_email_sent_at' => Carbon::now()]);
                } catch (\Exception $e) {
                    Log::error('Failed to send artist claim warning email: ' . $e->getMessage());
                }
            }

            // Send notice to registrant about pending claim
            try {
                Mail::to($user->email)->send(
                    new PendingClaimNoticeMail($unclaimedArtist, $gracePeriodEnds)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send pending claim notice email: ' . $e->getMessage());
            }
        }

        // Assign selected role (but artist will be added on verification if pending artist exists)
        $user->addRole($request->role);

        // Create user folder and settings
        $user->getOrCreateFolderSettings();

        // Don't auto-login - require email verification first
        // Store email in session for resend functionality
        session(['pending_verification_email' => $user->email]);
        
        // Send verification email with artist info
        Mail::to($user->email)->send(new EmailVerificationMail($user, $unclaimedArtist));

        $roleName = ucfirst(str_replace('_', ' ', $request->role));
        
        if ($hasPendingArtist) {
            $gracePeriodText = $gracePeriodEnabled 
                ? " Your claim will be processed after {$gracePeriodEnds->diffForHumans()}."
                : "";
            $successMessage = "Account created as {$roleName}! We found an artist profile '{$unclaimedArtist->stage_name}' linked to your email. Please verify your email to claim your profile.{$gracePeriodText}";
        } else {
            $successMessage = "Account created as {$roleName}! Please verify your email to complete registration.";
        }

        // Redirect to verification notice
        return redirect()->route('verification.notice')->with('success', $successMessage);
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
