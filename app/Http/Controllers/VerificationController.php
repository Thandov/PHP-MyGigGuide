<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\EmailVerificationMail;
use App\Models\User;
use App\Models\Artist;
use Carbon\Carbon;

class VerificationController extends Controller
{
    /**
     * Show the email verification notice.
     */
    public function notice()
    {
        return view('auth.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('verification.notice')->with('error', 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            // User already verified, login if not already logged in
            if (!Auth::check()) {
                Auth::login($user);
            }
            return redirect()->route('profile.show')->with('success', 'Email already verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Check for unclaimed artist with matching email (case-insensitive)
        $unclaimedArtist = Artist::whereNull('user_id')
            ->whereRaw('LOWER(contact_email) = ?', [strtolower($user->email)])
            ->first();

        if ($unclaimedArtist) {
            $gracePeriodEnabled = config('artist_claims.enable_grace_period', false);
            $canAutoClaim = true;
            $claimMessage = "Email verified successfully! ";

            // Check if this user has a pending claim
            $hasPendingClaim = $unclaimedArtist->pending_claim_user_id === $user->id;
            
            // Check for disputes
            if ($unclaimedArtist->dispute_raised) {
                $canAutoClaim = false;
                $claimMessage .= "Your artist profile '{$unclaimedArtist->stage_name}' claim is under review due to a dispute. An admin will verify your claim. ";
            }
            
            // Check grace period if enabled
            if ($gracePeriodEnabled && $unclaimedArtist->grace_period_ends_at) {
                if (Carbon::now()->lt($unclaimedArtist->grace_period_ends_at)) {
                    $canAutoClaim = false;
                    $remainingTime = $unclaimedArtist->grace_period_ends_at->diffForHumans();
                    $claimMessage .= "Your artist profile '{$unclaimedArtist->stage_name}' is pending. The claim will be processed {$remainingTime}. ";
                }
            }

            // Only auto-claim if no dispute and grace period passed (or disabled)
            if ($canAutoClaim) {
                // Link the artist to this user
                $unclaimedArtist->update([
                    'user_id' => $user->id,
                    'claim_status' => 'approved',
                    'pending_claim_user_id' => null,
                    'pending_claim_at' => null,
                ]);

                // Assign artist role if not already assigned
                if (!$user->hasRole('artist')) {
                    $user->addRole('artist');
                }

                $claimMessage .= "Your artist profile '{$unclaimedArtist->stage_name}' has been claimed. Welcome to My Gig Guide!";
            } else {
                // User verified email but claim is pending - update status
                if ($hasPendingClaim) {
                    $unclaimedArtist->update([
                        'claim_status' => 'pending',
                    ]);
                }
                
                $claimMessage .= "Please wait for claim processing or admin approval.";
            }

            // Login the user
            Auth::login($user);
            
            // Clear pending verification email from session
            session()->forget('pending_verification_email');

            return redirect()->route('profile.show')->with('success', $claimMessage);
        }

        // Login the user
        Auth::login($user);
        
        // Clear pending verification email from session
        session()->forget('pending_verification_email');

        return redirect()->route('profile.show')->with('success', 'Email verified successfully! Welcome to My Gig Guide!');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request)
    {
        $user = $request->user();
        
        // If user is not logged in, they might be trying to resend from registration
        if (!$user) {
            // Prefer explicit email from the request, otherwise fall back to the session value
            $email = $request->input('email') ?? session('pending_verification_email');

            if (!$email) {
                return back()
                    ->withInput($request->only('email'))
                    ->with('error', 'Please enter the email address you used when signing up.');
            }

            $user = User::where('email', $email)->first();
            
            if (!$user) {
                return back()
                    ->withInput($request->only('email'))
                    ->with('error', 'We could not find an account with that email address. Please double-check and try again.');
            }
        }
        
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('profile.show')->with('info', 'Email already verified.');
        }

        // Persist the email so subsequent resends still work for logged-out users
        session(['pending_verification_email' => $user->email]);

        // Check for unclaimed artist
        $unclaimedArtist = Artist::whereNull('user_id')
            ->whereRaw('LOWER(contact_email) = ?', [strtolower($user->email)])
            ->first();

        Mail::to($user->email)->send(new EmailVerificationMail($user, $unclaimedArtist));

        return back()->with('success', 'Verification email sent to ' . $user->email);
    }
}
