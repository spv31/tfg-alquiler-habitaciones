<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Room;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Throwable;

class RoomController extends Controller
{
	use AuthorizesRequests;
	/**
	 * Display a listing of the resource.
	 */
	public function index(Property $property)
	{
		try {
			$this->authorize('index', $property);

			// We get all rooms
			$rooms = $property->rooms();

			if ($rooms->count() < $property->total_rooms) {
				// TODO
				// Alert User needs to add more rooms
			}

			return response()->json([

			], 200);
		} catch (AuthorizationException $e) {
			return response()->json([
				'error' => 'No tienes permisos para ver las habitaciones de esta propiedad',
				'error_code' => 403
			], 403);
		} catch (Exception $e) {
			return response()->json([

			]);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		try {

		} catch () {
			
		}
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Property $property, Room $room)
	{
			try {
				$this->authorize('show', $property);

				//TODO
			} catch (AuthorizationException $e) {
				return response()->json([
					'error' => 'No tienes permisos para ver la habitación',
					'error_code' => 403,
				], 403);
			} catch (Exception $e) {
				return response()->json([
					'error' => 'Error inesperado al obtener los detalles de la habitación',
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
