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
      $properties = Property::where('user_id', Auth::id())->paginate(10);

      return PropertyResource::collection($properties);
    } catch (Exception $e) {
      return response()->json([
        'error' => 'Error al obtener las propiedades.',
        'message' => $e->getMessage()
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
        'message' => 'Propiedad creada exitosamente',
        'property' => new PropertyResource($property)
      ], 201);
    } catch (Exception $e) {
      Log::error('Error al crear la propiedad:', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);
      return response()->json([
        'error' => 'Error al crear la propiedad',
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
        'error' => 'Propiedad no encontrada',
        'error_code' => 404
      ], 404);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error' => 'No tienes permisos para ver esta propiedad',
        'error_code' => 403
      ], 403);
    } catch (Exception $e) {
      return response()->json([
        'error' => 'Error inesperado al obtener la propiedad',
        'message' => $e->getMessage()
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
        'error' => 'Propiedad no encontrada',
        'error_code' => 404
      ], 404);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error' => 'No tienes permisos para actualizar esta propiedad',
        'error_code' => 403
      ], 403);
    } catch (Exception $e) {
      return response()->json([
        'error' => 'Error inesperado al actualizar la propiedad',
        'message' => $e->getMessage()
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

      return response()->json(['message' => 'Propiedad eliminada exitosamente']);
    } catch (ModelNotFoundException $e) {
      return response()->json([
        'error' => 'Propiedad no encontrada',
        'error_code' => 404
      ], 404);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error' => 'No tienes permisos para eliminar esta propiedad',
        'error_code' => 403
      ], 403);
    } catch (Exception $e) {
      return response()->json([
        'error' => 'Error inesperado al eliminar la propiedad',
        'message' => $e->getMessage()
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
        'message' => 'Estado de la propiedad actualizado con éxito.',
        'property' => new PropertyResource($property->fresh()),
      ], 200);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error' => 'No tienes permisos para cambiar el estado de esta propiedad.',
        'error_code' => 403
      ], 403);
    } catch (Exception $e) {
      return response()->json([
        'error' => 'Error inesperado al cambiar el estado de la propiedad.',
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
