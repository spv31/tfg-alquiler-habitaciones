<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\InvitationService;
use App\Services\UserService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

  use AuthorizesRequests;

  public function __construct(
    protected UserService $userService,
    protected InvitationService $invitationService,
  ) {}

  public function registerOwner(Request $request)
  {
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users',
      'password' => 'required|string|min:6',
      'user_type' => 'required|in:individual,company',
      'identifier' => 'required|string|unique:users,identifier|max:30',
      'profile_picture' => 'nullable|string',
      'phone_number' => 'required|string|max:15',
      'address' => 'required|string|max:255',
    ]);

    try {
      $result = $this->userService->registerOwner($validatedData);

      if ($result['status'] === 'user_exists') {
        return response()->json(['error_key' => 'user_already_exists'], 400);
      }

      return response()->json(['message' => 'owner_registered', 'user' => new UserResource($result['user'])], 201);
    } catch (Exception $e) {
      return response()->json(['error_key' => 'unexpected_error'], 500);
    }
  }

  public function registerTenantFromInvitation(Request $request)
  {
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email',
      'password' => 'required|string|min:6',
      'identifier' => 'required|string|unique:users,identifier|max:30',
      'profile_picture' => 'nullable|string',
      'phone_number' => 'required|string|max:15',
      'token' => 'required|string',
    ]);

    try {
      $result = $this->userService->registerTenant($validatedData);

      if (isset($result['error_key'])) {
        return response()->json(['error_key' => $result['error_key']], 400);
      }

      return response()->json(['message' => 'tenant_registered', 'user' => new UserResource($result['user'])], 201);
    } catch (Exception $e) {
      return response()->json(['error_key' => 'unexpected_error'], 500);
    }
  }

  public function login(Request $request)
  {
    $credentials = $request->validate([
      'email' => 'required|email',
      'password' => 'required'
    ]);

    if (Auth::attempt($credentials)) {
      $request->session()->regenerate();
      return response()->json(['message' => 'login_successful', 'user' => new UserResource(Auth::user())], 200);
    }

    return response()->json(['error_key' => 'invalid_credentials'], 401);
  }

  public function logout(Request $request)
  {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json(['message' => 'logout_successful'], 200);
  }

  public function sendResetLink(Request $request)
  {
    $validatedData = $request->validate(['email' => 'required|email|exists:users,email']);

    try {
      $this->userService->sendPasswordResetLink($validatedData['email']);
      return response()->json(['message' => 'reset_link_sent'], 200);
    } catch (ValidationException $e) {
      return response()->json(['error_key' => 'reset_link_failed'], 400);
    }
  }

  public function resetPassword(Request $request)
  {
    $validatedData = $request->validate([
      'email' => 'required|email|exists:users,email',
      'token' => 'required',
      'password' => 'required|min:6',
    ]);

    try {
      $this->userService->resetPassword($validatedData);
      return response()->json(['message' => 'password_reset_successful'], 200);
    } catch (ValidationException $e) {
      return response()->json(['error_key' => 'password_reset_failed'], 400);
    }
  }

  public function update(Request $request, User $user)
  {
    $this->authorize('update', $user);

    $validated = $request->validate([
      'name'         => 'sometimes|string|max:255',
      'phone_number' => 'sometimes|nullable|string|max:15',
      'address'      => 'sometimes|nullable|string|max:255',
    ]);

    $user = $this->userService->updateProfile($user, $validated);

    return new UserResource($user);
  }

  public function updateAvatar(Request $request, User $user)
  {
    $this->authorize('update', $user);

    $validated = $request->validate([
      'image_base64' => 'required_without:file|string',
      'file'         => 'required_without:image_base64|image|max:2048',
    ]);

    $user = $this->userService->updateProfilePicture($user, $validated);

    return new UserResource($user);
  }
}
