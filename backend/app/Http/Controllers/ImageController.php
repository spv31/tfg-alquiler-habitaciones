<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

class ImageController extends Controller
{
  public function showPropertyImage(Property $property, $filename)
  {
    $path = storage_path("app/private/images/properties/{$property->id}/{$filename}");

    if (!file_exists($path)) {
      return response()->json(['error_key' => 'image_not_found'], 404);
    }

    $mime = mime_content_type($path);
    return response()->file($path, ['Content-Type' => $mime]);
  }

  public function showRoomImage(Property $property, $room, $filename)
  {
    $path = storage_path("app/private/images/properties/{$property->id}/rooms/{$room}/{$filename}");

    if (!file_exists($path)) {
      return response()->json(['error_key' => 'image_not_found'], 404);
    }

    $mime = mime_content_type($path);
    return response()->file($path, ['Content-Type' => $mime]);
  }

  public function showUserImage(User $user, $filename)
  {
    $path = storage_path("app/private/images/profile_pictures/{$user->id}/{$filename}");

    if (!file_exists($path)) {
      return response()->json(['error_key' => 'image_not_found'], 404);
    }

    $mime = mime_content_type($path);
    return response()->file($path, ['Content-Type' => $mime]);
  }
}
