<?php


use Illuminate\Support\Facades\Route;


// Route::middleware('auth:sanctum')->group(function () {
//     Route::resource('events', EventController::class);
//     Route::resource('tickets', TicketController::class);
//     Route::resource('bookings', BookingController::class);
// });
Route::get('/', function () {
  return response()->json([
    'status' => 'success',
    'message' => 'API is working!',
    'timestamp' => now(),
  ]);
});

require __DIR__ . '/v1/auth.php';
require __DIR__ . '/v1/event.php';
require __DIR__ . '/v1/ticket.php';
require __DIR__ . '/v1/booking.php';
require __DIR__ . '/v1/payment.php';


// Route::prefix('')->group(function () {
//     require __DIR__.'/v1/auth.php';
//     // require __DIR__.'/v1/events.php';
//     // require __DIR__.'/v1/bookings.php';
//     // require __DIR__.'/v1/tickets.php';
//     // require __DIR__.'/v1/profile.php';
//     // require __DIR__.'/v1/admin.php';
// });
