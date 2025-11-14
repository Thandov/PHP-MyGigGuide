<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Artist;
use App\Models\User;

class ClaimApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $artist;
    public $user;

    public function __construct(Artist $artist, User $user)
    {
        $this->artist = $artist;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Artist Profile Claim Approved - My Gig Guide')
                    ->view('emails.claim-approved')
                    ->with([
                        'artist' => $this->artist,
                        'user' => $this->user,
                    ]);
    }
}
