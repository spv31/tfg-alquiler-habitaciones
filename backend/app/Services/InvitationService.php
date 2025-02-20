<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;

class InvitationService
{
  /**
   * Create a new class instance.
   */
  public function __construct()
  {
    //
  }

  public function createInvitation(User $user, array $data): Invitation
  {
    $existingInvitation = Invitation::where('email', $data['email'])
      ->where('rentable_id', $data['room_id'] ?? $data['property_id'])
      ->where('status', 'pending')
      ->first();

    if ($existingInvitation) {
      throw new Exception('Ya existe una invitación pendiente para este usuario en esta propiedad o habitación.');
    }

    $invitation = Invitation::create([
      'email' => $data['email'],
      'token' => Str::random(32),
      'rentable_id' => $data['room_id'] ?? $data['property_id'],
      'rentable_type' => $data['room_id'] ? Room::class : Property::class,
      'owner_id' => $user->id,
      'status' => 'pending'
    ]);

    // Send email
    $this->sendInvitation($data['email'], $invitation);

    return $invitation;
  }

  public function sendInvitation(string $email, Invitation $invitation) 
  {
    app(MailService::class)->sendInvitationRegistrationMail($email, $invitation);
  }

  public function regenerateInvitation() {}

  public function acceptInvitation(string $token) {}

  public function verifyInvitationToken() {}
}
