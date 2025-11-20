<?php

use App\Http\Controllers\EventsController;
use Illuminate\Support\Facades\Route;

Route::get('/events', [EventsController::class, 'index']);
Route::get('/events/{event}', [EventsController::class, 'show']);



Route::middleware(['auth:sanctum', 'role:organizer'])->group(function () {
  Route::post('/events', [EventsController::class, 'store']);
  Route::delete('/events/{id}', [EventsController::class, 'destroy']);
  Route::put('/events/{id}', [EventsController::class, 'update']);
});

// Protected routes
