<?php

namespace App\Mail;

use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class TeamInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(protected Team $team, protected User $sender, protected ?string $email = null)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Team Invitation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: new MailMessage()
                ->subject('You have been invited to join a team')
                ->line("{$this->sender->name} has invited you to join the team '{$this->team->name}'.")
                ->action('Join Team', URL::signedRoute(
                    'team.join',
                    ['teamId' => $this->team->id, 'email' => $this->email],
                    now()->addDays(30) // Link valid for 30 days
                ))
                ->line('Thank you for using our application!')
                ->render()
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
