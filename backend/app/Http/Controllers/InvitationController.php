<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvitationRequest;
use App\Http\Resources\InvitationResource;
use App\Invitations\Exceptions\InvitationAlreadyExistsException;
use App\Invitations\Exceptions\RentableNotAvailableException;
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

  private function checkInvitationStatus(): void
  {
    Invitation::expireOld()->update(['status' => 'expired']);
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    try {
      // Before getting invitations, we check if they expire
      $this->checkInvitationStatus();

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
    } catch (RentableNotAvailableException $e) {
      return response()->json([
        'error_key' => 'rentable_not_available'
      ], 400);
    } catch (InvitationAlreadyExistsException $e) {
      return response()->json([
        'error_key' => 'invitation_already_exists'
      ], 400);
    } catch (Exception $e) {
      return response()->json([
        'error' => 'Error al crear la invitación.',
        'message' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(Invitation $invitation)
  {
    try {
      $this->authorize('viewAny', $invitation);

      $this->checkInvitationStatus();

      return new InvitationResource($invitation);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error' => 'No tienes permisos para ver esta invitación.',
        'error_code' => 403
      ], 403);
    } catch (Exception $e) {
      return response()->json([
        'error' => 'Error al obtener la invitación.',
        'message' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Invitation $invitation)
  {
    try {
      $this->authorize('update', $invitation);


      $validated = $request->validate([
        'status' => 'required|in:pending,accepted,canceled,expired'
      ]);

      $invitation->update($validated);

      return response()->json([
        'message' => 'Invitación actualizada con éxito.',
        'invitation' => new InvitationResource($invitation->fresh())
      ], 200);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error' => 'No tienes permisos para actualizar esta invitación.',
        'error_code' => 403
      ], 403);
    } catch (Exception $e) {
      return response()->json([
        'error' => 'Error al actualizar la invitación.',
        'message' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Invitation $invitation)
  {
    try {
      $this->authorize('delete', $invitation);

      $invitation->delete();

      return response()->json([
        'message' => 'Invitación eliminada exitosamente.'
      ], 200);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error' => 'No tienes permisos para eliminar esta invitación.',
        'error_code' => 403
      ], 403);
    } catch (Exception $e) {
      return response()->json([
        'error' => 'Error al eliminar la invitación.',
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
