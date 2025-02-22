<?php

namespace App\Services;

use App\Invitations\Exceptions\InvitationAlreadyExistsException;
use App\Invitations\Exceptions\RentableNotAvailableException;
use App\Models\Invitation;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;

class InvitationService
{
  private PropertyTenantService $propertyTenantService;

  /**
   * Create a new class instance.
   */
  public function __construct(PropertyTenantService $propertyTenantService)
  {
    $this->propertyTenantService = $propertyTenantService;
  }

  public function createInvitation(User $user, array $data): ?Invitation
  {
    $rentable = $data['room_id']
      ? Room::findOrFail($data['room_id'])
      : Property::findOrFail($data['property_id']);

    if ($rentable->status === 'unavailable' || $rentable->status === 'occupied') {
      throw new RentableNotAvailableException();
    }

    $existingInvitation = Invitation::where('email', $data['email'])
      ->where('rentable_id', $data['room_id'] ?? $data['property_id'])
      ->where('status', 'pending')
      ->first();

    if ($existingInvitation) {
      throw new InvitationAlreadyExistsException();
    }

    $invitation = Invitation::create([
      'email' => $data['email'],
      'token' => Str::random(32),
      'rentable_id' => $rentable->id,
      'rentable_type' => $data['room_id'] ? 'room' : 'property',
      'owner_id' => $user->id,
      'status' => 'pending'
    ]);

    // Send email
    $this->sendInvitation($data['email'], $invitation);

    return $invitation;
  }

  public function sendInvitation(string $email, Invitation $invitation): void
  {
    app(MailService::class)->sendInvitationRegistrationMail($email, $invitation);
  }

  public function regenerateInvitation() {}

  public function validateInvitation(string $email, string $token): ?Invitation
  {
    $invitation = Invitation::where('token', $token)
      ->where('email', $email)
      ->where('status', 'pending')
      ->first();

    if (!$invitation) return null;

    if (!$this->verifyInvitationToken($invitation, $token)) return null;

    return $invitation;
  }

  public function acceptInvitation(Invitation $invitation, User $user)
  {
    $invitation->update(['status' => 'accepted']);

    $this->propertyTenantService->assignTenant($invitation->rentable, $user);
  }

  public function verifyInvitationToken(Invitation $invitation, string $providedToken)
  {
    return $invitation->token === $providedToken;
  }
}
