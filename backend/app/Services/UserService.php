<?php

namespace App\Services;

use App\Models\User;

class UserService
{
  public function __construct(
    protected InvitationService $invitationService,
    protected UploadFilesService $uploadFilesService,
  ) {}

  public function registerUser(array $validatedData)
  {
    $existingUser = User::where('email', $validatedData['email'])->first();

    if ($existingUser) {
      // Invitation
      if (!empty($validatedData['token'])) {

        $this->invitationService->acceptInvitation($validatedData['token'], $existingUser);

        // Tenant already exists
        return [
          'status' => 'user_exists',
          'message' => 'User already existed and was linked to the property.',
          'user' => $existingUser,
        ];
      }

      // Owner already exists
      return [
        'status'  => 'user_exists',
        'message' => 'User already exists.',
        'user'    => $existingUser,
      ];
    }

    // Register tenant
    if (!empty($validatedData['token'])) {
      
      $validatedData['role'] = 'tenant';
      $user = $this->createUser($validatedData);
      $this->invitationService->acceptInvitation($validatedData['token'], $user);

      return [
        'status'  => 'tenant_registered',
        'message' => 'Tenant registered and invitation accepted.',
        'user'    => $user,
      ];
    }

    // Register owner
    $validatedData['role'] = 'owner';
    $user = $this->createUser($validatedData);

    return [
      'status'  => 'owner_registered',
      'message' => 'Owner registered successfully.',
      'user'    => $user,
    ];
  }

  public function createUser(array $validatedData)
  {
    $userData = [
      'name' => $validatedData['name'],
      'email' => $validatedData['email'],
      'password' => bcrypt($validatedData['password']),
      'user_type' => $validatedData['user_type'],
      'identifier' => $validatedData['identifier'],
      'role' => $validatedData['role'],
      'phone_number' => $validatedData['phone_number'],
      'address' => $validatedData['address'],
    ];

    if (!empty($validatedData['profile_picture'])) {
      $userData['profile_picture'] = $this->uploadFilesService->storeProfileImage($validatedData['profile_picture']);
    }

    $user = new User($userData);
    $user->save();

    return $user;
  }
}
