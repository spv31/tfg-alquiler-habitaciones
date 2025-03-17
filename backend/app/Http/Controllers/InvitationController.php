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
use Illuminate\Support\Facades\Log;

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
    Log::info("Expired invitations have been updated.");
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    try {
      $this->checkInvitationStatus();

      $invitations = Invitation::where('owner_id', Auth::id())->paginate(10);
      Log::info("User ID " . Auth::id() . " retrieved invitations list.");

      return InvitationResource::collection($invitations);
    } catch (Exception $e) {
      Log::error("Error fetching invitations for user ID " . Auth::id() . ": " . $e->getMessage());
      return response()->json(['error_key' => 'fetch_invitations_failed'], 500);
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
      Log::info("Invitation created by user ID " . Auth::id() . " for property ID " . $request->property_id . ".");

      return response()->json([
        'message_key' => 'invitation_sent',
        'invitation' => new InvitationResource($invitation)
      ], 201);
    } catch (AuthorizationException $e) {
      Log::warning("Unauthorized invitation attempt by user ID " . Auth::id() . " for property ID " . $request->property_id);
      return response()->json(['error_key' => 'unauthorized_send_invitation'], 403);
    } catch (RentableNotAvailableException $e) {
      Log::warning("Attempt to invite to a non-available rentable unit (Property ID: " . $request->property_id . ").");
      return response()->json(['error_key' => 'rentable_not_available'], 400);
    } catch (InvitationAlreadyExistsException $e) {
      Log::warning("Duplicate invitation attempt by user ID " . Auth::id() . " for property ID " . $request->property_id);
      return response()->json(['error_key' => 'invitation_already_exists'], 400);
    } catch (Exception $e) {
      Log::error("Failed to create invitation for user ID " . Auth::id() . ": " . $e->getMessage());
      return response()->json(['error_key' => 'create_invitation_failed'], 500);
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

      Log::info("User ID " . Auth::id() . " viewed invitation ID " . $invitation->id . ".");

      return new InvitationResource($invitation);
    } catch (AuthorizationException $e) {
      Log::warning("Unauthorized attempt to view invitation ID " . $invitation->id . " by user ID " . Auth::id());
      return response()->json(['error_key' => 'unauthorized_view_invitation'], 403);
    } catch (Exception $e) {
      Log::error("Error fetching invitation ID " . $invitation->id . " for user ID " . Auth::id() . ": " . $e->getMessage());
      return response()->json(['error_key' => 'fetch_invitation_failed'], 500);
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
      Log::info("Invitation ID " . $invitation->id . " updated by user ID " . Auth::id() . " to status: " . $validated['status']);

      return response()->json([
        'message_key' => 'invitation_updated',
        'invitation' => new InvitationResource($invitation->fresh())
      ], 200);
    } catch (AuthorizationException $e) {
      Log::warning("Unauthorized attempt to update invitation ID " . $invitation->id . " by user ID " . Auth::id());
      return response()->json(['error_key' => 'unauthorized_update_invitation'], 403);
    } catch (Exception $e) {
      Log::error("Error updating invitation ID " . $invitation->id . " by user ID " . Auth::id() . ": " . $e->getMessage());
      return response()->json(['error_key' => 'update_invitation_failed'], 500);
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

      Log::info("Invitation ID " . $invitation->id . " deleted by user ID " . Auth::id());

      return response()->json(['message_key' => 'invitation_deleted'], 200);
    } catch (AuthorizationException $e) {
      Log::warning("Unauthorized attempt to delete invitation ID " . $invitation->id . " by user ID " . Auth::id());
      return response()->json(['error_key' => 'unauthorized_delete_invitation'], 403);
    } catch (Exception $e) {
      Log::error("Error deleting invitation ID " . $invitation->id . " by user ID " . Auth::id() . ": " . $e->getMessage());
      return response()->json(['error_key' => 'delete_invitation_failed'], 500);
    }
  }
}
