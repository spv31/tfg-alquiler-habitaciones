<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
  public function showPropertyImage(Property $property, $filename)
  {
    $path = storage_path("app/private/images/properties/{$filename}");

    if (!file_exists($path)) {
      Log::warning("Imagen no encontrada", [
        'property_id' => $property->id,
        'filename' => $filename,
        'path' => $path,
      ]);

      return response()->json(['error_key' => 'image_not_found'], 404);
    }

    $mime = mime_content_type($path);

    return response()->file($path, ['Content-Type' => $mime]);
  }

  public function showRoomImage(Property $property, Room $room, $filename)
  {
    $path = storage_path("app/private/images/rooms/{$filename}");

    if (!file_exists($path)) {
      return response()->json(['error_key' => 'image_not_found'], 404);
    }

    $mime = mime_content_type($path);
    return response()->file($path, ['Content-Type' => $mime]);
  }

  public function showUserImage(User $user, $filename)
  {
    $path = storage_path("app/private/images/profile_pictures/{$filename}");

    if (!file_exists($path)) {
      return response()->json(['error_key' => 'image_not_found'], 404);
    }

    $mime = mime_content_type($path);
    return response()->file($path, ['Content-Type' => $mime]);
  }
}
