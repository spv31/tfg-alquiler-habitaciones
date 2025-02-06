<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\InvitationService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
  public function __construct(
    protected UserService $userService,
    protected InvitationService $invitationService,
  ) {}

  public function register(Request $request)
  {
    $validatedData = $request->validate([
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:users',
      'password' => 'required|string',
      'user_type' => 'required|in:individual,company',
      'identifier' => 'required|string|unique:users,identifier|max:30',
      'profile_picture' => 'nullable|string',
      'phone_number' => 'required|string|max:15',
      'address' => 'required|string|max:255',
      'token' => 'nullable|string',
    ]);

    try {
      // Register user
      $user = $this->userService->registerUser($validatedData);
    } catch (Exception $e) {
      return response()->json(['message' => 'Error', 500]);
    }

    return response()->json($user, 201);
  }

  public function login(Request $request)
  {
    $credentials = $request->validate([
      'email' => 'required|email',
      'password' => 'required'
    ]);

    if (Auth::attempt($credentials)) {
      $request->session()->regenerate();
      return response()->json(Auth::user(), 200);
    }

    return response()->json(['message' => 'Unauthorized'], 401);
  }

  public function logout(Request $request)
  {
    Auth::guard('web')->logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return response()->json(['message' => 'Logged out'], 200);
  }

  public function sendResetLink(Request $request)
  {
    $validatedData = $request->validate(['email' => 'required|email|exists:users,email']);

    try {
      $response = $this->userService->sendPasswordResetLink($validatedData['email']);
      return response()->json($response);
    } catch (ValidationException $e) {
      return response()->json(['error' => 'Error al enviar el correo'], 400);
    }
  }

  public function resetPassword(Request $request)
  {
    Log::info('Solicitud recibida para restablecer contraseña', [
      'email' => $request->input('email'),
      'token' => $request->input('token'),
      'password' => $request->input('password'),
    ]);
    $validatedData = $request->validate([
      'email' => 'required|email|exists:users,email',
      'token' => 'required',
      'password' => 'required|min:6',
    ]);

    try {
      $response = $this->userService->resetPassword($validatedData);
      return response()->json($response);
    } catch (ValidationException $e) {
      return response()->json(['error' => 'Error al restablecer la contraseña'], 400);
    }
  }
}
