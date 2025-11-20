<?php

use App\Http\Controllers\TicketsController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum', 'role:organizer'])->group(function () {
  Route::post('/events/{event_id}/tickets', [TicketsController::class, 'store']);
  Route::put('/tickets/{id}', [TicketsController::class, 'update']);

  Route::delete('/tickets/{id}', [TicketsController::class, 'destroy']);
});

// Protected routes
