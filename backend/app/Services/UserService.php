<?php

namespace App\Services;

use App\Models\Room;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Str;
use Stripe\StripeClient;

class UserService
{
  public function __construct(
    protected InvitationService $invitationService,
    protected UploadFilesService $uploadFilesService,
    protected StripeClient $stripe,
  ) {}

  /**
   * Ensures the owner has a Stripe Connect account or creates one if missing
   *
   * @param User $user
   * @return string
   */
  public function ensureOwnerStripeAccount(User $user): string
  {
    if ($user->role !== 'owner') {
      return $user->stripe_account_id ?? '';
    }

    if ($user->stripe_account_id) {
      logger()->info('Owner ya tiene Stripe account, no se crea otra.', [
        'user_id' => $user->id,
        'stripe_account_id'  => $user->stripe_account_id,
      ]);

      return $user->stripe_account_id;
    }

    logger()->info('Creando Stripe Connect account para owner…', [
      'user_id' => $user->id,
      'email' => $user->email,
    ]);

    $nameParts = Str::of($user->name)->explode(' ');
    $firstName = $nameParts->first();
    $lastName = $nameParts->slice(1)->implode(' ');

    try {
      $account = $this->stripe->accounts->create(
        [
          'type' => 'custom',
          'country' => 'ES',
          'email' => $user->email,
          'business_type' => 'individual',

          'individual' => [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->email,
            'dob' => ['day' => 1, 'month' => 1, 'year' => 1902],
            'address' => [
              'line1' => 'address_full_match',
              'city' => 'Madrid',
              'state' => 'M',
              'postal_code' => '28001',
              'country' => 'ES',
            ],
            'id_number'  => '000000000',
            'phone'      => '0000000000',
          ],
          'business_profile' => [
            'name' => $user->email,
            'url' => 'https://accessible.stripe.com',
            'product_description' => 'Gestión de alquileres y cobros de suministros',
            'mcc' => '6513',
          ],
          'tos_acceptance' => [
            'date' => time(),
            'ip' => request()->ip() ?? '127.0.0.1',
          ],
          'capabilities' => [
            'card_payments' => ['requested' => true],
            'transfers' => ['requested' => true],
            'sepa_debit_payments' => ['requested' => true],
          ],
          'external_account' => [
            'object' => 'bank_account',
            'country' => 'ES',
            'currency' => 'eur',
            'account_holder_name' => $firstName . ' ' . $lastName,
            'account_holder_type' => 'individual',
            'account_number' => 'ES0700120345030000067890',
          ],
        ]
      );

      logger()->info("Cuenta Stripe creada correctamente.", [
        'user_id' => $user->id,
        'stripe_account_id' => $account->id,
      ]);

      $user->stripe_account_id = $account->id;
      $user->save();

      return $account->id;
    } catch (Exception $e) {
      logger()->error("Error creando Stripe account: " . $e->getMessage(), [
        'user_id' => $user->id,
        'exception' => $e,
      ]);
      throw $e;
    }
  }

  /**
   * Ensures the tenant has a Stripe Customer or creatis one if missing
   * 
   * @param \App\Models\User $user
   * @return string
   */
  public function ensureTenantStripeCustomer(User $user): string
  {
    if (!$user->stripe_customer_id && $user->role === 'tenant') {
      $customer = $this->stripe->customers->create([
        'name'  => $user->name,
        'email' => $user->email,
      ]);
      $user->stripe_customer_id = $customer->id;
      $user->save();
    }
    return $user->stripe_customer_id;
  }


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

    $this->ensureOwnerStripeAccount($user);

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

    $this->ensureTenantStripeCustomer($user);

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

        if (!$existingUser->hasRole('tenant')) {
          $existingUser->assignRole('tenant');
        }

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
