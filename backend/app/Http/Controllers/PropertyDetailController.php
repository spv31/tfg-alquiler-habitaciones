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

class PropertyDetailController extends Controller
{
  use AuthorizesRequests;
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //  
  }

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
          'error_key' => 'property_details_not_found'
        ], 404);
      }

      return new PropertyDetailResource($details);
    } catch (ModelNotFoundException $e) {
      return response()->json([
        'error_key' => 'property_not_found'
      ], 404);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error_key' => 'unauthorized_property_details_access'
      ], 403);
    } catch (Exception $e) {
      return response()->json([
        'error_key' => 'fetch_property_details_failed'
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
      return response()->json([
        'message_key' => 'property_details_updated',
        'details' => new PropertyDetailResource($details)
      ], 200);
    } catch (ModelNotFoundException $e) {
      return response()->json([
        'error_key' => 'property_not_found'
      ], 404);
    } catch (AuthorizationException $e) {
      return response()->json([
        'error_key' => 'unauthorized_property_details_update'
      ], 403);
    } catch (Exception $e) {
      return response()->json([
        'error_key' => 'update_property_details_failed'
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
