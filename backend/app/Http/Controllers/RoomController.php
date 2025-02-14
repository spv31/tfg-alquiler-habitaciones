<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Property;
use App\Models\Room;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use RoomService;
use Throwable;

class RoomController extends Controller
{
	use AuthorizesRequests;

	private $roomService;

	public function __construct(RoomService $roomService)
	{
		$this->roomService = $roomService;
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(Property $property)
	{
		try {
			$this->authorize('index', $property);

			// We get all rooms
			$rooms = $property->rooms();

			$warning = null;
			if ($rooms->count() < $property->total_rooms) {
				// Alert User needs to add more rooms
				$warning = [
					'key' => 'missing_rooms_warning',
					'parms' => [
						'total_expected' => $property->total_rooms,
						'current' => $rooms->count(),
					]
				];
			}

			return response()->json([
				'rooms' => RoomResource::collection($rooms),
				'warning' => $warning,
			], 200);
		} catch (AuthorizationException $e) {
			return response()->json([
				'error' => 'No tienes permisos para ver las habitaciones de esta propiedad',
				'error_code' => 403
			], 403);
		} catch (Exception $e) {
			return response()->json([
				'error' => 'Error inesperado al obtener las habitaciones',
				'message' => $e->getMessage()
			], 500);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(StoreRoomRequest $request, Property $property)
	{
		try {
			$this->authorize('storeRoom', $property);

			if ($property->rooms()->count() >= $property->total_rooms) {
				return response()->json([
					'error' => 'La propiedad ya tiene todas las habitaciones creadas.',
					'error_code' => 'ROOMS_LIMIT_REACHED'
				], 400);
			}

			$room = $this->roomService->createRoom($property, $request->validated());

			return response()->json(new RoomResource($room), 201);
		} catch (AuthorizationException $e) {
			return response()->json([
				'error' => 'No tienes permisos para añadir una habitación a esta propiedad',
				'error_code' => 403
			], 403);
		} catch (Exception $e) {
			return response()->json([
				'error' => 'Error inesperado al crear la habitación.',
				'message' => $e->getMessage()
			], 500);
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
	public function update(StoreRoomRequest $request, Property $property, Room $room)
	{
		try {
			$this->authorize('updateRoom', $property);

			// Rare
			if ($property->id !== $room->property_id) {
				return response()->json([
					'error' => 'La habitación no pertenece a esta propiedad',
					'error_code' => 400
				], 400);
			}

			[$updatedRoom, $updated] = $this->roomService->updateRoom($room, $request->validated());

			$response = [
				'room' => new RoomResource($updatedRoom),
			];

			if (!$updated) {
				$response['warning'] = [
					'key' => 'no_update_performed',
					'message' => 'No se han realizado cambios en la habitación.'
				];
			}

			return response()->json($response, 200);
		} catch (AuthorizationException $e) {
			return response()->json([
				'error' => 'No tienes permisos para editar una habitación de esta propiedad',
				'error_code' => 403
			], 403);
		} catch (Exception $e) {
			return response()->json([
				'error' => 'Error inesperado al editar la habitación.',
				'message' => $e->getMessage()
			], 500);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Property $property, Room $room)
	{
		try {
			$this->authorize('delete', $property);

			// Rare
			if ($property->id !== $room->property_id) {
				return response()->json([
					'error' => 'La habitación no pertenece a esta propiedad',
					'error_code' => 400
				], 400);
			}

			$deleted = $this->roomService->deleteRoom($room);

			if (!$deleted) {
				return response()->json([
					'error' => 'No se pudo eliminar la habitación.',
					'error_code' => 500,
				], 500);
			}

			return response()->json([
				'message' => 'Habitación eliminada correctamente.',
			], 200);
		} catch (AuthorizationException $e) {
			return response()->json([
				'error' => 'No tienes permisos para eliminar una habitación de esta propiedad',
				'error_code' => 403
			], 403);
		} catch (Exception $e) {
			return response()->json([
				'error' => 'Error inesperado al eliminar la habitación.',
				'message' => $e->getMessage()
			], 500);
		}
	}
}
