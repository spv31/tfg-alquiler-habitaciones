<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyDetailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Auth Routes for login, register and logout
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Routes for properties (GET, POST, PUT, DELETE)
Route::apiResource('properties', PropertyController::class);

Route::get('/properties/{id}/details', [PropertyDetailController::class, 'show']);
Route::put('/properties/{id}/details', [PropertyDetailController::class, 'updateOrCreate']);