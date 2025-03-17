<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Property;
use App\Models\Room;
use App\Services\UploadFilesService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\RoomService;
use Illuminate\Support\Facades\Auth;
use Throwable;

class RoomController extends Controller
{
  use AuthorizesRequests;

  private $roomService;
  private $uploadFilesService;

  public function __construct(RoomService $roomService, UploadFilesService $uploadFilesService)
  {
    $this->roomService = $roomService;
    $this->uploadFilesService = $uploadFilesService;
  }

  public function ensureRoomBelongsToProperty(Property $property, Room $room)
  {
    if ($property->id !== $room->property_id) {
      Log::warning("La habitación [{$room->id}] no pertenece a la propiedad [{$property->id}].");
      throw new Exception('room_does_not_belong_to_property');
    }
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Property $property)
  {
    Log::info("Listando habitaciones para la propiedad [{$property->id}].");

    try {
      $this->authorize('view', $property);

      // We get all rooms
      $rooms = $property->rooms;

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
        Log::info("Advertencia para propiedad [{$property->id}]: " . json_encode($warning));
      }

      Log::info("Habitaciones recuperadas correctamente para la propiedad [{$property->id}].");

      return response()->json([
        'rooms' => RoomResource::collection($rooms),
        'warning' => $warning,
      ], 200);
    } catch (AuthorizationException $e) {
      Log::error("No autorizado para ver habitaciones en la propiedad [{$property->id}]: " . $e->getMessage());
      return response()->json(['error_key' => 'unauthorized_view_rooms'], 403);
    } catch (Exception $e) {
      Log::error("Error al obtener habitaciones para la propiedad [{$property->id}]: " . $e->getMessage());
      return response()->json(['error_key' => 'fetch_rooms_failed'], 500);
    }
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreRoomRequest $request, Property $property)
  {
    Log::info("Creando habitación para la propiedad [{$property->id}].");

    try {
      $this->authorize('storeRoom', $property);

      if ($property->rooms()->count() >= $property->total_rooms) {
        Log::warning("Límite de habitaciones alcanzado para la propiedad [{$property->id}].");
        return response()->json(['error_key' => 'rooms_limit_reached'], 400);
      }

      $room = $this->roomService->createRoom($property, $request->validated());
      Log::info("Habitación creada con éxito [{$room->id}] para la propiedad [{$property->id}].");

      if ($request->file('main_image')) {
        Log::info("Se recibió una imagen para la habitación [{$room->id}].");

        $file = $request->file('main_image');

        if (!$file->isValid()) {
          Log::error("El archivo no es válido.", ['error_code' => $file->getError()]);
          return response()->json([
            'error_key' => 'invalid_file_upload',
            'message' => 'El archivo no se ha subido correctamente.'
          ], 400);
        }

        Log::info("Detalles del archivo recibido", [
          'nombre_original' => $file->getClientOriginalName(),
          'mime_type' => $file->getMimeType(),
          'tamaño_kb' => $file->getSize() / 1024 . ' KB',
          'extensión' => $file->extension(),
        ]);

        // Guardar la imagen en el almacenamiento
        $extension = $file->extension();
        $contenidoBase64 = base64_encode(file_get_contents($file->getRealPath()));

        Log::info('Contenido Base64 generado correctamente', [
          'longitud' => strlen($contenidoBase64)
        ]);

        $rutaGuardada = $this->uploadFilesService->storeFile($contenidoBase64, 'images/rooms', $extension);

        Log::info('Imagen de habitación guardada correctamente', [
          'path' => $rutaGuardada
        ]);

        $room->images()->create([
          'image_path' => $rutaGuardada,
        ]);

        Log::info('Imagen de la habitación guardada en la base de datos correctamente.');
      } else {
        Log::info('No se recibió ninguna imagen en la solicitud.');
      }

      return response()->json([
        'message_key' => 'room_created',
        'room' => new RoomResource($room),
      ], 201);
    } catch (AuthorizationException $e) {
      Log::error("No autorizado para crear habitación en la propiedad [{$property->id}]: " . $e->getMessage());
      return response()->json(['error_key' => 'unauthorized_create_room'], 403);
    } catch (Exception $e) {
      Log::error("Error al crear habitación para la propiedad [{$property->id}]: " . $e->getMessage());
      return response()->json(['error_key' => 'create_room_failed'], 500);
    }
  }

  /**
   * Display the specified resource.
   */
  public function show(Property $property, Room $room)
  {
    Log::info("Mostrando detalles de la habitación [{$room->id}] para la propiedad [{$property->id}].");

    try {
      $this->authorize('view', $property);
      $this->ensureRoomBelongsToProperty($property, $room);

      Log::info("Detalles de la habitación [{$room->id}] obtenidos correctamente.");
      return response()->json(new RoomResource($room), 200);
    } catch (AuthorizationException $e) {
      Log::error("No autorizado para ver la habitación [{$room->id}] en la propiedad [{$property->id}]: " . $e->getMessage());
      return response()->json(['error_key' => 'unauthorized_view_room'], 403);
    } catch (Exception $e) {
      Log::error("Error al obtener detalles de la habitación [{$room->id}]: " . $e->getMessage());
      return response()->json(['error_key' => 'fetch_room_details_failed'], 500);
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateRoomRequest $request, Property $property, Room $room)
  {
    Log::info("Actualizando la habitación [{$room->id}] para la propiedad [{$property->id}].");

    try {
      $this->authorize('updateRoom', $property);
      $this->ensureRoomBelongsToProperty($property, $room);

      [$updatedRoom, $updated] = $this->roomService->updateRoom($room, $request->validated());

      if ($request->file('main_image')) {
        Log::info("Se recibió una nueva imagen para la habitación [{$room->id}].");

        $file = $request->file('main_image');

        if (!$file->isValid()) {
          Log::error("El archivo no es válido.", ['error_code' => $file->getError()]);
          return response()->json([
            'error_key' => 'invalid_file_upload',
            'message' => 'El archivo no se ha subido correctamente.'
          ], 400);
        }

        Log::info("Detalles del archivo recibido", [
          'nombre_original' => $file->getClientOriginalName(),
          'mime_type' => $file->getMimeType(),
          'tamaño_kb' => $file->getSize() / 1024 . ' KB',
          'extensión' => $file->extension(),
        ]);

        $extension = $file->extension();
        $contenidoBase64 = base64_encode(file_get_contents($file->getRealPath()));

        Log::info('Contenido Base64 generado correctamente', [
          'longitud' => strlen($contenidoBase64)
        ]);

        $rutaGuardada = $this->uploadFilesService->storeFile($contenidoBase64, 'images/rooms', $extension);

        Log::info('Nueva imagen de habitación guardada correctamente', [
          'path' => $rutaGuardada
        ]);

        if ($room->images()->exists()) {
          Log::info("Eliminando imagen anterior de la habitación [{$room->id}].");

          $oldImages = $room->images()->get();

          $oldImages->each(function ($image) {
            try {
              $this->uploadFilesService->deleteFile($image->image_path);
              Log::info('Imagen eliminada', ['path' => $image->image_path]);
            } catch (Exception $e) {
              Log::error('Error eliminando archivo físico', [
                'path' => $image->image_path,
                'error' => $e->getMessage()
              ]);
            }
          });

          $room->images()->delete();
          Log::info('Imagen(es) anterior(es) eliminada(s) correctamente.');
        } else {
          Log::info('No había imágenes previas asociadas a la habitación.');
        }

        $room->images()->create([
          'image_path' => $rutaGuardada,
        ]);

        Log::info('Nueva imagen de la habitación guardada en la base de datos correctamente.');
      } else {
        Log::info('No se recibió ninguna imagen en la solicitud.');
      }

      $response = [
        'room' => new RoomResource($updatedRoom),
      ];

      if (!$updated) {
        $response['warning'] = [
          'key' => 'no_update_performed',
        ];
        Log::warning("No se realizó ninguna actualización en la habitación [{$room->id}].");
      } else {
        Log::info("Habitación [{$room->id}] actualizada correctamente.");
      }

      return response()->json($response, 200);
    } catch (AuthorizationException $e) {
      Log::error("No autorizado para actualizar la habitación [{$room->id}] en la propiedad [{$property->id}]: " . $e->getMessage());
      return response()->json(['error_key' => 'unauthorized_update_room'], 403);
    } catch (Exception $e) {
      Log::error("Error al actualizar la habitación [{$room->id}] para la propiedad [{$property->id}]: " . $e->getMessage());
      return response()->json(['error_key' => 'update_room_failed'], 500);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Property $property, Room $room)
  {
    Log::info("Eliminando la habitación [{$room->id}] de la propiedad [{$property->id}].");

    try {
      $this->authorize('delete', $property);

      $this->ensureRoomBelongsToProperty($property, $room);

      $deleted = $this->roomService->deleteRoom($room);

      if (!$deleted) {
        Log::error("Fallo al eliminar la habitación [{$room->id}] para la propiedad [{$property->id}].");
        return response()->json(['error_key' => 'delete_room_failed'], 500);
      }

      Log::info("Habitación [{$room->id}] eliminada correctamente de la propiedad [{$property->id}].");
      return response()->json(['message_key' => 'room_deleted'], 200);
    } catch (AuthorizationException $e) {
      Log::error("No autorizado para eliminar la habitación [{$room->id}] en la propiedad [{$property->id}]: " . $e->getMessage());
      return response()->json(['error_key' => 'unauthorized_delete_room'], 403);
    } catch (Exception $e) {
      Log::error("Error al eliminar la habitación [{$room->id}] para la propiedad [{$property->id}]: " . $e->getMessage());
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
    Log::info("Cambiando el estado de la habitación [{$room->id}] para la propiedad [{$property->id}].");

    try {
      $this->authorize('updateRoom', $property);
      $this->ensureRoomBelongsToProperty($property, $room);

      $validatedStatus = $request->validate([
        'status' => 'required|in:available,occupied,unavailable',
      ]);

      $this->roomService->changeStatus($room, $validatedStatus['status']);

      Log::info("Estado de la habitación [{$room->id}] actualizado a: " . $validatedStatus['status']);

      return response()->json([
        'message_key' => 'room_status_updated',
        'room' => new RoomResource($room->refresh()),
      ], 200);
    } catch (AuthorizationException $e) {
      Log::error("No autorizado para cambiar el estado de la habitación [{$room->id}] en la propiedad [{$property->id}]: " . $e->getMessage());
      return response()->json(['error_key' => 'unauthorized_change_room_status'], 403);
    } catch (Exception $e) {
      Log::error("Error al cambiar el estado de la habitación [{$room->id}] para la propiedad [{$property->id}]: " . $e->getMessage());
      return response()->json(['error_key' => 'change_room_status_failed'], 500);
    }
  }
}
