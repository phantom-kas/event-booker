<?php

use App\Http\Controllers\BookingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
Route::post('/tickets/{id}/bookings', [BookingsController::class, 'book']);
Route::get('/bookings', [BookingsController::class, 'index']);
Route::put('/bookings/{id}/cancel', [BookingsController::class, 'cancel']);

})
?>