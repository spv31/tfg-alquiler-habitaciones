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

  public function ensureRoomBelongsToProperty(Property $property, Room $room)
  {
    if ($property->id !== $room->property_id) {
      throw new Exception('room_does_not_belong_to_property');
    }
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
      return response()->json(['error_key' => 'unauthorized_view_rooms'], 403);
    } catch (Exception $e) {
      return response()->json(['error_key' => 'fetch_rooms_failed'], 500);
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
        return response()->json(['error_key' => 'rooms_limit_reached'], 400);
      }

      $room = $this->roomService->createRoom($property, $request->validated());

      return response()->json([
        'message_key' => 'room_created',
        'room' => new RoomResource($room),
      ], 201);
    } catch (AuthorizationException $e) {
      return response()->json(['error_key' => 'unauthorized_create_room'], 403);
    } catch (Exception $e) {
      return response()->json(['error_key' => 'create_room_failed'], 500);
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(Property $property, Room $room)
  {
    try {
      $this->authorize('show', $property);

      $this->ensureRoomBelongsToProperty($property, $room);

      return response()->json(new RoomResource($room), 200);
    } catch (AuthorizationException $e) {
      return response()->json(['error_key' => 'unauthorized_view_room'], 403);
    } catch (Exception $e) {
      return response()->json(['error_key' => 'fetch_room_details_failed'], 500);
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(StoreRoomRequest $request, Property $property, Room $room)
  {
    try {
      $this->authorize('updateRoom', $property);

      $this->ensureRoomBelongsToProperty($property, $room);

      [$updatedRoom, $updated] = $this->roomService->updateRoom($room, $request->validated());

      $response = [
        'room' => new RoomResource($updatedRoom),
      ];

      if (!$updated) {
        $response['warning'] = [
          'key' => 'no_update_performed',
        ];
      }

      return response()->json($response, 200);
    } catch (AuthorizationException $e) {
      return response()->json(['error_key' => 'unauthorized_update_room'], 403);
    } catch (Exception $e) {
      return response()->json(['error_key' => 'update_room_failed'], 500);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Property $property, Room $room)
  {
    try {
      $this->authorize('delete', $property);

      $this->ensureRoomBelongsToProperty($property, $room);

      $deleted = $this->roomService->deleteRoom($room);

      if (!$deleted) {
        return response()->json(['error_key' => 'delete_room_failed'], 500);
      }

      return response()->json(['message_key' => 'room_deleted'], 200);
    } catch (AuthorizationException $e) {
      return response()->json(['error_key' => 'unauthorized_delete_room'], 403);
    } catch (Exception $e) {
      return response()->json(['error_key' => 'delete_room_failed'], 500);
    }
  }

  /**
   * Update the specified resource status in storage.
   * 
   * @param \Illuminate\Http\Request $request
   * @param \App\Models\Property $property
   * @param \App\Models\Room $room
   * @return mixed|\Illuminate\Http\JsonResponse
   */
  public function changeStatus(Request $request, Property $property, Room $room)
  {
    try {
      $this->authorize('updateRoom', $property);

      $this->ensureRoomBelongsToProperty($property, $room);

      $validatedStatus = $request->validate([
        'status' => 'required|in:available,occupied,unavailable',
      ]);

      $this->roomService->changeStatus($room, $validatedStatus['status']);

      return response()->json([
        'message_key' => 'room_status_updated',
        'room' => new RoomResource($room->refresh()),
      ], 200);
    } catch (AuthorizationException $e) {
      return response()->json(['error_key' => 'unauthorized_change_room_status'], 403);
    } catch (Exception $e) {
      return response()->json(['error_key' => 'change_room_status_failed'], 500);
    }
  }
}
