<?php

namespace App\Services;

use App\Models\Room;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class UserService
{
  public function __construct(
    protected InvitationService $invitationService,
    protected UploadFilesService $uploadFilesService,
  ) {}

  public function findUserByEmail(string $email)
  {
    return User::where('email', $email)->first();
  }

  /**
   * Registers an owner
   * 
   * @param array $validatedData
   * @return array{message: string, status: string, user: User|null|array{message: string, status: string, user: User}}
   */
  public function registerOwner(array $validatedData): array
  {
    $existingUser = $this->findUserByEmail($validatedData['email']);

    // Owner already exists
    if ($existingUser) {
      return [
        'status'  => 'user_exists',
        'message' => 'User already exists.',
        'user'    => $existingUser,
      ];
    }

    $validatedData['role'] = 'owner';
    $user = $this->createUser($validatedData);

    return [
      'status'  => 'owner_registered',
      'message' => 'Owner registered successfully.',
      'user'    => $user,
    ];
  }

  public function registerTenant(array $validatedData): array
  {
    $invitation = $this->invitationService->validateInvitation($validatedData['email'], $validatedData['token']);

    if (!$invitation) {
      return [
        'error_key' => 'invalid_invitation',
      ];
    }

    $existingUser = $this->findUserByEmail($validatedData['email']);

    if ($existingUser) {
      $this->invitationService->acceptInvitation($invitation, $existingUser);

      // Tenant already exists
      return [
        'status' => 'user_exists',
        'user' => $existingUser,
      ];
    }

    $rentable = $invitation->rentable;

    if ($rentable instanceof Room) {
      $validatedData['address'] = $rentable->property->address ?? null;
    } else {
      $validatedData['address'] = $rentable->address ?? null;
    }

    $validatedData['role'] = 'tenant';
    $validatedData['user_type'] = 'individual';
    $user = $this->createUser($validatedData);
    $this->invitationService->acceptInvitation($invitation, $user);

    return [
      'status'  => 'tenant_registered',
      'user'    => $user,
    ];
  }

  public function registerUser(array $validatedData)
  {
    $existingUser = $this->findUserByEmail($validatedData['email']);

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

    event(new Registered($user));

    if (!empty($validatedData['role'])) {
      $user->assignRole($validatedData['role']);
    }

    if ($user->role === 'owner') {
      app(MailService::class)->sendOwnerRegistrationMail($user);
    } else {
      app(MailService::class)->sendTenantRegistrationMail($user);
    }

    return $user;
  }

  public function updateProfile(User $user, array $data): User
  {
    $user->fill([
      'name'         => $data['name']          ?? $user->name,
      'phone_number' => $data['phone_number']  ?? $user->phone_number,
      'address'      => $data['address']       ?? $user->address,
    ]);

    $user->save();

    return $user->refresh();
  }

  public function updateProfilePicture(User $user, array $data): User
  {
    if ($user->profileImage) {
      $this->uploadFilesService->deleteFile('images/profile_pictures/' . $user->profileImage->image_path);
      $user->profileImage()->delete();
    }

    if (isset($data['image_base64'])) {
      $fileName = $this->uploadFilesService->storeProfileImage($data['image_base64']);
    } else {
      $file = $data['file'];
      $fileName = uniqid('file_') . '.' . $file->getClientOriginalExtension();
      $file->storeAs('images/profile_pictures', $fileName, 'private');
    }

    $user->profileImage()->create([
      'image_path' => basename($fileName),
    ]);

    return $user->refresh(); 
  }

  public function sendPasswordResetLink(string $email)
  {
    $status = Password::sendResetLink(['email' => $email]);

    if ($status !== Password::RESET_LINK_SENT) {
      throw ValidationException::withMessages(['email' => __($status)]);
    }

    return ['message' => 'Correo de restablecimiento enviado'];
  }

  public function resetPassword(array $validatedData)
  {
    $status = Password::reset(
      [
        'email' => $validatedData['email'],
        'token' => $validatedData['token'],
        'password' => $validatedData['password'],
      ],
      function (User $user, string $password) {
        $user->forceFill([
          'password' => bcrypt($password),
        ])->save();
      }
    );

    if ($status !== Password::PASSWORD_RESET) {
      throw ValidationException::withMessages(['email' => __($status)]);
    }

    return ['message' => 'Contraseña restablecida correctamente'];
  }
}
