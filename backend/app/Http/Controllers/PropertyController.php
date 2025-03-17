<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Services\PropertyServices;
use App\Services\UploadFilesService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PropertyController extends Controller
{
  use AuthorizesRequests;

  private $propertyServices;
  private $uploadFilesService;

  public function __construct(PropertyServices $propertyServices, UploadFilesService $uploadFilesService)
  {
    $this->propertyServices = $propertyServices;
    $this->uploadFilesService = $uploadFilesService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    try {
      Log::info('Inicio de búsqueda de propiedades para el usuario', ['user_id' => Auth::id()]);

      $properties = Property::where('user_id', Auth::id())->paginate(10);
      Log::info('Propiedades obtenidas exitosamente', ['total' => $properties->total()]);

      return PropertyResource::collection($properties);
    } catch (Exception $e) {
      Log::error('Error al obtener propiedades', ['error' => $e->getMessage()]);
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
      Log::info('Propiedad creada exitosamente', ['property_id' => $property->id]);

      if ($request->file('main_image')) {
        Log::info('Se recibió un archivo "main_image" en la petición');

        $file = $request->file('main_image');

        if (!$file->isValid()) {
          Log::error('El archivo no es válido.', ['error_code' => $file->getError()]);
          return response()->json([
            'error_key' => 'invalid_file_upload',
            'message' => 'El archivo no se ha subido correctamente.'
          ], 400);
        }

        Log::info('Detalles del archivo recibido', [
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

        $rutaGuardada = $this->uploadFilesService->storeFile($contenidoBase64, 'images/properties', $extension);

        Log::info('Imagen principal guardada correctamente', [
          'path' => $rutaGuardada
        ]);

        // Asociar la imagen a la propiedad
        $property->images()->create([
          'image_path' => $rutaGuardada,
        ]);

        Log::info('Imagen guardada en base de datos correctamente.');
      } else {
        Log::info('No se recibió ninguna imagen en la solicitud.');
      }

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
      Log::info('Intentando obtener propiedad', ['property_id' => $id]);

      $property = Property::with('details')->findOrFail($id);
      Log::info('Propiedad encontrada', ['property' => $property]);

      $this->authorize('view', $property);
      Log::info('Autorización concedida para ver propiedad');

      return new PropertyResource($property);
    } catch (ModelNotFoundException $e) {
      Log::warning('Propiedad no encontrada', ['property_id' => $id]);
      return response()->json([
        'error_key' => 'property_not_found'
      ], 404);
    } catch (AuthorizationException $e) {
      Log::warning('Acceso no autorizado a la propiedad', ['property_id' => $id]);
      return response()->json([
        'error_key' => 'unauthorized_property_access'
      ], 403);
    } catch (Exception $e) {
      Log::error('Error inesperado al obtener la propiedad', ['error' => $e->getMessage()]);
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
      Log::info('Intentando actualizar propiedad', ['property_id' => $id]);

      $property = Property::findOrFail($id);
      Log::info('Propiedad encontrada', ['property' => $property]);

      $this->authorize('update', $property);
      Log::info('Autorización concedida para actualizar propiedad');

      $validatedData = $request->validated();
      Log::info('Datos validados para actualizar propiedad', ['data' => $validatedData]);


      $updatedProperty = $this->propertyServices->updateProperty($property, $validatedData);
      Log::info('Datos recibidos en request()->all()', ['request_all' => $request->all()]);
      Log::info('Archivos recibidos en $_FILES', ['files' => $_FILES]);
      Log::info('request->hasFile("main_image")', ['result' => $request->hasFile('main_image')]);
      Log::info('request->file("main_image")', ['file' => $request->file('main_image')]);

      if ($request->file('main_image')) {
        Log::info('Se recibió un archivo "main_image" en la petición');

        $file = $request->file('main_image');

        if (!$file->isValid()) {
          Log::error('El archivo no es válido.', ['error_code' => $file->getError()]);
          return response()->json([
            'error_key' => 'invalid_file_upload',
            'message' => 'El archivo no se ha subido correctamente.'
          ], 400);
        }

        Log::info('Detalles del archivo recibido', [
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

        $rutaGuardada = $this->uploadFilesService->storeFile($contenidoBase64, 'images/properties', $extension);

        Log::info('Imagen principal guardada correctamente', [
          'path' => $rutaGuardada
        ]);

        if ($property->images()->exists()) {
          Log::info('Propiedad ya tenía imágenes previas. Procediendo a eliminarlas.');

          $images = $property->images()->get();

          $images->each(function ($image) {
            try {
              $this->uploadFilesService->deleteFile($image->image_path);
              Log::info('Archivo eliminado', ['path' => $image->image_path]);
            } catch (Exception $e) {
              Log::error('Error eliminando archivo físico', [
                'path' => $image->image_path,
                'error' => $e->getMessage()
              ]);
            }
          });

          $property->images()->delete();

          Log::info('Imagen(es) anteriores eliminadas correctamente');
        } else {
          Log::info('No había imágenes previas asociadas a la propiedad.');
        }

        $property->images()->create([
          'image_path' => $rutaGuardada,
        ]);

        Log::info('Imagen guardada en base de datos correctamente.');
      } else {
        Log::info('No se recibió ninguna imagen en la solicitud.');
      }


      Log::info('Propiedad actualizada con éxito', ['property_id' => $updatedProperty->id]);

      return new PropertyResource($updatedProperty);
    } catch (ModelNotFoundException $e) {
      Log::warning('Propiedad no encontrada al intentar actualizar', ['property_id' => $id]);
      return response()->json([
        'error_key' => 'property_not_found'
      ], 404);
    } catch (ValidationException $e) {
      return response()->json([
        'error_key' => $e->errors()['error_key'][0],
        'message' => $e->errors()['message'][0],
      ], 400);
    } catch (AuthorizationException $e) {
      Log::warning('Acceso no autorizado para actualizar propiedad', ['property_id' => $id]);
      return response()->json([
        'error_key' => 'unauthorized_property_update'
      ], 403);
    } catch (Exception $e) {
      Log::error('Error inesperado al actualizar la propiedad', ['error' => $e->getMessage()]);
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
      Log::info('Intentando eliminar propiedad', ['property_id' => $id]);

      $property = Property::findOrFail($id);
      Log::info('Propiedad encontrada', ['property' => $property]);

      // Autorizar que solo el propietario pueda eliminar la propiedad
      $this->authorize('delete', $property);
      Log::info('Autorización concedida para eliminar propiedad');

      $property->delete();
      Log::info('Propiedad eliminada con éxito', ['property_id' => $id]);

      return response()->json([
        'message_key' => 'property_deleted'
      ], 200);
    } catch (ModelNotFoundException $e) {
      Log::warning('Propiedad no encontrada al intentar eliminar', ['property_id' => $id]);
      return response()->json([
        'error_key' => 'property_not_found'
      ], 404);
    } catch (AuthorizationException $e) {
      Log::warning('Acceso no autorizado para eliminar propiedad', ['property_id' => $id]);
      return response()->json([
        'error_key' => 'unauthorized_property_delete'
      ], 403);
    } catch (Exception $e) {
      Log::error('Error inesperado al eliminar la propiedad', ['error' => $e->getMessage()]);
      return response()->json([
        'error_key' => 'delete_property_failed'
      ], 500);
    }
  }

  public function changeStatus(Request $request, Property $property)
  {
    try {
      Log::info('Intentando cambiar estado de propiedad', [
        'property_id' => $property->id,
        'current_status' => $property->status
      ]);

      $this->authorize('update', $property);
      Log::info('Autorización concedida para cambiar estado de la propiedad');

      $validatedStatus = $request->validate([
        'status' => 'required|in:available,unavailable,occupied,partially_occupied'
      ]);
      Log::info('Estado validado correctamente', ['new_status' => $validatedStatus['status']]);

      $this->propertyServices->changeStatus($property, $validatedStatus['status']);
      Log::info('Estado de la propiedad actualizado con éxito');

      return response()->json([
        'message_key' => 'property_status_updated',
        'property' => new PropertyResource($property->fresh()),
      ], 200);
    } catch (AuthorizationException $e) {
      Log::warning('Acceso no autorizado para cambiar estado de propiedad', ['property_id' => $property->id]);
      return response()->json([
        'error_key' => 'unauthorized_property_status_change'
      ], 403);
    } catch (Exception $e) {
      Log::error('Error inesperado al cambiar estado de la propiedad', ['error' => $e->getMessage()]);
      return response()->json([
        'error_key' => 'property_status_update_failed'
      ], 500);
    }
  }
}
