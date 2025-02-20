<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvitationRequest;
use App\Http\Resources\InvitationResource;
use App\Models\Invitation;
use App\Models\Property;
use App\Services\InvitationService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
  use AuthorizesRequests;

  private $invitationService;

  public function __construct(InvitationService $invitationService)
  {
    $this->invitationService = $invitationService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    try {
      $invitations = Invitation::where('owner_id', Auth::id())->paginate(10);

      return InvitationResource::collection($invitations);
    } catch (Exception $e) {
      return response()->json([
        'error' => 'Error al obtener las habitaciones.',
        'message' => $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreInvitationRequest $request)
  {
    try {
      $property = Property::findOrFail($request->property_id);
      $this->authorize('storeInvitation', $property);

      $invitation = $this->invitationService->createInvitation(Auth::user(), $request->validated());

      return response()->json([
        'message' => 'Invitación enviada con éxito.',
        'invitation' => new InvitationResource($invitation)
      ], 201);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error' => 'No tienes permiso para enviar invitaciones para esta propiedad.',
        'error_code' => 403
      ], 403);
    } catch (Exception $e) {
      if ($e->getMessage() === 'Ya existe una invitación pendiente para este usuario en esta propiedad o habitación.') {
        return response()->json([
          'error' => 'Ya existe una invitación pendiente para este usuario en esta propiedad o habitación.',
          'error_code' => 400
        ], 400);
      }
      return response()->json([
        'error' => 'Error al crear la invitación.',
        'message' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    try {
    } catch (Exception $e) {
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id) {}

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    try {
    } catch (Exception $e) {
    }
  }
}
