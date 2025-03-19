<?php

namespace App\Mail;

use App\Models\Invitation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationRegistrationMail extends Mailable
{
  use Queueable, SerializesModels;

  public $user;
  public $invitationType;
  public $locationDescription;
  public $registerUrl;

  /**
   * Create a new message instance.
   */
  public function __construct($recipient, Invitation $invitation)
  {
    $this->recipient = $recipient;

    $property = $invitation->rentable_type === Room::class
      ? $invitation->rentable->property
      : $invitation->rentable;

    $propertyAddress = $property->address ?? 'Ubicación no disponible';

    if ($invitation->rentable_type === Room::class) {
      $this->invitationType = 'una habitación en una propiedad';
      $this->locationDescription = 'Habitación #' . $invitation->rentable->room_number . ' en ' . $propertyAddress;
    } else {
      $this->invitationType = 'una propiedad completa';
      $this->locationDescription = $propertyAddress;
    }

    $this->registerUrl = env('FRONTEND_URL', 'http://localhost:3000') . '/register/tenant?token=' . $invitation->token;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      subject: 'Invitación para gestionar tu alquiler',
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      markdown: 'mail.auth.invitation_registration',
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
