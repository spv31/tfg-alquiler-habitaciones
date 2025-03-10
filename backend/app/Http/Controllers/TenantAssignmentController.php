<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyTenant;
use App\Models\Room;
use App\Models\User;
use App\Services\PropertyTenantService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TenantAssignmentController extends Controller
{
  private PropertyTenantService $propertyTenantService;

  public function __construct(PropertyTenantService $propertyTenantService)
  {
    $this->propertyTenantService = $propertyTenantService;
  }

  public function getAssignedRentable(Request $request)
  {
    try {
      $user = $request->user();

      $assignment = PropertyTenant::where('tenant_id', $user->id)->first();

      if (!$assignment) {
        return response()->json([
          'error_key' => 'no_rentable_assigned'
        ], 404);
      }

      return response()->json([
        'rentable' => $assignment->rentable,
      ], 200);
    } catch (Exception $e) {
      return response()->json([
        'error_key' => 'unexpected_error',
      ]);
    }
  }
  public function reassign(Request $request)
  {
    try {
      $validatedData = $request->validate([
        'tenant_id' => 'required|exists:users,id',
        'new_rentable_id' => 'required',
        'new_rentable_type' => 'required|in:property,room'
      ]);

      $tenant = User::findOrFail($validatedData['tenant_id']);

      // Obtener la asignación actual del inquilino
      $currentAssignment = PropertyTenant::where('tenant_id', $tenant->id)->first();

      if (!$currentAssignment) {
        return response()->json([
          'error_key' => 'tenant_not_assigned'
        ], 400);
      }

      // Buscar el nuevo rentable según el tipo
      $newRentable = $validatedData['new_rentable_type'] === 'property'
        ? Property::findOrFail($validatedData['new_rentable_id'])
        : Room::findOrFail($validatedData['new_rentable_id']);

      // Verificar que el nuevo rentable esté disponible
      if ($newRentable->status !== 'available') {
        return response()->json([
          'error_key' => 'rentable_already_occupied'
        ], 400);
      }

      // Eliminar asignación actual
      $this->propertyTenantService->removeTenant($currentAssignment->rentable, $tenant);

      // Asignar al nuevo rentable
      $this->propertyTenantService->assignTenant($newRentable, $tenant);

      return response()->json([
        'message' => 'tenant_reassigned'
      ], 200);
    } catch (ValidationException $e) {
      return response()->json([
        'error_key' => 'invalid_data'
      ], 422);
    } catch (ModelNotFoundException $e) {
      return response()->json([
        'error_key' => 'rentable_not_found'
      ], 404);
    } catch (Exception $e) {
      return response()->json([
        'error_key' => 'unexpected_error'
      ], 500);
    }
  }


  public function removeAssignment(Request $request)
  {
    try {
      $validatedData = $request->validate([
        'tenant_id' => 'required|exists:users,id',
      ]);

      $tenant = User::findOrFail($validatedData['tenant_id']);

      $assignment = PropertyTenant::where('tenant_id', $tenant->id)->first();

      if (!$assignment) {
        return response()->json([
          'error_key' => 'tenant_not_assigned'
        ], 400);
      }

      $this->propertyTenantService->removeTenant($assignment->rentable, $tenant);

      return response()->json([
        'message' => 'tenant_removed'
      ], 200);
    } catch (ValidationException $e) {
      return response()->json([
        'error_key' => 'invalid_data'
      ], 422);
    } catch (ModelNotFoundException $e) {
      return response()->json([
        'error_key' => 'tenant_not_found'
      ], 404);
    } catch (Exception $e) {
      return response()->json([
        'error_key' => 'unexpected_error'
      ], 500);
    }
  }
}
