<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyDetailController;
use App\Http\Controllers\RoomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    // Routes for properties (GET, POST, PUT, DELETE)
    Route::apiResource('properties', PropertyController::class);
    // Routes for creating or updating properties
    Route::get('/properties/{id}/details', [PropertyDetailController::class, 'show']);
    Route::put('/properties/{id}/details', [PropertyDetailController::class, 'updateOrCreate']);
    /**
     *  GET|HEAD        api/properties/{property}/rooms 
     *  POST            api/properties/{property}/rooms 
     *  GET|HEAD        api/properties/{property}/rooms/{room} 
     *  PUT|PATCH       api/properties/{property}/rooms/{room} 
     *  DELETE          api/properties/{property}/rooms/{room} 
     */
    Route::apiResource('properties.rooms', RoomController::class);
});


// Auth Routes for login, register and logout
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Routes por resetting password
Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);