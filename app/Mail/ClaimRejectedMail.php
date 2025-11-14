<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Artist;
use App\Models\User;

class ClaimRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $artist;
    public $user;
    public $reason;

    public function __construct(Artist $artist, User $user, $reason = null)
    {
        $this->artist = $artist;
        $this->user = $user;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Artist Profile Claim Rejected - My Gig Guide')
                    ->view('emails.claim-rejected')
                    ->with([
                        'artist' => $this->artist,
                        'user' => $this->user,
                        'reason' => $this->reason,
                    ]);
    }
}
