<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyDetailController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TenantAssignmentController;
use App\Http\Middleware\ExpireInvitationsMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
  return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
  // Properties
  Route::apiResource('properties', PropertyController::class);
  Route::post('/properties/{id}/update', [PropertyController::class, 'update']);
  Route::apiResource('properties.rooms', RoomController::class);
  Route::post('/properties/{property}/rooms/{room}/update', [RoomController::class, 'update']);

  // See tenants
  Route::get('/properties/{property}/tenants', [PropertyController::class, 'listPropertyTenants']);
  Route::get('/properties/{property}/rooms/{room}/tenants', [RoomController::class, 'listRoomTenants']);
  // Change status
  Route::patch('/properties/{property}/status', [PropertyController::class, 'changeStatus']);
  Route::patch('/properties/{property}/rooms/{room}/status', [RoomController::class, 'changeStatus'])->name('properties.rooms.changeStatus');
  // Routes for creating or updating properties
  Route::get('/properties/{id}/details', [PropertyDetailController::class, 'show']);
  Route::put('/properties/{id}/details', [PropertyDetailController::class, 'updateOrCreate']);
  // Invitations
  Route::middleware(ExpireInvitationsMiddleware::class)->group(function () {
    Route::apiResource('invitations', InvitationController::class);
    // Regenerate Invitation
    Route::post('/invitations/{invitation}/regenerate', [InvitationController::class, 'regenerate']);
  });
  // Reassignment of tenants/properties/rooms 
  Route::post('tenant-assignments/reassign', [TenantAssignmentController::class, 'reassign']);

  // Release tenats
  Route::delete('tenant-assignments/remove', [TenantAssignmentController::class, 'removeAssignment']);

  // Tenant View
  Route::get('/assigned-rentable', [TenantAssignmentController::class, 'getAssignedRentable']);
  
  // Property images
  Route::get('/properties/{property}/images/{filename}', [ImageController::class, 'showPropertyImage'])
    ->name('image.property.show');

  // Room images
  Route::get('/properties/{property}/rooms/{room}/images/{filename}', [ImageController::class, 'showRoomImage'])
    ->name('image.room.show');

  // User images
  Route::get('/users/{user}/avatar/{filename}', [ImageController::class, 'showUserImage'])
    ->name('image.user.show');
});

// Auth Routes for login, register and logout
Route::post('/register/owner', [AuthController::class, 'registerOwner']);
Route::post('/register/tenant', [AuthController::class, 'registerTenantFromInvitation']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Routes por resetting password
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
