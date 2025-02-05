<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePropertyDetailRequest;
use App\Http\Resources\PropertyDetailResource;
use App\Models\Property;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Throwable;

class PropertyDetailController extends Controller
{
  use AuthorizesRequests;
  /**
   * Display a listing of the resource.
   */
  public function index() {}

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    try {
      $property = Property::findOrFail($id);

      $this->authorize('show', $property);

      $details = $property->details;

      if (!$details) {
        return response()->json([
          'error' => 'No hay detalles para esta propiedad'
        ], 404);
      }

      return new PropertyDetailResource($details);
    } catch (ModelNotFoundException $e) {
      return response()->json([
        'error' => 'Propiedad no encontrada',
        'error_code' => 404
      ], 404);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error' => 'No tienes permisos para ver los detalles de esta propiedad',
        'error_code' => 403
      ], 403);
    } catch (Throwable $e) {
      return response()->json([
        'error' => 'Error inesperado al obtener los detalles de la propiedad',
        'message' => $e->getMessage()
      ], 500);
    }
  }

  public function updateOrCreate(UpdatePropertyDetailRequest $request, string $id)
  {
    try {
      $property = Property::findOrFail($id);

      $details = $property->details()->updateOrCreate(
        ['property_id' => $property->id],
        $request->validated()
      );
      return new PropertyDetailResource($details);
    } catch (ModelNotFoundException $e) {
      return response()->json([
        'error' => 'Propiedad no encontrada',
        'error_code' => 404
      ], 404);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error' => 'No tienes permisos para actualizar los detalles de esta propiedad',
        'error_code' => 403
      ], 403);
    } catch (Throwable $e) {
      return response()->json([
        'error' => 'Error inesperado al actualizar los detalles de la propiedad',
        'message' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }
}
