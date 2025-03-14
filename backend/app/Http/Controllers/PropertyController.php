<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Services\PropertyServices;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PropertyController extends Controller
{
  use AuthorizesRequests;

  private $propertyServices;

  public function __construct(PropertyServices $propertyServices)
  {
    $this->propertyServices = $propertyServices;
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    try {
      Log::info('Inicio del método index sin llamar a Auth');

      Log::info('Inicio de búsqueda de propiedades para el usuario', ['user_id' => Auth::id()]);

      $properties = Property::where('user_id', Auth::id())->paginate(10);
      Log::info('Propiedades obtenidas exitosamente', ['total' => $properties->total()]);

      return PropertyResource::collection($properties);
    } catch (Exception $e) {
      return response()->json([
        'error_key' => 'fetch_properties_failed',
      ], 500);
    }
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StorePropertyRequest $request)
  {
    try {

      Log::info('Usuario autenticado:', ['user' => auth()->user()]);
      $property = $this->propertyServices->createProperty($request->validated());

      return response()->json([
        'message_key' => 'property_created',
        'property' => new PropertyResource($property)
      ], 201);
    } catch (Exception $e) {
      Log::error('Error al crear la propiedad:', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return response()->json([
        'error_key' => 'property_creation_failed',
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
      $property = Property::with('details')->findOrFail($id);

      $this->authorize('view', $property);

      return new PropertyResource($property);
    } catch (ModelNotFoundException $e) {
      return response()->json([
        'error_key' => 'property_not_found'
      ], 404);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error_key' => 'unauthorized_property_access'
      ], 403);
    } catch (Exception $e) {
      return response()->json([
        'error_key' => 'fetch_property_failed'
      ], 500);
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdatePropertyRequest $request, string $id)
  {
    try {
      $property = Property::findOrFail($id);

      $this->authorize('update', $property);
      $updatedProperty = $this->propertyServices->updateProperty($property, $request->validated());

      return new PropertyResource($updatedProperty);
    } catch (ModelNotFoundException $e) {
      return response()->json([
        'error_key' => 'property_not_found'
      ], 404);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error_key' => 'unauthorized_property_update'
      ], 403);
    } catch (Exception $e) {
      return response()->json([
        'error_key' => 'update_property_failed'
      ], 500);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    try {
      $property = Property::findOrFail($id);

      // Autorizar que solo el propietario pueda eliminar la propiedad
      $this->authorize('delete', $property);

      $property->delete();

      return response()->json([
        'message_key' => 'property_deleted'
      ], 200);
    } catch (ModelNotFoundException $e) {
      return response()->json([
        'error_key' => 'property_not_found'
      ], 404);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error_key' => 'unauthorized_property_delete'
      ], 403);
    } catch (Exception $e) {
      return response()->json([
        'error_key' => 'delete_property_failed'
      ], 500);
    }
  }

  public function changeStatus(Request $request, Property $property)
  {
    try {
      $this->authorize('update', $property);

      $validatedStatus = $request->validate([
        'status' => 'required|in:available,unavailable,occupied,partially_occupied'
      ]);

      $this->propertyServices->changeStatus($property, $validatedStatus['status']);

      return response()->json([
        'message_key' => 'property_status_updated',
        'property' => new PropertyResource($property->fresh()),
      ], 200);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error_key' => 'unauthorized_property_status_change'
      ], 403);
    } catch (Exception $e) {
      return response()->json([
        'error_key' => 'property_status_update_failed'
      ], 500);
    }
  }
}
