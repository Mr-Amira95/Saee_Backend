<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $setPasswordUrl;
    public string $roleLabel;

    public function __construct(public User $user, string $token)
    {
        $this->setPasswordUrl = url('/set-password?token='.urlencode($token).'&email='.urlencode($user->email));
        $this->roleLabel      = $user->isDriver() ? 'Driver' : 'Client';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Welcome to Sa'ee Logistics – Set Your Password",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-invitation',
        );
    }
}
